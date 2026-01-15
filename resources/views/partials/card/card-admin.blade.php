<div
    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
>
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
        {{ $title }}
    </p>

    <div class="mt-3 flex items-end justify-between">
        <div>
            <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                {{ $value }}
            </h4>
        </div>

        <div class="flex items-center">
            @if(isset($icon))
            <div class="p-2 rounded-lg {{ $iconBgColor ?? 'bg-primary-50 dark:bg-primary-500/15' }}">
                {!! $icon !!}
            </div>
            @endif
        </div>
    </div>
</div>