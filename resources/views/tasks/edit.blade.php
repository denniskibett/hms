@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Task</h2>
                <p class="text-gray-600 dark:text-gray-400">Update task information for Task #T{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <a href="{{ route('tasks.show', $task) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Task
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Main Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('tasks.update', $task) }}" method="POST" id="taskForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Basic Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Task Title -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Task Title *
                                    </label>
                                    <input type="text" name="title" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $task->title }}" required>
                                </div>
                                
                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Description *
                                    </label>
                                    <textarea name="description" rows="4" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        required>{{ $task->description }}</textarea>
                                </div>
                                
                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Category
                                    </label>
                                    <select name="category" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">Select Category</option>
                                        <option value="housekeeping" {{ $task->category == 'housekeeping' ? 'selected' : '' }}>Housekeeping</option>
                                        <option value="maintenance" {{ $task->category == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="front_desk" {{ $task->category == 'front_desk' ? 'selected' : '' }}>Front Desk</option>
                                        <option value="kitchen" {{ $task->category == 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                                        <option value="management" {{ $task->category == 'management' ? 'selected' : '' }}>Management</option>
                                        <option value="guest_service" {{ $task->category == 'guest_service' ? 'selected' : '' }}>Guest Service</option>
                                        <option value="security" {{ $task->category == 'security' ? 'selected' : '' }}>Security</option>
                                        <option value="other" {{ $task->category == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                
                                <!-- Priority -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Priority *
                                    </label>
                                    <select name="priority" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ $task->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status *
                                    </label>
                                    <select name="status" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="overdue" {{ $task->status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    </select>
                                </div>
                                
                                <!-- Progress -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Progress (%)
                                    </label>
                                    <input type="number" name="progress" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        min="0" max="100" value="{{ $task->progress }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assignment & Scheduling -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Assignment & Scheduling</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Assigned To -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Assigned To *
                                    </label>
                                    <select name="assigned_to" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="">Select Assignee</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $task->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Due Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Due Date *
                                    </label>
                                    <input type="date" name="due_date" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $task->due_date->format('Y-m-d') }}" required>
                                </div>
                                
                                <!-- Start Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Start Date
                                    </label>
                                    <input type="date" name="start_date" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}">
                                </div>
                                
                                <!-- Time Spent -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Time Spent (hours)
                                    </label>
                                    <input type="number" name="time_spent" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        min="0" step="0.25" value="{{ $task->time_spent }}">
                                </div>
                                
                                <!-- Estimated Hours -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Estimated Hours
                                    </label>
                                    <input type="number" name="estimated_hours" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        min="0" step="0.25" value="{{ $task->estimated_hours }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Task Details -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Task Details</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Related To -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Related To
                                    </label>
                                    <div class="flex space-x-2">
                                        <select name="related_to_type" 
                                            class="w-1/3 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                            <option value="">Type</option>
                                            <option value="stay" {{ $task->related_to_type == 'stay' ? 'selected' : '' }}>Stay</option>
                                            <option value="room" {{ $task->related_to_type == 'room' ? 'selected' : '' }}>Room</option>
                                            <option value="guest" {{ $task->related_to_type == 'guest' ? 'selected' : '' }}>Guest</option>
                                            <option value="facility" {{ $task->related_to_type == 'facility' ? 'selected' : '' }}>Facility</option>
                                        </select>
                                        <input type="text" name="related_to" 
                                            class="w-2/3 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                            value="{{ $task->related_to }}" placeholder="ID or Reference">
                                    </div>
                                </div>
                                
                                <!-- Tags -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tags
                                    </label>
                                    <input type="text" name="tags" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $task->tags }}" placeholder="comma,separated,tags">
                                </div>
                                
                                <!-- Attachments -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Add More Attachments
                                    </label>
                                    <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Add additional files (existing attachments will be preserved)
                                    </p>
                                    
                                    @if($task->attachments && count($task->attachments) > 0)
                                    <div class="mt-3">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Existing Attachments</h4>
                                        <div class="space-y-2">
                                            @foreach($task->attachments as $attachment)
                                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900 rounded">
                                                <div class="flex items-center">
                                                    <i class="fas fa-paperclip text-gray-400 mr-2"></i>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $attachment['name'] }}</span>
                                                </div>
                                                <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" class="text-primary hover:text-primary/80">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Options -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Additional Options</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Recurrence -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Recurrence
                                    </label>
                                    <select name="recurrence" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">No Recurrence</option>
                                        <option value="daily" {{ $task->recurrence == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $task->recurrence == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ $task->recurrence == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ $task->recurrence == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>
                                
                                <!-- Dependencies -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Dependencies (Optional)
                                    </label>
                                    <input type="text" name="dependencies" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $task->dependencies }}" placeholder="Task IDs separated by commas">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Enter task IDs that must be completed before this task can start
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Internal Notes -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Internal Notes</h3>
                            </div>
                            
                            <div>
                                <textarea name="internal_notes" rows="4" 
                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                    placeholder="Add internal notes for this task...">{{ $task->internal_notes }}</textarea>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    These notes are visible to staff only, not to the assignee.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('tasks.show', $task) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-save mr-1"></i> Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Current Information -->
        <div class="col-span-12 lg:col-span-4">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm sticky top-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Current Information</h3>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $task->progress }}%</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Progress</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $task->time_spent ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Hours Spent</p>
                        </div>
                    </div>
                    
                    <!-- Current Status -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Current Status</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Priority:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ ucfirst($task->priority) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Due Date:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ $task->due_date->format('M d, Y') }}</span>
                            </div>
                            @if($task->due_date->isPast() && $task->status !== 'completed')
                            <div class="text-center p-2 bg-danger/10 text-danger rounded text-xs">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Assigned To -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Assigned To</h4>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium text-lg">
                                    {{ substr($task->assignedTo->name, 0, 1) }}
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->assignedTo->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->assignedTo->email }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Role: {{ $task->assignedTo->role }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Summary -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Activity Summary</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span class="text-gray-800 dark:text-white">{{ $task->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Created By:</span>
                                <span class="text-gray-800 dark:text-white">{{ $task->createdBy->name }}</span>
                            </div>
                            @if($task->updated_at != $task->created_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                                <span class="text-gray-800 dark:text-white">{{ $task->updated_at->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($task->completed_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Completed:</span>
                                <span class="text-gray-800 dark:text-white">{{ $task->completed_at->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Important Notes -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Editing Guidelines</h4>
                                <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Changing dates may affect task dependencies</li>
                                        <li>Consider notifying the assignee of major changes</li>
                                        <li>Progress updates should reflect actual work done</li>
                                        <li>Status changes may trigger notifications</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        const dueDate = new Date(document.querySelector('input[name="due_date"]').value);
        const startDate = document.querySelector('input[name="start_date"]').value;
        const progress = parseInt(document.querySelector('input[name="progress"]').value);
        
        if (startDate) {
            const start = new Date(startDate);
            if (start > dueDate) {
                e.preventDefault();
                alert('Start date cannot be after due date.');
                return;
            }
        }
        
        if (progress < 0 || progress > 100) {
            e.preventDefault();
            alert('Progress must be between 0 and 100.');
            return;
        }
        
        if (progress === 100) {
            const status = document.querySelector('select[name="status"]').value;
            if (status !== 'completed') {
                if (!confirm('Progress is 100% but status is not "Completed". Do you want to update status to "Completed"?') {
                    e.preventDefault();
                    return;
                }
                document.querySelector('select[name="status"]').value = 'completed';
            }
        }
        
        if (!confirm('Are you sure you want to update this task?')) {
            e.preventDefault();
        }
    });
    
    // Auto-update status based on progress
    const progressInput = document.querySelector('input[name="progress"]');
    const statusSelect = document.querySelector('select[name="status"]');
    
    progressInput.addEventListener('change', function() {
        const progress = parseInt(this.value);
        
        if (progress === 100 && statusSelect.value !== 'completed') {
            if (confirm('Progress is 100%. Do you want to update status to "Completed"?')) {
                statusSelect.value = 'completed';
            }
        } else if (progress > 0 && progress < 100 && statusSelect.value === 'pending') {
            statusSelect.value = 'in_progress';
        }
    });
});
</script>
@endsection