{{--
    Receive / issue / write off / count one item. Needs $departments, $canManage, $canReceive.
    open: $dispatch('open-move', { id, name, unit, quantity, unit_cost, action })
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $actions = array_filter([
        'in' => $canReceive ? 'Receive' : null,
        'out' => 'Issue',
        'damage' => 'Damaged',
        'expired' => 'Expired',
        'count' => $canManage ? 'Count' : null,
    ]);
    $reopen = old('action') && old('item_id');
@endphp

<div x-data="{
        open: false,
        item: {},
        action: @js(old('action', 'out')),
        init() {
            @if ($reopen)
                this.item = @js(['id' => (int) old('item_id'), 'name' => old('item_name'), 'unit' => old('item_unit'), 'quantity' => old('item_quantity')]);
                this.open = true;
            @endif
        },
        show(detail) { this.item = detail; this.action = detail.action ?? 'out'; this.open = true; },
     }"
     @open-move.window="show($event.detail)" @keydown.escape.window="open = false">
    <div x-show="open" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="open = false"></div>
        <form method="POST" :action="@js(route('inventory.move', ':id')).replace(':id', item.id)" class="relative w-full max-w-md space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            @csrf
            <input type="hidden" name="item_id" :value="item.id">
            <input type="hidden" name="item_name" :value="item.name">
            <input type="hidden" name="item_unit" :value="item.unit">
            <input type="hidden" name="item_quantity" :value="item.quantity">
            <input type="hidden" name="action" :value="action">

            <div>
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="item.name"></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="`${item.quantity} ${item.unit} in stock`"></p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($actions as $value => $label)
                    <button type="button" @click="action = @js($value)" :class="action === @js($value) ? 'border-primary bg-primary/10 text-primary' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                            class="rounded-lg border px-3 py-1.5 text-xs font-medium">{{ $label }}</button>
                @endforeach
            </div>

            @if ($errors->any() && $reopen)
                <div class="rounded-lg border border-error-200 bg-error-50 p-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                    @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
                </div>
            @endif

            <div>
                <label class="{{ $labelClass }}" x-text="action === 'count' ? 'Counted on the shelf' : 'Quantity'"></label>
                <div class="flex items-center gap-2">
                    <input type="number" name="quantity" required min="0" step="0.01" value="{{ old('quantity') }}" class="{{ $inputClass }}">
                    <span class="text-sm text-gray-500" x-text="item.unit"></span>
                </div>
            </div>
            <div x-show="action === 'in'">
                <label class="{{ $labelClass }}">Cost per unit ({{ SystemHelper::currencySymbol() }})</label>
                <input type="number" name="unit_cost" min="0" step="0.01" :required="action === 'in'" :value="item.unit_cost" class="{{ $inputClass }}">
            </div>
            <div x-show="action === 'out'">
                <label class="{{ $labelClass }}">To department</label>
                <select name="department_id" :required="action === 'out'" class="{{ $inputClass }}">
                    <option value="">Choose…</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((int) old('department_id') === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">Note <span class="font-normal text-gray-500" x-show="['in', 'out'].includes(action)">(optional)</span></label>
                <input type="text" name="notes" maxlength="500" value="{{ old('notes') }}" :required="!['in', 'out'].includes(action)" class="{{ $inputClass }}"
                       :placeholder="{ in: 'Supplier / delivery note', out: 'What for', damage: 'What happened', expired: 'Batch / date', count: 'Why it differs' }[action]">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save</button>
            </div>
        </form>
    </div>
</div>
