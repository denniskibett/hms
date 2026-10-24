<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A job for staff: cleaning a room, fixing a tap, a kitchen deep clean.
 * Cleaning and maintenance tasks on a room move the room's status along:
 * finishing the last one puts the room back in service.
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    protected $fillable = [
        'task_type_id',
        'title',
        'description',
        'assigned_to',
        'department_id',
        'shift_id',
        'room_id',
        'blocks_room',
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
        'parent_task_id',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'blocks_room' => 'boolean',
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
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            $task->status ??= $task->assigned_to ? TaskStatus::Assigned : TaskStatus::Pending;
            if ($task->assigned_to && ! $task->assigned_at) {
                $task->assigned_at = now();
            }

            // Start from the type's checklist and time
            $type = $task->taskType;
            if ($type) {
                $task->checklist ??= array_map(fn ($item) => ['item' => $item, 'completed' => false], $type->checklist_items) ?: null;
                $task->estimated_minutes ??= $type->default_estimated_minutes;
                $task->department_id ??= $type->department_id;
            }
        });
    }

    // ---- Relationships -----------------------------------------------------

    public function taskType()
    {
        return $this->belongsTo(TaskType::class)->withTrashed();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items()
    {
        return $this->hasMany(TaskItem::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->oldest();
    }

    // ---- Scopes ------------------------------------------------------------

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', TaskStatus::openValues());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->where('due_date', '<', now());
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->whereHas('taskType', fn ($q) => $q->where('category', $category));
    }

    /**
     * Tasks a user can see: everything for supervisors, otherwise their own,
     * their department's and those they created.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['admin', 'manager', 'receptionist', 'housekeeping'])) {
            return $query;
        }

        $departmentId = $user->staffProfile?->department_id;

        return $query->where(fn ($q) => $q
            ->where('assigned_to', $user->id)
            ->orWhere('created_by', $user->id)
            ->when($departmentId, fn ($q) => $q->orWhere('department_id', $departmentId)));
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhereHas('room', fn ($r) => $r->where('room_number', $term)));
    }

    // ---- Helpers -----------------------------------------------------------

    public function getIsOverdueAttribute(): bool
    {
        return $this->status->isOpen() && $this->due_date?->isPast();
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst((string) $this->priority);
    }

    public function getPriorityClassesAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'text-error-600 dark:text-error-400',
            'high' => 'text-warning-600 dark:text-warning-400',
            'low' => 'text-gray-400',
            default => 'text-gray-600 dark:text-gray-300',
        };
    }

    /**
     * [done, total] checklist items.
     */
    public function getChecklistCountsAttribute(): array
    {
        $items = collect($this->checklist ?? []);

        return [$items->where('completed', true)->count(), $items->count()];
    }

    public function isCleaning(): bool
    {
        return $this->taskType?->category === 'cleaning';
    }

    /**
     * After a task closes, put its room back in service when nothing is left
     * holding it: no open cleaning job for a room being cleaned, no open
     * blocking job for a room under maintenance.
     */
    public function releaseRoom(): void
    {
        $room = $this->room;
        if (! $room || $room->currentStay()->exists()) {
            return;
        }

        $open = fn () => Task::where('room_id', $room->id)->whereKeyNot($this->id)->open();

        $release = match ($room->status) {
            RoomStatus::Cleaning => $this->isCleaning() && ! $open()->category('cleaning')->exists(),
            RoomStatus::Maintenance => $this->blocks_room && ! $open()->where('blocks_room', true)->exists(),
            default => false,
        };

        if ($release) {
            $room->update(['status' => RoomStatus::Available]);
        }
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'task_type_id' => $this->task_type_id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'assigned_to' => $this->assigned_to ?? '',
            'room_id' => $this->room_id ?? '',
            'blocks_room' => $this->blocks_room,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->format('Y-m-d\TH:i'),
            'estimated_minutes' => $this->estimated_minutes,
        ];
    }
}
