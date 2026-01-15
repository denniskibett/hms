@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Task Details</h2>
                <p class="text-gray-600 dark:text-gray-400">View and manage task information</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    <i class="fas fa-edit mr-1"></i> Edit Task
                </a>
                @endcan
                @if($task->status !== 'completed')
                <form action="{{ route('tasks.create', $task) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-success text-white rounded hover:bg-success/90">
                        <i class="fas fa-check-circle mr-1"></i> Mark Complete
                    </button>
                </form>
                @endif
                <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Tasks
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column - Task Details -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <!-- Task Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $task->title }}</h1>
                                <span class="px-3 py-1 text-sm rounded-full 
                                    @if($task->priority === 'urgent') bg-danger/10 text-danger 
                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 
                                    @elseif($task->priority === 'medium') bg-warning/10 text-warning 
                                    @else bg-success/10 text-success @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="px-3 py-1 text-sm rounded-full 
                                    @if($task->status === 'completed') bg-success/10 text-success 
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 
                                    @elseif($task->status === 'overdue') bg-danger/10 text-danger 
                                    @else bg-warning/10 text-warning @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">Task #T{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($task->due_date->isPast() && $task->status !== 'completed')
                            <div class="px-3 py-1 bg-danger/10 text-danger rounded-full text-sm">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                            </div>
                            @endif
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Due: {{ $task->due_date->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Content -->
                <div class="p-6">
                    <!-- Description -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-3">Description</h3>
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! nl2br(e($task->description)) !!}
                        </div>
                    </div>

                    <!-- Task Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Assigned To</h4>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium text-lg">
                                        {{ substr($task->assignedTo->name ?? 'N/A', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->assignedTo->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->assignedTo->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Created By</h4>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium text-lg">
                                        {{ substr($task->createdBy->name ?? 'N/A', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->createdBy->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Category</h4>
                            <div class="flex items-center">
                                @if($task->category)
                                <div class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 text-sm">
                                    {{ $task->category }}
                                </div>
                                @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">No category</span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Estimated Hours</h4>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $task->estimated_hours ? $task->estimated_hours . ' hours' : 'Not specified' }}</p>
                        </div>
                        
                        @if($task->related_to)
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Related To</h4>
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded text-sm">
                                    {{ ucfirst($task->related_to_type) }}
                                </span>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    @if($task->related_to_type === 'stay')
                                    Stay #S{{ str_pad($task->related_to, 5, '0', STR_PAD_LEFT) }}
                                    @elseif($task->related_to_type === 'room')
                                    Room {{ $task->related_to }}
                                    @else
                                    {{ $task->related_to }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                        
                        @if($task->tags)
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Tags</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $task->tags) as $tag)
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs">
                                    {{ trim($tag) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Attachments -->
                    @if($task->attachments && count($task->attachments) > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-3">Attachments</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($task->attachments as $attachment)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                        <i class="fas fa-paperclip text-gray-600 dark:text-gray-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $attachment['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $attachment['size'] }}</p>
                                    </div>
                                    <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" class="text-primary hover:text-primary/80 ml-2">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Comments -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Comments</h3>
                        <div class="space-y-4" id="commentsContainer">
                            @foreach($task->comments as $comment)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium">
                                        {{ substr($comment->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                    {{ $comment->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            @if(auth()->id() === $comment->user_id)
                                            <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-danger hover:text-danger/80">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Add Comment Form -->
                        @if($task->status !== 'completed')
                        <div class="mt-6">
                            <form action="{{ route('tasks.store', $task) }}" method="POST" id="commentForm">
                                @csrf
                                <div class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <textarea name="comment" rows="3" 
                                            class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                            placeholder="Add a comment..." required></textarea>
                                        <div class="mt-2 flex justify-end">
                                            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded hover:bg-primary/90">
                                                <i class="fas fa-paper-plane mr-1"></i> Post Comment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Activity & Actions -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        @if($task->status !== 'completed')
                        <form action="{{ route('tasks.create', $task) }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full flex items-center p-3 bg-success text-white rounded hover:bg-success/90">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Mark as Complete</span>
                            </button>
                        </form>
                        @endif
                        
                        @if($task->status === 'completed')
                        <form action="{{ route('tasks.reopen', $task) }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full flex items-center p-3 bg-warning/10 text-warning rounded hover:bg-warning/20">
                                <i class="fas fa-redo mr-2"></i>
                                <span>Reopen Task</span>
                            </button>
                        </form>
                        @endif
                        
                        @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="flex items-center p-3 bg-primary/10 text-primary rounded hover:bg-primary/20">
                            <i class="fas fa-edit mr-2"></i>
                            <span>Edit Task</span>
                        </a>
                        @endcan
                        
                        @can('delete', $task)
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="block" onsubmit="return confirm('Are you sure you want to delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center p-3 bg-danger/10 text-danger rounded hover:bg-danger/20">
                                <i class="fas fa-trash mr-2"></i>
                                <span>Delete Task</span>
                            </button>
                        </form>
                        @endcan
                        
                        @if($task->assignedStaff && $task->assignedStaff->id === auth()->id())
                        <button onclick="showProgressModal()" class="w-full flex items-center p-3 bg-info/10 text-info rounded hover:bg-info/20">
                            <i class="fas fa-chart-line mr-2"></i>
                            <span>Update Progress</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Task Progress -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Task Progress</h4>
                    <div class="space-y-4">
                        <!-- Progress Bar -->
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600 dark:text-gray-400">Completion</span>
                                <span class="text-gray-800 dark:text-white font-medium">{{ $task->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: {{ $task->progress }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Time Tracking -->
                        <div>
                            <h5 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Time Spent</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $task->time_spent ?? 0 }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Hours</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $task->estimated_hours ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Estimated</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Activity Log</h4>
                    
                    <div class="space-y-4">
                        @php
                            $activities = [];
                            
                            // Task created
                            $activities[] = [
                                'icon' => 'fa-plus-circle',
                                'color' => 'primary',
                                'text' => 'Task created',
                                'time' => $task->created_at->diffForHumans(),
                                'user' => $task->createdBy->name ?? ''
                            ];
                            
                            // Status changes
                            if($task->status_changed_at)
                            $activities[] = [
                                'icon' => $task->status === 'completed' ? 'fa-check-circle' : 'fa-sync-alt',
                                'color' => $task->status === 'completed' ? 'success' : 'warning',
                                'text' => 'Status changed to ' . ucfirst(str_replace('_', ' ', $task->status)),
                                'time' => $task->status_changed_at->diffForHumans(),
                                'user' => $task->updatedBy->name ?? 'System'
                            ];
                            
                            // Progress updates
                            if($task->progress_updated_at)
                            $activities[] = [
                                'icon' => 'fa-chart-line',
                                'color' => 'info',
                                'text' => 'Progress updated to ' . $task->progress . '%',
                                'time' => $task->progress_updated_at->diffForHumans(),
                                'user' => $task->updatedBy->name ?? 'System'
                            ];
                            
                            // Comments
                            foreach($task->comments->take(2) as $comment)
                            $activities[] = [
                                'icon' => 'fa-comment',
                                'color' => 'gray',
                                'text' => 'Added a comment',
                                'time' => $comment->created_at->diffForHumans(),
                                'user' => $comment->user->name
                            ];
                            
                            // Last update
                            if($task->updated_at != $task->created_at)
                            $activities[] = [
                                'icon' => 'fa-edit',
                                'color' => 'warning',
                                'text' => 'Task updated',
                                'time' => $task->updated_at->diffForHumans(),
                                'user' => $task->updatedBy->name ?? 'System'
                            ];
                            
                            // Sort by timestamp
                            usort($activities, function($a, $b) {
                                return strtotime($b['time']) - strtotime($a['time']);
                            });
                        @endphp
                        
                        @foreach($activities as $activity)
                        <div class="flex items-start">
                            <div class="mr-3">
                                <div class="p-2 rounded-full bg-{{ $activity['color'] }}/10 text-{{ $activity['color'] }}">
                                    <i class="fas {{ $activity['icon'] }} text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800 dark:text-white">{{ $activity['text'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span>{{ $activity['user'] }}</span> • 
                                    <span>{{ $activity['time'] }}</span>
                                </p>
                            </div>
                        </div>
                        @endforeach
                        
                        @if(count($activities) === 0)
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-history text-2xl mb-2"></i>
                            <p>No activity recorded</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div id="progressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Update Task Progress</h3>
                <button onclick="closeProgressModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('tasks.create', $task) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Progress (%)</label>
                    <input type="range" name="progress" min="0" max="100" value="{{ $task->progress }}" 
                        class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer" 
                        oninput="updateProgressValue(this.value)">
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500">0%</span>
                        <span id="progressValue" class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->progress }}%</span>
                        <span class="text-xs text-gray-500">100%</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Spent (hours)</label>
                    <input type="number" name="time_spent" step="0.25" min="0" value="{{ $task->time_spent ?? 0 }}" 
                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" 
                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                        placeholder="Add any notes about your progress..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeProgressModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                        Update Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showProgressModal() {
    document.getElementById('progressModal').classList.remove('hidden');
}

function closeProgressModal() {
    document.getElementById('progressModal').classList.add('hidden');
}

function updateProgressValue(value) {
    document.getElementById('progressValue').textContent = value + '%';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('progressModal');
    if (event.target === modal) {
        closeProgressModal();
    }
}

// Handle comment form submission with AJAX
document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new comment to the list
            const commentsContainer = document.getElementById('commentsContainer');
            const newComment = document.createElement('div');
            newComment.className = 'flex items-start space-x-3';
            newComment.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium">
                        ${data.user_initials}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">${data.user_name}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                    Just now
                                </span>
                            </div>
                            <button type="button" onclick="deleteComment(this)" data-id="${data.comment_id}" class="text-xs text-danger hover:text-danger/80">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">${data.comment}</p>
                    </div>
                </div>
            `;
            commentsContainer.appendChild(newComment);
            
            // Clear the form
            form.reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while posting the comment.');
    });
});

function deleteComment(button) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    const commentId = button.getAttribute('data-id');
    const commentElement = button.closest('.flex.items-start.space-x-3');
    
    fetch(`/tasks/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            commentElement.remove();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the comment.');
    });
}
</script>
@endsection