<?php

namespace App\Models;

use App\Enums\MealPlan;
use App\Enums\StayStatus;
use Illuminate\Database\Eloquent\Builder;
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
        'meal_plan',
        'special_requests',
        'created_by',
        'check_in',
        'check_out',
    ];

    // `status` stays a string for now (many older views compare it as text);
    // use $stay->state for the StayStatus enum.
    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'meal_plan' => MealPlan::class,
        'adults' => 'integer',
        'children' => 'integer',
    ];

    // ---- Relationships ---------------------------------------------------

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roomAllocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }

    public function rooms()
    {
        return $this->hasManyThrough(Room::class, RoomAllocation::class, 'stay_id', 'id', 'id', 'room_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    public function facilityBookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'stay_service')
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Allocation covering today (or null).
     */
    public function currentRoomAllocation()
    {
        return $this->roomAllocations()
            ->whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>', today())
            ->first();
    }

    public function currentRoom()
    {
        return optional($this->currentRoomAllocation())->room;
    }

    /**
     * Open invoice for this stay (latest unpaid / partly paid).
     */
    public function currentInvoice()
    {
        return $this->invoices()
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->latest()
            ->first();
    }

    // ---- State -----------------------------------------------------------

    public function getStateAttribute(): StayStatus
    {
        return StayStatus::from($this->status);
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->status === StayStatus::CheckedIn->value;
    }

    public function getIsCheckedOutAttribute(): bool
    {
        return $this->status === StayStatus::CheckedOut->value;
    }

    // ---- Money (single definition used everywhere) ---------------------

    public function getNightsAttribute(): int
    {
        return (int) $this->arrival_date->diffInDays($this->departure_date);
    }

    /**
     * Σ rate_applied × nights for each room. rate_applied is the room's
     * full nightly price (already includes per-person maths at booking time).
     */
    public function getRoomChargesAttribute(): float
    {
        return round($this->roomAllocations->sum(fn (RoomAllocation $a) => $a->total), 2);
    }

    public function getAdditionalChargesAttribute(): float
    {
        return round($this->services->sum(fn ($s) => $s->pivot->quantity * $s->pivot->price), 2);
    }

    public function getTotalAmountAttribute(): float
    {
        return round($this->room_charges + $this->additional_charges, 2);
    }

    public function getAmountPaidAttribute(): float
    {
        return round((float) $this->invoices->sum('paid_amount'), 2);
    }

    public function getAllPaymentsAttribute()
    {
        return Payment::whereIn('invoice_id', $this->invoices()->pluck('id'))->get();
    }

    public function getPaymentStatusAttribute(): string
    {
        $total = $this->total_amount;
        $paid = $this->amount_paid;

        return match (true) {
            $total <= 0 => 'free',
            $paid >= $total => 'paid',
            $paid > 0 => 'partial',
            default => 'unpaid',
        };
    }

    // ---- Scopes ----------------------------------------------------------

    public function scopeWithStatus(Builder $query, StayStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof StayStatus ? $status->value : $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', StayStatus::activeValues());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', [StayStatus::Reserved->value, StayStatus::Booked->value])
            ->whereDate('arrival_date', '>', today());
    }

    /**
     * Expected to arrive today (or overdue arrivals not yet checked in).
     */
    public function scopeArrivingToday(Builder $query): Builder
    {
        return $query->whereIn('status', [StayStatus::Reserved->value, StayStatus::Booked->value])
            ->whereDate('arrival_date', '<=', today());
    }

    /**
     * In house and due out today (or overdue).
     */
    public function scopeDepartingToday(Builder $query): Builder
    {
        return $query->where('status', StayStatus::CheckedIn->value)
            ->whereDate('departure_date', '<=', today());
    }

    public function scopeInHouse(Builder $query): Builder
    {
        return $query->where('status', StayStatus::CheckedIn->value);
    }

    // Older names kept for existing callers
    public function scopeTodayCheckins(Builder $query): Builder
    {
        return $query->arrivingToday();
    }

    public function scopeTodayCheckouts(Builder $query): Builder
    {
        return $query->departingToday();
    }
}
