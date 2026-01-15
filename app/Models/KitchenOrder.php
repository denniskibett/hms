<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'order_number',
        'status',
        'type',
        'special_instructions',
        'placed_by',
        'preparation_started_at',
        'ready_at',
        'delivered_at',
    ];

    protected $casts = [
        'preparation_started_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Stay for this order.
     */
    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * User who placed this order.
     */
    public function placer()
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    /**
     * Items in this order.
     */
    public function items()
    {
        return $this->hasMany(KitchenOrderItem::class);
    }

    /**
     * Pending items.
     */
    public function pendingItems()
    {
        return $this->items()->where('status', 'pending');
    }

    /**
     * Preparing items.
     */
    public function preparingItems()
    {
        return $this->items()->where('status', 'preparing');
    }

    /**
     * Ready items.
     */
    public function readyItems()
    {
        return $this->items()->where('status', 'ready');
    }

    /**
     * Generate order number.
     */
    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Get order type options.
     */
    public static function getTypeOptions()
    {
        return [
            'dine_in' => 'Dine In',
            'room_service' => 'Room Service',
            'takeaway' => 'Takeaway',
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Calculate order total.
     */
    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price_at_order * $item->quantity;
        });
    }

    /**
     * Start order preparation.
     */
    public function startPreparation()
    {
        $this->status = 'preparing';
        $this->preparation_started_at = now();
        $this->save();
    }

    /**
     * Mark order as ready.
     */
    public function markAsReady()
    {
        $this->status = 'ready';
        $this->ready_at = now();
        $this->save();
    }

    /**
     * Mark order as delivered.
     */
    public function markAsDelivered()
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        $this->save();
    }
}