<?php

namespace App\Http\Controllers;

use App\Exceptions\BillingException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Stay;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * A stay's folio: take payments, void mistakes, extra charges, discounts,
 * print invoice and receipts.
 */
class StayBillingController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    public function storePayment(Request $request, Stay $stay): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:50', 'required_if:method,mobile_money,bank_transfer,cheque'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'payment_reference.required_if' => 'Enter the transaction code (e.g. the M-Pesa code) for this payment method.',
        ]);

        try {
            $invoice = $stay->currentInvoice() ?? $this->billing->syncStayInvoice($stay);
            if (! $invoice) {
                return back()->with('error', 'This booking has no invoice to pay.');
            }

            $payment = $this->billing->recordPayment($invoice, $data, $request->user());
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', "Payment {$payment->payment_reference} recorded.")
            ->with('receipt_url', route('stays.receipt', [$stay, $payment]));
    }

    public function voidPayment(Request $request, Stay $stay, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice?->stay_id === $stay->id, 404);

        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? null;

        try {
            $this->billing->voidPayment($payment, $request->user(), $reason);
        } catch (BillingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Payment {$payment->payment_reference} voided.");
    }

    public function storeCharge(Request $request, Stay $stay): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'unit_price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ]);

        try {
            $invoice = $this->folioInvoice($stay) ?? $this->billing->syncStayInvoice($stay);
            if (! $invoice) {
                return back()->with('error', 'This booking has no invoice to charge.');
            }

            $this->billing->addCharge($invoice, $data['description'], (float) $data['quantity'], (float) $data['unit_price'], $request->user());
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Charge \"{$data['description']}\" added.");
    }

    public function destroyCharge(Request $request, Stay $stay, InvoiceItem $item): RedirectResponse
    {
        abort_unless($item->invoice?->stay_id === $stay->id, 404);

        try {
            $this->billing->removeCharge($item, $request->user());
        } catch (BillingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Charge \"{$item->description}\" removed.");
    }

    public function updateDiscount(Request $request, Stay $stay): RedirectResponse
    {
        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required_unless:discount_amount,0', 'nullable', 'string', 'max:255'],
        ], [
            'reason.required_unless' => 'Give a reason for the discount.',
        ]);

        $invoice = $this->folioInvoice($stay);
        if (! $invoice) {
            return back()->with('error', 'This booking has no invoice to discount.');
        }

        try {
            $this->billing->setDiscount($invoice, (float) $data['discount_amount'], $data['reason'] ?? null, $request->user());
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', (float) $data['discount_amount'] > 0 ? 'Discount applied.' : 'Discount removed.');
    }

    public function invoice(Stay $stay): View
    {
        $invoice = $stay->invoices()->where('status', '!=', 'cancelled')->oldest('id')->first()
            ?? $stay->invoices()->latest('id')->firstOrFail();

        $invoice->load(['items', 'payments' => fn ($q) => $q->where('status', 'completed')->orderBy('received_at')]);
        $stay->load('guest.guestProfile');

        return view('stays.print.invoice', compact('stay', 'invoice'));
    }

    public function receipt(Stay $stay, Payment $payment): View
    {
        abort_unless($payment->invoice?->stay_id === $stay->id, 404);

        $payment->load(['invoice', 'receiver']);
        $stay->load('guest.guestProfile', 'roomAllocations.room');

        return view('stays.print.receipt', [
            'stay' => $stay,
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'methods' => Payment::METHODS,
        ]);
    }

    /**
     * The stay's live invoice (the one the folio shows).
     */
    private function folioInvoice(Stay $stay): ?Invoice
    {
        return $stay->invoices()->where('status', '!=', 'cancelled')->oldest('id')->first();
    }
}
