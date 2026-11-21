{{-- Photo banner. @include('public.partials.hero', ['image' => ..., 'eyebrow' => ?, 'title' => ..., 'text' => ?, 'tall' => bool]) --}}
@php $tall ??= false; @endphp
<section class="relative flex overflow-hidden {{ $tall ? 'min-h-[100svh] items-end' : 'min-h-[440px] items-end md:min-h-[520px]' }}">
    <div class="hero-media" style="background-image:url('{{ $image }}')" role="img" aria-label="{{ $alt ?? '' }}"></div>
    <div class="hero-shade"></div>
    <div class="relative mx-auto w-full max-w-[1240px] px-4 pb-14 pt-36 md:px-6 {{ $tall ? 'md:pb-20' : '' }}">
        @isset($eyebrow)
            <p class="mb-3 text-sm font-semibold uppercase tracking-[.16em] text-amber-glow" data-reveal="left">{{ $eyebrow }}</p>
        @endisset
        <h1 class="max-w-4xl font-display text-4xl font-bold leading-[1.08] text-white {{ $tall ? 'md:text-7xl' : 'md:text-6xl' }}" data-split>{{ $title }}</h1>
        @isset($text)
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-white/95 md:text-xl" data-reveal="up" style="--delay:.5s">{{ $text }}</p>
        @endisset
        {{ $slot ?? '' }}
        @yield('hero_extra')
    </div>
    @if ($tall)
        <a href="#after-hero" class="scroll-cue absolute bottom-6 left-1/2 hidden -translate-x-1/2 text-2xl text-white md:block" aria-label="Scroll down"><i class="fa-solid fa-chevron-down"></i></a>
    @endif
</section>
