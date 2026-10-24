<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stock used on a task (cleaning supplies, spare parts). One row per item per
 * task; using more adds to it. The cost is the item's average cost at the time.
 */
class TaskItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'inventory_item_id',
        'quantity_used',
        'unit_cost_at_time',
        'notes',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'unit_cost_at_time' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity_used * (float) $this->unit_cost_at_time, 2);
    }
}
