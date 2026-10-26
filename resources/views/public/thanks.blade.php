@extends('layouts.public')

@section('title', 'Request received')

@section('content')
<section class="mx-auto max-w-2xl px-6 py-24 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl text-green-600"><i class="fas fa-check"></i></div>
    <h1 class="mt-6 text-3xl font-bold text-gray-900">Thank you, we have your request</h1>
    <p class="mt-3 text-gray-600">Your reference is <span class="font-semibold text-gray-900">{{ $reference }}</span>. We'll call or text you shortly to confirm your stay.</p>
    @if (SystemHelper::contactPhone())
        <p class="mt-2 text-gray-600">In a hurry? Call us on <a href="tel:{{ SystemHelper::contactPhone() }}" class="font-semibold text-primary">{{ SystemHelper::contactPhone() }}</a>.</p>
    @endif
    <a href="{{ route('home') }}" class="mt-8 inline-block rounded-lg border border-primary px-6 py-3 font-semibold text-primary hover:bg-primary hover:text-white">Back to home</a>
</section>
@endsection
