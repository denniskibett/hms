<?php

namespace App\Enums;

/**
 * Kitchen order status. Must match the `kitchen_orders.status` enum column.
 *
 *   pending ──► preparing ──► ready ──► delivered
 *      │            │
 *      └────────────┴──► cancelled
 *
 * Food that has been served is not cancelled; refund it with a discount.
 */
enum KitchenOrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'New',
            self::Preparing => 'Preparing',
            self::Ready => 'Ready',
            self::Delivered => 'Served',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Preparing => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::Ready => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::Delivered => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            self::Cancelled => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * The next step on the kitchen board, with its button label.
     *
     * @return array{0: self, 1: string}|null
     */
    public function next(): ?array
    {
        return match ($this) {
            self::Pending => [self::Preparing, 'Start'],
            self::Preparing => [self::Ready, 'Ready'],
            self::Ready => [self::Delivered, 'Served'],
            default => null,
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return ($this->next()[0] ?? null) === $status
            || ($status === self::Cancelled && in_array($this, [self::Pending, self::Preparing], true));
    }

    /**
     * Still on the kitchen board.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Preparing, self::Ready], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
