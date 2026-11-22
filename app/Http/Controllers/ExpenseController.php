<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Money paid out: running costs and payments to suppliers. The same journey as Breve:
 *   anyone on the staff records an expense (awaiting approval) -> a manager approves or rejects it
 *   -> finance or a manager pays it (method + reference). Managers and finance can record one that is
 *   already paid in one step. Receipts (photo or PDF) are kept privately and shown on the record page.
 * Staff outside finance see only the expenses they recorded.
 */
class ExpenseController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public static function canApprove(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public static function canPay(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'finance']);
    }

    /** Finance and managers see every expense; anyone else only their own. */
    private function visible(Request $request)
    {
        return Expense::query()->unless(self::canPay($request->user()), fn ($q) => $q->where('created_by', $request->user()->id));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(array_keys(Expense::STATUSES))],
            'category' => ['nullable', Rule::in(array_keys(Expense::CATEGORIES))],
            'supplier_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $from = Carbon::parse($filters['from'] ?? now()->startOfMonth())->toDateString();
        $to = Carbon::parse($filters['to'] ?? today())->toDateString();

        $query = $this->visible($request)
            ->with(['supplier:id,name', 'purchaseOrder:id,po_number', 'department:id,name', 'creator:id,name'])
            ->whereBetween('spent_on', [$from, $to])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->where('status', '!=', 'void'))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term));

        $total = round((float) (clone $query)->paid()->sum('amount'), 2);
        $byCategory = (clone $query)->paid()->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->pluck('total', 'category');
        $expenses = $query->latest('spent_on')->latest('id')->paginate(SystemHelper::paginationLimit())->withQueryString();

        // The Breve cards: paid this month, waiting for approval, approved but not paid yet
        $mine = $this->visible($request);
        $stats = [
            'month' => round((float) (clone $mine)->paid()->where('spent_on', '>=', now()->startOfMonth()->toDateString())->sum('amount'), 2),
            'pending' => round((float) (clone $mine)->where('status', 'pending')->sum('amount'), 2),
            'pending_count' => (clone $mine)->where('status', 'pending')->count(),
            'unpaid' => round((float) (clone $mine)->where('status', 'approved')->sum('amount'), 2),
            'unpaid_count' => (clone $mine)->where('status', 'approved')->count(),
        ];

        $owed = self::canPay($request->user())
            ? $this->supplierBalances()->filter(fn ($row) => $row['balance'] > 0)->sortByDesc('balance')->values()
            : collect();

        return view('expenses.index', array_merge(
            compact('expenses', 'filters', 'from', 'to', 'total', 'byCategory', 'owed', 'stats'),
            $this->formOptions(),
        ));
    }

    public function show(Request $request, Expense $expense): View
    {
        abort_unless(self::canPay($request->user()) || $expense->created_by === $request->user()->id, 403);
        $expense->load(['supplier', 'purchaseOrder', 'department', 'creator', 'approver']);

        return view('expenses.show', ['expense' => $expense, 'methods' => Payment::METHODS]);
    }

    public function store(Request $request): JsonResponse
    {
        $canPay = self::canPay($request->user());
        $paidNow = $canPay && $request->boolean('paid_now');

        $data = $request->validate([
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'method' => [$paidNow ? 'required' : 'nullable', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100', $paidNow ? 'required_if:method,mobile_money,bank_transfer,cheque' : 'nullable'],
            'supplier_id' => ['nullable', 'required_if:category,supplier_payment', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'reference.required_if' => 'Enter the M-Pesa code, cheque or bank reference.',
            'supplier_id.required_if' => 'Choose the supplier being paid.',
            'spent_on.before_or_equal' => 'The date of the bill or receipt cannot be in the future.',
            'method.required' => 'Choose how it was paid, or untick "Already paid" to send it for approval.',
        ]);

        $expense = DB::transaction(function () use ($data, $request, $paidNow) {
            if (! empty($data['purchase_order_id'])) {
                $this->checkOrder((int) $data['purchase_order_id'], (int) ($data['supplier_id'] ?? 0), (float) $data['amount']);
            }

            $expense = Expense::create(collect($data)->except('receipt')->all() + [
                'expense_number' => 'TMP-' . Str::uuid(),
                'status' => $paidNow ? 'paid' : 'pending',
                'created_by' => $request->user()->id,
                'approved_by' => $paidNow ? $request->user()->id : null,
                'approved_at' => $paidNow ? now() : null,
                'paid_at' => $paidNow ? now() : null,
            ]);
            $expense->update([
                'expense_number' => sprintf('EXP-%s-%05d', now()->format('Y'), $expense->id),
                'receipt_path' => $request->hasFile('receipt') ? $request->file('receipt')->store('receipts/expenses', 'local') : null,
            ]);

            return $expense;
        });

        $this->core->log($request->user()->id, $paidNow ? 'expense_recorded' : 'expense_requested', "{$expense->expense_number}: {$expense->description} " . number_format((float) $expense->amount, 2), ['expense_id' => $expense->id]);

        return response()->json([
            'success' => true,
            'message' => $paidNow ? "{$expense->expense_number} recorded as paid." : "{$expense->expense_number} sent for approval.",
        ], 201);
    }

    /** A manager approves or rejects an expense that is awaiting approval. */
    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(self::canApprove($request->user()), 403);
        $data = $request->validate(['decision' => ['required', Rule::in(['approve', 'reject'])], 'reason' => ['nullable', 'required_if:decision,reject', 'string', 'max:255']],
            ['reason.required_if' => 'Say why it is rejected, so whoever asked knows.']);

        if ($expense->status !== 'pending') {
            return back()->with('error', "{$expense->expense_number} is {$expense->status_label}; only expenses awaiting approval can be approved or rejected.");
        }

        $rejected = $data['decision'] === 'reject';
        $expense->update([
            'status' => $rejected ? 'rejected' : 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'void_reason' => $rejected ? $data['reason'] . ' (' . $request->user()->name . ')' : null,
        ]);
        $this->core->log($request->user()->id, $rejected ? 'expense_rejected' : 'expense_approved', "{$expense->expense_number} " . ($rejected ? 'rejected' : 'approved'), ['expense_id' => $expense->id]);

        return back()->with('success', "{$expense->expense_number} " . ($rejected ? 'rejected.' : 'approved. Finance can pay it now.'));
    }

    /** Finance or a manager pays an approved expense (a manager may also pay one still awaiting approval). */
    public function pay(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(self::canPay($request->user()), 403);
        if (! in_array($expense->status, ['approved', 'pending'], true)) {
            return back()->with('error', "{$expense->expense_number} is {$expense->status_label} and can't be paid.");
        }
        if ($expense->status === 'pending' && ! self::canApprove($request->user())) {
            return back()->with('error', "{$expense->expense_number} needs a manager's approval before it is paid.");
        }

        $data = $request->validate([
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100', 'required_if:method,mobile_money,bank_transfer,cheque'],
        ], ['reference.required_if' => 'Enter the M-Pesa code, cheque or bank reference.']);

        DB::transaction(function () use ($expense, $data, $request) {
            if ($expense->purchase_order_id) {
                $this->checkOrder($expense->purchase_order_id, (int) $expense->supplier_id, (float) $expense->amount);
            }
            $expense->update($data + [
                'status' => 'paid',
                'paid_at' => now(),
                'approved_by' => $expense->approved_by ?? $request->user()->id,
                'approved_at' => $expense->approved_at ?? now(),
            ]);
        });
        $this->core->log($request->user()->id, 'expense_paid', "{$expense->expense_number} paid " . number_format((float) $expense->amount, 2), ['expense_id' => $expense->id]);

        return back()->with('success', "{$expense->expense_number} marked as paid.");
    }

    public function receipt(Request $request, Expense $expense): StreamedResponse
    {
        abort_unless(self::canPay($request->user()) || $expense->created_by === $request->user()->id, 403);
        abort_unless($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path), 404);

        return Storage::disk('local')->response($expense->receipt_path, $expense->expense_number . '.' . pathinfo($expense->receipt_path, PATHINFO_EXTENSION));
    }

    /**
     * Cancel an expense entered by mistake; it stays on record, marked void.
     */
    public function void(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(self::canPay($request->user()), 403);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']])['reason'];

        if ($expense->status === 'void') {
            return back()->with('error', "{$expense->expense_number} is already void.");
        }

        $expense->update(['status' => 'void', 'void_reason' => $reason . ' (' . $request->user()->name . ', ' . now()->format('d M Y H:i') . ')']);
        $this->core->log($request->user()->id, 'expense_voided', "{$expense->expense_number} voided: {$reason}", ['expense_id' => $expense->id]);

        return back()->with('success', "{$expense->expense_number} voided.");
    }

    /** A supplier payment against an order: the right supplier, and no more than is owed for goods received. */
    private function checkOrder(int $orderId, int $supplierId, float $amount): void
    {
        $order = PurchaseOrder::whereKey($orderId)->lockForUpdate()->first();
        if ($order->supplier_id !== $supplierId) {
            throw ValidationException::withMessages(['purchase_order_id' => "{$order->po_number} is not from this supplier."]);
        }
        if ($amount > $order->amount_owed + 0.009) {
            throw ValidationException::withMessages(['amount' => 'Only ' . number_format($order->amount_owed, 2) . " is owed on {$order->po_number} for goods received so far."]);
        }
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
