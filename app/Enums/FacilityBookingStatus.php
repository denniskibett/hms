<?php

namespace App\Enums;

/**
 * Event (facility booking) status.
 *
 *   tentative ──► confirmed ──► completed
 *       │    ◄──     │
 *       └────────────┴──► cancelled
 *
 * tentative = venue held for the client, not yet confirmed.
 */
enum FacilityBookingStatus: string
{
    case Tentative = 'tentative';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Tentative => 'Tentative',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Tentative => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Confirmed => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::Completed => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::Cancelled => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Tentative => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Tentative, self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Bookings that hold the venue for their dates.
     */
    public function holdsVenue(): bool
    {
        return in_array($this, [self::Tentative, self::Confirmed], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
