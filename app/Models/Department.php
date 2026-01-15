<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Staff members in this department.
     */
    public function staff()
    {
        return $this->hasMany(StaffProfile::class);
    }

    /**
     * Scope for active departments.
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}