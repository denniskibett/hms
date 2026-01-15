<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Stay;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\FinanceService;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class InvoiceController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private CoreService $coreService
    ) {}

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Invoice::class);
        
        // Calculate statistics
        $overdueAmount = Invoice::overdue()->sum('due_amount');
        $dueNext30Days = Invoice::where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(30))
            ->whereIn('status', ['sent', 'partial'])
            ->sum('due_amount');
        
        // Get average payment days (simplified)
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

        // Get invoices with filtering
        $query = Invoice::with(['stay.guest']);

        // Apply filters
        if ($request->has('status') && $request->status != 'All') {
            if ($request->status == 'Unpaid') {
                $query->whereIn('status', ['sent', 'partial']);
            } elseif ($request->status == 'Draft') {
                $query->where('status', 'draft');
            } elseif ($request->status == 'Paid') {
                $query->where('status', 'paid');
            } elseif ($request->status == 'Overdue') {
                $query->overdue();
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('stay.guest', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('customer')) {
            $query->whereHas('stay.guest', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->customer}%");
            });
        }

        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        // Get paginated results
        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        $stays = Stay::with(['guest', 'roomAllocations.room'])
            ->where('status', 'checked-in')
            ->orWhere('status', 'reserved')
            ->get();

        return view('invoices.index', compact(
            'invoices',
            'overdueAmount',
            'dueNext30Days',
            'avgDays',
            'upcomingPayout',
            'stays'
        ));
    }

    public function create()
    {
        // $this->authorize('create', Invoice::class);
        
        $stays = Stay::with(['guest', 'roomAllocations.room'])
            ->where('status', 'checked-in')
            ->orWhere('status', 'reserved')
            ->get();
        
        return view('finance.invoices.create', compact('stays'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Invoice::class);
        
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


    public function show(Invoice $invoice)
    {
        // $this->authorize('view', $invoice);
        
        $invoice->load([
            'stay.guest.guest',
            'stay.roomAllocations.room',
            'items',
            'payments.receiver',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        // $this->authorize('update', $invoice);
        
        $invoice->load(['items']);
        $stays = Stay::with('guest')->get();
        
        return view('invoices.edit', compact('invoice', 'stays'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // $this->authorize('update', $invoice);
        
        $validated = $request->validate([
            'due_date' => 'required|date',
            'status' => 'required|in:draft,sent,partial,paid',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $invoice->update($validated);
            
            $this->coreService->log(auth()->id(), 'invoice_updated', 
                "Invoice #{$invoice->invoice_number} updated");

            return redirect()->route('finance.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }


    public function send(Invoice $invoice)
    {
        // $this->authorize('update', $invoice);
        
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

            $this->coreService->log(auth()->id(), 'invoice_sent', 
                "Invoice #{$invoice->invoice_number} sent to guest");

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process payment for invoice.
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        // $this->authorize('processPayment', $invoice);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->due_amount,
            'method' => 'required|in:cash,credit_card,mobile_money,bank_transfer,cheque',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->financeService->processPayment($invoice, $validated);
            
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
        // $this->authorize('update', $invoice);
        
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
    public function recordRefund(Request $request, Invoice $invoice)
    {
        // $this->authorize('processRefund', $invoice);
        
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
     * Download invoice as PDF.
     */
    public function download(Invoice $invoice)
    {
        // $this->authorize('view', $invoice);
        
        $invoice->load([
            'stay.guest',
            'stay.roomAllocations.room',
            'items',
            'payments',
        ]);

        $pdf = PDF::loadView('finance.invoices.pdf', compact('invoice'));
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Delete invoice.
     */
    public function destroy(Invoice $invoice)
    {
        // $this->authorize('delete', $invoice);
        
        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be deleted.');
        }

        try {
            $invoiceNumber = $invoice->invoice_number;
            $invoice->delete();
            
            $this->coreService->log(auth()->id(), 'invoice_deleted', 
                "Invoice #{$invoiceNumber} deleted");

            return redirect()->route('finance.invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice statistics for dashboard.
     */
    public function getStats()
    {
        $today = now();
        $weekAgo = $today->copy()->subDays(7);
        $monthAgo = $today->copy()->subDays(30);

        return [
            'today_revenue' => Invoice::whereDate('issue_date', $today)->sum('total'),
            'today_payments' => Payment::whereDate('received_at', $today)->sum('amount'),
            'pending_invoices' => Invoice::whereIn('status', ['sent', 'partial'])->count(),
            'overdue_invoices' => Invoice::overdue()->count(),
            'total_revenue' => Invoice::sum('total'),
            'total_paid' => Invoice::sum('paid_amount'),
            'outstanding' => Invoice::whereIn('status', ['sent', 'partial'])->sum('due_amount'),
        ];
    }

    /**
     * Export invoices.
     */
    public function export(Request $request)
    {
        // $this->authorize('export', Invoice::class);
        
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
}