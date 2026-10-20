<?php

namespace App\Enums;

/**
 * Room status values. Must match the `rooms.status` enum column.
 */
enum RoomStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Cleaning = 'cleaning';
    case Maintenance = 'maintenance';
    case OutOfOrder = 'out_of_order';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Occupied => 'Occupied',
            self::Cleaning => 'Cleaning',
            self::Maintenance => 'Maintenance',
            self::OutOfOrder => 'Out of Order',
            self::Reserved => 'Reserved',
        };
    }

    /**
     * Tailwind classes for the status badge (partials/status-badge).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            self::Occupied => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
            self::Cleaning => 'bg-info-50 text-info-600 dark:bg-info-500/15 dark:text-info-500',
            self::Maintenance, self::OutOfOrder => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            self::Reserved => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        };
    }

    /**
     * Statuses that take a room out of service (can't be booked at all).
     */
    public function isOutOfService(): bool
    {
        return in_array($this, [self::Maintenance, self::OutOfOrder], true);
    }

    /**
     * value => label, for <select> options.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
