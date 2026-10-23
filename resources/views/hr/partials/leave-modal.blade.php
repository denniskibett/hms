{{--
    Record leave. Parent scope needs `leaveOpen`. Pass $fixedStaff for one
    employee, or $staff (active staff list) to choose.
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-show="leaveOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="leaveOpen = false">
    <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="leaveOpen = false"></div>
    <form method="POST" action="{{ route('hr.leave.store') }}" class="relative w-full max-w-lg space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
        @csrf
        <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Record leave{{ isset($fixedStaff) ? ' for ' . $fixedStaff->user->name : '' }}</h4>

        @if (isset($fixedStaff))
            <input type="hidden" name="user_id" value="{{ $fixedStaff->user_id }}">
        @else
            <div>
                <label class="{{ $labelClass }}">Employee *</label>
                <select name="user_id" required class="{{ $inputClass }}">
                    <option value="">Choose…</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->user_id }}" @selected((int) old('user_id') === $member->user_id)>{{ $member->user->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="{{ $labelClass }}">Type *</label>
            <select name="leave_type" class="{{ $inputClass }}">
                @foreach (\App\Models\LeaveRequest::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('leave_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">From *</label>
                <input type="date" name="start_date" required value="{{ old('start_date', today()->toDateString()) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">To *</label>
                <input type="date" name="end_date" required value="{{ old('end_date', today()->toDateString()) }}" class="{{ $inputClass }}">
            </div>
        </div>
        <div>
            <label class="{{ $labelClass }}">Reason *</label>
            <input type="text" name="reason" required maxlength="1000" value="{{ old('reason') }}" class="{{ $inputClass }}">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">It is saved as pending until a manager or HR approves it.</p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="leaveOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save</button>
        </div>
    </form>
</div>
