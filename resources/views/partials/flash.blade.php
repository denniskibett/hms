{{--
    Session flash messages (->with('success'|'error'|'warning'|'status', '...')).
    Included once in layouts/app; dismissible.
--}}
@php
    $flash = collect([
        'success' => ['bg-success-50 border-success-200 text-success-700 dark:bg-success-500/10 dark:border-success-500/30 dark:text-success-400', 'fa-check-circle'],
        'status' => ['bg-success-50 border-success-200 text-success-700 dark:bg-success-500/10 dark:border-success-500/30 dark:text-success-400', 'fa-check-circle'],
        'warning' => ['bg-warning-50 border-warning-200 text-warning-700 dark:bg-warning-500/10 dark:border-warning-500/30 dark:text-warning-400', 'fa-exclamation-triangle'],
        'error' => ['bg-error-50 border-error-200 text-error-700 dark:bg-error-500/10 dark:border-error-500/30 dark:text-error-400', 'fa-times-circle'],
    ])->filter(fn ($style, $key) => is_string(session($key)) && session($key) !== '');
@endphp

@foreach ($flash as $key => [$classes, $icon])
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm {{ $classes }}" role="alert">
        <i class="fas {{ $icon }} mt-0.5"></i>
        <p class="flex-1">{{ session($key) }}</p>
        <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endforeach

@if ($errors->any() && ! ($hideValidationSummary ?? false))
    <div x-data="{ show: true }" x-show="show"
         class="mb-4 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" role="alert">
        <div class="flex items-start justify-between gap-3">
            <p class="font-medium"><i class="fas fa-times-circle mr-1"></i> Please fix the following:</p>
            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><i class="fas fa-times"></i></button>
        </div>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
