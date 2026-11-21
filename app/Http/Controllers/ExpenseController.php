<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Money paid out: running costs and payments to suppliers.
 */
class ExpenseController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'category' => ['nullable', Rule::in(array_keys(Expense::CATEGORIES))],
            'supplier_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'void' => ['nullable', 'boolean'],
        ]);
        $from = Carbon::parse($filters['from'] ?? now()->startOfMonth())->toDateString();
        $to = Carbon::parse($filters['to'] ?? today())->toDateString();

        $query = Expense::with(['supplier:id,name', 'purchaseOrder:id,po_number', 'department:id,name', 'creator:id,name'])
            ->whereBetween('spent_on', [$from, $to])
            ->when(! ($filters['void'] ?? false), fn ($q) => $q->paid())
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term));

        $total = round((float) (clone $query)->paid()->sum('amount'), 2);
        $byCategory = (clone $query)->paid()->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->pluck('total', 'category');
        $expenses = $query->latest('spent_on')->latest('id')->paginate(SystemHelper::paginationLimit())->withQueryString();

        $owed = $this->supplierBalances()->filter(fn ($row) => $row['balance'] > 0)->sortByDesc('balance')->values();

        return view('expenses.index', array_merge(
            compact('expenses', 'filters', 'from', 'to', 'total', 'byCategory', 'owed'),
            $this->formOptions(),
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100', 'required_if:method,mobile_money,bank_transfer,cheque'],
            'supplier_id' => ['nullable', 'required_if:category,supplier_payment', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ], [
            'reference.required_if' => 'Enter the M-Pesa code, cheque or bank reference.',
            'supplier_id.required_if' => 'Choose the supplier being paid.',
            'spent_on.before_or_equal' => 'Record expenses once they are paid.',
        ]);

        $expense = DB::transaction(function () use ($data, $request) {
            if (! empty($data['purchase_order_id'])) {
                $order = PurchaseOrder::whereKey($data['purchase_order_id'])->lockForUpdate()->first();
                if ($order->supplier_id !== (int) ($data['supplier_id'] ?? 0)) {
                    throw ValidationException::withMessages(['purchase_order_id' => "{$order->po_number} is not from this supplier."]);
                }
                if ((float) $data['amount'] > $order->amount_owed) {
                    throw ValidationException::withMessages(['amount' => "Only " . number_format($order->amount_owed, 2) . " is owed on {$order->po_number} for goods received so far."]);
                }
            }

            $expense = Expense::create($data + [
                'expense_number' => 'TMP-' . Str::uuid(),
                'status' => 'paid',
                'created_by' => $request->user()->id,
            ]);
            $expense->update(['expense_number' => sprintf('EXP-%s-%05d', now()->format('Y'), $expense->id)]);

            return $expense;
        });

        $this->core->log($request->user()->id, 'expense_recorded', "{$expense->expense_number}: {$expense->description} " . number_format((float) $expense->amount, 2), ['expense_id' => $expense->id]);

        return response()->json(['success' => true, 'message' => "{$expense->expense_number} recorded."], 201);
    }

    /**
     * Cancel an expense entered by mistake; it stays on record, marked void.
     */
    public function void(Request $request, Expense $expense): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']])['reason'];

        if ($expense->status === 'void') {
            return back()->with('error', "{$expense->expense_number} is already void.");
        }

        $expense->update(['status' => 'void', 'void_reason' => $reason . ' (' . $request->user()->name . ', ' . now()->format('d M Y H:i') . ')']);
        $this->core->log($request->user()->id, 'expense_voided', "{$expense->expense_number} voided: {$reason}", ['expense_id' => $expense->id]);

        return back()->with('success', "{$expense->expense_number} voided.");
    }

    /**
     * Received value, payments and balance for every supplier with activity.
     */
    private function supplierBalances()
    {
        $received = DB::table('inventory_transactions as t')
            ->join('purchase_orders as p', 'p.id', '=', 't.reference_id')
            ->where('t.reference_type', 'purchase_order')
            ->groupBy('p.supplier_id')
            ->selectRaw('p.supplier_id, SUM(t.total_value) as total')
            ->pluck('total', 'supplier_id');
        $paid = Expense::paid()->whereNotNull('supplier_id')->groupBy('supplier_id')->selectRaw('supplier_id, SUM(amount) as total')->pluck('total', 'supplier_id');

        return Supplier::whereIn('id', $received->keys()->merge($paid->keys())->unique())->orderBy('name')->get(['id', 'name', 'payment_terms'])
            ->map(fn (Supplier $s) => [
                'supplier' => $s,
                'received' => round((float) ($received[$s->id] ?? 0), 2),
                'paid' => round((float) ($paid[$s->id] ?? 0), 2),
                'balance' => round((float) ($received[$s->id] ?? 0) - (float) ($paid[$s->id] ?? 0), 2),
            ]);
    }

    private function formOptions(): array
    {
        // Orders with goods received and something still owed, for the supplier-payment form
        $openOrders = PurchaseOrder::whereIn('status', ['partially_received', 'received'])
            ->with('supplier:id,name')
            ->latest('id')
            ->take(200)
            ->get()
            ->map(fn (PurchaseOrder $o) => ['id' => $o->id, 'supplier_id' => $o->supplier_id, 'label' => $o->po_number, 'owed' => $o->amount_owed])
            ->filter(fn ($o) => $o['owed'] > 0)
            ->values();

        return [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'openOrders' => $openOrders,
        ];
    }
}
