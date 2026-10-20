<?php

namespace App\Enums;

/**
 * How a room rate is charged.
 *
 * PerRoom:   one price for the room per night (city hotels: "KES 14,500 double, BB").
 * PerPerson: price per adult per night, children at the child rate
 *            (resorts / lodges: "KES 9,500 per person sharing, HB").
 */
enum PricingBasis: string
{
    case PerRoom = 'per_room';
    case PerPerson = 'per_person';

    public function label(): string
    {
        return match ($this) {
            self::PerRoom => 'Per room',
            self::PerPerson => 'Per person',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $b) => [$b->value => $b->label()])->all();
    }
}
