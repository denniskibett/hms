@extends('layouts.public')

@section('title', 'Contact')

@php
    $phone = SystemHelper::contactPhone();
    $email = SystemHelper::contactEmail();
    $address = SystemHelper::address();
@endphp

@section('content')
<section class="mx-auto grid max-w-[1240px] grid-cols-1 gap-12 px-4 py-14 md:px-6 lg:grid-cols-2">
    <div class="flex flex-col gap-6">
        <h1 class="font-display text-4xl md:text-5xl" data-split>Contact us</h1>
        <p class="text-lg leading-relaxed text-muted" data-reveal="left">Questions about rooms, company accounts, events or the restaurant? Send a message or call.</p>
        <dl class="grid gap-4" data-stagger=".1" data-effect="left">
            @if ($phone)<div><dt class="text-[13px] font-semibold text-muted">Phone</dt><dd><a href="tel:{{ $phone }}" class="font-semibold text-ink">{{ $phone }}</a></dd></div>@endif
            @if ($email)<div><dt class="text-[13px] font-semibold text-muted">Email</dt><dd><a href="mailto:{{ $email }}" class="font-semibold text-clay-text">{{ $email }}</a></dd></div>@endif
            @if ($address)<div><dt class="text-[13px] font-semibold text-muted">Address</dt><dd class="font-semibold">{{ $address }}</dd></div>@endif
        </dl>
        @if ($address)
            <iframe title="Map" src="https://maps.google.com/maps?q={{ urlencode(SystemHelper::appName() . ', ' . $address) }}&output=embed" class="h-[300px] w-full rounded-xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-reveal="blur"></iframe>
        @endif
    </div>
    <div data-reveal="right">@include('public.partials.contact-form')</div>
</section>
@endsection
