@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $statusClasses = [
        'active' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
        'inactive' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        'suspended' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
    ];
    $formData = fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email ?? '', 'phone' => $u->phone ?? '', 'status' => $u->status, 'roles' => $u->roles->pluck('name')->values()->all()];
@endphp

@section('content')
<div x-data="{ pageName: `Users` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Users</h2>
            <p class="text-gray-600 dark:text-gray-400">Who can sign in, and what they can do</p>
        </div>
        <button type="button" @click="$dispatch('open-user-form')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
            <i class="fas fa-user-plus"></i> New Account
        </button>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Active staff accounts', 'value' => $stats['staff'], 'icon' => 'fa-id-badge', 'tone' => 'primary', 'href' => route('users.index')])
        @include('partials.stat-card', ['label' => 'Admins', 'value' => $stats['admins'], 'icon' => 'fa-user-shield', 'tone' => 'info', 'href' => route('users.index', ['role' => 'admin'])])
        @include('partials.stat-card', ['label' => 'Guest accounts', 'value' => $stats['guests'], 'icon' => 'fa-users', 'tone' => 'gray', 'href' => route('users.index', ['role' => 'guest'])])
        @include('partials.stat-card', ['label' => 'Staff blocked', 'value' => $stats['blocked'], 'icon' => 'fa-user-lock', 'tone' => 'error', 'href' => route('users.index', ['status' => 'inactive'])])
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email or phone" class="{{ $inputClass }} lg:col-span-2">
        <select name="role" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="staff" @selected($filters['role'] === 'staff')>All staff</option>
            <option value="all" @selected($filters['role'] === 'all')>Everyone (incl. guests)</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ Str::headline($role) }}</option>
            @endforeach
        </select>
        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">Any status</option>
            @foreach (\App\Http\Controllers\UserController::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Sign-in / contact</th>
                        <th class="px-5 py-3 font-medium">Roles</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('users.show', $user) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $user->name }}</a>
                                @if ($user->id === auth()->id())<span class="ml-1 text-xs text-gray-500">(you)</span>@endif
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($user->staffProfile)<a href="{{ route('hr.staff.show', $user->staffProfile) }}" class="hover:text-primary">HR record</a>@endif
                                    @if ($user->guestProfile)<a href="{{ route('guests.show', $user->guestProfile) }}" class="hover:text-primary">Guest profile</a>@endif
                                </p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $user->email ?: 'No email (cannot sign in)' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->phone }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $role)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ Str::headline($role->name) }}</span>
                                    @empty
                                        <span class="text-xs text-warning-600">No role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses[$user->status] ?? '' }}">{{ \App\Http\Controllers\UserController::STATUSES[$user->status] ?? $user->status }}</span></td>
                            <td class="px-5 py-3 text-right">
                                <button type="button" @click="$dispatch('open-user-form', @js($formData($user)))" class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-edit"></i> Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No accounts match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $users->links() }}</div>
        @endif
    </div>
</div>

@include('users.partials.modal-form')
@endsection
