<?php

namespace App\Models;

use App\Enums\KitchenOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A restaurant or room-service order. Either charged to an in-house guest's
 * room (shows on the stay's invoice) or billed on its own invoice and paid
 * at the counter.
 */
class KitchenOrder extends Model
{
    use HasFactory;

    public const TYPES = [
        'dine_in' => 'Dine-in',
        'room_service' => 'Room service',
        'takeaway' => 'Takeaway',
    ];

    protected $fillable = [
        'stay_id',
        'charge_to_room',
        'order_number',
        'status',
        'type',
        'customer_name',
        'table_number',
        'special_instructions',
        'placed_by',
        'preparation_started_at',
        'ready_at',
        'delivered_at',
        'cancel_reason',
    ];

    protected $casts = [
        'status' => KitchenOrderStatus::class,
        'charge_to_room' => 'boolean',
        'preparation_started_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function placer()
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function items()
    {
        return $this->hasMany(KitchenOrderItem::class, 'order_id');
    }

    /**
     * The order's own invoice (walk-in orders only).
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [KitchenOrderStatus::Pending->value, KitchenOrderStatus::Preparing->value, KitchenOrderStatus::Ready->value]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('order_number', 'like', "%{$term}%")
            ->orWhere('customer_name', 'like', "%{$term}%")
            ->orWhere('table_number', $term)
            ->orWhereHas('stay.guest', fn ($g) => $g->where('name', 'like', "%{$term}%")));
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->items->sum(fn (KitchenOrderItem $item) => $item->total), 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Who the food is for: "Table 4", "Room 104 · Jane Doe", "Takeaway · John".
     * Load stay.guest and stay.roomAllocations.room for in-house orders.
     */
    public function getServeToAttribute(): string
    {
        $guest = $this->stay?->guest?->name;
        $room = $this->stay?->roomAllocations->map(fn ($a) => $a->room?->room_number)->filter()->implode(', ');

        return match (true) {
            $this->type === 'room_service' => trim('Room ' . ($room ?: '?') . ($guest ? " · {$guest}" : '')),
            $this->type === 'dine_in' => trim('Table ' . ($this->table_number ?: '—') . ($guest ?? $this->customer_name ? ' · ' . ($guest ?? $this->customer_name) : '')),
            default => 'Takeaway' . ($guest ?? $this->customer_name ? ' · ' . ($guest ?? $this->customer_name) : ''),
        };
    }

    public function currentInvoice(): ?Invoice
    {
        return $this->invoices()->where('status', '!=', 'cancelled')->oldest('id')->first();
    }

    /**
     * Minutes since the order was placed (for the kitchen board).
     */
    public function getAgeMinutesAttribute(): int
    {
        return (int) $this->created_at?->diffInMinutes(now());
    }
}
