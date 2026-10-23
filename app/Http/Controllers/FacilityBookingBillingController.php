<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesFolio;
use App\Models\FacilityBooking;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * An event's folio: the same actions as a stay's, on the event's invoice.
 */
class FacilityBookingBillingController extends Controller
{
    use HandlesFolio;

    public function __construct(private BillingService $billing)
    {
    }

    public function storePayment(Request $request, FacilityBooking $event): RedirectResponse
    {
        return $this->folioPayment($request, $event->currentInvoice() ?? $this->billing->syncFacilityBookingInvoice($event));
    }

    public function voidPayment(Request $request, FacilityBooking $event, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice?->facility_booking_id === $event->id, 404);

        return $this->folioVoidPayment($request, $payment);
    }

    public function storeCharge(Request $request, FacilityBooking $event): RedirectResponse
    {
        return $this->folioCharge($request, $event->currentInvoice() ?? $this->billing->syncFacilityBookingInvoice($event));
    }

    public function destroyCharge(Request $request, FacilityBooking $event, InvoiceItem $item): RedirectResponse
    {
        abort_unless($item->invoice?->facility_booking_id === $event->id, 404);

        return $this->folioRemoveCharge($request, $item);
    }

    public function updateDiscount(Request $request, FacilityBooking $event): RedirectResponse
    {
        return $this->folioDiscount($request, $event->currentInvoice());
    }

    public function invoice(FacilityBooking $event): View
    {
        $invoice = $event->currentInvoice() ?? $event->invoices()->latest('id')->firstOrFail();

        $invoice->load(['items', 'payments' => fn ($q) => $q->where('status', 'completed')->orderBy('received_at')]);
        $event->load('facility');

        return view('events.print.invoice', compact('event', 'invoice'));
    }

    public function receipt(FacilityBooking $event, Payment $payment): View
    {
        abort_unless($payment->invoice?->facility_booking_id === $event->id, 404);

        $payment->load(['invoice', 'receiver']);
        $event->load('facility');

        return view('events.print.receipt', [
            'event' => $event,
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'methods' => Payment::METHODS,
        ]);
    }
}
