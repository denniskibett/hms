<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One stock movement. quantity is signed (+ in, - out); balance_after is the
 * item's stock straight after it.
 */
class InventoryTransaction extends Model
{
    public const TYPES = [
        'in' => 'Received',
        'out' => 'Issued / used',
        'adjustment' => 'Stock count',
        'damage' => 'Damaged',
        'expired' => 'Expired',
    ];

    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'balance_after',
        'unit_cost',
        'total_value',
        'reference_type',
        'reference_id',
        'department_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Where the movement came from, as a link when there is a page for it.
     *
     * @return array{0: string, 1: ?string} [label, url]
     */
    public function source(): array
    {
        return match ($this->reference_type) {
            'task' => ['Task #' . $this->reference_id, route('tasks.show', $this->reference_id)],
            'purchase_order' => ['Purchase order #' . $this->reference_id, route('purchase-orders.show', $this->reference_id)],
            default => ['Manual', null],
        };
    }
}
