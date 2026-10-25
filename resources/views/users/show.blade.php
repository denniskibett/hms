@extends('layouts.app')

@php
    $formData = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email ?? '', 'phone' => $user->phone ?? '', 'status' => $user->status, 'roles' => $user->roles->pluck('name')->values()->all()];
    $canSignIn = $user->status === 'active' && $user->email;
@endphp

@section('content')
<div x-data="{ pageName: @js($user->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"><i class="fas fa-arrow-left"></i> Back to Users</a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $user->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $user->roles->pluck('name')->map(fn ($r) => Str::headline($r))->implode(', ') ?: 'No role' }} ·
                <span class="{{ $canSignIn ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-400' }}">{{ $canSignIn ? 'Can sign in' : 'Cannot sign in' }}</span>
            </p>
        </div>
        <button type="button" @click="$dispatch('open-user-form', @js($formData))" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
            <i class="fas fa-edit"></i> Edit account
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @component('dashboard.partials.panel', ['title' => 'Recent activity'])
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($activity as $entry)
                        <li class="flex flex-wrap justify-between gap-2 px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ $entry->description ?: Str::headline($entry->action) }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $entry->created_at?->format('d M Y H:i') }}{{ $entry->ip_address ? ' · ' . $entry->ip_address : '' }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Nothing recorded yet.</li>
                    @endforelse
                </ul>
            @endcomponent
        </div>

        <div class="space-y-6">
            @component('dashboard.partials.panel', ['title' => 'Account'])
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd class="break-all text-right text-gray-800 dark:text-white/90">{{ $user->email ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd class="text-gray-800 dark:text-white/90">{{ $user->phone ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="text-gray-800 dark:text-white/90">{{ \App\Http\Controllers\UserController::STATUSES[$user->status] ?? $user->status }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Created</dt><dd class="text-gray-800 dark:text-white/90">{{ $user->created_at?->format('d M Y') }}</dd></div>
                </dl>
            @endcomponent

            @if ($user->staffProfile || $user->guestProfile)
                @component('dashboard.partials.panel', ['title' => 'Linked records'])
                    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        @if ($user->staffProfile)
                            <li class="flex justify-between px-5 py-3">
                                <span class="text-gray-800 dark:text-white/90">Staff: {{ $user->staffProfile->job_title ?: 'no job title' }}{{ $user->staffProfile->department ? ', ' . $user->staffProfile->department->name : '' }}</span>
                                <a href="{{ route('hr.staff.show', $user->staffProfile) }}" class="text-primary hover:underline">HR</a>
                            </li>
                        @endif
                        @if ($user->guestProfile)
                            <li class="flex justify-between px-5 py-3">
                                <span class="text-gray-800 dark:text-white/90">Guest profile</span>
                                <a href="{{ route('guests.show', $user->guestProfile) }}" class="text-primary hover:underline">Open</a>
                            </li>
                        @endif
                    </ul>
                @endcomponent
            @endif
        </div>
    </div>
</div>

@include('users.partials.modal-form')
@endsection
