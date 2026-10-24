<?php

namespace App\Enums;

/**
 * Purchase order status. Must match the `purchase_orders.status` enum column.
 *
 *   draft ──► submitted ──► approved ──► ordered ──► partially_received ──► received
 *     ▲            │
 *     └── sent back┘        (anything before a delivery can be cancelled)
 */
enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Ordered = 'ordered';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Awaiting approval',
            self::Approved => 'Approved',
            self::Ordered => 'Ordered',
            self::PartiallyReceived => 'Part received',
            self::Received => 'Received',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            self::Submitted => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Approved, self::Ordered => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::PartiallyReceived => 'bg-primary/10 text-primary',
            self::Received => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::Cancelled => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * Lines and supplier can still be changed.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Deliveries can be booked in.
     */
    public function canReceive(): bool
    {
        return in_array($this, [self::Approved, self::Ordered, self::PartiallyReceived], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Submitted, self::Approved, self::Ordered], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
