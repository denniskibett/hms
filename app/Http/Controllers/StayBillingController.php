<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesFolio;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Stay;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A stay's folio: take payments, void mistakes, extra charges, discounts,
 * print invoice and receipts.
 */
class StayBillingController extends Controller
{
    use HandlesFolio;

    public function __construct(private BillingService $billing)
    {
    }

    public function storePayment(Request $request, Stay $stay): RedirectResponse
    {
        return $this->folioPayment($request, $stay->currentInvoice() ?? $this->billing->syncStayInvoice($stay));
    }

    public function voidPayment(Request $request, Stay $stay, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice?->stay_id === $stay->id, 404);

        return $this->folioVoidPayment($request, $payment);
    }

    public function storeCharge(Request $request, Stay $stay): RedirectResponse
    {
        return $this->folioCharge($request, $this->folioInvoice($stay) ?? $this->billing->syncStayInvoice($stay));
    }

    public function destroyCharge(Request $request, Stay $stay, InvoiceItem $item): RedirectResponse
    {
        abort_unless($item->invoice?->stay_id === $stay->id, 404);

        return $this->folioRemoveCharge($request, $item);
    }

    public function updateDiscount(Request $request, Stay $stay): RedirectResponse
    {
        return $this->folioDiscount($request, $this->folioInvoice($stay));
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
