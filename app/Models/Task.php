<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_type_id',
        'title',
        'description',
        'assigned_to',
        'department_id',
        'shift_id',
        'room_id',
        'stay_id',
        'facility_id',
        'status',
        'priority',
        'estimated_minutes',
        'actual_minutes',
        'due_date',
        'scheduled_start',
        'scheduled_end',
        'created_by',
        'verified_by',
        'assigned_at',
        'started_at',
        'completed_at',
        'verified_at',
        'estimated_cost',
        'actual_cost',
        'checklist',
        'notes',
        'verification_notes',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_task_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'checklist' => 'array',
        'is_recurring' => 'boolean',
        'recurrence_end_date' => 'date',
    ];

    protected $appends = [
        'is_overdue',
        'progress_percentage',
        'checklist_progress',
        'total_items_cost',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Task type.
     */
    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * Assigned staff member.
     */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Department responsible.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Shift for the task.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Room for the task.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Stay related to task.
     */
    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * Facility for task.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * User who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who verified the task.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Parent task for recurring tasks.
     */
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Child tasks for recurring tasks.
     */
    public function childTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Items used in this task.
     */
    public function items()
    {
        return $this->hasMany(TaskItem::class);
    }

    /**
     * Comments on this task.
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }


    // ==================== SCOPES ====================

    /**
     * Scope for housekeeping tasks.
     */
    public function scopeHousekeeping($query)
    {
        return $query->whereHas('taskType', function ($q) {
            $q->where('category', 'cleaning');
        });
    }

    /**
     * Scope for kitchen tasks.
     */
    public function scopeKitchen($query)
    {
        return $query->whereHas('taskType', function ($q) {
            $q->where('category', 'kitchen');
        });
    }

    /**
     * Scope for maintenance tasks.
     */
    public function scopeMaintenance($query)
    {
        return $query->whereHas('taskType', function ($q) {
            $q->where('category', 'maintenance');
        });
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for assigned tasks.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope for in-progress tasks.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }


    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'verified', 'cancelled']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeByAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeByRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Check if task is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && !in_array($this->status, ['completed', 'verified', 'cancelled']);
    }

    /**
     * Calculate progress percentage.
     */
    public function getProgressPercentageAttribute()
    {
        $statusProgress = [
            'pending' => 0,
            'assigned' => 25,
            'in_progress' => 50,
            'completed' => 75,
            'verified' => 100,
            'cancelled' => 0,
            'on_hold' => 10,
        ];
        
        return $statusProgress[$this->status] ?? 0;
    }

    /**
     * Calculate checklist progress.
     */
    public function getChecklistProgressAttribute()
    {
        $checklist = $this->checklist ?? [];
        
        if (empty($checklist)) {
            return null;
        }
        
        $completed = 0;
        foreach ($checklist as $item) {
            if (isset($item['completed']) && $item['completed']) {
                $completed++;
            }
        }
        
        return [
            'total' => count($checklist),
            'completed' => $completed,
            'percentage' => count($checklist) > 0 ? ($completed / count($checklist)) * 100 : 0,
        ];
    }

    /**
     * Calculate total cost of items used.
     */
    public function getTotalItemsCostAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity_used * $item->unit_cost_at_time;
        });
    }

    /**
     * Calculate total task cost.
     */
    public function getTotalCostAttribute()
    {
        return $this->actual_cost ?? ($this->total_items_cost + $this->estimated_cost);
    }

    /**
     * Calculate time remaining.
     */
    public function getTimeRemainingAttribute()
    {
        if (in_array($this->status, ['completed', 'verified'])) {
            return 'Completed';
        }
        
        $remaining = $this->due_date->diffForHumans();
        
        if ($this->is_overdue) {
            return 'Overdue by ' . $this->due_date->diffInDays(now()) . ' days';
        }
        
        return $remaining;
    }

    // ==================== METHODS ====================

    /**
     * Assign task to staff.
     */
    public function assignTo($userId, $departmentId = null, $shiftId = null)
    {
        $this->assigned_to = $userId;
        $this->department_id = $departmentId;
        $this->shift_id = $shiftId;
        $this->status = 'assigned';
        $this->assigned_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Start task.
     */
    public function start()
    {
        $this->status = 'in_progress';
        $this->started_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Complete task.
     */
    public function complete($actualMinutes = null, $notes = null)
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->actual_minutes = $actualMinutes ?: $this->estimated_minutes;
        $this->notes = $notes ?: ($this->notes ?? 'Task completed');
        $this->save();
        
        return $this;
    }

    /**
     * Verify task.
     */
    public function verify($verifiedBy, $notes = null)
    {
        $this->status = 'verified';
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        $this->verification_notes = $notes ?: ($this->verification_notes ?? 'Task verified');
        $this->save();
        
        return $this;
    }

    /**
     * Use inventory item in task.
     */
    public function useItem($inventoryItemId, $quantity, $unitCost = null)
    {
        $item = InventoryItem::findOrFail($inventoryItemId);
        
        if ($item->quantity < $quantity) {
            throw new \Exception("Insufficient stock for {$item->name}. Available: {$item->quantity}, Requested: {$quantity}");
        }
        
        $taskItem = TaskItem::create([
            'task_id' => $this->id,
            'inventory_item_id' => $inventoryItemId,
            'quantity_used' => $quantity,
            'unit_cost_at_time' => $unitCost ?? $item->unit_cost,
        ]);
        
        // Update inventory
        $item->updateQuantity($quantity, 'subtract', "Used in task #{$this->id}");
        
        return $taskItem;
    }

    /**
     * Add comment to task.
     */
    public function addComment($content, $userId = null)
    {
        return TaskComment::create([
            'task_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'content' => $content,
        ]);
    }

    /**
     * Update checklist item.
     */
    public function updateChecklistItem($itemIndex, $completed, $notes = null)
    {
        $checklist = $this->checklist ?? [];
        
        if (isset($checklist[$itemIndex])) {
            $checklist[$itemIndex]['completed'] = $completed;
            $checklist[$itemIndex]['completed_at'] = $completed ? now() : null;
            $checklist[$itemIndex]['completed_by'] = $completed ? auth()->id() : null;
            $checklist[$itemIndex]['notes'] = $notes ?? $checklist[$itemIndex]['notes'] ?? null;
            
            $this->checklist = $checklist;
            $this->save();
        }
        
        return $this;
    }
}