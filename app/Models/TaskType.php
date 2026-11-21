<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A kind of job (Check-out Cleaning, Room Maintenance…) with its default
 * checklist and time. Codes CHECKOUT_CLEAN and DAILY_CLEAN are used by the system.
 */
class TaskType extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'cleaning' => 'Cleaning',
        'maintenance' => 'Maintenance',
        'kitchen' => 'Kitchen',
        'reception' => 'Front office',
        'admin' => 'Admin',
        'hr' => 'HR',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'category',
        'description',
        'default_checklist',
        'default_estimated_minutes',
        'default_estimated_cost',
        'requires_room',
        'requires_inventory',
        'is_active',
    ];

    protected $casts = [
        'default_checklist' => 'array',
        'requires_room' => 'boolean',
        'requires_inventory' => 'boolean',
        'is_active' => 'boolean',
        'default_estimated_cost' => 'decimal:2',
        'default_estimated_minutes' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Checklist item names.
     *
     * @return array<string>
     */
    public function getChecklistItemsAttribute(): array
    {
        return collect($this->default_checklist ?? [])->map(fn ($i) => is_array($i) ? ($i['item'] ?? '') : (string) $i)->filter()->values()->all();
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'department_id' => $this->department_id ?? '',
            'description' => $this->description ?? '',
            'checklist' => implode("\n", $this->checklist_items),
            'default_estimated_minutes' => $this->default_estimated_minutes,
            'requires_room' => $this->requires_room,
            'is_active' => $this->is_active,
        ];
    }
}
