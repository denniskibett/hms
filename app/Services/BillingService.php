<?php

namespace App\Services;

use App\Enums\FacilityBookingStatus;
use App\Enums\KitchenOrderStatus;
use App\Enums\StayStatus;
use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\FacilityBooking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KitchenOrder;
use App\Models\Payment;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns invoices and payments.
 *
 * - Each stay has one invoice, created at booking. Its room, service and food
 *   (room-charged restaurant orders) lines are rebuilt from the stay whenever it
 *   changes; manual charges are left alone.
 * - Walk-in restaurant orders have their own invoice, paid at the counter.
 * - Each event (facility booking) has its own invoice the same way: its facility
 *   lines are rebuilt from the event's items.
 * - Totals are always recalculated from lines and completed payments — never
 *   adjusted incrementally — so they can't drift.
 * - Taxes (VAT + Tourism Levy) use the rates stored on the invoice when it was
 *   created. With tax-inclusive prices the guest pays the line total and the
 *   invoice shows how much of it is tax.
 */
class BillingService
{
    private const SYNCED_SOURCES = ['room', 'service', 'food'];

    public function __construct(private CoreService $core)
    {
    }

    /**
     * Create or refresh the invoice for a stay. Returns null when a cancelled
     * stay never had an invoice.
     */
    public function syncStayInvoice(Stay $stay): ?Invoice
    {
        return DB::transaction(function () use ($stay) {
            $invoice = $stay->invoices()->where('status', '!=', 'cancelled')->lockForUpdate()->oldest('id')->first();

            if ($stay->state === StayStatus::Cancelled) {
                if ($invoice && $this->paidTotal($invoice) <= 0) {
                    $invoice->update(['status' => 'cancelled']);
                }

                return $invoice;
            }

            $invoice ??= $this->newInvoice(['stay_id' => $stay->id], $stay->departure_date);

            $invoice->items()->whereIn('source_type', self::SYNCED_SOURCES)->delete();

            $stay->loadMissing('roomAllocations.room.roomType', 'services');

            foreach ($stay->roomAllocations as $allocation) {
                $room = $allocation->room;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_type' => 'room',
                    'reference_id' => $allocation->id,
                    'description' => sprintf(
                        'Room %s%s, %s, %s → %s',
                        $room->room_number ?? '?',
                        $room?->roomType ? ' (' . $room->roomType->name . ')' : '',
                        $stay->meal_plan->value,
                        $allocation->from_date->format('d M'),
                        $allocation->to_date->format('d M'),
                    ),
                    'quantity' => $allocation->nights,
                    'unit_price' => $allocation->rate_applied,
                ]);
            }

            foreach ($stay->services as $service) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_type' => 'service',
                    'reference_id' => $service->id,
                    'description' => $service->name,
                    'quantity' => $service->pivot->quantity,
                    'unit_price' => $service->pivot->price,
                ]);
            }

            $orders = $stay->kitchenOrders()
                ->with('items')
                ->where('charge_to_room', true)
                ->where('status', '!=', KitchenOrderStatus::Cancelled->value)
                ->orderBy('id')
                ->get();

            foreach ($orders as $order) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_type' => 'food',
                    'reference_id' => $order->id,
                    'description' => "{$order->type_label} {$order->order_number}, " . $order->created_at->format('d M H:i'),
                    'quantity' => 1,
                    'unit_price' => $order->total,
                ]);
            }

            $invoice->update(['due_date' => $stay->departure_date]);

            return $this->recalculate($invoice);
        });
    }

    /**
     * Create or refresh the invoice for an event. A cancelled event's unpaid
     * invoice is cancelled with it. Refuses changes that would leave the client
     * overpaid (the caller's transaction rolls back).
     */
    public function syncFacilityBookingInvoice(FacilityBooking $booking): ?Invoice
    {
        return DB::transaction(function () use ($booking) {
            $invoice = $booking->invoices()->where('status', '!=', 'cancelled')->lockForUpdate()->oldest('id')->first();

            if ($booking->status === FacilityBookingStatus::Cancelled) {
                if ($invoice && $this->paidTotal($invoice) <= 0) {
                    $invoice->update(['status' => 'cancelled']);
                }

                return $invoice;
            }

            $invoice ??= $this->newInvoice(['facility_booking_id' => $booking->id], $booking->end_date);

            $invoice->items()->where('source_type', 'facility')->delete();

            foreach ($booking->items()->get() as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_type' => 'facility',
                    'reference_id' => $item->id,
                    'description' => "{$item->description} ({$item->quantity_label})",
                    'quantity' => round((float) $item->quantity * $item->days, 2),
                    'unit_price' => $item->unit_price,
                ]);
            }

            $invoice->update(['due_date' => $booking->end_date]);
            $invoice = $this->recalculate($invoice);

            if ((float) $invoice->total < $this->paidTotal($invoice)) {
                throw new BillingException('This change would leave the client overpaid. Void a payment first.');
            }

            return $invoice;
        });
    }

    /**
     * Create or refresh a walk-in order's invoice (orders charged to a room go on
     * the stay's invoice instead). A cancelled order's unpaid invoice is cancelled.
     */
    public function syncKitchenOrderInvoice(KitchenOrder $order): ?Invoice
    {
        return DB::transaction(function () use ($order) {
            $invoice = $order->invoices()->where('status', '!=', 'cancelled')->lockForUpdate()->oldest('id')->first();

            if ($order->status === KitchenOrderStatus::Cancelled) {
                if ($invoice && $this->paidTotal($invoice) <= 0) {
                    $invoice->update(['status' => 'cancelled']);
                }

                return $invoice;
            }

            $invoice ??= $this->newInvoice(['kitchen_order_id' => $order->id], today());

            $invoice->items()->where('source_type', 'food')->delete();

            foreach ($order->items()->with('variant.menuItem')->get() as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_type' => 'food',
                    'reference_id' => $item->id,
                    'description' => $item->variant?->full_name ?? 'Menu item',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price_at_order,
                ]);
            }

            return $this->recalculate($invoice);
        });
    }

    /**
     * Recompute subtotal, taxes, total, paid amount and status from the lines
     * and completed payments.
     */
    public function recalculate(Invoice $invoice): Invoice
    {
        $gross = (float) $invoice->items()->get()->sum(fn (InvoiceItem $i) => (float) $i->quantity * (float) $i->unit_price);
        $discount = (float) $invoice->discount_amount;
        $vatRate = (float) $invoice->vat_rate;
        $levyRate = (float) $invoice->levy_rate;
        $taxRate = ($vatRate + $levyRate) / 100;

        if ($invoice->tax_inclusive) {
            // Prices already include tax: pull the tax out of the total
            $total = round(max($gross - $discount, 0), 2);
            $net = $taxRate > 0 ? $total / (1 + $taxRate) : $total;
            $vat = round($net * $vatRate / 100, 2);
            $levy = round($net * $levyRate / 100, 2);
            $subtotal = round($total - $vat - $levy, 2);
        } else {
            // Prices exclude tax: add it on top
            $subtotal = round(max($gross - $discount, 0), 2);
            $vat = round($subtotal * $vatRate / 100, 2);
            $levy = round($subtotal * $levyRate / 100, 2);
            $total = round($subtotal + $vat + $levy, 2);
        }

        $paid = $this->paidTotal($invoice);

        $status = match (true) {
            $invoice->status === 'cancelled' => 'cancelled',
            $total > 0 && $paid >= $total => 'paid',
            $paid > 0 => 'partial',
            default => 'sent',
        };

        $invoice->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'levy_amount' => $levy,
            'tax_amount' => round($vat + $levy, 2),
            'total' => $total,
            'paid_amount' => $paid,
            'status' => $status,
        ]);

        return $invoice->fresh(['items', 'payments']);
    }

    /**
     * Record money received. The invoice row is locked so two cashiers can't
     * both take the last balance.
     *
     * $data: amount, method, payment_reference (e.g. M-Pesa code, optional), received_at (optional), notes (optional)
     */
    public function recordPayment(Invoice $invoice, array $data, User $by): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $by) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $amount = round((float) $data['amount'], 2);

            if ($invoice->status === 'cancelled') {
                throw new BillingException("Invoice {$invoice->invoice_number} is cancelled.");
            }
            if ($amount <= 0) {
                throw new BillingException('Enter an amount greater than zero.');
            }

            $balance = round((float) $invoice->total - $this->paidTotal($invoice), 2);
            if ($amount > $balance) {
                throw new BillingException('That is more than the balance of ' . SystemHelper::currencySymbol() . ' ' . number_format($balance, 2) . '.');
            }

            $reference = trim((string) ($data['payment_reference'] ?? ''));
            if ($reference !== '' && Payment::where('payment_reference', $reference)->exists()) {
                throw new BillingException("Payment reference {$reference} has already been used.");
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_reference' => $reference !== '' ? $reference : 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'amount' => $amount,
                'status' => 'completed',
                'method' => $data['method'],
                'received_by' => $by->id,
                'received_at' => $data['received_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculate($invoice);
            $this->core->log($by->id, 'payment_received', "Payment {$payment->payment_reference} of {$amount} on {$invoice->invoice_number}", ['payment_id' => $payment->id]);

            return $payment;
        });
    }

    /**
     * Void a payment taken by mistake (it stays on record, marked cancelled).
     */
    public function voidPayment(Payment $payment, User $by, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $by, $reason) {
            if ($payment->status !== 'completed') {
                throw new BillingException("Payment {$payment->payment_reference} is not a completed payment.");
            }

            Invoice::whereKey($payment->invoice_id)->lockForUpdate()->first();

            $payment->update([
                'status' => 'cancelled',
                'notes' => trim(($payment->notes ? $payment->notes . "\n" : '') . 'Voided by ' . $by->name . ' on ' . now()->format('d M Y H:i') . ($reason ? ": {$reason}" : '')),
            ]);

            $this->recalculate($payment->invoice);
            $this->core->log($by->id, 'payment_voided', "Payment {$payment->payment_reference} voided" . ($reason ? ": {$reason}" : ''), ['payment_id' => $payment->id]);

            return $payment->fresh();
        });
    }

    /**
     * Add an extra charge (laundry, minibar, damages…) to an invoice.
     */
    public function addCharge(Invoice $invoice, string $description, float $quantity, float $unitPrice, User $by): InvoiceItem
    {
        return DB::transaction(function () use ($invoice, $description, $quantity, $unitPrice, $by) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'cancelled') {
                throw new BillingException("Invoice {$invoice->invoice_number} is cancelled.");
            }

            $item = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'source_type' => 'other',
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);

            $this->recalculate($invoice);
            $this->core->log($by->id, 'invoice_charge_added', "Charge \"{$description}\" (" . round($quantity * $unitPrice, 2) . ") on {$invoice->invoice_number}", ['invoice_item_id' => $item->id]);

            return $item;
        });
    }

    /**
     * Remove an extra charge. Room and service lines come from the booking,
     * so they change by editing the booking instead.
     */
    public function removeCharge(InvoiceItem $item, User $by): Invoice
    {
        return DB::transaction(function () use ($item, $by) {
            $invoice = Invoice::whereKey($item->invoice_id)->lockForUpdate()->firstOrFail();

            if ($item->source_type !== 'other') {
                throw new BillingException('Room, service, food and event lines follow their booking or order. Change it there instead.');
            }

            $lineTotal = round((float) $item->quantity * (float) $item->unit_price, 2);
            if (round((float) $invoice->total - $lineTotal, 2) < $this->paidTotal($invoice)) {
                throw new BillingException('Removing this charge would leave the guest overpaid. Void a payment first.');
            }

            $item->delete();
            $this->core->log($by->id, 'invoice_charge_removed', "Charge \"{$item->description}\" removed from {$invoice->invoice_number}", ['invoice_id' => $invoice->id]);

            return $this->recalculate($invoice);
        });
    }

    /**
     * Set the invoice discount (an amount off the tax-inclusive total). 0 removes it.
     */
    public function setDiscount(Invoice $invoice, float $amount, ?string $reason, User $by): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount, $reason, $by) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $amount = round($amount, 2);

            if ($invoice->status === 'cancelled') {
                throw new BillingException("Invoice {$invoice->invoice_number} is cancelled.");
            }

            $gross = round((float) $invoice->items()->get()->sum(fn (InvoiceItem $i) => (float) $i->quantity * (float) $i->unit_price), 2);
            if ($amount < 0 || $amount > $gross) {
                throw new BillingException('The discount must be between 0 and the charges of ' . SystemHelper::currencySymbol() . ' ' . number_format($gross, 2) . '.');
            }

            $invoice->update(['discount_amount' => $amount]);
            $updated = $this->recalculate($invoice);

            // Throwing rolls the discount back
            if ((float) $updated->total < $this->paidTotal($updated)) {
                throw new BillingException('That discount would leave the guest overpaid. Void a payment first.');
            }

            $note = 'Discount ' . number_format($amount, 2) . ' set by ' . $by->name . ' on ' . now()->format('d M Y H:i') . ($reason ? ": {$reason}" : '');
            $updated->update(['notes' => trim(($updated->notes ? $updated->notes . "\n" : '') . $note)]);
            $this->core->log($by->id, 'invoice_discount', "{$invoice->invoice_number}: {$note}", ['invoice_id' => $invoice->id]);

            return $updated->fresh(['items', 'payments']);
        });
    }

    private function paidTotal(Invoice $invoice): float
    {
        return round((float) $invoice->payments()->where('status', 'completed')->sum('amount'), 2);
    }

    /**
     * @param  array  $owner  ['stay_id' => …] or ['facility_booking_id' => …]
     */
    private function newInvoice(array $owner, $dueDate): Invoice
    {
        $invoice = Invoice::create($owner + [
            'invoice_number' => 'TMP-' . Str::uuid(),
            'status' => 'sent',
            'issue_date' => today(),
            'due_date' => $dueDate,
            'tax_inclusive' => SystemHelper::pricesIncludeTax(),
            'vat_rate' => SystemHelper::vatPercent(),
            'levy_rate' => SystemHelper::tourismLevyPercent(),
        ]);

        // Number from the id: unique without racing on "last number + 1"
        $invoice->update(['invoice_number' => sprintf('INV-%s-%05d', now()->format('Y'), $invoice->id)]);

        return $invoice;
    }
}
