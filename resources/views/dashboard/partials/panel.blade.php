{{--
    Dashboard card with a title, optional "see all" link and a body slot.
    @component('dashboard.partials.panel', ['title' => ..., 'href' => route(...), 'link' => 'See all'])
        ...body...
    @endcomponent
--}}
<div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
        @isset($href)
            <a href="{{ $href }}" class="text-sm text-primary hover:underline">{{ $link ?? 'See all' }}</a>
        @endisset
    </div>
    {{ $slot }}
</div>
