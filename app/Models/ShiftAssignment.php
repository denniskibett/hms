<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'date',
        'status',
        'overtime_hours',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * User assigned to this shift.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shift details.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'absent' => 'Absent',
            'on_leave' => 'On Leave',
        ];
    }
}