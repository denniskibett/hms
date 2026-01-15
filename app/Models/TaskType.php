<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskType extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    /**
     * Department for this task type.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Tasks of this type.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope for active task types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for housekeeping task types.
     */
    public function scopeHousekeeping($query)
    {
        return $query->where('category', 'cleaning');
    }

    /**
     * Scope for kitchen task types.
     */
    public function scopeKitchen($query)
    {
        return $query->where('category', 'kitchen');
    }

    /**
     * Scope for maintenance task types.
     */
    public function scopeMaintenance($query)
    {
        return $query->where('category', 'maintenance');
    }

    /**
     * Get category options.
     */
    public static function getCategoryOptions()
    {
        return [
            'cleaning' => 'Housekeeping/Cleaning',
            'maintenance' => 'Maintenance',
            'kitchen' => 'Kitchen',
            'reception' => 'Reception/Front Desk',
            'admin' => 'Administrative',
            'hr' => 'Human Resources',
            'other' => 'Other',
        ];
    }

    /**
     * Get default checklist items.
     */
    public function getChecklistItemsAttribute()
    {
        return $this->default_checklist ?? [];
    }

    /**
     * Create a new task of this type.
     */
    public function createTask($data)
    {
        $defaults = [
            'title' => $this->name,
            'description' => $this->description,
            'estimated_minutes' => $this->default_estimated_minutes,
            'estimated_cost' => $this->default_estimated_cost,
            'checklist' => $this->default_checklist,
            'department_id' => $this->department_id,
        ];
        
        $taskData = array_merge($defaults, $data, ['task_type_id' => $this->id]);
        
        return Task::create($taskData);
    }
}