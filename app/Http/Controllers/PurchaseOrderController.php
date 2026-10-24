<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Helpers\SystemHelper;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPrice;
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
 * Purchase orders: draft → submitted → approved (manager) → ordered → received.
 * Deliveries go into stock at the price paid.
 */
class PurchaseOrderController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(PurchaseOrderStatus::class)],
            'supplier_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = PurchaseOrder::with(['supplier:id,name', 'requester:id,name', 'items'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->withStatus($s))
            ->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->orderByRaw("status = 'submitted' desc")
            ->latest('id')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $stats = [
            'awaiting' => PurchaseOrder::withStatus(PurchaseOrderStatus::Submitted)->count(),
            'outstanding' => PurchaseOrder::outstanding()->count(),
            'outstanding_value' => round((float) PurchaseOrder::outstanding()->sum('total'), 2),
            'received_month' => round((float) DB::table('inventory_transactions')
                ->where('reference_type', 'purchase_order')
                ->where('created_at', '>=', today()->startOfMonth())
                ->sum('total_value'), 2),
        ];

        return view('procurement.orders.index', array_merge(compact('orders', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $order = $purchaseOrder->load(['supplier', 'requester', 'approver', 'items.inventoryItem', 'receipts.creator', 'receipts.item']);

        return view('procurement.orders.show', array_merge(compact('order'), $this->formOptions()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data, $request) {
            $order = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => 'TMP-' . Str::uuid(),
                'requested_by' => $request->user()->id,
                'status' => PurchaseOrderStatus::Draft,
                'delivery_date' => $data['delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $order->update(['po_number' => sprintf('PO-%s-%05d', now()->format('Y'), $order->id)]);
            $order->items()->createMany($data['items']);
            $order->recalculateTotal();

            return $order;
        });

        $this->core->log($request->user()->id, 'purchase_order_created', "Purchase order {$order->po_number} drafted", ['purchase_order_id' => $order->id]);

        return response()->json(['success' => true, 'message' => "{$order->po_number} saved as a draft.", 'url' => route('purchase-orders.show', $order)], 201);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $purchaseOrder->status->isEditable()) {
            return response()->json(['success' => false, 'message' => 'Only draft orders can be changed. Send it back to draft first.'], 422);
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($data['items']);
            $purchaseOrder->recalculateTotal();
        });

        return response()->json(['success' => true, 'message' => "{$purchaseOrder->po_number} updated."]);
    }

    /**
     * Draft orders for one supplier's low-stock items, topping each up to
     * twice its reorder level.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $supplier = Supplier::findOrFail($request->validate(['supplier_id' => ['required', 'exists:suppliers,id']])['supplier_id']);
        $items = $supplier->items()->active()->lowStock()->get();

        if ($items->isEmpty()) {
            return back()->with('error', "Nothing from {$supplier->name} is running low.");
        }

        $prices = $supplier->latestPrices();
        $lines = $items->map(fn (InventoryItem $item) => [
            'inventory_item_id' => $item->id,
            'quantity' => max(round((float) $item->reorder_level * 2 - (float) $item->quantity, 2), 1),
            'unit_price' => $prices[$item->id] ?? (float) $item->unit_cost,
        ])->all();

        $request->merge(['supplier_id' => $supplier->id, 'items' => $lines, 'notes' => 'Reorder of low stock']);
        $response = $this->store($request);

        return redirect($response->getData()->url)->with('success', 'Draft order created from low stock. Check the quantities, then submit it.');
    }

    /**
     * Submit, send back, approve, mark ordered or cancel.
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'status' => ['required', Rule::in(['submitted', 'draft', 'approved', 'ordered', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn () => in_array($request->input('status'), ['draft', 'cancelled'], true))],
        ], ['note.required' => 'Say why.']);
        $to = PurchaseOrderStatus::from($data['status']);
        $from = $purchaseOrder->status;

        $allowed = match ($to) {
            PurchaseOrderStatus::Submitted => $from === PurchaseOrderStatus::Draft,
            PurchaseOrderStatus::Draft => $from === PurchaseOrderStatus::Submitted,
            PurchaseOrderStatus::Approved => in_array($from, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Submitted], true),
            PurchaseOrderStatus::Ordered => $from === PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Cancelled => $from->canCancel(),
            default => false,
        };
        if (! $allowed) {
            return back()->with('error', "A {$from->label()} order can't be moved to {$to->label()}.");
        }
        if (in_array($to, [PurchaseOrderStatus::Approved, PurchaseOrderStatus::Draft], true) && ! $user->hasAnyRole(['admin', 'manager'])) {
            return back()->with('error', 'Only a manager can approve or send back an order.');
        }
        if ($purchaseOrder->items()->doesntExist()) {
            return back()->with('error', 'The order has no items.');
        }

        $note = ! empty($data['note']) ? ucfirst($to === PurchaseOrderStatus::Draft ? 'sent back' : $to->value) . ' by ' . $user->name . ': ' . $data['note'] : null;

        $purchaseOrder->update(array_filter([
            'status' => $to,
            'approved_by' => $to === PurchaseOrderStatus::Approved ? $user->id : null,
            'approved_at' => $to === PurchaseOrderStatus::Approved ? now() : null,
            'ordered_at' => $to === PurchaseOrderStatus::Ordered ? now() : null,
            'notes' => $note ? trim(($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '') . $note) : null,
        ]));

        $this->core->log($user->id, 'purchase_order_' . $to->value, "{$purchaseOrder->po_number}: {$to->label()}", ['purchase_order_id' => $purchaseOrder->id]);

        return back()->with('success', "{$purchaseOrder->po_number} is now {$to->label()}.");
    }

    /**
     * Book in a delivery: each line's quantity received and price paid.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! $purchaseOrder->status->canReceive()) {
            return back()->with('error', "A {$purchaseOrder->status->label()} order can't be received.");
        }

        $data = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'supplier_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $received = 0;

        DB::transaction(function () use ($purchaseOrder, $data, $user, &$received) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $lines = $order->items()->with('inventoryItem')->get()->keyBy('id');

            foreach ($data['lines'] as $lineId => $line) {
                $quantity = round((float) ($line['quantity'] ?? 0), 2);
                if ($quantity <= 0 || ! $lines->has($lineId)) {
                    continue;
                }

                $poLine = $lines[$lineId];
                if ($quantity > $poLine->outstanding) {
                    throw ValidationException::withMessages(["lines.{$lineId}.quantity" => "Only {$poLine->inventoryItem->qty($poLine->outstanding)} of {$poLine->inventoryItem->name} is still to come."]);
                }
                $price = isset($line['unit_price']) ? (float) $line['unit_price'] : (float) $poLine->unit_price;

                $poLine->inventoryItem->move('in', $quantity, $user, [
                    'unit_cost' => $price,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $order->id,
                    'notes' => "{$order->po_number} from {$order->supplier->name}" . (! empty($data['supplier_reference']) ? " ({$data['supplier_reference']})" : ''),
                ]);
                SupplierPrice::record($order->supplier_id, $poLine->inventory_item_id, $price);

                $poLine->update(['received_quantity' => (float) $poLine->received_quantity + $quantity]);
                $received++;
            }

            if (! $received) {
                throw ValidationException::withMessages(['lines' => 'Enter how much arrived.']);
            }

            $complete = $order->items()->get()->every(fn ($i) => $i->outstanding <= 0);
            $order->update(array_filter([
                'status' => $complete ? PurchaseOrderStatus::Received : PurchaseOrderStatus::PartiallyReceived,
                'received_at' => $complete ? now() : null,
                'supplier_reference' => $data['supplier_reference'] ?? null,
            ]));
        });

        $this->core->log($user->id, 'purchase_order_received', "{$purchaseOrder->po_number}: {$received} lines received", ['purchase_order_id' => $purchaseOrder->id]);

        return back()->with('success', 'Delivery booked into stock.');
    }

    private function formOptions(): array
    {
        $user = auth()->user();

        return [
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'stockItems' => InventoryItem::active()->orderBy('name')->get(['id', 'name', 'unit_of_measure', 'unit_cost', 'quantity', 'reorder_level', 'primary_supplier_id']),
            'supplierPrices' => DB::table('supplier_prices')->whereNull('effective_to')->get(['supplier_id', 'inventory_item_id', 'unit_price'])
                ->groupBy('supplier_id')->map(fn ($rows) => $rows->pluck('unit_price', 'inventory_item_id')),
            'isManager' => $user->hasAnyRole(['admin', 'manager']),
            'canBuy' => $user->hasAnyRole(['admin', 'manager', 'procurement', 'kitchen']),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('status', 'active')],
            'delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.inventory_item_id' => ['required', 'distinct', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ], [
            'items.required' => 'Add at least one item.',
            'items.*.inventory_item_id.distinct' => 'Each item can only be on the order once.',
        ]);

        $data['items'] = array_map(fn ($i) => [
            'inventory_item_id' => $i['inventory_item_id'],
            'quantity' => $i['quantity'],
            'unit_price' => $i['unit_price'],
        ], $data['items']);

        return $data;
    }
}
