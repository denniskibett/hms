{{--
    New order modal: pick dishes from the menu, say who it's for, and either
    charge it to an in-house guest's room or take payment now.
      @include('kitchen.partials.order-modal')     (needs $menu, $inHouse, $methods)
      open: $dispatch('open-order-form')
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="orderForm(@js(['menu' => $menu, 'inHouse' => $inHouse, 'currency' => $currency]))"
     @open-order-form.window="open()" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-3 sm:p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">New Order</h4>
                <button type="button" @click="close()" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-400">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-5 lg:overflow-hidden">
                <!-- Menu -->
                <div class="flex min-h-0 flex-col border-b border-gray-100 p-5 lg:col-span-3 lg:border-b-0 lg:border-r dark:border-gray-800">
                    <input type="search" x-model="search" placeholder="Search the menu…" class="{{ $inputClass }}">
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" @click="category = null" :class="category === null ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'" class="rounded-full px-3 py-1 text-xs font-medium">All</button>
                        <template x-for="c in menu" :key="c.id">
                            <button type="button" @click="category = c.id" x-text="c.name" :class="category === c.id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'" class="rounded-full px-3 py-1 text-xs font-medium"></button>
                        </template>
                    </div>

                    <div class="custom-scrollbar mt-4 min-h-0 flex-1 space-y-2 overflow-y-auto lg:max-h-[60vh]">
                        <template x-for="item in visibleItems" :key="item.key">
                            <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                                <p class="font-medium text-gray-800 dark:text-white/90" x-text="item.name"></p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="v in item.variants" :key="v.id">
                                        <button type="button" @click="add(v)" class="rounded-lg border border-primary/30 bg-primary/5 px-3 py-1.5 text-xs font-medium text-primary hover:bg-primary/10">
                                            <span x-text="item.variants.length > 1 ? v.name + ' · ' : ''"></span><span x-text="money(v.price)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <p x-show="!visibleItems.length" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            @if (collect($menu)->isEmpty())
                                The menu is empty. Add dishes under <strong>Menu</strong> first.
                            @else
                                Nothing matches.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Order -->
                <form @submit.prevent="submit()" class="flex min-h-0 flex-col lg:col-span-2">
                    <div class="custom-scrollbar min-h-0 flex-1 space-y-4 overflow-y-auto p-5 lg:max-h-[70vh]">
                        @include('facilities.partials.form-errors')

                        <div class="grid grid-cols-3 gap-2">
                            @foreach (\App\Models\KitchenOrder::TYPES as $value => $label)
                                <button type="button" @click="setType(@js($value))"
                                        :class="form.type === @js($value) ? 'border-primary bg-primary/10 text-primary' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                                        class="rounded-lg border px-2 py-2 text-xs font-medium">{{ $label }}</button>
                            @endforeach
                        </div>

                        <div x-show="form.type === 'dine_in'">
                            <label class="{{ $labelClass }}">Table</label>
                            <input type="text" x-model="form.table_number" maxlength="20" class="{{ $inputClass }}" placeholder="e.g. 4">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">In-house guest <span x-show="form.type !== 'room_service'" class="font-normal text-gray-500">(optional)</span></label>
                            <select x-model.number="form.stay_id" @change="if (!form.stay_id) form.charge_to_room = false" :required="form.type === 'room_service'" class="{{ $inputClass }}">
                                <option value="">— Not a hotel guest —</option>
                                <template x-for="s in inHouse" :key="s.id">
                                    <option :value="s.id" x-text="s.label"></option>
                                </template>
                            </select>
                        </div>

                        <div x-show="!form.stay_id">
                            <label class="{{ $labelClass }}">Customer name</label>
                            <input type="text" x-model="form.customer_name" maxlength="255" class="{{ $inputClass }}" placeholder="Optional">
                        </div>

                        <!-- Lines -->
                        <div class="space-y-2">
                            <template x-for="(line, i) in form.items" :key="line.variant.id">
                                <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="line.variant.full_name"></p>
                                        <p class="whitespace-nowrap text-sm text-gray-800 dark:text-white/90" x-text="money(line.quantity * line.variant.price)"></p>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" @click="line.quantity > 1 ? line.quantity-- : form.items.splice(i, 1)" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-minus text-xs"></i></button>
                                        <span class="w-6 text-center text-sm font-medium text-gray-800 dark:text-white/90" x-text="line.quantity"></span>
                                        <button type="button" @click="line.quantity < 100 && line.quantity++" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 text-gray-600 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-plus text-xs"></i></button>
                                        <input type="text" x-model="line.notes" maxlength="255" placeholder="Note (e.g. no onions)" class="h-8 flex-1 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                    </div>
                                </div>
                            </template>
                            <p x-show="!form.items.length" class="rounded-lg border border-dashed border-gray-300 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Tap dishes on the menu to add them.</p>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Kitchen instructions</label>
                            <input type="text" x-model="form.special_instructions" maxlength="1000" class="{{ $inputClass }}" placeholder="e.g. Serve together, allergy: nuts">
                        </div>

                        <!-- Billing -->
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">
                            <label x-show="form.stay_id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" x-model="form.charge_to_room" class="h-4 w-4 rounded border-gray-300 text-primary">
                                Charge to the guest's room (paid at check-out)
                            </label>
                            <div x-show="!form.charge_to_room" class="space-y-3" :class="form.stay_id ? 'mt-3' : ''">
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" x-model="payNow" class="h-4 w-4 rounded border-gray-300 text-primary">
                                    Paid now
                                </label>
                                <div x-show="payNow" class="grid grid-cols-2 gap-3">
                                    <select x-model="payment.method" class="{{ $inputClass }}">
                                        @foreach ($methods as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" x-model="payment.payment_reference" maxlength="50" x-show="needsReference"
                                           :required="payNow && needsReference" placeholder="M-Pesa code" class="{{ $inputClass }} uppercase">
                                </div>
                                <p x-show="!payNow" class="text-xs text-gray-500 dark:text-gray-400">Not paid yet: take payment from the order page later.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total (incl. tax)</p>
                            <p class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="money(total)"></p>
                        </div>
                        <button type="submit" :disabled="isLoading || !form.items.length" :class="{ 'cursor-not-allowed opacity-50': isLoading || !form.items.length }"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                            <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                            <i class="fas fa-paper-plane" x-show="!isLoading"></i> Send to kitchen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderForm', (config) => ({
        url: @js(route('kitchen.orders.store')),
        menu: config.menu,
        inHouse: config.inHouse,
        isOpen: false,
        isLoading: false,
        errors: {},
        search: '',
        category: null,
        payNow: true,
        payment: { method: 'cash', payment_reference: '' },
        form: {},
        draftKey: window.formDrafts.key('kitchen-order', null),
        draftRestored: false,

        init() {
            this.form = this.blank();
            // Keep a half-taken order until it is sent (see window.formDrafts)
            this.$watch('form', () => {
                if (!this.isOpen) return;
                this.form.items.length || this.form.special_instructions || this.form.customer_name
                    ? window.formDrafts.save(this.draftKey, this.form)
                    : window.formDrafts.clear(this.draftKey);
            });
        },

        blank() {
            return { type: 'dine_in', stay_id: '', charge_to_room: false, customer_name: '', table_number: '', special_instructions: '', items: [] };
        },

        get visibleItems() {
            const term = this.search.trim().toLowerCase();
            return this.menu
                .filter(c => this.category === null || c.id === this.category)
                .flatMap(c => c.items.map((item, i) => ({ ...item, key: `${c.id}-${i}` })))
                .filter(item => !term || item.name.toLowerCase().includes(term));
        },

        get total() {
            return this.form.items.reduce((sum, line) => sum + line.quantity * line.variant.price, 0);
        },

        get needsReference() {
            return !['cash', 'credit_card'].includes(this.payment.method);
        },

        money(value) {
            return config.currency + ' ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        open() {
            const draft = window.formDrafts.load(this.draftKey);
            this.form = draft ? { ...this.blank(), ...draft } : this.blank();
            this.draftRestored = !!draft;
            this.payNow = true;
            this.payment = { method: 'cash', payment_reference: '' };
            this.search = '';
            this.errors = {};
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        discardDraft() {
            window.formDrafts.clear(this.draftKey);
            this.form = this.blank();
            this.draftRestored = false;
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        setType(type) {
            this.form.type = type;
            // Room service is for in-house guests and usually goes on the room
            if (type === 'room_service') this.form.charge_to_room = true;
        },

        add(variant) {
            const line = this.form.items.find(l => l.variant.id === variant.id);
            line ? line.quantity++ : this.form.items.push({ variant, quantity: 1, notes: '' });
        },

        async submit() {
            this.isLoading = true;
            this.errors = {};
            const chargeToRoom = !!this.form.stay_id && this.form.charge_to_room;

            try {
                const response = await fetch(this.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        ...this.form,
                        stay_id: this.form.stay_id || null,
                        charge_to_room: chargeToRoom,
                        items: this.form.items.map(l => ({ menu_item_variant_id: l.variant.id, quantity: l.quantity, notes: l.notes })),
                        payment: !chargeToRoom && this.payNow ? this.payment : null,
                    }),
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success !== false) {
                    window.formDrafts.clear(this.draftKey);
                    this.close();
                    if (data.receipt_url) window.open(data.receipt_url, '_blank');
                    window.location.reload();
                    return;
                }
                if (response.status === 422 && data.errors) this.errors = data.errors;
                else this.errors = { form: [data.message || `Request failed (${response.status})`] };
            } catch (error) {
                console.error(error);
                this.errors = { form: ['Network error. Please try again.'] };
            } finally {
                this.isLoading = false;
            }
        },
    }));
});
</script>
@endonce
