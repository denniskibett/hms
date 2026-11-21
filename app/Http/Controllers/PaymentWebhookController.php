<?php

namespace App\Http\Controllers;

use App\Services\Payments\OnlinePayments;
use App\Services\Payments\PaymentGateways;
use App\Services\Payments\SharetPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Payment confirmations from Sharet Pay and M-Pesa Daraja. No session or CSRF;
 * each is authenticated on its own (signature / secret URL token + a matching
 * pending request) and settles a payment at most once.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(private OnlinePayments $payments) {}

    /** POST /webhooks/sharet */
    public function sharet(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $secret = PaymentGateways::config('sharet')['webhook_secret'] ?? null;

        if (! SharetPay::verifySignature($request->header('Sharet-Signature'), $raw, $secret)) {
            Log::warning('Sharet webhook rejected: bad or missing signature', ['ip' => $request->ip()]);

            return response()->json(['error' => 'invalid_signature'], 400);
        }

        $event = json_decode($raw, true) ?: [];
        $id = (string) ($event['id'] ?? '');
        $type = (string) ($event['type'] ?? '');
        $data = (array) ($event['data'] ?? []);
        if ($id === '' || ! str_starts_with($type, 'payment_request.')) {
            return response()->json(['received' => true, 'ignored' => true]);
        }
        if (! Cache::add('sharet_event_' . $id, true, now()->addDays(7))) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        $payment = $this->payments->findByExternalId('sharet', (string) ($data['id'] ?? ''));
        if ($payment) {
            match ($data['status'] ?? null) {
                'paid' => $this->payments->settle($payment, $data['transaction_id'] ?? null, ['paid_via' => $data['paid_via'] ?? null]),
                'expired', 'cancelled' => $this->payments->fail($payment, 'Sharet Pay link ' . $data['status'], 'cancelled'),
                default => null,
            };
        }

        return response()->json(['received' => true]);
    }

    /** POST /webhooks/mpesa/{token}: Daraja STK push result. */
    public function mpesa(Request $request, string $token): JsonResponse
    {
        $expected = PaymentGateways::config('mpesa')['callback_token'] ?? null;
        if (! $expected || ! hash_equals($expected, $token)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Rejected'], 403);
        }

        $cb = (array) $request->input('Body.stkCallback', []);
        $checkoutId = (string) ($cb['CheckoutRequestID'] ?? '');
        $payment = $checkoutId !== '' ? $this->payments->findByExternalId('mpesa', $checkoutId) : null;

        if ($payment) {
            if ((string) ($cb['ResultCode'] ?? '') === '0') {
                $items = collect($cb['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
                $this->payments->settle($payment, $items['MpesaReceiptNumber'] ?? null, [
                    'paid_amount' => $items['Amount'] ?? null,
                    'paid_phone' => isset($items['PhoneNumber']) ? (string) $items['PhoneNumber'] : null,
                ]);
            } else {
                $this->payments->fail($payment, (string) ($cb['ResultDesc'] ?? 'Not paid'), (string) ($cb['ResultCode'] ?? '') === '1032' ? 'cancelled' : 'failed');
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
