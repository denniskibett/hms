{{--
    Booking form for stays.create and stays.edit.
    Expects: $stay (new or existing), $guests, $rooms, $lines (room rows).
    In-house stays only allow changing the departure date and notes.
--}}
@php
    $isNew = ! $stay->exists;
    $inHouse = $stay->exists && $stay->state === \App\Enums\StayStatus::CheckedIn;
    $currency = SystemHelper::currencySymbol();
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $config = [
        'quoteUrl' => route('stays.quote'),
        'stayId' => $stay->id,
        'inHouse' => $inHouse,
        'arrival' => old('arrival_date', $stay->arrival_date?->toDateString()),
        'departure' => old('departure_date', $stay->departure_date?->toDateString()),
        'mealPlan' => old('meal_plan', $stay->meal_plan?->value ?? 'BB'),
        'lines' => collect($lines)->map(fn ($l) => [
            'room_id' => (string) ($l['room_id'] ?? ''),
            'adults' => (int) ($l['adults'] ?? 1),
            'children' => (int) ($l['children'] ?? 0),
            'rate' => $l['rate'] ?? '',
            'guest_notes' => $l['guest_notes'] ?? '',
        ])->values(),
        'rooms' => $rooms->map(fn ($r) => [
            'id' => $r->id,
            'label' => "{$r->room_number} — " . ($r->roomType->name ?? 'Room'),
            'capacity' => $r->roomType->capacity ?? 2,
        ])->values(),
    ];
@endphp

<form method="POST" action="{{ $isNew ? route('stays.store') : route('stays.update', $stay) }}"
      x-data="bookingForm(@js($config))" class="space-y-6">
    @csrf
    @unless ($isNew)
        @method('PUT')
    @endunless

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Left: booking details -->
        <div class="space-y-6 xl:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-medium text-gray-800 dark:text-white/90">Guest & dates</h3>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Guest *</label>
                        @if ($inHouse)
                            <p class="text-gray-800 dark:text-white/90">{{ $stay->guest->name ?? '—' }}</p>
                        @else
                            <div class="flex gap-2">
                                <select name="guest_id" required x-ref="guestSelect" class="{{ $inputClass }}"
                                        x-on:guest-saved.window="addGuest($event.detail.guest)">
                                    <option value="">Select guest</option>
                                    @foreach ($guests as $guest)
                                        <option value="{{ $guest->id }}" @selected((string) old('guest_id', $stay->guest_id) === (string) $guest->id)>
                                            {{ $guest->name }}{{ $guest->phone ? ' — ' . $guest->phone : ($guest->email ? ' — ' . $guest->email : '') }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" @click="$dispatch('open-guest-form')" title="Register a new guest"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    <i class="fas fa-user-plus"></i> New
                                </button>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Arrival *</label>
                        <input type="date" name="arrival_date" x-model="arrival" @change="refresh()" required
                               @if ($inHouse) disabled @endif class="{{ $inputClass }} disabled:opacity-60">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Departure *</label>
                        <input type="date" name="departure_date" x-model="departure" :min="arrival" @change="refresh()" required class="{{ $inputClass }}">
                    </div>

                    @unless ($inHouse)
                        <div>
                            <label class="{{ $labelClass }}">Meal plan *</label>
                            <select name="meal_plan" x-model="mealPlan" @change="refresh()" class="{{ $inputClass }}">
                                @foreach (\App\Enums\MealPlan::options() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Status *</label>
                            <select name="status" class="{{ $inputClass }}">
                                <option value="booked" @selected(old('status', $stay->status) === 'booked')>Confirmed</option>
                                <option value="reserved" @selected(old('status', $stay->status) === 'reserved')>Provisional (not yet confirmed)</option>
                                @if ($isNew)
                                    <option value="checked_in" @selected(old('status') === 'checked_in') :disabled="arrival !== today">Walk-in: check in now</option>
                                @endif
                            </select>
                        </div>
                    @endunless

                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Special requests</label>
                        <textarea name="special_requests" rows="2" class="{{ $inputClass }} h-auto"
                                  placeholder="Late arrival, airport pickup, dietary needs…">{{ old('special_requests', $stay->special_requests) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Rooms -->
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Rooms</h3>
                    @unless ($inHouse)
                        <button type="button" @click="addLine()"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-medium text-primary hover:bg-primary/20">
                            <i class="fas fa-plus"></i> Add room
                        </button>
                    @endunless
                </div>

                @if ($inHouse)
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">The guest is checked in, so only the departure date can change. Extending checks the rooms are free for the extra nights.</p>
                @endif

                <div class="space-y-4">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="rounded-xl border p-4"
                             :class="lineQuote(index) && !lineQuote(index).is_available ? 'border-error-300 bg-error-50/50 dark:border-error-500/40 dark:bg-error-500/5' : 'border-gray-200 dark:border-gray-800'">
                            <div class="grid grid-cols-2 gap-3 md:grid-cols-12">
                                <div class="col-span-2 md:col-span-4">
                                    <label class="{{ $labelClass }}">Room</label>
                                    <select :name="`rooms[${index}][room_id]`" x-model="line.room_id" @change="refresh()" required
                                            :disabled="inHouse" class="{{ $inputClass }} disabled:opacity-60">
                                        <option value="">Select room</option>
                                        <template x-for="room in rooms" :key="room.id">
                                            <option :value="String(room.id)"
                                                    :disabled="isTaken(room.id, index)"
                                                    x-text="room.label + (isFree(room.id) ? '' : ' (booked)')"></option>
                                        </template>
                                    </select>
                                    <template x-if="inHouse"><input type="hidden" :name="`rooms[${index}][room_id]`" :value="line.room_id"></template>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="{{ $labelClass }}">Adults</label>
                                    <input type="number" min="1" :max="capacity(line.room_id)" :name="`rooms[${index}][adults]`"
                                           x-model.number="line.adults" @change="refresh()" :disabled="inHouse" class="{{ $inputClass }} disabled:opacity-60">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="{{ $labelClass }}">Children</label>
                                    <input type="number" min="0" :name="`rooms[${index}][children]`"
                                           x-model.number="line.children" @change="refresh()" :disabled="inHouse" class="{{ $inputClass }} disabled:opacity-60">
                                </div>
                                <div class="col-span-2 md:col-span-3">
                                    <label class="{{ $labelClass }}">Rate / night <span class="font-normal text-gray-400">(optional)</span></label>
                                    <input type="number" min="0" step="0.01" :name="`rooms[${index}][rate]`"
                                           x-model="line.rate" @change="refresh()" :disabled="inHouse"
                                           :placeholder="lineQuote(index) ? 'Price list: ' + money(lineQuote(index).quoted_nightly) : 'From price list'"
                                           class="{{ $inputClass }} disabled:opacity-60">
                                </div>
                                <div class="flex items-end justify-end md:col-span-1">
                                    <button type="button" @click="removeLine(index)" x-show="!inHouse && lines.length > 1" title="Remove room"
                                            class="h-11 w-11 rounded-lg text-error-500 hover:bg-error-50 dark:hover:bg-error-500/10">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="col-span-2 md:col-span-12">
                                    <input type="text" :name="`rooms[${index}][guest_notes]`" x-model="line.guest_notes" :disabled="inHouse"
                                           placeholder="Notes for this room (e.g. extra bed, names of occupants)" class="{{ $inputClass }} h-9 disabled:opacity-60">
                                </div>
                            </div>

                            <!-- Line quote -->
                            <template x-if="lineQuote(index)">
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <span class="text-gray-500 dark:text-gray-400" x-text="lineQuote(index).description"></span>
                                    <span class="font-medium"
                                          :class="lineQuote(index).is_available ? 'text-gray-800 dark:text-white/90' : 'text-error-600 dark:text-error-400'">
                                        <template x-if="!lineQuote(index).is_available"><span><i class="fas fa-exclamation-triangle"></i> Not available for these dates · </span></template>
                                        <span x-text="money(lineQuote(index).nightly) + ' × ' + quote.nights + ' = ' + money(lineQuote(index).total)"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right: summary -->
        <div class="space-y-6">
            <div class="sticky top-24 rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-medium text-gray-800 dark:text-white/90">Summary</h3>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nights</dt><dd class="font-medium text-gray-800 dark:text-white/90" x-text="quote ? quote.nights : '—'"></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Rooms</dt><dd class="font-medium text-gray-800 dark:text-white/90" x-text="lines.filter(l => l.room_id).length"></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Guests</dt><dd class="font-medium text-gray-800 dark:text-white/90" x-text="guestSummary()"></dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-3 text-base dark:border-gray-800">
                        <dt class="font-medium text-gray-800 dark:text-white/90">Room charges</dt>
                        <dd class="font-bold text-gray-800 dark:text-white/90" x-text="quote ? money(quote.total) : '—'"></dd>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Before taxes. The invoice adds VAT and Tourism Levy.</p>
                </dl>

                <p x-show="loading" class="mt-3 text-xs text-gray-500"><i class="fas fa-spinner fa-spin"></i> Updating price…</p>
                <p x-show="error" x-text="error" class="mt-3 text-sm text-error-600 dark:text-error-400"></p>
                <p x-show="quote && !quote.all_available" class="mt-3 text-sm text-error-600 dark:text-error-400">
                    <i class="fas fa-exclamation-triangle"></i> Some rooms are not available. Change the room or the dates.
                </p>

                <div class="mt-5 flex flex-col gap-2">
                    <button type="submit" :disabled="quote && !quote.all_available"
                            class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $isNew ? 'Save Booking' : 'Save Changes' }}
                    </button>
                    <a href="{{ $isNew ? route('stays.index') : route('stays.show', $stay) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Outside the booking <form>: the modal has its own form --}}
@include('guests.partials.modal-form')

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingForm', (config) => ({
        rooms: config.rooms,
        lines: config.lines,
        arrival: config.arrival,
        departure: config.departure,
        mealPlan: config.mealPlan,
        inHouse: config.inHouse,
        today: new Date().toLocaleDateString('en-CA'),
        quote: null,
        freeRoomIds: null,
        loading: false,
        error: '',
        timer: null,

        init() {
            this.refresh();
        },

        // A guest registered from the modal joins the picker, selected
        addGuest(guest) {
            const select = this.$refs.guestSelect;
            if (!select) return;
            const label = guest.name + (guest.phone ? ` — ${guest.phone}` : (guest.email ? ` — ${guest.email}` : ''));
            let option = [...select.options].find(o => o.value === String(guest.user_id));
            if (!option) {
                option = new Option(label, guest.user_id);
                select.add(option, 1);
            }
            option.text = label;
            select.value = String(guest.user_id);
        },

        addLine() {
            this.lines.push({ room_id: '', adults: 1, children: 0, rate: '', guest_notes: '' });
        },

        removeLine(index) {
            this.lines.splice(index, 1);
            this.refresh();
        },

        capacity(roomId) {
            return this.rooms.find(r => String(r.id) === String(roomId))?.capacity ?? 10;
        },

        isFree(roomId) {
            return this.freeRoomIds === null || this.freeRoomIds.includes(Number(roomId));
        },

        // Already chosen on another line
        isTaken(roomId, index) {
            return this.lines.some((l, i) => i !== index && String(l.room_id) === String(roomId));
        },

        lineQuote(index) {
            const line = this.lines[index];
            return this.quote?.rooms.find(r => String(r.room_id) === String(line.room_id)) ?? null;
        },

        guestSummary() {
            const adults = this.lines.reduce((sum, l) => sum + (Number(l.adults) || 0), 0);
            const children = this.lines.reduce((sum, l) => sum + (Number(l.children) || 0), 0);
            return `${adults} adults` + (children ? `, ${children} children` : '');
        },

        money(value) {
            return @js($currency) + ' ' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        // Debounced live quote from the server (same maths as saving)
        refresh() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.fetchQuote(), 250);
        },

        async fetchQuote() {
            this.error = '';
            if (!this.arrival || !this.departure || this.departure <= this.arrival) {
                this.quote = null;
                this.error = this.arrival && this.departure ? 'Departure must be after arrival.' : '';
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(config.quoteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        stay_id: config.stayId,
                        arrival_date: this.arrival,
                        departure_date: this.departure,
                        meal_plan: this.mealPlan,
                        rooms: this.lines.filter(l => l.room_id).map(l => ({
                            room_id: Number(l.room_id),
                            adults: Number(l.adults) || 1,
                            children: Number(l.children) || 0,
                            rate: l.rate === '' || l.rate === null ? null : Number(l.rate),
                        })),
                    }),
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    this.quote = data.quote;
                    this.freeRoomIds = data.available_room_ids;
                } else {
                    this.quote = null;
                    this.error = data.message || 'Could not price this booking.';
                }
            } catch (e) {
                console.error(e);
                this.error = 'Could not reach the server to price this booking.';
            } finally {
                this.loading = false;
            }
        },
    }));
});
</script>
