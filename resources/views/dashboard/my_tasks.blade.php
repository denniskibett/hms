{{-- The signed-in person's open tasks. --}}
@component('dashboard.partials.panel', ['title' => 'My tasks', 'href' => route('tasks.index', ['view' => 'mine'])])
    @include('dashboard.partials.task-list', ['tasks' => $data['tasks'], 'empty' => 'Nothing assigned to you.'])
    @if ($data['unassigned'])
        <p class="border-t border-gray-100 px-5 py-3 text-sm dark:border-gray-800">
            <a href="{{ route('tasks.index', ['view' => 'open']) }}" class="text-primary hover:underline">{{ $data['unassigned'] }} {{ Str::plural('task', $data['unassigned']) }} not assigned to anyone</a>
        </p>
    @endif
@endcomponent
