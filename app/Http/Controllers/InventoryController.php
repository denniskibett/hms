<?php

namespace App\Http\Controllers;

use App\Exceptions\StockException;
use App\Helpers\SystemHelper;
use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
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
 * The store: stock items, receiving, issuing, write-offs and stock counts.
 */
class InventoryController extends Controller
{
    /**
     * Who may do which stock movement. Everyone with access may issue and write off.
     */
    private const MOVE_ROLES = [
        'in' => ['admin', 'manager', 'procurement', 'kitchen'],
        'out' => ['admin', 'manager', 'procurement', 'kitchen', 'housekeeping'],
        'damage' => ['admin', 'manager', 'procurement', 'kitchen', 'housekeeping'],
        'expired' => ['admin', 'manager', 'procurement', 'kitchen', 'housekeeping'],
        'count' => ['admin', 'manager', 'procurement'],
    ];

    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'item_type' => ['nullable', Rule::in(array_keys(InventoryItem::TYPES))],
            'stock' => ['nullable', Rule::in(['low', 'out'])],
            'search' => ['nullable', 'string', 'max:100'],
            'inactive' => ['nullable', 'boolean'],
        ]);

        $items = InventoryItem::query()
            ->when(! ($filters['inactive'] ?? false), fn ($q) => $q->active())
            ->when($filters['item_type'] ?? null, fn ($q, $t) => $q->where('item_type', $t))
            ->when(($filters['stock'] ?? null) === 'low', fn ($q) => $q->lowStock())
            ->when(($filters['stock'] ?? null) === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->orderBy('name')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $active = InventoryItem::active();
        $stats = [
            'items' => (clone $active)->count(),
            'low' => (clone $active)->lowStock()->count(),
            'out' => (clone $active)->where('quantity', '<=', 0)->count(),
            'value' => round((float) (clone $active)->sum(DB::raw('quantity * unit_cost')), 2),
        ];

        return view('inventory.index', array_merge(compact('items', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(InventoryItem $inventory): View
    {
        $item = $inventory;
        $movements = $item->transactions()->with(['creator:id,name', 'department:id,name'])->latest('id')->paginate(25);
        $usedLast30 = (float) -$item->transactions()->whereIn('type', ['out', 'damage', 'expired'])->where('created_at', '>=', now()->subDays(30))->sum('quantity');

        return view('inventory.show', array_merge(compact('item', 'movements', 'usedLast30'), $this->formOptions()));
    }

    /**
     * Every stock movement, newest first.
     */
    public function movements(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(InventoryTransaction::TYPES))],
            'department_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $movements = InventoryTransaction::with(['item:id,name,unit_of_measure', 'creator:id,name', 'department:id,name'])
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($filters['department_id'] ?? null, fn ($q, $id) => $q->where('department_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('id')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        return view('inventory.movements', array_merge(compact('movements', 'filters'), $this->formOptions()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $opening = $request->validate([
            'opening_quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'opening_unit_cost' => ['nullable', 'required_with:opening_quantity', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $item = DB::transaction(function () use ($data, $opening, $request) {
            $data['sku'] ??= $this->nextSku($data['item_type']);
            $item = InventoryItem::create($data);

            if ((float) ($opening['opening_quantity'] ?? 0) > 0) {
                $item->move('in', (float) $opening['opening_quantity'], $request->user(), [
                    'unit_cost' => (float) $opening['opening_unit_cost'],
                    'notes' => 'Opening stock',
                ]);
            }

            return $item;
        });

        $this->core->log($request->user()->id, 'inventory_item_created', "Stock item {$item->name} ({$item->sku}) added", ['inventory_item_id' => $item->id]);

        return response()->json(['success' => true, 'message' => "{$item->name} added.", 'url' => route('inventory.show', $item)], 201);
    }

    public function update(Request $request, InventoryItem $inventory): JsonResponse
    {
        $data = $this->validated($request, $inventory);
        $data['sku'] ??= $inventory->sku;
        $inventory->update($data);

        return response()->json(['success' => true, 'message' => "{$inventory->name} updated."]);
    }

    /**
     * Receive, issue, write off or count stock.
     */
    public function move(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(array_keys(self::MOVE_ROLES))],
            'quantity' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'unit_cost' => ['nullable', 'required_if:action,in', 'numeric', 'min:0', 'max:9999999'],
            'department_id' => ['nullable', 'required_if:action,out', 'exists:departments,id'],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:action,damage,expired,count'],
        ], [
            'department_id.required_if' => 'Say which department the stock is going to.',
            'unit_cost.required_if' => 'Enter the cost per unit.',
            'notes.required_if' => 'Add a note explaining this.',
        ]);

        abort_unless($request->user()->hasAnyRole(self::MOVE_ROLES[$data['action']]), 403);

        $quantity = (float) $data['quantity'];
        [$type, $change] = match ($data['action']) {
            'in' => ['in', $quantity],
            'out', 'damage', 'expired' => [$data['action'], -$quantity],
            'count' => ['adjustment', $quantity - (float) $inventory->quantity],
        };

        if ($data['action'] === 'count' && round($change, 2) == 0) {
            return back()->with('success', "Count matches: {$inventory->qty()} of {$inventory->name}.");
        }

        try {
            $inventory->move($type, $change, $request->user(), [
                'unit_cost' => $data['action'] === 'in' ? (float) $data['unit_cost'] : null,
                'department_id' => $data['department_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (StockException $e) {
            throw ValidationException::withMessages(['quantity' => $e->getMessage()]);
        }

        $this->core->log($request->user()->id, 'stock_' . $data['action'], "{$inventory->name}: " . ($change > 0 ? '+' : '') . round($change, 2) . " {$inventory->unit_of_measure}", ['inventory_item_id' => $inventory->id]);

        return back()->with('success', InventoryTransaction::TYPES[$type] . ": {$inventory->name}, now {$inventory->fresh()->qty()} in stock.");
    }

    private function formOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'canManage' => auth()->user()->hasAnyRole(['admin', 'manager', 'procurement']),
            'canReceive' => auth()->user()->hasAnyRole(self::MOVE_ROLES['in']),
        ];
    }

    private function nextSku(string $type): string
    {
        $prefix = Str::upper(Str::substr($type, 0, 3));
        $last = InventoryItem::where('sku', 'like', "{$prefix}-%")->orderByDesc('id')->value('sku');
        $next = $last ? (int) Str::afterLast($last, '-') + 1 : 1;

        do {
            $sku = sprintf('%s-%04d', $prefix, $next++);
        } while (InventoryItem::where('sku', $sku)->exists());

        return $sku;
    }

    private function validated(Request $request, ?InventoryItem $item = null): array
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'manager', 'procurement']), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_items', 'name')->ignore($item?->id)],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('inventory_items', 'sku')->ignore($item?->id)],
            'barcode' => ['nullable', 'string', 'max:50'],
            'item_type' => ['required', Rule::in(array_keys(InventoryItem::TYPES))],
            'unit_of_measure' => ['required', 'string', 'max:20'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['boolean'],
        ]);

        $data['status'] ??= true;
        $data['category'] = in_array($data['item_type'], ['linen', 'maintenance', 'office'], true) ? 'non_consumable' : 'consumable';

        return $data;
    }
}
