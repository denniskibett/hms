<?php

namespace App\Services\Payments;

use App\Helpers\SystemHelper;
use Illuminate\Support\Facades\Crypt;

/**
 * Online payment settings (system.settings.payments). Secrets are stored
 * encrypted and never shown back on the settings screen.
 *
 *  sharet: api_key, webhook_secret, base_url, enabled
 *  mpesa:  environment (sandbox|production), consumer_key, consumer_secret,
 *          shortcode, passkey, type (paybill|till), callback_token, enabled
 */
class PaymentGateways
{
    public const LABELS = ['sharet' => 'Sharet Pay', 'mpesa' => 'M-Pesa'];

    public const SECRETS = [
        'sharet' => ['api_key', 'webhook_secret'],
        'mpesa' => ['consumer_key', 'consumer_secret', 'passkey'],
    ];

    public static function config(string $provider): array
    {
        $row = (array) SystemHelper::get("payments.{$provider}", []);
        foreach (self::SECRETS[$provider] ?? [] as $key) {
            if (! empty($row[$key])) {
                try {
                    $row[$key] = Crypt::decryptString($row[$key]);
                } catch (\Throwable) {
                    $row[$key] = null;
                }
            }
        }

        return $row;
    }

    public static function enabled(string $provider): bool
    {
        $c = self::config($provider);

        return match ($provider) {
            'sharet' => ! empty($c['enabled']) && ! empty($c['api_key']),
            'mpesa' => ! empty($c['enabled']) && ! empty($c['consumer_key']) && ! empty($c['consumer_secret']) && ! empty($c['shortcode']) && ! empty($c['passkey']),
            default => false,
        };
    }

    /**
     * Merge form input into the settings array. Blank secret fields keep the saved value.
     */
    public static function merge(array $settings, string $provider, array $input): array
    {
        $current = (array) ($settings['payments'][$provider] ?? []);

        foreach ($input as $key => $value) {
            if (in_array($key, self::SECRETS[$provider] ?? [], true)) {
                if (filled($value)) {
                    $current[$key] = Crypt::encryptString(trim((string) $value));
                }
            } else {
                $current[$key] = is_string($value) ? trim($value) : $value;
            }
        }

        if ($provider === 'mpesa' && empty($current['callback_token'])) {
            $current['callback_token'] = bin2hex(random_bytes(16));
        }

        $settings['payments'][$provider] = $current;

        return $settings;
    }

    public static function hasSecret(string $provider, string $key): bool
    {
        return ! empty(SystemHelper::get("payments.{$provider}.{$key}"));
    }
}
