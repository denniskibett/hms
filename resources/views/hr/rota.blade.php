@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';

    // Cell data for Alpine: "userId|Y-m-d" => assignment or leave
    $cells = [];
    foreach ($staff as $member) {
        foreach ($days as $day) {
            $date = $day->toDateString();
            $a = $assignments->get($member->user_id)?->get($date);
            $onLeave = $leave->get($member->user_id)?->first(fn ($l) => $l->start_date->lte($day) && $l->end_date->gte($day));
            $cells["{$member->user_id}|{$date}"] = [
                'shift_id' => $a?->shift_id ?? '',
                'status' => $a?->status ?? 'scheduled',
                'overtime_hours' => (float) ($a?->overtime_hours ?? 0),
                'notes' => $a?->notes ?? '',
                'leave' => $onLeave?->type_label,
            ];
        }
    }
    $shiftList = $shifts->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'hours' => $s->hours_label, 'is_active' => $s->is_active, 'type' => $s->type])->values();
    $palette = ['morning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400', 'evening' => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500', 'night' => 'bg-gray-800 text-white dark:bg-gray-700', 'custom' => 'bg-primary/10 text-primary'];
@endphp

@section('content')
<div x-data="{ pageName: `Rota` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="rota(@js(['cells' => $cells, 'shifts' => $shiftList, 'palette' => $palette, 'week' => $start->toDateString()]))">
    @include('hr.partials.header', [
        'title' => 'Rota',
        'subtitle' => 'Who works which shift. Click a day to set the shift, mark attendance or add overtime.',
        'actions' => '<button type="button" @click="$dispatch(\'open-shift-form\')" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-clock"></i> Shifts</button>'
            . '<button type="button" @click="copyWeek()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-copy"></i> Copy last week</button>',
    ])

    <!-- Week & department -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('hr.rota.index', array_filter(['week' => $start->copy()->subWeek()->toDateString(), 'department_id' => $filters['department_id'] ?? null])) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-chevron-left"></i></a>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $start->format('d M') }} – {{ $days->last()->format('d M Y') }}</span>
            <a href="{{ route('hr.rota.index', array_filter(['week' => $start->copy()->addWeek()->toDateString(), 'department_id' => $filters['department_id'] ?? null])) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-chevron-right"></i></a>
            <a href="{{ route('hr.rota.index', array_filter(['department_id' => $filters['department_id'] ?? null])) }}" class="text-sm text-primary hover:underline">This week</a>
        </div>
        <form method="GET" action="{{ route('hr.rota.index') }}">
            <input type="hidden" name="week" value="{{ $start->toDateString() }}">
            <select name="department_id" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="">All departments</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($shifts->isEmpty())
        <div class="rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
            No shifts set up yet. Add your shift times (e.g. Morning 06:00–14:00) with the <strong>Shifts</strong> button.
        </div>
    @endif

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="sticky left-0 bg-gray-50 px-4 py-3 text-left font-medium dark:bg-gray-900">Employee</th>
                        @foreach ($days as $day)
                            <th class="min-w-28 px-2 py-3 text-center font-medium {{ $day->isToday() ? 'text-primary' : '' }}">{{ $day->format('D') }}<br><span class="text-xs font-normal">{{ $day->format('d M') }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($staff as $member)
                        <tr>
                            <td class="sticky left-0 whitespace-nowrap bg-white px-4 py-2 dark:bg-gray-900">
                                <a href="{{ route('hr.staff.show', $member) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $member->user->name }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->job_title }}</p>
                            </td>
                            @foreach ($days as $day)
                                @php $key = $member->user_id . '|' . $day->toDateString(); @endphp
                                <td class="px-1 py-1 text-center">
                                    <button type="button" @click="edit(@js($key), @js($member->user->name . ', ' . $day->format('D d M')))"
                                            class="h-14 w-full rounded-lg border border-dashed border-gray-200 px-1 text-xs hover:border-primary dark:border-gray-700"
                                            :class="cellClass(@js($key))">
                                        <template x-if="cells[@js($key)].leave && !cells[@js($key)].shift_id">
                                            <span class="font-medium" x-text="cells[@js($key)].leave + ' leave'"></span>
                                        </template>
                                        <template x-if="cells[@js($key)].shift_id">
                                            <span>
                                                <span class="block font-medium" x-text="shiftName(cells[@js($key)].shift_id)"></span>
                                                <span class="block opacity-75" x-text="statusText(cells[@js($key)])"></span>
                                            </span>
                                        </template>
                                        <template x-if="!cells[@js($key)].shift_id && !cells[@js($key)].leave">
                                            <span class="text-gray-300 dark:text-gray-600">Off</span>
                                        </template>
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No staff{{ ($filters['department_id'] ?? null) ? ' in this department' : '' }}. Add employees under Staff.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit a day -->
    <div x-show="editing" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="editing = null">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="editing = null"></div>
        <form @submit.prevent="save()" class="relative w-full max-w-md space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="title"></h4>
            <p x-show="draft.leave" class="text-sm text-info-600 dark:text-info-400" x-text="'Approved ' + draft.leave + ' leave on this day.'"></p>
            <div>
                <label class="{{ $labelClass }}">Shift</label>
                <select x-model="draft.shift_id" class="{{ $inputClass }}">
                    <option value="">Off</option>
                    <template x-for="s in shifts" :key="s.id">
                        <option :value="s.id" :selected="String(s.id) === String(draft.shift_id)" x-show="s.is_active || String(s.id) === String(draft.shift_id)" x-text="`${s.name} (${s.hours})`"></option>
                    </template>
                </select>
            </div>
            <div x-show="draft.shift_id" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Attendance</label>
                    <select x-model="draft.status" class="{{ $inputClass }}">
                        @foreach (\App\Models\ShiftAssignment::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Overtime (hours)</label>
                    <input type="number" x-model.number="draft.overtime_hours" min="0" max="24" step="0.5" class="{{ $inputClass }}">
                </div>
                <div class="col-span-2">
                    <label class="{{ $labelClass }}">Note</label>
                    <input type="text" x-model="draft.notes" maxlength="255" class="{{ $inputClass }}">
                </div>
            </div>
            <p x-show="draft.shift_id && draft.overtime_hours > 0 && draft.status !== 'completed'" class="text-xs text-warning-600 dark:text-warning-400">Overtime is paid only on shifts marked Worked.</p>
            <p x-show="error" x-text="error" class="text-sm text-error-600"></p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="editing = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="saving" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
            </div>
        </form>
    </div>

    @include('hr.partials.shifts-modal')
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rota', (config) => ({
        cells: config.cells,
        shifts: config.shifts,
        editing: null,
        title: '',
        draft: {},
        saving: false,
        error: '',

        shiftName(id) {
            return this.shifts.find(s => String(s.id) === String(id))?.name ?? '?';
        },

        statusText(cell) {
            const labels = @js(\App\Models\ShiftAssignment::STATUSES);
            const overtime = cell.overtime_hours > 0 ? ` +${cell.overtime_hours}h` : '';
            return cell.status === 'scheduled' ? this.shifts.find(s => String(s.id) === String(cell.shift_id))?.hours ?? '' : labels[cell.status] + overtime;
        },

        cellClass(key) {
            const cell = this.cells[key];
            if (!cell.shift_id) return cell.leave ? 'border-solid border-info-200 bg-info-50 text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400' : '';
            if (cell.status === 'absent') return 'border-solid border-error-200 bg-error-50 text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400';
            const type = this.shifts.find(s => String(s.id) === String(cell.shift_id))?.type ?? 'custom';
            return 'border-solid border-transparent ' + config.palette[type];
        },

        edit(key, title) {
            this.editing = key;
            this.title = title;
            this.draft = { ...this.cells[key] };
            this.error = '';
        },

        async post(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(body),
            });
            return [response, await response.json().catch(() => ({}))];
        },

        async save() {
            const [userId, date] = this.editing.split('|');
            this.saving = true;
            this.error = '';
            try {
                const [response, data] = await this.post(@js(route('hr.rota.assign')), {
                    user_id: userId, date, shift_id: this.draft.shift_id || null,
                    status: this.draft.status, overtime_hours: this.draft.overtime_hours || 0, notes: this.draft.notes,
                });
                if (!response.ok || data.success === false) {
                    this.error = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Could not save.');
                    return;
                }
                this.cells[this.editing] = { ...this.draft, status: this.draft.shift_id ? this.draft.status : 'scheduled' };
                this.editing = null;
            } finally {
                this.saving = false;
            }
        },

        async copyWeek() {
            if (!confirm('Fill this week\'s empty days with last week\'s shifts?')) return;
            const [response, data] = await this.post(@js(route('hr.rota.copy-week')), { week: config.week });
            alert(data.message || (response.ok ? 'Done' : 'Could not copy'));
            if (response.ok) window.location.reload();
        },
    }));
});
</script>
@endsection
