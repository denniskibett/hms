@extends('layouts.app')

@php
    use App\Enums\KitchenOrderStatus;

    $columns = [
        [KitchenOrderStatus::Pending, 'border-warning-400'],
        [KitchenOrderStatus::Preparing, 'border-info-400'],
        [KitchenOrderStatus::Ready, 'border-success-400'],
    ];
@endphp

@section('content')
<div x-data="{ pageName: `Kitchen` }">
    @include('partials.breadcrumb')
</div>

{{-- Refresh every 30 s so new orders appear, unless a dialog is open --}}
<div class="space-y-6"
     x-data
     x-init="setInterval(() => { if (!document.body.classList.contains('overflow-hidden')) window.location.reload() }, 30000)">
    @include('kitchen.partials.header', ['title' => 'Kitchen', 'subtitle' => 'Orders waiting to be prepared, cooked and served. Refreshes every 30 seconds.'])

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @foreach ($columns as [$status, $accent])
            @php $list = $orders->get($status->value, collect()); @endphp
            <div class="rounded-2xl border-t-4 {{ $accent }} bg-gray-50 p-4 dark:bg-white/[0.02]">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-white/90">{{ $status->label() }}</h3>
                    <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $list->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($list as $order)
                        @include('kitchen.partials.order-card', ['order' => $order])
                    @empty
                        <p class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">None</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('kitchen.partials.order-modal')
@endsection
