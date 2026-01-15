<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'code',
        'price',
        'description',
        'is_active',
    ];

    public function stays()
    {
        return $this->belongsToMany(
            Stay::class,
            'stay_service'
        )->withPivot(['quantity', 'price'])->withTimestamps();
    }
}
