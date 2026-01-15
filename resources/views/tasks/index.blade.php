@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Tasks</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage and track tasks</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <button id="filterDropdownButton" onclick="toggleFilterDropdown()" class="flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <i class="fas fa-filter mr-2"></i> Filter
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg z-10">
                        <div class="p-4">
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white mb-3">Filter Tasks</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                                    <select id="filterStatus" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Priority</label>
                                    <select id="filterPriority" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm">
                                        <option value="">All Priorities</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Assigned To</label>
                                    <select id="filterAssigned" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <button onclick="resetFilters()" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300">
                                        Reset
                                    </button>
                                    <button onclick="applyFilters()" class="px-3 py-1 bg-primary text-white text-xs rounded hover:bg-primary/90">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                    <i class="fas fa-plus mr-1"></i> New Task
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 mr-4">
                    <i class="fas fa-list-check text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Tasks</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-warning/10 text-warning mr-4">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-success/10 text-success mr-4">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-danger/10 text-danger mr-4">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Overdue</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['overdue'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-1 px-6 overflow-x-auto">
                <button onclick="showTaskTab('all')" id="all-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap">All Tasks</button>
                <button onclick="showTaskTab('my')" id="my-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">My Tasks</button>
                <button onclick="showTaskTab('today')" id="today-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Due Today</button>
                <button onclick="showTaskTab('upcoming')" id="upcoming-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Upcoming</button>
                <button onclick="showTaskTab('overdue')" id="overdue-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Overdue</button>
            </nav>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Task
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Priority
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Assigned To
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Due Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="tasksTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tasks as $task)
                    <tr data-status="{{ $task->status }}" data-priority="{{ $task->priority }}" data-assigned="{{ $task->assigned_to }}" data-due="{{ $task->due_date->format('Y-m-d') }}">
                        <td class="px-4 py-4">
                            <div>
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-primary dark:hover:text-primary">
                                    {{ $task->title }}
                                </a>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate max-w-xs">
                                    {{ $task->description }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($task->priority === 'urgent') bg-danger/10 text-danger 
                                @elseif($task->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 
                                @elseif($task->priority === 'medium') bg-warning/10 text-warning 
                                @else bg-success/10 text-success @endif">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($task->status === 'completed') bg-success/10 text-success 
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 
                                @elseif($task->status === 'overdue') bg-danger/10 text-danger 
                                @else bg-warning/10 text-warning @endif">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-medium">
                                        {{ substr($task->assignedTo->name ?? 'N/A', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $task->assignedTo->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $task->due_date->format('M d, Y') }}
                            </div>
                            @if($task->status !== 'completed' && $task->due_date->isPast())
                            <div class="text-xs text-danger mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i> Overdue
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                @can('update', $task)
                                <a href="{{ route('tasks.edit', $task) }}" class="text-warning hover:text-warning/80" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete', $task)
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:text-danger/80" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                                @if($task->status !== 'completed')
                                <form action="{{ route('tasks.create', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-success hover:text-success/80" title="Mark Complete">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        @if($tasks->isEmpty())
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                <i class="fas fa-tasks text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tasks found</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
                Get started by creating a new task or adjust your filters to see more results.
            </p>
            @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                <i class="fas fa-plus mr-1"></i> Create New Task
            </a>
            @endcan
        </div>
        @endif

        <!-- Pagination -->
        @if($tasks->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('filterDropdown');
    const button = document.getElementById('filterDropdownButton');
    
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

function applyFilters() {
    const statusFilter = document.getElementById('filterStatus').value;
    const priorityFilter = document.getElementById('filterPriority').value;
    const assignedFilter = document.getElementById('filterAssigned').value;
    const rows = document.querySelectorAll('#tasksTableBody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (statusFilter && row.dataset.status !== statusFilter) {
            show = false;
        }
        
        if (priorityFilter && row.dataset.priority !== priorityFilter) {
            show = false;
        }
        
        if (assignedFilter && row.dataset.assigned !== assignedFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    document.getElementById('filterDropdown').classList.add('hidden');
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterPriority').value = '';
    document.getElementById('filterAssigned').value = '';
    
    const rows = document.querySelectorAll('#tasksTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    document.getElementById('filterDropdown').classList.add('hidden');
}

function showTaskTab(tab) {
    // Remove active class from all tabs
    document.getElementById('all-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('all-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('my-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('my-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('today-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('today-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('upcoming-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('upcoming-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('overdue-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('overdue-tab').classList.add('border-transparent', 'text-gray-500');
    
    // Add active class to selected tab
    document.getElementById(tab + '-tab').classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(tab + '-tab').classList.add('border-primary', 'text-primary');
    
    // Filter rows based on tab
    const rows = document.querySelectorAll('#tasksTableBody tr');
    const today = new Date().toISOString().split('T')[0];
    
    rows.forEach(row => {
        let show = true;
        const dueDate = row.dataset.due;
        
        switch(tab) {
            case 'my':
                show = row.dataset.assigned === '{{ auth()->id() }}';
                break;
            case 'today':
                show = dueDate === today;
                break;
            case 'upcoming':
                show = dueDate > today;
                break;
            case 'overdue':
                show = dueDate < today && row.dataset.status !== 'completed';
                break;
            default:
                show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endsection