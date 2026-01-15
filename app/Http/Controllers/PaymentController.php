<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Stay;
use App\Services\FinanceService;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class PaymentController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private CoreService $coreService
    ) {}

    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Payment::class);
        
        $query = Payment::with(['invoice.stay.guest', 'receiver']);

        // Apply filters
        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
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
        $payments = $query->orderBy('received_at', 'desc')->paginate(10);

         $invoices = Invoice::with(['stay.guest'])
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_amount', '>', 0)
            ->get();

        return view('payments.index', compact(
            'payments',
            'todayPayments',
            'weekPayments',
            'monthPayments',
            'totalPayments',
            'methods',
            'invoices'
        ));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        // $this->authorize('create', Payment::class);
        
        $invoices = Invoice::with(['stay.guest'])
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_amount', '>', 0)
            ->get();

        return view('payments.create', compact('invoices'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Payment::class);
        
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,credit_card,mobile_money,bank_transfer,cheque',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'received_at' => 'required|date',
        ]);

        try {
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            
            // Validate amount doesn't exceed due amount
            if ($validated['amount'] > $invoice->due_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Payment amount cannot exceed due amount of KSH ' . number_format($invoice->due_amount, 2));
            }

            $payment = $this->financeService->processPayment($invoice, $validated);
            
            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        // $this->authorize('view', $payment);
        
        $payment->load([
            'invoice.stay.guest.guestProfile',
            'invoice.items',
            'receiver',
        ]);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the payment.
     */
    public function edit(Payment $payment)
    {
        // $this->authorize('update', $payment);
        
        $invoices = Invoice::with('stay.guest')->get();
        
        return view('payments.edit', compact('payment', 'invoices'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        // $this->authorize('update', $payment);
        
        $validated = $request->validate([
            'method' => 'required|in:cash,credit_card,mobile_money,bank_transfer,cheque',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'received_at' => 'required|date',
        ]);

        try {
            $payment->update($validated);
            
            $this->coreService->log(auth()->id(), 'payment_updated', 
                "Payment #{$payment->payment_reference} updated");

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    /**
     * Record refund for payment.
     */
    public function refund(Request $request, Payment $payment)
    {
        // $this->authorize('refund', $payment);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500',
            'method' => 'required|in:cash,bank_transfer,cheque',
        ]);

        try {
            $refund = $this->financeService->recordRefund(
                $payment->invoice,
                $validated['amount'],
                $validated['reason']
            );
            
            $this->coreService->log(auth()->id(), 'refund_processed', 
                "Refund of KSH {$validated['amount']} processed for payment #{$payment->payment_reference}");

            return redirect()->route('payments.show', $refund)
                ->with('success', 'Refund processed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error processing refund: ' . $e->getMessage());
        }
    }

    /**
     * Download payment receipt as PDF.
     */
    public function receipt(Payment $payment)
    {
        // $this->authorize('view', $payment);
        
        $payment->load([
            'invoice.stay.guest',
            'invoice.stay.roomAllocations.room',
            'invoice.items',
            'receiver',
        ]);

        $pdf = PDF::loadView('payments.receipt', compact('payment'));
        
        return $pdf->download("receipt-{$payment->payment_reference}.pdf");
    }

    /**
     * Delete payment.
     */
    public function destroy(Payment $payment)
    {
        // $this->authorize('delete', $payment);
        
        // Only allow deletion of recent payments (within 24 hours)
        if ($payment->created_at->diffInHours(now()) > 24) {
            return redirect()->back()
                ->with('error', 'Only payments created within the last 24 hours can be deleted.');
        }

        try {
            $reference = $payment->payment_reference;
            
            // Update invoice if this payment is for an invoice
            if ($payment->invoice) {
                $invoice = $payment->invoice;
                $invoice->paid_amount -= $payment->amount;
                
                if ($invoice->paid_amount >= $invoice->total) {
                    $invoice->status = 'paid';
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'sent';
                }
                
                $invoice->save();
            }
            
            $payment->delete();
            
            $this->coreService->log(auth()->id(), 'payment_deleted', 
                "Payment #{$reference} deleted");

            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Export payments.
     */
    public function export(Request $request)
    {
        // $this->authorize('export', Payment::class);
        
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

    /**
     * Get payment statistics.
     */
    public function getStats()
    {
        return [
            'today' => Payment::whereDate('received_at', today())
                ->where('amount', '>', 0)
                ->sum('amount'),
            'week' => Payment::whereBetween('received_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('amount', '>', 0)
                ->sum('amount'),
            'month' => Payment::whereMonth('received_at', now()->month)
                ->whereYear('received_at', now()->year)
                ->where('amount', '>', 0)
                ->sum('amount'),
            'total' => Payment::where('amount', '>', 0)->sum('amount'),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
        ];
    }
}