@extends('layouts.public')

@section('title', 'Request received')

@section('content')
<section class="mx-auto max-w-2xl px-4 py-24 text-center">
    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-4xl text-green-700" data-reveal="zoom"><i class="fa-solid fa-check"></i></div>
    <h1 class="mt-6 font-display text-3xl md:text-4xl" data-split>Thank you, we have your request</h1>
    <p class="mt-4 text-lg text-muted" data-reveal="up">Your reference is <span class="font-semibold text-ink">{{ $reference }}</span>. We'll call or text you shortly to confirm your stay.</p>
    @if (SystemHelper::contactPhone())
        <p class="mt-2 text-muted" data-reveal="up">In a hurry? Call us on <a href="tel:{{ SystemHelper::contactPhone() }}" class="font-semibold text-clay-text">{{ SystemHelper::contactPhone() }}</a>.</p>
    @endif
    <a href="{{ route('home') }}" class="site-btn-outline mt-8" data-reveal="up">Back to home</a>
</section>
@endsection
