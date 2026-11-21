<?php

namespace App\Services\Payments;

use App\Helpers\SystemHelper;
use App\Models\Invoice;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sharet Pay (Sharet Africa API v1): pay links the guest opens to pay by
 * M-Pesa or card. Auth "Authorization: Bearer sk_…"; webhooks are signed
 * "Sharet-Signature: t=<unix>,v1=<hex HMAC-SHA256(secret, "t.body")>".
 */
class SharetPay
{
    public const DEFAULT_BASE_URL = 'https://sharet.africa/api/v1';

    /**
     * @return array{external_id: string, pay_url: ?string, expires_at: ?string}
     */
    public function createPayLink(Invoice $invoice, float $amount, string $reference, ?string $phone = null): array
    {
        $res = $this->http()
            ->withHeaders(['Idempotency-Key' => $reference])
            ->post($this->url('/payment-requests'), array_filter([
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => SystemHelper::currencyCode(),
                'channels' => ['mpesa', 'card', 'sharet'],
                'reference' => mb_substr(SystemHelper::appName() . ' ' . $invoice->invoice_number, 0, 140),
                'payer' => $phone ? ['phone' => $phone] : null,
                'expires_in_minutes' => 60 * 24 * 3,
                'metadata' => ['hms_invoice' => (string) $invoice->id, 'hms_payment' => $reference],
            ]));

        if (! $res->successful()) {
            Log::error('Sharet Pay link failed', ['invoice_id' => $invoice->id, 'status' => $res->status(), 'body' => $res->json()]);
            throw new \RuntimeException('Sharet Pay could not create the link: ' . ($res->json('detail') ?? $res->json('message') ?? 'error ' . $res->status()));
        }

        return [
            'external_id' => (string) $res->json('id'),
            'pay_url' => $res->json('pay_url'),
            'expires_at' => $res->json('expires_at'),
        ];
    }

    public function fetch(string $id): ?array
    {
        $res = $this->http()->get($this->url('/payment-requests/' . urlencode($id)));

        return $res->successful() ? $res->json() : null;
    }

    public static function verifySignature(?string $header, string $payload, ?string $secret, int $tolerance = 300): bool
    {
        if (! $header || ! $secret) {
            return false;
        }
        $t = null;
        $signatures = [];
        foreach (explode(',', $header) as $piece) {
            [$k, $v] = array_pad(explode('=', trim($piece), 2), 2, null);
            if ($k === 't') {
                $t = $v;
            } elseif ($k === 'v1' && $v) {
                $signatures[] = $v;
            }
        }
        if (! $t || ! ctype_digit($t) || abs(time() - (int) $t) > $tolerance) {
            return false;
        }
        $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }

        return false;
    }

    private function http(): PendingRequest
    {
        return Http::withToken((string) (PaymentGateways::config('sharet')['api_key'] ?? ''))
            ->acceptJson()->asJson()->timeout(20);
    }

    private function url(string $path): string
    {
        return rtrim(PaymentGateways::config('sharet')['base_url'] ?? '' ?: self::DEFAULT_BASE_URL, '/') . $path;
    }
}
