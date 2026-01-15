<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Stay;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use App\Models\CapitalInvestment;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Services\CoreService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceService
{
    public function __construct(
        private CoreService $coreService
    ) {}
    
    // ==================== INVOICE MANAGEMENT ====================
    
    /**
     * Create invoice for stay.
     */
    public function createStayInvoice(Stay $stay): Invoice
    {
        $invoice = Invoice::create([
            'stay_id' => $stay->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'issue_date' => now(),
            'due_date' => $stay->departure_date,
            'status' => 'draft',
        ]);
        
        // Add room charges
        $this->addRoomCharges($invoice, $stay);
        
        // Calculate totals
        $this->calculateInvoiceTotals($invoice);
        
        $this->coreService->log(auth()->id(), 'invoice_created', 
            "Invoice #{$invoice->invoice_number} created for Stay #{$stay->id}");
        
        return $invoice->fresh();
    }
    
    /**
     * Add room charges to invoice.
     */
    private function addRoomCharges(Invoice $invoice, Stay $stay): void
    {
        foreach ($stay->roomAllocations as $allocation) {
            $nights = $allocation->from_date->diffInDays($allocation->to_date);
            $total = $allocation->rate_applied * $nights;
            
            $invoice->items()->create([
                'description' => "Room {$allocation->room->room_number} - {$nights} nights",
                'quantity' => $nights,
                'unit_price' => $allocation->rate_applied,
                'source_type' => 'room',
                'reference_id' => $allocation->id,
            ]);
        }
    }
    
    /**
     * Add additional charge to invoice.
     */
    public function addInvoiceCharge(Invoice $invoice, array $data): InvoiceItem
    {
        $item = $invoice->items()->create([
            'description' => $data['description'],
            'quantity' => $data['quantity'] ?? 1,
            'unit_price' => $data['unit_price'],
            'source_type' => $data['source_type'] ?? 'other',
            'reference_id' => $data['reference_id'] ?? null,
        ]);
        
        $this->calculateInvoiceTotals($invoice);
        
        return $item;
    }
    
    /**
     * Calculate invoice totals.
     */
    private function calculateInvoiceTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->items()->sum(DB::raw('quantity * unit_price'));
        $tax = $subtotal * 0.16; // 16% VAT (Kenya)
        
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
    
    /**
     * Finalize invoice (when guest checks out).
     */
    public function finalizeStayInvoice(Stay $stay): Invoice
    {
        $invoice = $stay->currentInvoice();
        
        if (!$invoice) {
            $invoice = $this->createStayInvoice($stay);
        }
        
        $invoice->update([
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);
        
        // Send notification
        if ($stay->guest->email) {
            $this->coreService->sendNotification($stay->guest, 'invoice_generated', [
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total,
                'due_date' => $invoice->due_date->format('Y-m-d'),
            ]);
        }
        
        return $invoice->fresh();
    }
    
    /**
     * Add extension charges.
     */
    public function addStayExtensionCharges(Stay $stay, $oldDeparture, $newDepartureDate): float
    {
        $invoice = $stay->currentInvoice();
        $extensionDays = Carbon::parse($oldDeparture)->diffInDays($newDepartureDate);
        $dailyRate = $stay->currentRoomAllocation->rate_applied;
        
        $total = $extensionDays * $dailyRate;
        
        $this->addInvoiceCharge($invoice, [
            'description' => "Stay extension - {$extensionDays} additional days",
            'quantity' => $extensionDays,
            'unit_price' => $dailyRate,
            'source_type' => 'room',
        ]);
        
        return $total;
    }
    
    // ==================== PAYMENT PROCESSING ====================
    
    /**
     * Process payment for invoice.
     */
    public function processPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_reference' => Payment::generatePaymentReference(),
                'amount' => $data['amount'],
                'method' => $data['method'],
                'payment_details' => $data['payment_details'] ?? null,
                'received_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Update invoice
            $invoice->paid_amount += $data['amount'];
            
            if ($invoice->paid_amount >= $invoice->total) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }
            
            $invoice->save();
            
            // Record transaction
            $this->recordTransaction([
                'type' => 'income',
                'amount' => $data['amount'],
                'description' => "Payment for Invoice #{$invoice->invoice_number}",
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'account_id' => $this->getCashAccount()->id,
            ]);
            
            // Send receipt
            if ($invoice->stay && $invoice->stay->guest->email) {
                $this->coreService->sendNotification($invoice->stay->guest, 'payment_receipt', [
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $data['amount'],
                    'method' => $data['method'],
                    'payment_date' => now()->format('Y-m-d'),
                ]);
            }
            
            $this->coreService->log(auth()->id(), 'payment_processed', 
                "Payment of {$data['amount']} processed for Invoice #{$invoice->invoice_number}");
            
            return $payment;
        });
    }
    
    /**
     * Process partial payment.
     */
    public function processPartialPayment(Invoice $invoice, float $amount, string $method): Payment
    {
        return $this->processPayment($invoice, [
            'amount' => $amount,
            'method' => $method,
        ]);
    }
    
    /**
     * Record refund.
     */
    public function recordRefund(Invoice $invoice, float $amount, string $reason): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $reason) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_reference' => 'REF-' . Payment::generatePaymentReference(),
                'amount' => -$amount, // Negative amount for refund
                'method' => 'cash',
                'payment_details' => ['reason' => $reason],
                'received_by' => auth()->id(),
                'notes' => "Refund: {$reason}",
            ]);
            
            // Update invoice
            $invoice->paid_amount -= $amount;
            $invoice->save();
            
            // Record transaction
            $this->recordTransaction([
                'type' => 'expense',
                'amount' => $amount,
                'description' => "Refund for Invoice #{$invoice->invoice_number} - {$reason}",
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'account_id' => $this->getCashAccount()->id,
            ]);
            
            return $payment;
        });
    }

    public function getPendingPaymentsCount(): int
    {
        return Payment::where('status', 'pending')->count();
    }
    
    public function getInvoicesToApproveCount(): int
    {
        return Invoice::where('status', 'pending_approval')->count();
    }
    // ==================== CAPITAL & INVESTMENT MANAGEMENT ====================
    
    /**
     * Record capital investment.
     */
    public function recordCapitalInvestment(array $data): CapitalInvestment
    {
        return DB::transaction(function () use ($data) {
            $investment = CapitalInvestment::create([
                'investor_name' => $data['investor_name'],
                'amount' => $data['amount'],
                'investment_type' => $data['investment_type'] ?? 'equity',
                'description' => $data['description'],
                'investment_date' => $data['investment_date'] ?? now(),
                'terms' => $data['terms'] ?? null,
                'expected_return' => $data['expected_return'] ?? null,
                'created_by' => auth()->id(),
            ]);
            
            // Record transaction
            $this->recordTransaction([
                'type' => 'capital',
                'amount' => $data['amount'],
                'description' => "Capital investment from {$data['investor_name']}",
                'reference_type' => 'capital_investment',
                'reference_id' => $investment->id,
                'account_id' => $this->getBankAccount()->id,
            ]);
            
            $this->coreService->log(auth()->id(), 'capital_recorded', 
                "Capital investment of {$data['amount']} from {$data['investor_name']}");
            
            return $investment;
        });
    }
    
    /**
     * Record other income (not from invoices).
     */
    public function recordOtherIncome(array $data): Transaction
    {
        return $this->recordTransaction([
            'type' => 'income',
            'amount' => $data['amount'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'other',
            'reference_type' => $data['reference_type'] ?? 'manual',
            'reference_id' => $data['reference_id'] ?? null,
            'account_id' => $this->getCashAccount()->id,
            'notes' => $data['notes'] ?? null,
        ]);
    }
    
    // ==================== EXPENSE MANAGEMENT ====================
    
    /**
     * Record expense.
     */
    public function recordExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $expense = Expense::create([
                'description' => $data['description'],
                'amount' => $data['amount'],
                'category' => $data['category'] ?? 'operational',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'paid_to' => $data['paid_to'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'expense_date' => $data['expense_date'] ?? now(),
                'approved_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Record transaction
            $this->recordTransaction([
                'type' => 'expense',
                'amount' => $data['amount'],
                'description' => $data['description'],
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'account_id' => $this->getAccountForMethod($data['payment_method'])->id,
            ]);
            
            $this->coreService->log(auth()->id(), 'expense_recorded', 
                "Expense of {$data['amount']} for {$data['description']}");
            
            return $expense;
        });
    }
    
    /**
     * Process purchase order payment.
     */
    public function payPurchaseOrder(PurchaseOrder $purchaseOrder, array $data): Payment
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            // Record expense
            $expense = $this->recordExpense([
                'description' => "Payment for PO #{$purchaseOrder->po_number}",
                'amount' => $data['amount'],
                'category' => 'purchases',
                'payment_method' => $data['method'],
                'paid_to' => $purchaseOrder->supplier->name,
                'reference_number' => $purchaseOrder->po_number,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Update PO payment status
            $purchaseOrder->payment_status = $data['amount'] >= $purchaseOrder->total ? 'paid' : 'partial';
            $purchaseOrder->save();
            
            return Payment::create([
                'invoice_id' => null, // No invoice for PO payments
                'payment_reference' => 'PO-PAY-' . $purchaseOrder->po_number,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'payment_details' => ['po_id' => $purchaseOrder->id],
                'received_by' => auth()->id(),
                'notes' => "Payment for PO #{$purchaseOrder->po_number}",
            ]);
        });
    }
    
    // ==================== TRANSACTION MANAGEMENT ====================
    
    /**
     * Record financial transaction.
     */
    public function recordTransaction(array $data): Transaction
    {
        $transaction = Transaction::create([
            'type' => $data['type'], // income, expense, capital, transfer
            'amount' => $data['amount'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'other',
            'account_id' => $data['account_id'],
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'transaction_date' => $data['transaction_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
        
        // Update account balance
        $account = FinancialAccount::find($data['account_id']);
        if ($account) {
            if (in_array($data['type'], ['income', 'capital'])) {
                $account->balance += $data['amount'];
            } else {
                $account->balance -= $data['amount'];
            }
            $account->save();
        }
        
        return $transaction;
    }
    
    /**
     * Transfer between accounts.
     */
    public function transferFunds(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Debit from account
            $debitTransaction = $this->recordTransaction([
                'type' => 'expense',
                'amount' => $data['amount'],
                'description' => "Transfer to {$data['to_account']}",
                'category' => 'transfer',
                'account_id' => $data['from_account_id'],
                'reference_type' => 'transfer',
                'reference_id' => null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Credit to account
            $creditTransaction = $this->recordTransaction([
                'type' => 'income',
                'amount' => $data['amount'],
                'description' => "Transfer from {$data['from_account']}",
                'category' => 'transfer',
                'account_id' => $data['to_account_id'],
                'reference_type' => 'transfer',
                'reference_id' => null,
                'notes' => $data['notes'] ?? null,
            ]);
            
            return [
                'debit' => $debitTransaction,
                'credit' => $creditTransaction,
            ];
        });
    }
    
    // ==================== FINANCIAL REPORTING ====================
    
    /**
     * Get income statement.
     */
    public function getIncomeStatement($startDate, $endDate): array
    {
        $revenue = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $expenses = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $capital = Transaction::where('type', 'capital')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'revenue' => [
                'room_sales' => $this->getRoomRevenue($startDate, $endDate),
                'food_sales' => $this->getFoodRevenue($startDate, $endDate),
                'other_income' => $this->getOtherIncome($startDate, $endDate),
                'total' => $revenue,
            ],
            'expenses' => [
                'staff_costs' => $this->getStaffCosts($startDate, $endDate),
                'inventory_costs' => $this->getInventoryCosts($startDate, $endDate),
                'utility_costs' => $this->getUtilityCosts($startDate, $endDate),
                'other_expenses' => $this->getOtherExpenses($startDate, $endDate),
                'total' => $expenses,
            ],
            'capital_inflows' => $capital,
            'net_income' => $revenue - $expenses,
            'gross_profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
        ];
    }
    
    /**
     * Get balance sheet snapshot.
     */
    public function getBalanceSheet($asOfDate): array
    {
        $assets = FinancialAccount::where('account_type', 'asset')->sum('balance');
        $liabilities = FinancialAccount::where('account_type', 'liability')->sum('balance');
        $equity = FinancialAccount::where('account_type', 'equity')->sum('balance');
        
        // Calculate retained earnings
        $netIncome = $this->getIncomeStatement(
            Carbon::parse($asOfDate)->startOfYear(),
            $asOfDate
        )['net_income'];
        
        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'current_assets' => $this->getCurrentAssets($asOfDate),
                'fixed_assets' => $this->getFixedAssets($asOfDate),
                'total_assets' => $assets,
            ],
            'liabilities' => [
                'current_liabilities' => $this->getCurrentLiabilities($asOfDate),
                'long_term_liabilities' => $this->getLongTermLiabilities($asOfDate),
                'total_liabilities' => $liabilities,
            ],
            'equity' => [
                'capital' => $equity,
                'retained_earnings' => $netIncome,
                'total_equity' => $equity + $netIncome,
            ],
            'balance_check' => $assets === ($liabilities + $equity + $netIncome),
        ];
    }
    
    /**
     * Get cash flow statement.
     */
    public function getCashFlowStatement($startDate, $endDate): array
    {
        $operating = Transaction::whereIn('type', ['income', 'expense'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $investing = Transaction::where('category', 'investment')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $financing = Transaction::whereIn('category', ['capital', 'loan'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
        
        $openingBalance = $this->getAccountBalance($startDate);
        $closingBalance = $this->getAccountBalance($endDate);
        
        return [
            'operating_activities' => $operating,
            'investing_activities' => $investing,
            'financing_activities' => $financing,
            'net_cash_flow' => $operating + $investing + $financing,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }
    
    // ==================== HELPER METHODS ====================
    
    private function getCashAccount(): FinancialAccount
    {
        return FinancialAccount::firstOrCreate(
            ['code' => 'CASH'],
            [
                'name' => 'Cash on Hand',
                'account_type' => 'asset',
                'balance' => 0,
            ]
        );
    }
    
    private function getBankAccount(): FinancialAccount
    {
        return FinancialAccount::firstOrCreate(
            ['code' => 'BANK'],
            [
                'name' => 'Bank Account',
                'account_type' => 'asset',
                'balance' => 0,
            ]
        );
    }
    
    private function getAccountForMethod(string $method): FinancialAccount
    {
        return match($method) {
            'cash' => $this->getCashAccount(),
            'bank_transfer', 'cheque' => $this->getBankAccount(),
            default => $this->getCashAccount(),
        };
    }
    
    // Report helper methods (simplified)
    private function getRoomRevenue($start, $end): float
    {
        return Invoice::whereBetween('issue_date', [$start, $end])
            ->whereHas('items', function ($q) {
                $q->where('source_type', 'room');
            })
            ->sum('total');
    }
    
    private function getFoodRevenue($start, $end): float
    {
        return Invoice::whereBetween('issue_date', [$start, $end])
            ->whereHas('items', function ($q) {
                $q->where('source_type', 'food');
            })
            ->sum('total');
    }
    
    private function getStaffCosts($start, $end): float
    {
        return Transaction::where('category', 'payroll')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');
    }
    
    private function getAccountBalance($date): float
    {
        return FinancialAccount::sum('balance');
    }
    
    // Other report methods would be implemented similarly...
}