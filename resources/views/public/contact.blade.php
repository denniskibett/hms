@extends('layouts.public')

@section('title', 'Contact')

@php
    $field = 'h-12 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-primary focus:outline-none';
@endphp

@section('content')
<section class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-6 py-16 lg:grid-cols-2">
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Contact us</h1>
        <p class="mt-3 text-gray-600">Questions about rooms, events or anything else? Send us a message or call.</p>
        <dl class="mt-8 space-y-4 text-gray-700">
            @if (SystemHelper::address())<div><dt class="text-sm text-gray-500">Address</dt><dd>{{ SystemHelper::address() }}</dd></div>@endif
            @if (SystemHelper::contactPhone())<div><dt class="text-sm text-gray-500">Phone</dt><dd><a href="tel:{{ SystemHelper::contactPhone() }}" class="hover:text-primary">{{ SystemHelper::contactPhone() }}</a></dd></div>@endif
            @if (SystemHelper::contactEmail())<div><dt class="text-sm text-gray-500">Email</dt><dd><a href="mailto:{{ SystemHelper::contactEmail() }}" class="hover:text-primary">{{ SystemHelper::contactEmail() }}</a></dd></div>@endif
        </dl>
    </div>

    <form method="POST" action="{{ route('contact.submit') }}" class="rounded-2xl border border-gray-200 p-6">
        @csrf
        @if (session('success'))
            <p class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</p>
        @endif
        @if (session('error') || $errors->any())
            <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                {{ session('error') }}
                @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
            </div>
        @endif
        <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="space-y-4">
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-gray-700">Name *</span>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100" class="{{ $field }}">
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-gray-700">Email *</span>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="{{ $field }}">
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-gray-700">Message *</span>
                <textarea name="message" rows="5" required maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-primary focus:outline-none">{{ old('message') }}</textarea>
            </label>
        </div>
        <button type="submit" class="mt-5 rounded-lg bg-primary px-8 py-3 font-semibold text-white hover:bg-primary/90">Send message</button>
    </form>
</section>
@endsection
