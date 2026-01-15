<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Stay;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\CapitalInvestment;
use App\Models\Transaction;
use App\Services\FinanceService;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private CoreService $coreService
    ) {}
    
    /**
     * Display financial dashboard.
     */
    public function index()
    {
        // $this->authorize('viewFinancials', \App\Models\User::class);
        
        $today = now();
        $weekAgo = now()->subDays(7);
        $monthAgo = now()->subDays(30);
        
        // Quick stats
        $stats = [
            'today_revenue' => Invoice::whereDate('issue_date', $today)->sum('total'),
            'today_payments' => Payment::whereDate('received_at', $today)->sum('amount'),
            'pending_invoices' => Invoice::whereIn('status', ['sent', 'partial'])->count(),
            'pending_invoices_amount' => Invoice::whereIn('status', ['sent', 'partial'])->sum('due_amount'),
            'overdue_invoices' => Invoice::overdue()->count(),
            'overdue_invoices_amount' => Invoice::overdue()->sum('due_amount'),
            'total_expenses' => Payment::whereMonth('created_at', $today->month)->sum('amount'),
            'total_capital' => Payment::sum('amount'),
        ];
        
        // Recent transactions
        $recentTransactions = Payment::with(['account'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Account balances
        $accounts = Payment::all();
        
        // Recent invoices
        $recentInvoices = Invoice::with(['stay.guest'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Recent payments
        $recentPayments = Payment::with(['invoice.stay.guest', 'receiver'])
            ->orderBy('received_at', 'desc')
            ->limit(5)
            ->get();

        return view('finance.index', compact(
            'stats', 
            'recentTransactions', 
            'accounts',
            'recentInvoices',
            'recentPayments'
        ));
    }
    
    /**
     * Create a new invoice.
     */
    public function createInvoice()
    {
        $this->authorize('create', Invoice::class);
        
        $stays = Stay::with(['guest', 'roomAllocations.room'])
            ->whereIn('status', ['checked-in', 'reserved'])
            ->get();
        
        return view('finance.invoices.create', compact('stays'));
    }
    
    /**
     * Store a newly created invoice.
     */
    public function storeInvoice(Request $request)
    {
        $this->authorize('create', Invoice::class);
        
        $validated = $request->validate([
            'stay_id' => 'required|exists:stays,id',
            'due_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.source_type' => 'required|in:room,food,facility,service,other',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Get stay
                $stay = Stay::findOrFail($validated['stay_id']);
                
                // Create invoice
                $invoice = Invoice::create([
                    'stay_id' => $stay->id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'issue_date' => now(),
                    'due_date' => $validated['due_date'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Add items
                $subtotal = 0;
                foreach ($validated['items'] as $itemData) {
                    $item = $invoice->items()->create([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'source_type' => $itemData['source_type'],
                    ]);
                    $subtotal += $item->total;
                }

                // Calculate totals
                $tax = $subtotal * 0.16; // 16% VAT
                
                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'total' => $subtotal + $tax,
                ]);

                // Log action
                $this->coreService->log(auth()->id(), 'invoice_created', 
                    "Invoice #{$invoice->invoice_number} created for Stay #{$stay->id}");

                return redirect()->route('finance.invoices.show', $invoice)
                    ->with('success', 'Invoice created successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }
    
    /**
     * Display a listing of invoices.
     */
    public function invoices(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);
        
        $query = Invoice::with(['stay.guest']);
        
        // Apply filters
        if ($request->has('status')) {
            if ($request->status == 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->input('status'));
            }
        }
        
        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->input('date_to'));
        }
        
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->input('search') . '%')
                  ->orWhereHas('stay.guest', function ($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->input('search') . '%');
                  });
            });
        }
        
        $invoices = $query->latest()->paginate(20);
        
        // Calculate statistics for the view
        $overdueAmount = Invoice::overdue()->sum('due_amount');
        $dueNext30Days = Invoice::where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(30))
            ->whereIn('status', ['sent', 'partial'])
            ->sum('due_amount');
        
        // Get average payment days
        $paidInvoices = Invoice::where('status', 'paid')
            ->whereNotNull('paid_amount')
            ->get();
        
        $avgDays = 0;
        if ($paidInvoices->count() > 0) {
            $totalDays = 0;
            foreach ($paidInvoices as $invoice) {
                $lastPayment = $invoice->payments()->latest()->first();
                if ($lastPayment) {
                    $days = $invoice->issue_date->diffInDays($lastPayment->received_at);
                    $totalDays += $days;
                }
            }
            $avgDays = round($totalDays / $paidInvoices->count());
        }
        
        // Upcoming payout (invoices due in next 7 days)
        $upcomingPayout = Invoice::where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->whereIn('status', ['sent', 'partial'])
            ->sum('due_amount');

        return view('finance.invoices.index', compact(
            'invoices',
            'overdueAmount',
            'dueNext30Days',
            'avgDays',
            'upcomingPayout'
        ));
    }
    
    /**
     * Display the specified invoice.
     */
    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load([
            'stay.guest.guestProfile',
            'stay.roomAllocations.room',
            'items',
            'payments.receiver',
        ]);
        
        return view('finance.invoices.show', compact('invoice'));
    }
    
    /**
     * Send invoice to guest.
     */
    public function sendInvoice(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        try {
            $invoice->update(['status' => 'sent']);
            
            // Send notification
            if ($invoice->stay && $invoice->stay->guest->email) {
                $this->coreService->sendNotification($invoice->stay->guest, 'invoice_generated', [
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total,
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                ]);
            }
            
            return redirect()->route('finance.invoices.show', $invoice)
                ->with('success', 'Invoice sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error sending invoice: ' . $e->getMessage());
        }
    }
    
    /**
     * Process payment for invoice.
     */
    public function processPayment(Invoice $invoice, Request $request)
    {
        $this->authorize('processPayment', $invoice);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->due_amount,
            'method' => 'required|in:cash,credit_card,mobile_money,bank_transfer,cheque',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $payment = $this->financeService->processPayment($invoice, $request->all());
            
            return redirect()->route('finance.invoices.show', $invoice)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Add charge to invoice.
     */
    public function addCharge(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'source_type' => 'required|in:room,food,facility,service,other',
        ]);

        try {
            return DB::transaction(function () use ($invoice, $validated) {
                // Add charge
                $item = $invoice->items()->create($validated);
                
                // Recalculate totals
                $subtotal = $invoice->items()->sum(DB::raw('quantity * unit_price'));
                $tax = $subtotal * 0.16;
                
                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'total' => $subtotal + $tax,
                ]);

                $this->coreService->log(auth()->id(), 'invoice_charge_added', 
                    "Charge added to Invoice #{$invoice->invoice_number}");

                return redirect()->route('finance.invoices.show', $invoice)
                    ->with('success', 'Charge added successfully.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding charge: ' . $e->getMessage());
        }
    }
    
    /**
     * Record refund for invoice.
     */
    public function recordRefund(Invoice $invoice, Request $request)
    {
        $this->authorize('processRefund', $invoice);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->paid_amount,
            'reason' => 'required|string|max:500',
            'method' => 'required|in:cash,bank_transfer,cheque',
        ]);

        try {
            $refund = $this->financeService->recordRefund($invoice, $validated['amount'], $validated['reason']);
            
            return redirect()->route('finance.invoices.show', $invoice)
                ->with('success', 'Refund recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording refund: ' . $e->getMessage());
        }
    }
    
    /**
     * Display a listing of payments.
     */
    public function payments(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        
        $query = Payment::with(['invoice.stay.guest', 'receiver']);
        
        // Apply filters
        if ($request->has('method')) {
            $query->where('method', $request->input('method'));
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('date_from')) {
            $query->where('received_at', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->where('received_at', '<=', $request->input('date_to'));
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($q2) use ($search) {
                      $q2->where('invoice_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice.stay.guest', function ($q3) use ($search) {
                      $q3->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Calculate statistics
        $todayPayments = Payment::whereDate('received_at', today())
            ->where('amount', '>', 0)
            ->sum('amount');
        
        $weekPayments = Payment::whereBetween('received_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('amount', '>', 0)
            ->sum('amount');
        
        $monthPayments = Payment::whereMonth('received_at', now()->month)
            ->whereYear('received_at', now()->year)
            ->where('amount', '>', 0)
            ->sum('amount');
        
        $totalPayments = Payment::where('amount', '>', 0)->sum('amount');
        
        // Get payment methods distribution
        $methods = Payment::select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->where('amount', '>', 0)
            ->groupBy('method')
            ->get();

        // Get paginated results
        $payments = $query->orderBy('received_at', 'desc')->paginate(20);
        
        return view('finance.payments.index', compact(
            'payments',
            'todayPayments',
            'weekPayments',
            'monthPayments',
            'totalPayments',
            'methods'
        ));
    }
    
    /**
     * Display a listing of expenses.
     */
    public function expenses(Request $request)
    {
        $this->authorize('viewAny', Expense::class);
        
        $query = Expense::query();
        
        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->has('date_from')) {
            $query->where('expense_date', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->where('expense_date', '<=', $request->input('date_to'));
        }
        
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('paid_to', 'like', '%' . $request->input('search') . '%');
        }
        
        $expenses = $query->latest()->paginate(20);
        
        return view('finance.expenses.index', compact('expenses'));
    }
    
    /**
     * Record a new expense.
     */
    public function createExpense()
    {
        $this->authorize('create', Expense::class);
        
        return view('finance.expenses.create');
    }
    
    /**
     * Store a new expense.
     */
    public function storeExpense(Request $request)
    {
        $this->authorize('create', Expense::class);
        
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|in:operational,purchases,payroll,utility,maintenance,other',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
            'paid_to' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $expense = $this->financeService->recordExpense($validated);
            
            return redirect()->route('finance.expenses.index')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording expense: ' . $e->getMessage());
        }
    }
    
    /**
     * Display capital investments.
     */
    public function capitalInvestments()
    {
        $this->authorize('viewCapital', \App\Models\User::class);
        
        $investments = CapitalInvestment::latest()->paginate(20);
        
        return view('finance.capital.index', compact('investments'));
    }
    
    /**
     * Record capital investment.
     */
    public function storeCapitalInvestment(Request $request)
    {
        $this->authorize('create', CapitalInvestment::class);
        
        $validated = $request->validate([
            'investor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'investment_type' => 'required|in:equity,loan,grant,other',
            'description' => 'required|string|max:500',
            'investment_date' => 'required|date',
            'terms' => 'nullable|string',
            'expected_return' => 'nullable|numeric|min:0',
        ]);
        
        try {
            $investment = $this->financeService->recordCapitalInvestment($validated);
            
            return redirect()->route('finance.capital.index')
                ->with('success', 'Capital investment recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording investment: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate financial reports.
     */
    public function reports(Request $request)
    {
        $this->authorize('viewFinancials', \App\Models\User::class);
        
        $reportType = $request->input('report', 'income');
        $period = $request->input('period', 'month');
        
        // Determine date range
        switch ($period) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = now()->startOfQuarter();
                $endDate = now()->endOfQuarter();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }
        
        $report = null;
        
        switch ($reportType) {
            case 'income':
                $report = $this->financeService->getIncomeStatement($startDate, $endDate);
                break;
            case 'balance':
                $report = $this->financeService->getBalanceSheet($endDate);
                break;
            case 'cashflow':
                $report = $this->financeService->getCashFlowStatement($startDate, $endDate);
                break;
        }
        
        return view('finance.reports.show', compact('report', 'reportType', 'period', 'startDate', 'endDate'));
    }
    
    /**
     * Export financial report.
     */
    public function exportReport(Request $request)
    {
        $this->authorize('exportFinancials', \App\Models\User::class);
        
        $reportType = $request->input('report');
        $format = $request->input('format', 'pdf');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Generate report data
        $report = null;
        switch ($reportType) {
            case 'income':
                $report = $this->financeService->getIncomeStatement($startDate, $endDate);
                break;
            case 'balance':
                $report = $this->financeService->getBalanceSheet($endDate);
                break;
        }
        
        // Export logic would go here (PDF, Excel, etc.)
        // For now, return JSON
        return response()->json([
            'success' => true,
            'report' => $report,
            'format' => $format,
        ]);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load([
            'stay.guest',
            'stay.roomAllocations.room',
            'items',
            'payments',
        ]);

        // You'll need to install and configure a PDF library like dompdf
        // $pdf = PDF::loadView('finance.invoices.pdf', compact('invoice'));
        
        // For now, return a view
        return view('finance.invoices.pdf', compact('invoice'));
    }

    /**
     * Create a new transaction.
     */
    public function createTransaction()
    {
        $this->authorize('create', Transaction::class);
        
        $accounts = Payment::all();
        
        return view('finance.transactions.create', compact('accounts'));
    }

    /**
     * Store a new transaction.
     */
    public function storeTransaction(Request $request)
    {
        $this->authorize('create', Transaction::class);
        
        $validated = $request->validate([
            'type' => 'required|in:income,expense,capital,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'account_id' => 'required|exists:financial_accounts,id',
            'category' => 'nullable|string|max:100',
            'created_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $transaction = $this->financeService->recordTransaction($validated);
            
            return redirect()->route('finance.index')
                ->with('success', 'Transaction recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording transaction: ' . $e->getMessage());
        }
    }

    /**
     * Transfer funds between accounts.
     */
    public function transferFunds(Request $request)
    {
        $this->authorize('create', Transaction::class);
        
        $validated = $request->validate([
            'from_account_id' => 'required|exists:financial_accounts,id',
            'to_account_id' => 'required|exists:financial_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $transfers = $this->financeService->transferFunds($validated);
            
            return redirect()->route('finance.index')
                ->with('success', 'Funds transferred successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error transferring funds: ' . $e->getMessage());
        }
    }

    /**
     * Export invoices.
     */
    public function exportInvoices(Request $request)
    {
        $this->authorize('export', Invoice::class);
        
        $query = Invoice::with(['stay.guest']);

        // Apply filters
        if ($request->has('status') && $request->status != 'All') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'Invoice Number',
                'Guest Name',
                'Guest Email',
                'Issue Date',
                'Due Date',
                'Subtotal',
                'Tax',
                'Total',
                'Paid Amount',
                'Due Amount',
                'Status',
                'Notes'
            ]);

            // Data
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->stay->guest->name ?? 'N/A',
                    $invoice->stay->guest->email ?? 'N/A',
                    $invoice->issue_date->format('Y-m-d'),
                    $invoice->due_date->format('Y-m-d'),
                    $invoice->subtotal,
                    $invoice->tax_amount,
                    $invoice->total,
                    $invoice->paid_amount,
                    $invoice->due_amount,
                    $invoice->status,
                    $invoice->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export payments.
     */
    public function exportPayments(Request $request)
    {
        $this->authorize('export', Payment::class);
        
        $query = Payment::with(['invoice.stay.guest', 'receiver']);

        // Apply filters
        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        if ($request->has('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('received_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payments-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'Payment Reference',
                'Date & Time',
                'Invoice Number',
                'Guest Name',
                'Payment Method',
                'Amount',
                'Status',
                'Received By',
                'Notes'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_reference,
                    $payment->received_at->format('Y-m-d H:i:s'),
                    $payment->invoice->invoice_number ?? 'N/A',
                    $payment->invoice->stay->guest->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $payment->method)),
                    $payment->amount,
                    $payment->status,
                    $payment->receiver->name ?? 'System',
                    $payment->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}