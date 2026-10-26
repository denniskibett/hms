{{--
    Alpine component behind most add / edit modals: open with a record (edit)
    or nothing (new), save as JSON, show the server's validation errors.
      x-data="facilityForm({ store: url, update: url-with-:id, destroy: url-with-:id, blank: {...} })"
      open:   $dispatch('open-venue-form', record?)
      after save / delete: follows data.url when given, otherwise reloads.
    What is typed is kept as a draft (window.formDrafts) until it is saved or
    discarded, so a reload or a closed dialog doesn't lose it. A record passed
    in without an id is a prefill and starts fresh.
--}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('facilityForm', (config) => ({
        isOpen: false,
        isLoading: false,
        errors: {},
        form: {},
        original: null,
        draftKey: null,
        draftRestored: false,
        pristine: null,

        init() {
            this.form = structuredClone(config.blank);
            // Keep a draft only once something has been changed
            this.$watch('form', () => {
                if (!this.isOpen || !this.draftKey) return;
                JSON.stringify(this.form) === this.pristine
                    ? window.formDrafts.clear(this.draftKey)
                    : window.formDrafts.save(this.draftKey, this.form);
            });
        },

        open(record = null) {
            const isPrefill = record && !record.id && Object.keys(record).length > 0;
            this.original = record;
            this.form = { ...structuredClone(config.blank), ...(record ?? {}) };
            this.pristine = JSON.stringify(this.form);
            this.draftKey = window.formDrafts.key(config.store || config.update, record?.id);
            this.draftRestored = false;

            const draft = isPrefill ? null : window.formDrafts.load(this.draftKey);
            if (draft) {
                this.form = { ...this.form, ...draft, id: record?.id ?? null };
                this.draftRestored = true;
            }

            this.errors = {};
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        discardDraft() {
            window.formDrafts.clear(this.draftKey);
            this.form = { ...structuredClone(config.blank), ...(this.original ?? {}) };
            this.pristine = JSON.stringify(this.form);
            this.draftRestored = false;
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        submit() {
            const isEdit = !!this.form.id;
            return this.send(isEdit ? config.update.replace(':id', this.form.id) : config.store, isEdit ? 'PUT' : 'POST', this.form);
        },

        destroy() {
            if (!this.form.id || !confirm(`Delete ${this.form.name}? This cannot be undone.`)) return;
            return this.send(config.destroy.replace(':id', this.form.id), 'DELETE');
        },

        async send(url, method, body = null) {
            this.isLoading = true;
            this.errors = {};

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success !== false) {
                    window.formDrafts.clear(this.draftKey);
                    this.close();
                    if (data.url && method !== 'PUT') window.location.href = data.url;
                    else window.location.reload();
                    return;
                }
                if (response.status === 422 && data.errors) this.errors = data.errors;
                else if (response.status === 419) this.errors = { form: ['Your session expired. Reload the page and save again; what you typed is kept.'] };
                else this.errors = { form: [data.message || `Request failed (${response.status})`] };
                // Long forms: bring the error list (at the top) into view
                this.$nextTick(() => this.$root.querySelectorAll('.overflow-y-auto').forEach(el => el.scrollTo({ top: 0, behavior: 'smooth' })));
            } catch (error) {
                console.error(error);
                this.errors = { form: ['Network error. Please try again; what you typed is kept.'] };
            } finally {
                this.isLoading = false;
            }
        },
    }));
});
</script>
@endonce
