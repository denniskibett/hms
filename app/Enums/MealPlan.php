<?php

namespace App\Enums;

/**
 * Board basis a rate (and a stay) is sold on.
 */
enum MealPlan: string
{
    case RoomOnly = 'RO';
    case BedAndBreakfast = 'BB';
    case HalfBoard = 'HB';
    case FullBoard = 'FB';
    case AllInclusive = 'AI';

    public function label(): string
    {
        return match ($this) {
            self::RoomOnly => 'Room Only',
            self::BedAndBreakfast => 'Bed & Breakfast',
            self::HalfBoard => 'Half Board',
            self::FullBoard => 'Full Board',
            self::AllInclusive => 'All Inclusive',
        };
    }

    public function badgeClasses(): string
    {
        return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }

    /**
     * value => "BB – Bed & Breakfast", for <select> options.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $p) => [$p->value => "{$p->value} – {$p->label()}"])->all();
    }
}
