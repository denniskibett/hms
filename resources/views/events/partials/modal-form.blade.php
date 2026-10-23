{{--
    Book / edit event modal.
      @include('events.partials.modal-form')        (needs $venues and $packages)
      open: $dispatch('open-event-form')                        new
            $dispatch('open-event-form', { facility_id: 3 })    new, venue picked
            $dispatch('open-event-form', @js($event->formData()))  edit
      Lines: venue hire is added from the venue's daily rate; package quantities
      follow the pax and days until changed by hand.
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $cellInput = 'h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="eventForm(@js([
        'venues' => $venues,
        'packages' => $packages,
        'bases' => \App\Models\FacilityBookingItem::BASES,
        'currency' => $currency,
     ]))"
     @open-event-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <button type="button" @click="close()"
                    class="absolute right-3 top-3 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Event' : 'Book Event'"></h4>

            <form @submit.prevent="submit()" class="space-y-6">
                @include('facilities.partials.form-errors')

                <!-- Event -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label class="{{ $labelClass }}">Event *</label>
                        <input type="text" x-model="form.title" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Annual Staff Training">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Guests (pax) *</label>
                        <input type="number" x-model.number="form.pax" @input="paxChanged()" required min="1" class="{{ $inputClass }}">
                        <p class="mt-1 text-xs text-warning-600 dark:text-warning-500" x-show="venue && form.pax > venue.capacity" x-text="`${venue?.name} seats ${venue?.capacity}`"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Venue *</label>
                        <select x-model.number="form.facility_id" @change="venueChanged()" required class="{{ $inputClass }}">
                            <option value="">Choose…</option>
                            <template x-for="v in venues" :key="v.id">
                                <option :value="v.id" :selected="v.id === form.facility_id" :disabled="v.status !== 'available'"
                                        x-text="`${v.name} (${v.capacity})${v.status !== 'available' ? ' – ' + v.status : ''}`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">From *</label>
                        <input type="date" x-model="form.start_date" @change="datesChanged()" required class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">To *</label>
                        <input type="date" x-model="form.end_date" @change="datesChanged()" :min="form.start_date" required class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Start time</label>
                        <input type="time" x-model="form.start_time" class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">End time</label>
                        <input type="time" x-model="form.end_time" class="{{ $inputClass }}">
                    </div>
                    <div class="sm:col-span-2" x-show="!form.id">
                        <label class="{{ $labelClass }}">Status</label>
                        <select x-model="form.status" class="{{ $inputClass }}">
                            <option value="tentative">Tentative (hold)</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                    </div>
                </div>

                <!-- Client -->
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Client</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Organisation / name *</label>
                            <input type="text" x-model="form.client_name" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Kenya Red Cross">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Contact person</label>
                            <input type="text" x-model="form.contact_name" maxlength="255" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Phone *</label>
                            <input type="tel" x-model="form.contact_phone" required maxlength="30" class="{{ $inputClass }}" placeholder="e.g. 0712 345 678">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Email</label>
                            <input type="email" x-model="form.contact_email" maxlength="255" class="{{ $inputClass }}">
                        </div>
                    </div>
                </fieldset>

                <!-- Lines -->
                <fieldset>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <legend class="text-sm font-medium text-gray-800 dark:text-white/90">Packages &amp; charges <span class="font-normal text-gray-500">(prices include tax)</span></legend>
                        <div class="flex gap-2">
                            <select @change="addPackage($event.target.value); $event.target.value = ''" class="h-9 rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">+ Add package…</option>
                                <template x-for="p in availablePackages" :key="p.id">
                                    <option :value="p.id" x-text="`${p.name} – ${money(p.price)}`"></option>
                                </template>
                            </select>
                            <button type="button" @click="addLine()" class="h-9 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">+ Other</button>
                        </div>
                    </div>

                    <div class="custom-scrollbar overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 dark:text-gray-400">
                                    <th class="py-1 pr-2 font-medium">Description</th>
                                    <th class="w-40 py-1 pr-2 font-medium">Charged</th>
                                    <th class="w-20 py-1 pr-2 font-medium">Qty</th>
                                    <th class="w-16 py-1 pr-2 font-medium">Days</th>
                                    <th class="w-28 py-1 pr-2 font-medium">Price</th>
                                    <th class="w-28 py-1 text-right font-medium">Total</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, i) in form.items" :key="i">
                                    <tr>
                                        <td class="py-1 pr-2"><input type="text" x-model="item.description" required maxlength="255" class="{{ $cellInput }}"></td>
                                        <td class="py-1 pr-2">
                                            <select x-model="item.charge_basis" class="{{ $cellInput }}">
                                                <template x-for="(label, value) in bases" :key="value">
                                                    <option :value="value" :selected="value === item.charge_basis" x-text="label"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-1 pr-2"><input type="number" x-model.number="item.quantity" @input="item.auto_qty = false" :disabled="item.charge_basis === 'venue'" min="0.01" step="0.01" required class="{{ $cellInput }} disabled:opacity-50"></td>
                                        <td class="py-1 pr-2"><input type="number" x-model.number="item.days" @input="item.auto_days = false" min="1" :max="days" required class="{{ $cellInput }}"></td>
                                        <td class="py-1 pr-2"><input type="number" x-model.number="item.unit_price" min="0" step="0.01" required class="{{ $cellInput }}"></td>
                                        <td class="whitespace-nowrap py-1 text-right text-gray-800 dark:text-white/90" x-text="money(lineTotal(item))"></td>
                                        <td class="py-1 text-right"><button type="button" @click="form.items.splice(i, 1)" class="text-gray-400 hover:text-error-500" title="Remove"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </template>
                                <tr x-show="!form.items.length"><td colspan="7" class="py-3 text-center text-gray-500 dark:text-gray-400">Add a package or charge.</td></tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-100 dark:border-gray-800">
                                    <td colspan="5" class="pt-3 text-right font-medium text-gray-700 dark:text-gray-300">Total</td>
                                    <td class="whitespace-nowrap pt-3 text-right text-base font-semibold text-gray-800 dark:text-white/90" x-text="money(total)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </fieldset>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Notes</label>
                        <textarea x-model="form.notes" rows="2" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}" placeholder="Setup (theatre, classroom, U-shape), dietary needs, deposit terms"></textarea>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Linked room booking #</label>
                        <input type="number" x-model.number="form.stay_id" min="1" class="{{ $inputClass }}" placeholder="Optional">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="close()" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</button>
                    <button type="submit" :disabled="isLoading" :class="{ 'cursor-not-allowed opacity-50': isLoading }"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                        <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                        <span x-text="form.id ? 'Save Changes' : 'Book Event'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('eventForm', (config) => ({
        urls: {
            store: @js(route('events.store')),
            update: @js(route('events.update', ':id')),
        },
        venues: config.venues,
        packages: config.packages,
        bases: config.bases,
        isOpen: false,
        isLoading: false,
        errors: {},
        form: {},

        init() {
            this.form = this.blank();
        },

        blank() {
            const today = new Date().toLocaleDateString('en-CA');
            return {
                id: null, facility_id: '', stay_id: '', title: '', client_name: '', contact_name: '',
                contact_phone: '', contact_email: '', start_date: today, end_date: today,
                start_time: '08:00', end_time: '17:00', pax: 20, status: 'tentative', notes: '', items: [],
            };
        },

        get venue() {
            return this.venues.find(v => v.id === this.form.facility_id) ?? null;
        },

        get days() {
            const from = new Date(this.form.start_date), to = new Date(this.form.end_date);
            return isNaN(from) || isNaN(to) || to < from ? 1 : Math.round((to - from) / 86400000) + 1;
        },

        get availablePackages() {
            return this.packages.filter(p => !p.facility_id || p.facility_id === this.form.facility_id);
        },

        get total() {
            return this.form.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
        },

        lineTotal(item) {
            const qty = item.charge_basis === 'venue' ? 1 : (Number(item.quantity) || 0);
            return qty * (Number(item.days) || 0) * (Number(item.unit_price) || 0);
        },

        money(value) {
            return config.currency + ' ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        open(data = null) {
            const base = this.blank();
            this.form = { ...base, ...(data ?? {}) };
            this.form.stay_id ??= '';
            // Existing lines keep their quantities; only new lines follow pax / days
            this.form.items = (data?.items ?? []).map(item => ({ ...item, auto_qty: false, auto_days: false }));
            if (!this.form.id && this.form.facility_id) this.venueChanged();
            this.errors = {};
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        venueChanged() {
            this.form.items = this.form.items.filter(item => item.charge_basis !== 'venue');
            const venue = this.venue;
            if (venue && Number(venue.base_rate) > 0) {
                this.form.items.unshift({
                    facility_package_id: null, description: `Venue hire: ${venue.name}`, charge_basis: 'venue',
                    quantity: 1, days: this.days, unit_price: Number(venue.base_rate), auto_qty: false, auto_days: true,
                });
            }
        },

        paxChanged() {
            this.form.items.forEach(item => {
                if (item.auto_qty && item.charge_basis === 'per_person') item.quantity = this.form.pax;
            });
        },

        datesChanged() {
            if (this.form.end_date < this.form.start_date) this.form.end_date = this.form.start_date;
            this.form.items.forEach(item => {
                if (item.auto_days || item.days > this.days) item.days = this.days;
            });
        },

        addPackage(id) {
            const p = this.packages.find(p => String(p.id) === String(id));
            if (!p) return;
            const perPerson = p.charge_basis === 'per_person';
            this.form.items.push({
                facility_package_id: p.id, description: p.name, charge_basis: p.charge_basis,
                quantity: perPerson ? this.form.pax : 1, days: this.days, unit_price: Number(p.price),
                auto_qty: perPerson, auto_days: true,
            });
        },

        addLine() {
            this.form.items.push({
                facility_package_id: null, description: '', charge_basis: 'per_unit',
                quantity: 1, days: this.days, unit_price: 0, auto_qty: false, auto_days: true,
            });
        },

        async submit() {
            const isEdit = !!this.form.id;
            this.isLoading = true;
            this.errors = {};

            try {
                const response = await fetch(isEdit ? this.urls.update.replace(':id', this.form.id) : this.urls.store, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ ...this.form, stay_id: this.form.stay_id || null, start_time: this.form.start_time || null, end_time: this.form.end_time || null }),
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success !== false) {
                    this.close();
                    isEdit ? window.location.reload() : window.location.href = data.url;
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
