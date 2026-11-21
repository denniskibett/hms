<?php

namespace App\Services\Payments;

use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CoreService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Ask a guest to pay an invoice online, and settle it when the provider confirms.
 *
 * Each request is a *pending* row in `payments` (payment_details.provider,
 * .external_id, .pay_url, .phone). It doesn't count towards the balance until
 * the Sharet Pay webhook or the M-Pesa callback marks it completed, exactly once.
 */
class OnlinePayments
{
    public function __construct(
        private BillingService $billing,
        private CoreService $core,
        private SharetPay $sharet,
        private MpesaDaraja $mpesa,
    ) {}

    /**
     * Send an M-Pesa prompt to the phone, or create a Sharet Pay link.
     */
    public function request(Invoice $invoice, string $provider, User $by, ?float $amount = null, ?string $phone = null): Payment
    {
        if (! PaymentGateways::enabled($provider)) {
            throw new BillingException((PaymentGateways::LABELS[$provider] ?? $provider) . ' is not set up. Add its keys under Settings › Payments.');
        }
        if ($invoice->status === 'cancelled') {
            throw new BillingException("Invoice {$invoice->invoice_number} is cancelled.");
        }

        $balance = round((float) $invoice->total - (float) $invoice->paid_amount, 2);
        $amount = round($amount ?? $balance, 2);
        if ($amount <= 0 || $amount > $balance) {
            throw new BillingException('Enter an amount between 1 and the balance of ' . SystemHelper::currencySymbol() . ' ' . number_format($balance, 2) . '.');
        }
        if ($provider === 'mpesa') {
            $phone = MpesaDaraja::msisdn((string) $phone) ?? throw new BillingException('Enter the M-Pesa phone number, e.g. 0712 345 678.');
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_reference' => 'ONL-' . strtoupper($provider) . '-' . now()->format('ymdHis') . '-' . strtoupper(Str::random(4)),
            'amount' => $amount,
            'status' => 'pending',
            'method' => $provider === 'sharet' ? 'credit_card' : 'mobile_money',
            'payment_details' => ['provider' => $provider, 'phone' => $phone],
            'received_by' => $by->id,
            'received_at' => now(),
            'notes' => 'Online payment requested by ' . $by->name,
        ]);

        try {
            $details = $provider === 'sharet'
                ? $this->sharet->createPayLink($invoice, $amount, $payment->payment_reference, $phone)
                : $this->mpesa->stkPush($phone, $amount, $invoice->invoice_number, $payment->payment_reference);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'payment_details' => $payment->payment_details + ['error' => $e->getMessage()]]);
            throw new BillingException($e->getMessage());
        }

        $payment->update(['payment_details' => $payment->payment_details + $details]);
        $this->core->log($by->id, 'online_payment_requested', "{$provider} request {$payment->payment_reference} for {$amount} on {$invoice->invoice_number}", ['payment_id' => $payment->id]);

        return $payment->fresh();
    }

    public function findByExternalId(string $provider, string $externalId): ?Payment
    {
        return Payment::where('status', 'pending')
            ->where('payment_details->provider', $provider)
            ->where('payment_details->external_id', $externalId)
            ->first()
            ?? Payment::where('payment_details->provider', $provider)
                ->where('payment_details->external_id', $externalId)
                ->first();
    }

    /**
     * The provider says it's paid: mark completed (once) and update the invoice.
     */
    public function settle(Payment $payment, ?string $receipt, array $extra = []): Payment
    {
        return DB::transaction(function () use ($payment, $receipt, $extra) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->status !== 'pending') {
                return $payment;   // already settled or voided (duplicate callback)
            }

            $reference = $receipt && ! Payment::where('payment_reference', $receipt)->exists() ? $receipt : $payment->payment_reference;
            $payment->update([
                'status' => 'completed',
                'payment_reference' => $reference,
                'received_at' => now(),
                'payment_details' => ($payment->payment_details ?? []) + $extra + ['receipt' => $receipt, 'paid_at' => now()->toIso8601String()],
            ]);

            $this->billing->recalculate($payment->invoice);
            $this->core->log($payment->received_by, 'payment_received', "Online payment {$reference} of {$payment->amount} on {$payment->invoice->invoice_number}", ['payment_id' => $payment->id]);

            return $payment;
        });
    }

    public function fail(Payment $payment, string $reason, string $status = 'failed'): void
    {
        if ($payment->status !== 'pending') {
            return;
        }
        $payment->update([
            'status' => $status === 'cancelled' ? 'cancelled' : 'failed',
            'payment_details' => ($payment->payment_details ?? []) + ['error' => $reason],
        ]);
        Log::info('Online payment not completed', ['payment_id' => $payment->id, 'reason' => $reason]);
    }
}
