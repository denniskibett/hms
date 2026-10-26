<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\BillingException;
use App\Models\GuestMessage;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Folio actions shared by stays and events: take and void payments, add and
 * remove extra charges, set a discount. The controller finds the invoice and
 * checks it belongs to the stay / event; BillingService does the work.
 *
 * Expects $this->billing (BillingService).
 */
trait HandlesFolio
{
    protected function folioPayment(Request $request, ?Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:50', 'required_if:method,mobile_money,bank_transfer,cheque'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'payment_reference.required_if' => 'Enter the transaction code (e.g. the M-Pesa code) for this payment method.',
        ]);

        if (! $invoice) {
            return back()->with('error', 'This booking has no invoice to pay.');
        }

        try {
            $payment = $this->billing->recordPayment($invoice, $data, $request->user());
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        GuestMessage::forPayment($payment, $request->user());

        return back()
            ->with('success', "Payment {$payment->payment_reference} recorded.")
            ->with('receipt_url', $invoice->receiptUrl($payment));
    }

    protected function folioVoidPayment(Request $request, Payment $payment): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? null;

        try {
            $this->billing->voidPayment($payment, $request->user(), $reason);
        } catch (BillingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Payment {$payment->payment_reference} voided.");
    }

    protected function folioCharge(Request $request, ?Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'unit_price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ]);

        if (! $invoice) {
            return back()->with('error', 'This booking has no invoice to charge.');
        }

        try {
            $this->billing->addCharge($invoice, $data['description'], (float) $data['quantity'], (float) $data['unit_price'], $request->user());
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Charge \"{$data['description']}\" added.");
    }

    protected function folioRemoveCharge(Request $request, InvoiceItem $item): RedirectResponse
    {
        try {
            $this->billing->removeCharge($item, $request->user());
        } catch (BillingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Charge \"{$item->description}\" removed.");
    }

    protected function folioDiscount(Request $request, ?Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required_unless:discount_amount,0', 'nullable', 'string', 'max:255'],
        ], [
            'reason.required_unless' => 'Give a reason for the discount.',
        ]);

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
}
