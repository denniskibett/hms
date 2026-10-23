{{--
    Shift patterns: list plus add / edit form. Needs $shifts.
    open: $dispatch('open-shift-form')
--}}
@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('hr.shifts.store'),
        'update' => route('hr.shifts.update', ':id'),
        'destroy' => route('hr.shifts.destroy', ':id'),
        'blank' => ['id' => null, 'name' => '', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning', 'is_active' => true],
     ]))"
     @open-shift-form.window="open()" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="close()"></div>
        <div class="relative w-full max-w-lg space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Shifts</h4>

            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($shifts as $shift)
                    <li class="flex items-center justify-between py-2 {{ $shift->is_active ? '' : 'opacity-60' }}">
                        <span class="text-gray-800 dark:text-white/90">{{ $shift->name }} <span class="text-gray-500">{{ $shift->hours_label }} · {{ $shift->duration_hours }}h</span></span>
                        <button type="button" @click="form = @js($shift->formData()); errors = {}" class="text-gray-400 hover:text-primary"><i class="fas fa-edit"></i></button>
                    </li>
                @empty
                    <li class="py-2 text-gray-500 dark:text-gray-400">None yet.</li>
                @endforelse
            </ul>

            <form @submit.prevent="submit()" class="space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                @include('facilities.partials.form-errors')
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="form.id ? 'Edit ' + form.name : 'New shift'"></p>
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" x-model="form.name" required maxlength="50" placeholder="Name, e.g. Morning" class="{{ $inputClass }}">
                    <select x-model="form.type" class="{{ $inputClass }}">
                        @foreach (\App\Models\Shift::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="time" x-model="form.start_time" required class="{{ $inputClass }}">
                    <input type="time" x-model="form.end_time" required class="{{ $inputClass }}">
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                    <input type="checkbox" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-primary"> In use
                </label>
                <div class="flex items-center justify-between">
                    <div class="flex gap-3">
                        <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete</button>
                        <button type="button" x-show="form.id" @click="open()" class="text-sm text-gray-500 hover:underline">New instead</button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</button>
                        <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facilities.partials.scripts')
