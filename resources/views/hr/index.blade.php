@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $hasFilters = ($filters['search'] ?? null) || ($filters['department_id'] ?? null) || $filters['status'] !== 'current';
@endphp

@section('content')
<div x-data="{ pageName: `Staff` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ departmentsOpen: false }">
    @include('hr.partials.header', [
        'title' => 'Staff',
        'subtitle' => 'Employees, their jobs, pay details and system access',
        'actions' => '<button type="button" @click="departmentsOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-sitemap"></i> Departments</button>'
            . '<button type="button" @click="$dispatch(\'open-staff-form\')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-user-plus"></i> New Employee</button>',
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Employees', 'value' => $stats['active'], 'icon' => 'fa-users', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'On Leave Today', 'value' => $stats['on_leave_today'], 'icon' => 'fa-umbrella-beach', 'tone' => 'info', 'href' => route('hr.leave.index', ['status' => 'approved'])])
        @include('partials.stat-card', ['label' => 'Leave to Approve', 'value' => $stats['pending_leave'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('hr.leave.index', ['status' => 'pending'])])
        @include('partials.stat-card', ['label' => 'Monthly Basic Pay', 'value' => $money($stats['monthly_salaries']), 'icon' => 'fa-money-check-alt', 'tone' => 'success', 'href' => route('hr.payroll.index')])
    </div>

    <form method="GET" action="{{ route('hr.staff.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, phone, email, ID or job" class="{{ $inputClass }} lg:col-span-2">
        <select name="department_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="current" @selected($filters['status'] === 'current')>Current staff</option>
                @foreach (\App\Models\StaffProfile::EMPLOYMENT_STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($hasFilters)
                <a href="{{ route('hr.staff.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Clear</a>
            @endif
        </div>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Employee</th>
                        <th class="px-5 py-3 font-medium">Job</th>
                        <th class="px-5 py-3 font-medium">Contact</th>
                        <th class="px-5 py-3 font-medium">Access</th>
                        <th class="px-5 py-3 text-right font-medium">Basic pay</th>
                        <th class="px-5 py-3 font-medium">Terms</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($staff as $member)
                        @php $role = $member->user->roles->first(fn ($r) => $r->name !== 'guest'); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('hr.staff.show', $member) }}" class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{{ Str::upper(Str::substr($member->user->name, 0, 1)) }}</span>
                                    <span class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $member->user->name }}</span>
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $member->job_title ?: '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->department->name ?? 'No department' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $member->user->phone ?: '—' }}</p>
                                @if ($member->user->email)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->user->email }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $role ? Str::headline($role->name) : 'None' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-800 dark:text-white/90">{{ $money($member->salary) }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="text-gray-700 dark:text-gray-300">{{ $member->status_label }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">since {{ $member->hire_date->format('M Y') }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users mb-3 text-4xl"></i>
                                <p class="text-lg">{{ $hasFilters ? 'No staff match these filters' : 'No staff on record yet' }}</p>
                                @unless ($hasFilters)
                                    <button type="button" @click="$dispatch('open-staff-form')" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                        <i class="fas fa-user-plus"></i> Add the first employee
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($staff->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $staff->links() }}</div>
        @endif
    </div>

    <!-- Departments -->
    <div x-show="departmentsOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="departmentsOpen = false"
         x-data="{
            name: '', error: '',
            async send(url, method, body = null) {
                this.error = '';
                const r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: body ? JSON.stringify(body) : null });
                const data = await r.json().catch(() => ({}));
                if (r.ok && data.success !== false) return window.location.reload();
                this.error = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Something went wrong.');
            },
         }">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="departmentsOpen = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Departments</h4>
            <ul class="mt-4 divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @foreach ($departments as $department)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-gray-800 dark:text-white/90">{{ $department->name }}</span>
                        <button type="button" @click="confirm(@js('Delete ' . $department->name . '?')) && send(@js(route('hr.departments.destroy', $department)), 'DELETE')" class="text-gray-400 hover:text-error-500"><i class="fas fa-trash"></i></button>
                    </li>
                @endforeach
            </ul>
            <form @submit.prevent="send(@js(route('hr.departments.store')), 'POST', { name })" class="mt-4 flex gap-2">
                <input type="text" x-model="name" required maxlength="100" placeholder="New department" class="{{ $inputClass }}">
                <button type="submit" class="rounded-lg bg-primary px-4 text-sm font-medium text-white hover:bg-primary/90">Add</button>
            </form>
            <p x-show="error" x-text="error" class="mt-2 text-sm text-error-600"></p>
        </div>
    </div>
</div>

@include('hr.partials.staff-modal')
@endsection
