<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $suppliers = Supplier::withCount(['purchaseOrders', 'items'])
            ->withSum(['purchaseOrders as outstanding_total' => fn ($q) => $q->outstanding()], 'total')
            ->when($filters['status'] ?? 'active', fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->orderBy('name')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        return view('procurement.suppliers.index', compact('suppliers', 'filters'));
    }

    public function show(Supplier $supplier): View
    {
        $orders = $supplier->purchaseOrders()->with('items')->latest('id')->take(20)->get();
        $prices = $supplier->prices()->with('inventoryItem:id,name,unit_of_measure')->whereNull('effective_to')->get()->sortBy(fn ($p) => $p->inventoryItem?->name);
        $lowStock = $supplier->items()->active()->lowStock()->orderBy('name')->get();
        $account = ['received' => $supplier->receivedValue(), 'paid' => $supplier->paidValue()];
        $account['balance'] = round($account['received'] - $account['paid'], 2);
        $payments = $supplier->expenses()->paid()->with('purchaseOrder:id,po_number')->latest('spent_on')->take(10)->get();

        return view('procurement.suppliers.show', compact('supplier', 'orders', 'prices', 'lowStock', 'account', 'payments'));
    }

    public function store(Request $request): JsonResponse
    {
        $supplier = Supplier::create($this->validated($request));

        return response()->json(['success' => true, 'message' => "{$supplier->name} added.", 'url' => route('suppliers.show', $supplier)], 201);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($this->validated($request, $supplier));

        return response()->json(['success' => true, 'message' => "{$supplier->name} updated."]);
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplier?->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'regex:/^[A-Z]\d{9}[A-Z]$/i'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'kra_pin.regex' => 'A KRA PIN looks like P051234567X.',
        ]);

        if (! empty($data['kra_pin'])) {
            $data['kra_pin'] = Str::upper($data['kra_pin']);
        }

        return $data;
    }
}
