<?php

namespace App\Models;

use App\Helpers\SystemHelper;
use App\Mail\GuestMessageMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

use function Illuminate\Support\defer;

/**
 * An SMS or email to a guest. Automatic messages come from TEMPLATES (wording
 * editable under settings messages.templates.<key>, on/off under
 * messages.auto.<key>); staff can also type their own.
 *
 * Messages are saved first, then delivered after the page has been sent so a
 * slow SMS gateway never holds up the front desk.
 */
class GuestMessage extends Model
{
    public const CHANNELS = ['sms' => 'SMS', 'email' => 'Email'];

    public const STATUSES = [
        'pending' => 'Sending',
        'sent' => 'Sent',
        'failed' => 'Failed',
        'test' => 'Test mode',
    ];

    // Placeholders: {name} {hotel} {hotel_phone} {reference} {arrival} {departure}
    // {nights} {total} {room} {reason} {amount} {method} {payment_ref} {balance}
    public const TEMPLATES = [
        'request_received' => [
            'label' => 'Website request received',
            'subject' => 'We have your booking request',
            'body' => 'Hi {name}, thank you for your booking request {reference} at {hotel} for {arrival} to {departure}. We will call you shortly to confirm.',
        ],
        'request_declined' => [
            'label' => 'Website request declined',
            'subject' => 'About your booking request',
            'body' => 'Hi {name}, sorry, we cannot take booking request {reference} for {arrival} to {departure}: {reason}. Call us on {hotel_phone}.',
        ],
        'booking_confirmed' => [
            'label' => 'Booking confirmed',
            'subject' => 'Your booking is confirmed',
            'body' => 'Hi {name}, your booking {reference} at {hotel} is confirmed: {arrival} to {departure}, {nights}. Total {total}. See you soon!',
        ],
        'checked_in' => [
            'label' => 'Welcome at check-in',
            'subject' => 'Welcome to {hotel}',
            'body' => 'Welcome to {hotel}, {name}! You are in room {room}. For anything you need, call reception on {hotel_phone}.',
        ],
        'payment_received' => [
            'label' => 'Payment received',
            'subject' => 'Payment received',
            'body' => 'Hi {name}, we have received {amount} ({method}, ref {payment_ref}) for {reference}. Balance now {balance}. Thank you, {hotel}.',
        ],
    ];

    protected $fillable = [
        'guest_id',
        'stay_id',
        'booking_request_id',
        'channel',
        'recipient',
        'template',
        'subject',
        'body',
        'status',
        'provider',
        'provider_ref',
        'error',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function bookingRequest()
    {
        return $this->belongsTo(BookingRequest::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    */

    public static function templateBody(string $template): string
    {
        return SystemHelper::get("messages.templates.{$template}") ?: self::TEMPLATES[$template]['body'];
    }

    public static function autoSends(string $template): bool
    {
        return (bool) SystemHelper::get("messages.auto.{$template}", true);
    }

    public static function render(string $text, array $values): string
    {
        $values += [
            'hotel' => SystemHelper::appName(),
            'hotel_phone' => SystemHelper::contactPhone(),
        ];

        return trim(strtr($text, collect($values)->mapWithKeys(fn ($v, $k) => ['{' . $k . '}' => (string) $v])->all()));
    }

    /**
     * Send one automatic message by SMS (if there's a phone) and email (if
     * there's an address), unless that message is switched off.
     *
     * $to: name, phone, email; $links: guest_id, stay_id, booking_request_id
     */
    public static function notify(string $template, array $to, array $values, array $links = [], ?User $by = null): void
    {
        if (! self::autoSends($template)) {
            return;
        }

        $values['name'] = Str::before(trim($to['name'] ?? ''), ' ') ?: 'Guest';
        $body = self::render(self::templateBody($template), $values);
        $attributes = $links + ['template' => $template, 'sent_by' => $by?->id];

        if ($phone = self::normalisePhone($to['phone'] ?? null)) {
            self::queue('sms', $phone, $body, $attributes);
        }
        if (filter_var($to['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
            self::queue('email', $to['email'], $body, $attributes + [
                'subject' => self::render(self::TEMPLATES[$template]['subject'], $values),
            ]);
        }
    }

    public static function forBookingRequest(BookingRequest $request, string $template, array $values = [], ?User $by = null): void
    {
        self::notify($template, $request->only('name', 'phone', 'email'), $values + [
            'reference' => $request->reference,
            'arrival' => $request->arrival_date->format('j M'),
            'departure' => $request->departure_date->format('j M Y'),
        ], ['booking_request_id' => $request->id], $by);
    }

    public static function forStay(Stay $stay, string $template, array $values = [], ?User $by = null): void
    {
        $stay->loadMissing('guest', 'roomAllocations.room');

        if (! $stay->guest) {
            return;
        }

        self::notify($template, $stay->guest->only('name', 'phone', 'email'), $values + [
            'reference' => "#{$stay->id}",
            'arrival' => $stay->arrival_date->format('j M'),
            'departure' => $stay->departure_date->format('j M Y'),
            'nights' => $stay->nights . ' ' . Str::plural('night', $stay->nights),
            'total' => SystemHelper::currencySymbol() . ' ' . number_format($stay->currentInvoice()?->total ?? $stay->total_amount, 2),
            'room' => $stay->roomAllocations->pluck('room.room_number')->filter()->join(', ') ?: '-',
        ], ['guest_id' => $stay->guest_id, 'stay_id' => $stay->id], $by);
    }

    /**
     * Receipt by SMS/email for a payment on a stay or event invoice.
     * Restaurant walk-ins have no contact details, so get nothing.
     */
    public static function forPayment(Payment $payment, ?User $by = null): void
    {
        $invoice = $payment->invoice()->with('stay.guest', 'facilityBooking')->first();
        $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2);
        $values = [
            'amount' => $money($payment->amount),
            'method' => Payment::METHODS[$payment->method] ?? $payment->method,
            'payment_ref' => $payment->payment_reference,
            'balance' => $money($invoice?->balance ?? 0),
        ];

        if ($invoice?->stay) {
            self::forStay($invoice->stay, 'payment_received', $values, $by);
        } elseif ($event = $invoice?->facilityBooking) {
            self::notify('payment_received', [
                'name' => $event->contact_name ?: $event->client_name,
                'phone' => $event->contact_phone,
                'email' => $event->contact_email,
            ], $values + ['reference' => $event->reference], [], $by);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Sending
    |--------------------------------------------------------------------------
    */

    /**
     * Save the message and deliver it once the response has gone out.
     */
    public static function queue(string $channel, string $recipient, string $body, array $attributes = []): self
    {
        $message = self::create($attributes + compact('channel', 'recipient', 'body') + ['status' => 'pending']);

        defer(fn () => $message->deliver());

        return $message;
    }

    public function deliver(): void
    {
        try {
            [$status, $provider, $reference] = $this->channel === 'sms' ? $this->sendSms() : $this->sendEmail();
            $this->update(['status' => $status, 'provider' => $provider, 'provider_ref' => $reference, 'error' => null, 'sent_at' => now()]);
        } catch (Throwable $e) {
            report($e);
            $this->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 250)]);
        }
    }

    /**
     * @return array{0: string, 1: string, 2: ?string} status, provider, provider reference
     */
    private function sendSms(): array
    {
        return match ($driver = config('services.sms.driver')) {
            'log' => $this->logOnly('sms'),
            default => throw new \RuntimeException("SMS provider '{$driver}' is not set up."),
        };
    }

    private function sendEmail(): array
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return $this->logOnly('mail');
        }

        Mail::to($this->recipient)->send(new GuestMessageMail($this));

        return ['sent', $mailer, null];
    }

    // Test mode: write it to the log instead of sending
    private function logOnly(string $provider): array
    {
        Log::info("[test mode] {$this->channel} to {$this->recipient}: {$this->body}");

        return ['test', $provider, null];
    }

    /**
     * Kenyan numbers to 2547XXXXXXXX / 2541XXXXXXXX; other countries need the
     * leading +. Null if it can't be a phone number.
     */
    public static function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        $digits = match (true) {
            strlen($digits) === 10 && str_starts_with($digits, '0') => '254' . substr($digits, 1),
            strlen($digits) === 9 && in_array($digits[0], ['7', '1'], true) => '254' . $digits,
            default => $digits,
        };

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? $digits : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    */

    public function getTemplateLabelAttribute(): string
    {
        return self::TEMPLATES[$this->template]['label'] ?? 'Typed by staff';
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            'failed' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
            'test' => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-400',
            default => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        };
    }

    public static function inTestMode(): bool
    {
        return config('services.sms.driver') === 'log';
    }
}
