<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An order to a supplier. Received lines go into stock through
 * InventoryItem::move(); `total` is the ordered value.
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'po_number',
        'requested_by',
        'status',
        'total',
        'notes',
        'supplier_reference',
        'approved_by',
        'approved_at',
        'ordered_at',
        'received_at',
        'delivery_date',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'delivery_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Stock movements from deliveries on this order.
     */
    public function receipts()
    {
        return $this->hasMany(InventoryTransaction::class, 'reference_id')->where('reference_type', 'purchase_order');
    }

    public function scopeWithStatus(Builder $query, PurchaseOrderStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof PurchaseOrderStatus ? $status->value : $status);
    }

    /**
     * Ordered (or approved) and not fully delivered.
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', [PurchaseOrderStatus::Approved->value, PurchaseOrderStatus::Ordered->value, PurchaseOrderStatus::PartiallyReceived->value]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('po_number', 'like', "%{$term}%")
            ->orWhere('supplier_reference', 'like', "%{$term}%")
            ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%")));
    }

    public function recalculateTotal(): void
    {
        $this->update(['total' => round((float) $this->items()->get()->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price), 2)]);
    }

    public function getIsLateAttribute(): bool
    {
        return $this->delivery_date?->isPast() && ! $this->delivery_date->isToday()
            && in_array($this->status, [PurchaseOrderStatus::Approved, PurchaseOrderStatus::Ordered, PurchaseOrderStatus::PartiallyReceived], true);
    }

    /**
     * Value of what has been delivered so far.
     */
    public function getReceivedValueAttribute(): float
    {
        return round((float) $this->items->sum(fn ($i) => (float) $i->received_quantity * (float) $i->unit_price), 2);
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'delivery_date' => $this->delivery_date?->toDateString() ?? '',
            'notes' => $this->notes ?? '',
            'items' => $this->items->map(fn (PurchaseOrderItem $i) => [
                'inventory_item_id' => $i->inventory_item_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->values()->all(),
        ];
    }
}
