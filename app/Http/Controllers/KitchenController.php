<?php

namespace App\Http\Controllers;

use App\Enums\KitchenOrderStatus;
use App\Enums\StayStatus;
use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\KitchenOrder;
use App\Models\MenuCategory;
use App\Models\MenuItemVariant;
use App\Models\Payment;
use App\Models\Stay;
use App\Services\BillingService;
use App\Services\CoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Restaurant and room-service orders: the live kitchen board, order history,
 * taking orders and moving them through the kitchen.
 */
class KitchenController extends Controller
{
    public function __construct(private BillingService $billing, private CoreService $core)
    {
    }

    /**
     * Kitchen board: orders not yet served, oldest first.
     */
    public function index(): View
    {
        $orders = KitchenOrder::with($this->orderRelations())
            ->active()
            ->oldest()
            ->get()
            ->groupBy(fn (KitchenOrder $order) => $order->status->value);

        $stats = $this->todayStats();

        return view('kitchen.index', array_merge(compact('orders', 'stats'), $this->formOptions()));
    }

    /**
     * All orders, newest first.
     */
    public function orders(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(KitchenOrderStatus::class)],
            'type' => ['nullable', Rule::in(array_keys(KitchenOrder::TYPES))],
            'date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = KitchenOrder::with($this->orderRelations())
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->latest()
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $stats = $this->todayStats();

        return view('kitchen.orders', array_merge(compact('orders', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(KitchenOrder $order): View
    {
        $order->load(array_merge($this->orderRelations(), ['invoices.items', 'invoices.payments']));

        return view('kitchen.show', compact('order'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(KitchenOrder::TYPES))],
            'stay_id' => ['nullable', 'required_if:type,room_service', 'required_if_accepted:charge_to_room', 'integer', 'exists:stays,id'],
            'charge_to_room' => ['boolean'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'table_number' => ['nullable', 'string', 'max:20'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.menu_item_variant_id' => ['required', 'integer', 'exists:menu_item_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'payment' => ['nullable', 'array'],
            'payment.method' => ['required_with:payment', Rule::in(array_keys(Payment::METHODS))],
            'payment.payment_reference' => ['nullable', 'string', 'max:50', 'required_if:payment.method,mobile_money,bank_transfer,cheque'],
        ], [
            'stay_id.required_if' => 'Choose the in-house guest for room service.',
            'stay_id.required_if_accepted' => 'Choose the in-house guest to charge.',
            'items.required' => 'Add at least one item.',
            'payment.payment_reference.required_if' => 'Enter the transaction code (e.g. the M-Pesa code).',
        ]);

        $data['charge_to_room'] = (bool) ($data['charge_to_room'] ?? false);
        $stay = isset($data['stay_id']) ? Stay::find($data['stay_id']) : null;

        if ($stay && $stay->state !== StayStatus::CheckedIn) {
            throw ValidationException::withMessages(['stay_id' => "Booking #{$stay->id} is not checked in."]);
        }
        if ($data['charge_to_room'] && ! empty($data['payment'])) {
            throw ValidationException::withMessages(['payment' => 'An order charged to the room is paid at check-out.']);
        }

        $variants = MenuItemVariant::with('menuItem')
            ->whereIn('id', collect($data['items'])->pluck('menu_item_variant_id'))
            ->get()
            ->keyBy('id');
        foreach ($data['items'] as $i => $line) {
            $variant = $variants[$line['menu_item_variant_id']];
            if (! $variant->is_active || ! $variant->menuItem?->is_active) {
                throw ValidationException::withMessages(["items.{$i}.menu_item_variant_id" => "{$variant->full_name} is not on the menu."]);
            }
        }

        try {
            [$order, $payment] = DB::transaction(function () use ($data, $variants, $stay, $request) {
                $order = KitchenOrder::create([
                    'stay_id' => $stay?->id,
                    'charge_to_room' => $data['charge_to_room'],
                    'order_number' => 'TMP-' . Str::uuid(),
                    'status' => KitchenOrderStatus::Pending,
                    'type' => $data['type'],
                    'customer_name' => $data['customer_name'] ?? null,
                    'table_number' => $data['type'] === 'dine_in' ? ($data['table_number'] ?? null) : null,
                    'special_instructions' => $data['special_instructions'] ?? null,
                    'placed_by' => $request->user()->id,
                ]);
                $order->update(['order_number' => sprintf('KO-%05d', $order->id)]);

                foreach ($data['items'] as $line) {
                    $order->items()->create([
                        'menu_item_variant_id' => $line['menu_item_variant_id'],
                        'quantity' => $line['quantity'],
                        'price_at_order' => $variants[$line['menu_item_variant_id']]->price,
                        'notes' => $line['notes'] ?? null,
                    ]);
                }

                if ($order->charge_to_room) {
                    $this->billing->syncStayInvoice($stay);

                    return [$order, null];
                }

                $invoice = $this->billing->syncKitchenOrderInvoice($order);
                $payment = empty($data['payment']) ? null : $this->billing->recordPayment($invoice, [
                    'amount' => $invoice->total,
                    'method' => $data['payment']['method'],
                    'payment_reference' => $data['payment']['payment_reference'] ?? null,
                ], $request->user());

                return [$order, $payment];
            });
        } catch (BillingException $e) {
            throw ValidationException::withMessages(['payment' => $e->getMessage()]);
        }

        $this->core->log($request->user()->id, 'kitchen_order_created', "Order {$order->order_number} placed", ['kitchen_order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'message' => "Order {$order->order_number} sent to the kitchen.",
            'url' => route('kitchen.orders.show', $order),
            'receipt_url' => $payment ? route('kitchen.orders.receipt', [$order, $payment]) : null,
        ], 201);
    }

    /**
     * Move an order along the board, or cancel it.
     */
    public function updateStatus(Request $request, KitchenOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(KitchenOrderStatus::class)],
            'reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:255'],
        ], [
            'reason.required_if' => 'Give a reason for cancelling.',
        ]);
        $status = KitchenOrderStatus::from($data['status']);

        if (! $order->status->canTransitionTo($status)) {
            return back()->with('error', "Order {$order->order_number} is {$order->status->label()} and can't be marked {$status->label()}.");
        }

        DB::transaction(function () use ($order, $status, $data) {
            $order->update(array_filter([
                'status' => $status,
                'preparation_started_at' => $status === KitchenOrderStatus::Preparing ? now() : null,
                'ready_at' => $status === KitchenOrderStatus::Ready ? now() : null,
                'delivered_at' => $status === KitchenOrderStatus::Delivered ? now() : null,
                'cancel_reason' => $status === KitchenOrderStatus::Cancelled ? $data['reason'] : null,
            ]));

            if ($status === KitchenOrderStatus::Cancelled) {
                $order->charge_to_room && $order->stay
                    ? $this->billing->syncStayInvoice($order->stay)
                    : $this->billing->syncKitchenOrderInvoice($order);
            }
        });

        $this->core->log($request->user()->id, 'kitchen_order_status', "Order {$order->order_number} marked {$status->label()}", ['kitchen_order_id' => $order->id]);

        return back()->with('success', "Order {$order->order_number}: {$status->label()}.");
    }

    private function orderRelations(): array
    {
        return [
            'items.variant.menuItem',
            'stay.guest',
            'stay.roomAllocations.room',
            'placer',
            'invoices' => fn ($q) => $q->where('status', '!=', 'cancelled'),
        ];
    }

    private function todayStats(): array
    {
        $today = KitchenOrder::with('items')->whereDate('created_at', today())->get();
        $served = $today->where('status', '!=', KitchenOrderStatus::Cancelled);

        return [
            'active' => KitchenOrder::active()->count(),
            'orders_today' => $served->count(),
            'sales_today' => round($served->sum(fn (KitchenOrder $o) => $o->total), 2),
            'room_charges_today' => round($served->where('charge_to_room', true)->sum(fn (KitchenOrder $o) => $o->total), 2),
        ];
    }

    /**
     * Menu and in-house guests for the order modal.
     */
    private function formOptions(): array
    {
        $menu = MenuCategory::active()
            ->ordered()
            ->with(['activeItems' => fn ($q) => $q->orderBy('name')->with(['variants' => fn ($v) => $v->active()->orderBy('price')])])
            ->get()
            ->map(fn (MenuCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'items' => $category->activeItems
                    ->filter(fn ($item) => $item->variants->isNotEmpty())
                    ->map(fn ($item) => [
                        'name' => $item->name,
                        'variants' => $item->variants->map(fn (MenuItemVariant $v) => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'full_name' => $v->setRelation('menuItem', $item)->full_name,
                            'price' => (float) $v->price,
                        ])->values(),
                    ])->values(),
            ])
            ->filter(fn ($category) => $category['items']->isNotEmpty())
            ->values();

        $inHouse = Stay::with(['guest:id,name', 'roomAllocations.room:id,room_number'])
            ->where('status', StayStatus::CheckedIn->value)
            ->get()
            ->map(fn (Stay $stay) => [
                'id' => $stay->id,
                'label' => 'Room ' . ($stay->roomAllocations->map(fn ($a) => $a->room?->room_number)->filter()->implode(', ') ?: '?') . ' · ' . ($stay->guest->name ?? 'Guest'),
            ])
            ->sortBy('label')
            ->values();

        return ['menu' => $menu, 'inHouse' => $inHouse, 'methods' => Payment::METHODS];
    }
}
