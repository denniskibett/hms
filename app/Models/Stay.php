<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stay extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guest_id',
        'status',
        'arrival_date',
        'departure_date',
        'adults',
        'children',
        'special_requests',
        'created_by',
        'check_in',
        'check_out',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * Guest for this stay.
     */
    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }


    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Room allocations for this stay.
     */
    public function roomAllocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }

    /**
     * Rooms through allocations.
     */
    public function rooms()
    {
        return $this->hasManyThrough(Room::class, RoomAllocation::class, 'stay_id', 'id', 'id', 'room_id');
    }

    /**
     * Current room allocation.
     */
    public function currentRoomAllocation()
    {
        return $this->roomAllocations()
            ->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now())
            ->first();
    }

    /**
     * Current room.
     */
    public function currentRoom()
    {
        return optional($this->currentRoomAllocation())->room;
    }

    /**
     * All invoices for this stay.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Current or main invoice for this stay.
     */
    public function currentInvoice()
    {
        return $this->invoices()
            ->whereIn('status', ['draft', 'sent', 'partial'])
            ->latest()
            ->first();
    }

    /**
     * Payments through invoices.
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    /**
     * Get all payments for this stay.
     */
    public function getAllPaymentsAttribute()
    {
        return \App\Models\Payment::whereIn('invoice_id', $this->invoices()->pluck('id'))->get();
    }

    /**
     * Get total nights.
     */
    public function getNightsAttribute()
    {
        return $this->arrival_date->diffInDays($this->departure_date);
    }

    /**
     * Get room charges.
     */
    public function getRoomChargesAttribute()
    {
        if (!$this->roomAllocations) {
            return 0;
        }
        
        return $this->roomAllocations->sum(function ($allocation) {
            $nights = $this->nights;
            $adultCharge = $allocation->rate_applied * $nights * $allocation->adults;
            $childCharge = $allocation->rate_applied * 0.5 * $nights * $allocation->children;
            return $adultCharge + $childCharge;
        });
    }

    /**
     * Get additional charges (currently 0 - we're not using services).
     */
    public function getAdditionalChargesAttribute()
    {
        return 0;
    }

    /**
     * Get total amount with calculations.
     */
    public function getTotalAmountAttribute()
    {
        return $this->room_charges + $this->additional_charges;
    }

    /**
     * Get amount paid.
     */
    public function getAmountPaidAttribute()
    {
        return $this->invoices->sum('paid_amount');
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatusAttribute()
    {
        $total = $this->total_amount;
        $paid = $this->amount_paid;

        if ($total <= 0) {
            return 'free';
        } elseif ($paid >= $total) {
            return 'paid';
        } elseif ($paid > 0) {
            return 'partial';
        } else {
            return 'unpaid';
        }
    }

    /**
     * Check if stay is checked in.
     */
    public function getIsCheckedInAttribute()
    {
        return $this->check_in !== null && $this->check_out === null;
    }

    /**
     * Check if stay is checked out.
     */
    public function getIsCheckedOutAttribute()
    {
        return $this->check_out !== null;
    }

    /**
     * Scope for active stays.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['reserved', 'booked', 'checked_in']);
    }

    /**
     * Scope for upcoming stays.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'reserved')
            ->where('arrival_date', '>', now());
    }

    /**
     * Scope for today's check-ins.
     */
    public function scopeTodayCheckins($query)
    {
        return $query->where('status', 'booked')
            ->whereDate('arrival_date', today());
    }

    /**
     * Scope for today's check-outs.
     */
    public function scopeTodayCheckouts($query)
    {
        return $query->where('status', 'checked_in')
            ->whereDate('departure_date', today());
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'stay_service'
        )->withPivot(['quantity', 'price'])->withTimestamps();
    }

}