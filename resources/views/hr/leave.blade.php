@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

@section('content')
<div x-data="{ pageName: `Leave` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ leaveOpen: @js($errors->any() && old('reason') !== null) }">
    @include('hr.partials.header', [
        'title' => 'Leave',
        'subtitle' => 'Requests, approvals and who is away',
        'actions' => '<button type="button" @click="leaveOpen = true" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-plus"></i> Record Leave</button>',
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Waiting for Approval', 'value' => $stats['pending'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('hr.leave.index', ['status' => 'pending'])])
        @include('partials.stat-card', ['label' => 'Away Today', 'value' => $stats['on_leave_today'], 'icon' => 'fa-umbrella-beach', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Approved, Coming Up', 'value' => $stats['upcoming'], 'icon' => 'fa-calendar-alt', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Annual Days per Year', 'value' => $stats['entitlement'], 'icon' => 'fa-calendar-check', 'tone' => 'gray'])
    </div>

    <form method="GET" action="{{ route('hr.leave.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-gray-900">
        <select name="user_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">Everyone</option>
            @foreach ($staff as $member)
                <option value="{{ $member->user_id }}" @selected((int) ($filters['user_id'] ?? 0) === $member->user_id)>{{ $member->user->name }}</option>
            @endforeach
        </select>
        <select name="leave_type" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach (\App\Models\LeaveRequest::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['leave_type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\LeaveRequest::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Employee</th>
                        <th class="px-5 py-3 font-medium">Leave</th>
                        <th class="px-5 py-3 font-medium">Dates</th>
                        <th class="px-5 py-3 font-medium">Reason</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($requests as $request)
                        <tr x-data="{ rejectOpen: false }">
                            <td class="px-5 py-3">
                                @if ($request->staffProfile)
                                    <a href="{{ route('hr.staff.show', $request->staffProfile) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $request->user->name ?? '—' }}</a>
                                @else
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $request->user->name ?? '—' }}</span>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->staffProfile?->department?->name }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $request->type_label }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $request->start_date->format('d M') }} → {{ $request->end_date->format('d M Y') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->days }} {{ Str::plural('day', $request->days) }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                {{ $request->reason }}
                                @if ($request->approval_notes)<p class="text-xs text-gray-500 dark:text-gray-400">Note: {{ $request->approval_notes }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $request->status_classes }}">{{ \App\Models\LeaveRequest::STATUSES[$request->status] }}</span>
                                @if ($request->approver)<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">by {{ $request->approver->name }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($request->status === 'pending' && $request->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('hr.leave.decide', $request) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="rounded-lg bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600 hover:bg-success-500/20">Approve</button>
                                        </form>
                                        <button type="button" @click="rejectOpen = !rejectOpen" class="rounded-lg bg-error-500/10 px-2.5 py-1 text-xs font-medium text-error-600 hover:bg-error-500/20">Reject</button>
                                    @endif
                                    @if (in_array($request->status, ['pending', 'approved'], true))
                                        <form method="POST" action="{{ route('hr.leave.decide', $request) }}" onsubmit="return confirm('Cancel this leave?')">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                                <form x-show="rejectOpen" x-cloak method="POST" action="{{ route('hr.leave.decide', $request) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="approval_notes" required maxlength="500" placeholder="Why?" class="h-8 rounded-lg border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <button type="submit" class="rounded-lg bg-error-500 px-2.5 text-xs font-medium text-white">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-umbrella-beach mb-3 text-4xl"></i><p>No leave on record</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $requests->links() }}</div>
        @endif
    </div>

    @include('hr.partials.leave-modal')
</div>
@endsection
