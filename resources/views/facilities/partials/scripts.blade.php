{{--
    Alpine component behind the venue and package modals: open with a record
    (edit) or nothing (new), save as JSON, show the server's validation errors.
      x-data="facilityForm({ name: 'venue', store: url, update: url-with-:id, destroy: url-with-:id, blank: {...} })"
      open:   $dispatch('open-venue-form', record?)
      after save / delete: follows data.url when given, otherwise reloads.
--}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('facilityForm', (config) => ({
        isOpen: false,
        isLoading: false,
        errors: {},
        form: {},

        init() {
            this.form = structuredClone(config.blank);
        },

        open(record = null) {
            this.form = { ...structuredClone(config.blank), ...(record ?? {}) };
            this.errors = {};
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
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
                    this.close();
                    if (data.url && method !== 'PUT') window.location.href = data.url;
                    else window.location.reload();
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
