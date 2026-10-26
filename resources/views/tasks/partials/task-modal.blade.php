{{--
    New / edit task. Needs $taskTypes, $staff, $rooms.
    open: $dispatch('open-task-form') | $dispatch('open-task-form', { room_id: 3 }) | $dispatch('open-task-form', @js($task->formData()))
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $types = $taskTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'category' => $t->category, 'requires_room' => $t->requires_room, 'minutes' => $t->default_estimated_minutes])->values();
@endphp

<div x-data="facilityForm(@js([
        'store' => route('tasks.store'),
        'update' => route('tasks.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'task_type_id' => '', 'title' => '', 'description' => '', 'assigned_to' => '', 'room_id' => '', 'blocks_room' => false,
                    'priority' => 'medium', 'due_date' => now()->addHours(2)->format('Y-m-d\TH:i'), 'estimated_minutes' => null,
                    'can_repeat' => true, 'recurrence_pattern' => '', 'recurrence_interval' => 1, 'recurrence_end_date' => ''],
     ]))"
     @open-task-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form x-data="{ types: @js($types) }" @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Task' : 'New Task'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Kind of job *</label>
                    <select x-model.number="form.task_type_id" required class="{{ $inputClass }}"
                            @change="const t = types.find(t => t.id === form.task_type_id); if (t) { form.title ||= t.name; form.estimated_minutes ||= t.minutes; }">
                        <option value="">Choose…</option>
                        @foreach ($taskTypes->groupBy('category') as $category => $group)
                            <optgroup label="{{ \App\Models\TaskType::CATEGORIES[$category] ?? $category }}">
                                @foreach ($group as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Room <span class="font-normal text-gray-500" x-show="!types.find(t => t.id === form.task_type_id)?.requires_room">(optional)</span></label>
                    <select x-model="form.room_id" class="{{ $inputClass }}" :required="!!types.find(t => t.id === form.task_type_id)?.requires_room">
                        <option value="">—</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}">Room {{ $room->room_number }} ({{ $room->status->label() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Title *</label>
                    <input type="text" x-model="form.title" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Fix leaking tap in bathroom">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Details</label>
                    <textarea x-model="form.description" rows="2" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}"></textarea>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Assign to</label>
                    <select x-model="form.assigned_to" class="{{ $inputClass }}">
                        <option value="">Anyone (not assigned)</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Priority *</label>
                    <select x-model="form.priority" class="{{ $inputClass }}">
                        @foreach (\App\Models\Task::PRIORITIES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Due *</label>
                    <input type="datetime-local" x-model="form.due_date" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Expected time (minutes)</label>
                    <input type="number" x-model.number="form.estimated_minutes" min="1" class="{{ $inputClass }}">
                </div>
            </div>

            <label x-show="form.room_id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" x-model="form.blocks_room" class="h-4 w-4 rounded border-gray-300 text-primary">
                Take the room out of service until this is done
            </label>

            <!-- Repeat (only on the first task of a series) -->
            <fieldset x-show="form.can_repeat" class="rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">
                <legend class="sr-only">Repeat</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="{{ $labelClass }}">Repeats</label>
                        <select x-model="form.recurrence_pattern" class="{{ $inputClass }}">
                            <option value="">Never</option>
                            @foreach (\App\Models\Task::REPEATS as $value => [$one, $many])
                                <option value="{{ $value }}">Every {{ $one }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="form.recurrence_pattern">
                        <label class="{{ $labelClass }}">How often</label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">every</span>
                            <input type="number" x-model.number="form.recurrence_interval" min="1" max="365" class="{{ $inputClass }}">
                            <span class="text-sm text-gray-600 dark:text-gray-400"
                                  x-text="({ daily: ['day', 'days'], weekly: ['week', 'weeks'], monthly: ['month', 'months'], yearly: ['year', 'years'] })[form.recurrence_pattern]?.[form.recurrence_interval > 1 ? 1 : 0]"></span>
                        </div>
                    </div>
                    <div x-show="form.recurrence_pattern">
                        <label class="{{ $labelClass }}">Until (optional)</label>
                        <input type="date" x-model="form.recurrence_end_date" class="{{ $inputClass }}">
                    </div>
                </div>
                <p x-show="form.recurrence_pattern" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    The next copy appears a day before it is due, with the same person, room and checklist.
                </p>
            </fieldset>

            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">
                    <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                    <span x-text="form.id ? 'Save Changes' : 'Create Task'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
