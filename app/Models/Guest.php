<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $table = 'guest_profiles';

    protected $fillable = [
        'user_id',
        'id_type',
        'id_number',
        'nationality',
        'address',
        'emergency_contact',
        'preferences',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
        'preferences' => 'array',
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function stays()
    {
        return $this->hasManyThrough(Stay::class, User::class, 'id', 'guest_id');
    }

    /**
     * Current active stay.
     */
    public function currentStay()
    {
        return $this->stays()
            ->whereIn('stays.status', ['booked', 'checked_in'])
            ->where('stays.departure_date', '>=', now())
            ->latest('stays.created_at')
            ->first();
    }

    /**
     * Scope for guests by ID type.
     */
    public function scopeByIdType($query, $type)
    {
        return $query->where('id_type', $type);
    }

    /**
     * Scope for guests by nationality.
     */
    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    /**
     * Get ID type options.
     */
    public static function getIdTypeOptions()
    {
        return [
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'driving_license' => 'Driving License',
            'other' => 'Other',
        ];
    }


}