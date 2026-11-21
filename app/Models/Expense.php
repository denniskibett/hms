<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Money paid out. Category supplier_payment settles what a supplier is owed
 * for goods received (optionally against one purchase order).
 */
class Expense extends Model
{
    public const CATEGORIES = [
        'supplier_payment' => 'Supplier payment',
        'utilities' => 'Electricity & water',
        'rent' => 'Rent & rates',
        'fuel_transport' => 'Fuel & transport',
        'repairs' => 'Repairs & maintenance',
        'communication' => 'Internet & phone',
        'marketing' => 'Marketing',
        'bank_charges' => 'Bank & M-Pesa charges',
        'licences' => 'Licences & permits',
        'professional' => 'Professional fees',
        'staff_welfare' => 'Staff meals & welfare',
        'other' => 'Other',
    ];

    protected $fillable = [
        'expense_number',
        'spent_on',
        'category',
        'description',
        'amount',
        'method',
        'reference',
        'supplier_id',
        'purchase_order_id',
        'department_id',
        'status',
        'void_reason',
        'created_by',
    ];

    protected $casts = [
        'spent_on' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('expense_number', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhere('reference', 'like', "%{$term}%")
            ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%")));
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getMethodLabelAttribute(): string
    {
        return Payment::METHODS[$this->method] ?? $this->method;
    }
}
