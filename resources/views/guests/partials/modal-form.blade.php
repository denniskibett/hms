{{--
    Add / edit guest modal. Self-contained, so any page can use it:
      @include('guests.partials.modal-form')
      open:   $dispatch('open-guest-form')                 (new guest)
              $dispatch('open-guest-form', @js($guest->formData()))   (edit)
      result: window event 'guest-saved' with { guest, created }
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="guestForm" @open-guest-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5" x-cloak>
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <button type="button" @click="close()"
                    class="absolute right-3 top-3 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Guest' : 'New Guest'"></h4>

            <form @submit.prevent="submit()" class="space-y-6">
                <div x-show="draftRestored" x-cloak
                     class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400">
                    <span><i class="fas fa-history mr-1"></i> We kept what you typed last time. Check it, then save.</span>
                    <button type="button" @click="discardDraft()" class="text-xs font-medium underline">Discard and start again</button>
                </div>
                <div x-show="Object.keys(errors).length" x-cloak
                     class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                    <p class="mb-1 font-medium">Please fix the following:</p>
                    <ul class="list-inside list-disc space-y-0.5">
                        <template x-for="(messages, field) in errors" :key="field">
                            <li x-text="messages[0]"></li>
                        </template>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Full name *</label>
                        <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}" placeholder="As on ID">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Phone *</label>
                        <input type="tel" x-model="form.phone" required maxlength="30" class="{{ $inputClass }}" placeholder="e.g. 0712 345 678">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Email</label>
                        <input type="email" x-model="form.email" maxlength="255" class="{{ $inputClass }}" placeholder="Optional">
                    </div>
                </div>

                <!-- Identification -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="{{ $labelClass }}">ID type</label>
                        <select x-model="form.id_type" class="{{ $inputClass }}">
                            <option value="">Not captured yet</option>
                            @foreach (\App\Models\Guest::ID_TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">ID number</label>
                        <input type="text" x-model="form.id_number" maxlength="50" class="{{ $inputClass }}" :required="!!form.id_type">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Date of birth</label>
                        <input type="date" x-model="form.dob" class="{{ $inputClass }}" :max="today">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nationality</label>
                        <input type="text" x-model="form.nationality" list="guest-countries" maxlength="100" class="{{ $inputClass }}">
                        <datalist id="guest-countries">
                            @foreach (\App\Models\Guest::countries() as $country)
                                <option value="{{ $country }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Address</label>
                        <input type="text" x-model="form.address" maxlength="500" class="{{ $inputClass }}" placeholder="Town, P.O. Box…">
                    </div>
                </div>

                <!-- Emergency contact -->
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Emergency contact</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <input type="text" x-model="form.emergency_contact.name" maxlength="255" class="{{ $inputClass }}" placeholder="Name">
                        <input type="tel" x-model="form.emergency_contact.phone" maxlength="30" class="{{ $inputClass }}" placeholder="Phone">
                        <input type="text" x-model="form.emergency_contact.relationship" maxlength="50" class="{{ $inputClass }}" placeholder="Relationship">
                    </div>
                </fieldset>

                <!-- Preferences -->
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Preferences</legend>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in tagOptions" :key="tag">
                            <button type="button" @click="toggleTag(tag)" x-text="tag"
                                    :class="form.preferences.tags.includes(tag)
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]'"
                                    class="rounded-full border px-3 py-1 text-xs font-medium"></button>
                        </template>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Allergies / dietary</label>
                            <textarea x-model="form.preferences.allergies" rows="2" maxlength="1000"
                                      class="{{ str_replace('h-11', '', $inputClass) }}"></textarea>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Notes</label>
                            <textarea x-model="form.preferences.notes" rows="2" maxlength="1000"
                                      class="{{ str_replace('h-11', '', $inputClass) }}"></textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isLoading" :class="{ 'cursor-not-allowed opacity-50': isLoading }"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                        <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                        <span x-text="form.id ? 'Save Changes' : 'Register Guest'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('guestForm', () => ({
        urls: {
            store: @js(route('guests.store')),
            update: @js(route('guests.update', ':id')),
        },
        tagOptions: @js(\App\Models\Guest::PREFERENCE_TAGS),
        today: new Date().toLocaleDateString('en-CA'),
        isOpen: false,
        isLoading: false,
        errors: {},
        form: {},
        original: null,
        draftKey: null,
        draftRestored: false,
        pristine: null,

        init() {
            this.form = this.blank();
            // Keep what was typed until it is saved (see window.formDrafts)
            this.$watch('form', () => {
                if (!this.isOpen || !this.draftKey) return;
                JSON.stringify(this.form) === this.pristine
                    ? window.formDrafts.clear(this.draftKey)
                    : window.formDrafts.save(this.draftKey, this.form);
            });
        },

        blank() {
            return {
                id: null, name: '', phone: '', email: '', dob: '',
                id_type: '', id_number: '', nationality: 'Kenya', address: '',
                emergency_contact: { name: '', phone: '', relationship: '' },
                preferences: { tags: [], allergies: '', notes: '' },
            };
        },

        open(guest = null) {
            const base = this.blank();
            this.form = guest
                ? { ...base, ...guest,
                    emergency_contact: { ...base.emergency_contact, ...(guest.emergency_contact ?? {}) },
                    preferences: { ...base.preferences, ...(guest.preferences ?? {}), tags: [...(guest.preferences?.tags ?? [])] } }
                : base;
            // Options not in the preset list (from older records) stay selectable
            this.form.preferences.tags.forEach(t => { if (!this.tagOptions.includes(t)) this.tagOptions.push(t); });
            this.original = guest;
            this.pristine = JSON.stringify(this.form);
            this.draftKey = window.formDrafts.key('guest', guest?.id);
            this.draftRestored = false;
            const draft = window.formDrafts.load(this.draftKey);
            if (draft) {
                this.form = { ...this.form, ...draft, id: guest?.id ?? null };
                this.draftRestored = true;
            }
            this.errors = {};
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        discardDraft() {
            window.formDrafts.clear(this.draftKey);
            this.open(this.original);
            window.formDrafts.clear(this.draftKey);
            this.draftRestored = false;
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        toggleTag(tag) {
            const tags = this.form.preferences.tags;
            tags.includes(tag) ? tags.splice(tags.indexOf(tag), 1) : tags.push(tag);
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
                    body: JSON.stringify(this.form),
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success !== false) {
                    window.formDrafts.clear(this.draftKey);
                    this.close();
                    this.$dispatch('guest-saved', { guest: data.guest, created: !isEdit, message: data.message });
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
