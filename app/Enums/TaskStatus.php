<?php

namespace App\Enums;

/**
 * Task status. Must match the `tasks.status` enum column.
 *
 *   pending ──► assigned ──► in_progress ──► completed ──► verified
 *      └──────────┴──────────────┴──► on_hold / cancelled
 *
 * pending = nobody assigned yet. on_hold goes back to assigned / pending.
 */
enum TaskStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Not assigned',
            self::Assigned => 'To do',
            self::InProgress => 'In progress',
            self::Completed => 'Done',
            self::Verified => 'Checked',
            self::OnHold => 'On hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::Assigned => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
            self::InProgress => 'bg-primary/10 text-primary',
            self::Completed => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            self::Verified => 'bg-success-100 text-success-800 dark:bg-success-500/25 dark:text-success-400',
            self::OnHold => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
            self::Cancelled => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
        };
    }

    /**
     * Still needs doing.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Assigned, self::InProgress, self::OnHold], true);
    }

    /**
     * @return array<self>
     */
    public static function open(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => $s->isOpen()));
    }

    public static function openValues(): array
    {
        return array_map(fn (self $s) => $s->value, self::open());
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, match ($this) {
            self::Pending, self::Assigned => [self::InProgress, self::Completed, self::OnHold, self::Cancelled],
            self::InProgress => [self::Completed, self::OnHold, self::Cancelled],
            self::OnHold => [self::InProgress, self::Cancelled],
            self::Completed => [self::Verified, self::InProgress],
            self::Verified, self::Cancelled => [],
        }, true);
    }
}
