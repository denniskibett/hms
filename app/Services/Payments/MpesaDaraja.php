<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Safaricom M-Pesa Daraja: STK push (Lipa na M-Pesa Online) to the guest's
 * phone. The result comes back to /webhooks/mpesa/{callback_token}; the
 * token is random per install, and the CheckoutRequestID must match a
 * pending request of ours, so a forged call can't settle anything.
 */
class MpesaDaraja
{
    /** 0712345678 / +254712345678 / 712345678 → 254712345678 */
    public static function msisdn(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $digits = match (true) {
            str_starts_with($digits, '254') => $digits,
            str_starts_with($digits, '0') => '254' . substr($digits, 1),
            strlen($digits) === 9 => '254' . $digits,
            default => $digits,
        };

        return preg_match('/^254[17]\d{8}$/', $digits) ? $digits : null;
    }

    /**
     * @return array{external_id: string, merchant_request_id: ?string}
     */
    public function stkPush(string $msisdn, float $amount, string $accountReference, string $ourReference): array
    {
        $c = PaymentGateways::config('mpesa');
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $till = ($c['type'] ?? 'paybill') === 'till';

        $res = Http::withToken($this->token())->acceptJson()->asJson()->timeout(30)
            ->post($this->base() . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $c['shortcode'],
                'Password' => base64_encode($c['shortcode'] . $c['passkey'] . $timestamp),
                'Timestamp' => $timestamp,
                'TransactionType' => $till ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
                'Amount' => (int) ceil($amount),
                'PartyA' => $msisdn,
                'PartyB' => $till && ! empty($c['till_number']) ? $c['till_number'] : $c['shortcode'],
                'PhoneNumber' => $msisdn,
                'CallBackURL' => route('webhooks.mpesa', ['token' => $c['callback_token'] ?? 'unset']),
                'AccountReference' => mb_substr($accountReference, 0, 12),
                'TransactionDesc' => mb_substr('Hotel bill ' . $ourReference, 0, 13),
            ]);

        if (! $res->successful() || (string) $res->json('ResponseCode') !== '0') {
            Log::error('M-Pesa STK push failed', ['status' => $res->status(), 'body' => $res->json()]);
            throw new \RuntimeException('M-Pesa did not send the prompt: ' . ($res->json('errorMessage') ?? $res->json('ResponseDescription') ?? 'error ' . $res->status()));
        }

        return [
            'external_id' => (string) $res->json('CheckoutRequestID'),
            'merchant_request_id' => $res->json('MerchantRequestID'),
        ];
    }

    /**
     * Ask Daraja what happened to a prompt (for callbacks that never arrive).
     */
    public function query(string $checkoutRequestId): ?array
    {
        $c = PaymentGateways::config('mpesa');
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $res = Http::withToken($this->token())->acceptJson()->asJson()->timeout(30)
            ->post($this->base() . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $c['shortcode'],
                'Password' => base64_encode($c['shortcode'] . $c['passkey'] . $timestamp),
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        return $res->successful() ? $res->json() : null;
    }

    private function token(): string
    {
        $c = PaymentGateways::config('mpesa');

        return Cache::remember('mpesa_token_' . md5(($c['consumer_key'] ?? '') . $this->base()), now()->addMinutes(50), function () use ($c) {
            $res = Http::withBasicAuth((string) $c['consumer_key'], (string) $c['consumer_secret'])->timeout(20)
                ->get($this->base() . '/oauth/v1/generate', ['grant_type' => 'client_credentials']);
            if (! $res->successful() || ! $res->json('access_token')) {
                throw new \RuntimeException('M-Pesa sign-in failed. Check the consumer key and secret.');
            }

            return $res->json('access_token');
        });
    }

    private function base(): string
    {
        return (PaymentGateways::config('mpesa')['environment'] ?? 'sandbox') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }
}
