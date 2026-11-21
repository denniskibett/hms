@extends('layouts.public')

@section('title', 'Restaurant & bar')
@section('header_on_dark', '1')

@section('content')
    @include('public.partials.hero', [
        'image' => asset('images/site/lobby.jpg'),
        'eyebrow' => 'Eat & drink',
        'title' => 'Restaurant & bar',
        'text' => 'Open to hotel guests and visitors. Staying with us? Charge it to your room and pay once at check-out.',
    ])

    <div id="after-hero" class="mx-auto flex max-w-[1240px] flex-col gap-12 px-4 py-16 md:px-6 lg:flex-row lg:items-start"
         x-data="{ tab: @js((string) optional($menu->first())->id) }">
        <div class="min-w-0 flex-1">
            @if ($menu->isNotEmpty())
                <div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Menu sections" data-stagger=".06" data-effect="down">
                    @foreach ($menu as $category)
                        <button type="button" role="tab" :aria-selected="tab === '{{ $category->id }}'" @click="tab = '{{ $category->id }}'"
                                class="rounded-full border-[1.5px] px-4 py-2.5 text-[15px] font-semibold transition"
                                :class="tab === '{{ $category->id }}' ? 'border-clay bg-clay text-white' : 'border-[#D6CEC2] bg-white text-ink hover:border-clay'">{{ $category->name }}</button>
                    @endforeach
                </div>
                @foreach ($menu as $category)
                    <div x-show="tab === '{{ $category->id }}'" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-4" role="tabpanel" class="overflow-hidden rounded-xl border border-line bg-white" data-reveal="up">
                        @foreach ($category->activeItems as $item)
                            <div class="flex justify-between gap-4 border-b border-[#F1ECE4] px-5 py-4 transition last:border-0 hover:bg-cream/60">
                                <div>
                                    <div class="font-semibold">{{ $item->name }}</div>
                                    @if ($item->description)<div class="mt-1 text-sm text-muted">{{ $item->description }}</div>@endif
                                </div>
                                @if ($item->price_range)<div class="whitespace-nowrap font-bold">{{ $item->price_range }}</div>@endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <div class="rounded-xl border border-line bg-white p-8 text-muted" data-reveal="up">Our menu is being updated. Call the front desk to hear today's dishes.</div>
            @endif
        </div>

        <aside class="flex w-full flex-col gap-4 lg:w-[360px] lg:flex-none" data-stagger=".12" data-effect="right">
            <div class="flex flex-col gap-3 rounded-xl border border-line bg-white p-6">
                <h2 class="text-lg font-semibold">Staying with us?</h2>
                <p class="text-[15px] leading-relaxed text-muted">Give your room number and the order goes on your room bill. Room service is in your guest account too.</p>
                <a href="{{ route('login') }}" class="font-semibold text-clay-text">Sign in to order <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
            <a href="{{ route('contact', ['topic' => 'Restaurant table']) }}" class="site-btn">Reserve a table</a>
        </aside>
    </div>
@endsection
