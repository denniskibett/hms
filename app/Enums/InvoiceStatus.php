<?php

namespace App\Enums;

/**
 * Invoice status for display. BillingService stores draft/sent/partial/paid/cancelled;
 * "overdue" is worked out from the due date (Invoice::display_status).
 */
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Unpaid',
            self::Partial => 'Part paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft, self::Cancelled => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            self::Sent => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::Partial => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Paid => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::Overdue => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * Filter options for the invoice register.
     */
    public static function options(): array
    {
        return collect([self::Sent, self::Partial, self::Overdue, self::Paid, self::Cancelled])
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
