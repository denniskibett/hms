{{--
    Kinds of job (task types) with their checklists. Managers only. Needs $taskTypes, $departments.
    open: $dispatch('open-type-form')
--}}
@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('task-types.store'),
        'update' => route('task-types.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'name' => '', 'category' => 'cleaning', 'department_id' => '', 'description' => '', 'checklist' => '', 'default_estimated_minutes' => 30, 'requires_room' => false, 'is_active' => true],
     ]))"
     @open-type-form.window="open()" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="close()"></div>
        <div class="relative grid max-h-[92vh] w-full max-w-3xl grid-cols-1 gap-6 overflow-y-auto rounded-2xl bg-white p-6 md:grid-cols-2 dark:bg-gray-900">
            <div>
                <h4 class="mb-3 text-lg font-medium text-gray-800 dark:text-white/90">Kinds of job</h4>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @foreach ($taskTypes as $type)
                        <li class="flex items-center justify-between py-2">
                            <span>
                                <span class="text-gray-800 dark:text-white/90">{{ $type->name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\TaskType::CATEGORIES[$type->category] ?? $type->category }} · {{ count($type->checklist_items) }} checklist items</span>
                            </span>
                            <button type="button" @click="form = @js($type->formData()); errors = {}" class="text-gray-400 hover:text-primary"><i class="fas fa-edit"></i></button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <form @submit.prevent="submit()" class="space-y-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="form.id ? 'Edit ' + form.name : 'New kind of job'"></p>
                @include('facilities.partials.form-errors')
                <input type="text" x-model="form.name" required maxlength="100" placeholder="Name, e.g. AC Servicing" class="{{ $inputClass }}">
                <div class="grid grid-cols-2 gap-3">
                    <select x-model="form.category" class="{{ $inputClass }}">
                        @foreach (\App\Models\TaskType::CATEGORIES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select x-model="form.department_id" class="{{ $inputClass }}">
                        <option value="">Any department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <textarea x-model="form.checklist" rows="5" maxlength="3000" placeholder="Checklist, one item per line" class="{{ str_replace('h-10', '', $inputClass) }}"></textarea>
                <div class="grid grid-cols-2 gap-3">
                    <input type="number" x-model.number="form.default_estimated_minutes" min="1" placeholder="Minutes" class="{{ $inputClass }}">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400"><input type="checkbox" x-model="form.requires_room" class="h-4 w-4 rounded border-gray-300 text-primary"> Needs a room</label>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400"><input type="checkbox" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-primary"> In use</label>
                <div class="flex items-center justify-between">
                    <button type="button" x-show="form.id" @click="open()" class="text-sm text-gray-500 hover:underline">New instead</button>
                    <div class="ml-auto flex gap-3">
                        <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</button>
                        <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facilities.partials.scripts')
