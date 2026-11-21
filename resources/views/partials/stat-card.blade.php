{{--
    Summary number card.
    @include('partials.stat-card', [
        'label' => 'Available', 'value' => 12, 'icon' => 'fa-door-open',
        'tone' => 'success',            // primary | success | warning | error | info | gray
        'href' => route(...),            // optional: makes the card a link
    ])
--}}
@php
    $tones = [
        'primary' => ['text-primary', 'bg-primary/10'],
        'success' => ['text-success-600 dark:text-success-500', 'bg-success-500/10'],
        'warning' => ['text-warning-600 dark:text-warning-500', 'bg-warning-500/10'],
        'error' => ['text-error-600 dark:text-error-500', 'bg-error-500/10'],
        'info' => ['text-info-600 dark:text-info-500', 'bg-info-500/10'],
        'gray' => ['text-gray-600 dark:text-gray-400', 'bg-gray-500/10'],
    ];
    [$textClass, $bgClass] = $tones[$tone ?? 'primary'] ?? $tones['primary'];
    $tag = isset($href) ? 'a' : 'div';
@endphp
<{{ $tag }} @isset($href) href="{{ $href }}" @endisset
    class="block rounded-2xl border border-gray-200 bg-white p-5 {{ isset($href) ? 'transition hover:border-gray-300 hover:shadow-theme-sm' : '' }}">
    <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $bgClass }}">
            <i class="fas {{ $icon }} text-lg {{ $textClass }}"></i>
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm text-gray-500">{{ $label }}</p>
            <h4 class="mt-0.5 text-2xl font-bold {{ ($tone ?? 'primary') === 'error' && (float) preg_replace('/[^\d.]/', '', (string) $value) > 0 ? 'text-error-600' : 'text-gray-800' }}">{{ $value }}</h4>
        </div>
    </div>
</{{ $tag }}>
