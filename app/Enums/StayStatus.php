<?php

namespace App\Enums;

/**
 * Stay (booking) status. Must match the `stays.status` enum column.
 *
 *   reserved ──► booked ──► checked_in ──► checked_out
 *       │           │
 *       └───────────┴──► cancelled
 *
 * reserved = provisional hold (not yet confirmed), booked = confirmed.
 */
enum StayStatus: string
{
    case Reserved = 'reserved';
    case Booked = 'booked';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Reserved => 'Provisional',
            self::Booked => 'Confirmed',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Reserved => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Booked => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::CheckedIn => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::CheckedOut => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            self::Cancelled => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * Statuses this one may move to.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Reserved => [self::Booked, self::CheckedIn, self::Cancelled],
            self::Booked => [self::Reserved, self::CheckedIn, self::Cancelled],
            self::CheckedIn => [self::CheckedOut],
            self::CheckedOut, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Stays that hold their rooms for their dates.
     */
    public function holdsRooms(): bool
    {
        return in_array($this, [self::Reserved, self::Booked, self::CheckedIn], true);
    }

    /**
     * Dates, rooms and guests can still be changed.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Reserved, self::Booked, self::CheckedIn], true);
    }

    /**
     * @return array<string>
     */
    public static function activeValues(): array
    {
        return [self::Reserved->value, self::Booked->value, self::CheckedIn->value];
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
