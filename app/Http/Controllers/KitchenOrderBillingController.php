<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesFolio;
use App\Models\InvoiceItem;
use App\Models\KitchenOrder;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A walk-in order's folio. Orders charged to a room are paid on the stay instead.
 */
class KitchenOrderBillingController extends Controller
{
    use HandlesFolio;

    public function __construct(private BillingService $billing)
    {
    }

    public function storePayment(Request $request, KitchenOrder $order): RedirectResponse
    {
        abort_if($order->charge_to_room, 404);

        return $this->folioPayment($request, $order->currentInvoice() ?? $this->billing->syncKitchenOrderInvoice($order));
    }

    public function voidPayment(Request $request, KitchenOrder $order, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice?->kitchen_order_id === $order->id, 404);

        return $this->folioVoidPayment($request, $payment);
    }

    public function storeCharge(Request $request, KitchenOrder $order): RedirectResponse
    {
        abort_if($order->charge_to_room, 404);

        return $this->folioCharge($request, $order->currentInvoice());
    }

    public function destroyCharge(Request $request, KitchenOrder $order, InvoiceItem $item): RedirectResponse
    {
        abort_unless($item->invoice?->kitchen_order_id === $order->id, 404);

        return $this->folioRemoveCharge($request, $item);
    }

    public function updateDiscount(Request $request, KitchenOrder $order): RedirectResponse
    {
        return $this->folioDiscount($request, $order->currentInvoice());
    }

    public function invoice(KitchenOrder $order): View
    {
        $invoice = $order->currentInvoice() ?? $order->invoices()->latest('id')->firstOrFail();
        $invoice->load(['items', 'payments' => fn ($q) => $q->where('status', 'completed')->orderBy('received_at')]);

        return view('kitchen.print.invoice', compact('order', 'invoice'));
    }

    public function receipt(KitchenOrder $order, Payment $payment): View
    {
        abort_unless($payment->invoice?->kitchen_order_id === $order->id, 404);

        $payment->load(['invoice.items', 'receiver']);

        return view('kitchen.print.receipt', [
            'order' => $order,
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'methods' => Payment::METHODS,
        ]);
    }
}
