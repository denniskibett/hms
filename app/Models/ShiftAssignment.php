<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A person on a shift on a day (the rota). After the day, HR marks it worked
 * or absent and records overtime, which feeds payroll.
 */
class ShiftAssignment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Worked',
        'absent' => 'Absent',
        'on_leave' => 'On leave',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
