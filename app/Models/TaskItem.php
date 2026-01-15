<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $appends = [
        'total_cost',
    ];

    /**
     * Task this item belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Inventory item used.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Calculate item total cost.
     */
    public function getTotalCostAttribute()
    {
        return $this->quantity_used * $this->unit_cost_at_time;
    }
}