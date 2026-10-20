{{--
    Alpine component shared by rooms.index and rooms.show.
    Usage: <div x-data="rooms(@js($config))"> … modals included inside … </div>
--}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rooms', (config) => ({
        roomTypes: config.roomTypes ?? [],
        urls: {
            store: @js(route('rooms.store')),
            update: @js(route('rooms.update', ':id')),
            destroy: @js(route('rooms.destroy', ':id')),
            status: @js(route('rooms.status', ':id')),
            show: @js(route('rooms.show', ':id')),
            index: @js(route('rooms.index')),
            availability: @js(route('rooms.availability')),
        },

        isCreateModalOpen: false,
        isEditModalOpen: false,
        isAvailabilityModalOpen: false,
        isLoading: false,
        errors: {},

        formData: {},

        availabilityData: {
            room_id: null,
            room_number: '',
            arrival_date: '',
            departure_date: '',
            available_rooms: [],
            is_available: false,
        },

        availableFeatures: [
            'Sea View', 'Mountain View', 'Garden View', 'Balcony', 'Mini Bar',
            'Safe', 'TV', 'AC', 'Heating', 'Bathtub', 'Shower',
            'Kitchenette', 'Coffee Maker', 'Free WiFi', 'Room Service', 'Work Desk',
        ],

        init() {
            this.resetForm();
            this.availabilityData.arrival_date = this.isoDate(0);
            this.availabilityData.departure_date = this.isoDate(1);

            if (config.openCreate) {
                this.openCreateModal();
                this.formData.room_type_id = config.createRoomTypeId ?? '';
            }
        },

        // ---- Modals -------------------------------------------------------

        openCreateModal() {
            this.resetForm();
            this.isCreateModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        openEditModal(room) {
            this.errors = {};
            this.formData = {
                id: room.id,
                room_number: room.room_number,
                room_type_id: room.room_type_id,
                status: room.status,
                floor: room.floor,
                wing: room.wing ?? '',
                adult_price: room.adult_price ?? '',
                child_price: room.child_price ?? '',
                features: [...(room.features ?? [])],
                new_feature: '',
            };
            this.isEditModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        openAvailabilityModal(room) {
            this.availabilityData.room_id = room.id;
            this.availabilityData.room_number = room.room_number;
            this.availabilityData.available_rooms = [];
            this.availabilityData.is_available = false;
            this.isAvailabilityModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.isCreateModalOpen = false;
            this.isEditModalOpen = false;
            this.isAvailabilityModalOpen = false;
            this.isLoading = false;
            document.body.classList.remove('overflow-hidden');
        },

        resetForm() {
            this.errors = {};
            this.formData = {
                id: null,
                room_number: '',
                room_type_id: '',
                status: 'available',
                floor: 1,
                wing: '',
                adult_price: '',
                child_price: '',
                features: [],
                new_feature: '',
            };
        },

        // ---- Features -----------------------------------------------------

        addFeature() {
            const feature = (this.formData.new_feature || '').trim();
            if (feature && !this.formData.features.includes(feature)) {
                this.formData.features.push(feature);
            }
            this.formData.new_feature = '';
        },

        removeFeature(index) {
            this.formData.features.splice(index, 1);
        },

        // ---- Actions ------------------------------------------------------

        async submitForm() {
            const isEdit = !!this.formData.id;
            const { new_feature, ...payload } = this.formData;

            const data = await this.send(
                isEdit ? this.url('update', this.formData.id) : this.urls.store,
                isEdit ? 'PUT' : 'POST',
                payload,
            );

            if (data) {
                this.closeModal();
                this.reloadSoon(data.message);
            }
        },

        async changeStatus(room, status) {
            if (!confirm(`Change room ${room.room_number} status?`)) return;

            const data = await this.send(this.url('status', room.id), 'PATCH', { status });
            if (data) this.reloadSoon(data.message);
        },

        async deleteRoom(room) {
            if (!confirm(`Delete room ${room.room_number}? This cannot be undone.`)) return;

            const data = await this.send(this.url('destroy', room.id), 'DELETE');
            if (data) {
                this.showToast(data.message, 'success');
                setTimeout(() => window.location.href = this.urls.index, 800);
            }
        },

        async checkAvailability() {
            const a = this.availabilityData;
            if (!a.arrival_date || !a.departure_date || a.departure_date <= a.arrival_date) {
                this.showToast('Departure date must be after arrival date', 'error');
                return;
            }

            const params = new URLSearchParams({ arrival_date: a.arrival_date, departure_date: a.departure_date });
            const data = await this.send(`${this.urls.availability}?${params}`, 'GET');
            if (!data) return;

            const target = data.rooms.find(r => r.id === a.room_id);
            a.available_rooms = data.rooms;
            a.is_available = target?.is_available ?? false;

            if (a.is_available) {
                this.showToast(`Room ${a.room_number} is available for these dates`, 'success');
            } else if (target?.next_available_date) {
                this.showToast(`Room ${a.room_number} is booked; free from ${this.formatDisplayDate(target.next_available_date)}`, 'warning');
            } else {
                this.showToast(`Room ${a.room_number} is not available for these dates`, 'warning');
            }
        },

        // ---- Helpers ------------------------------------------------------

        url(name, id) {
            return this.urls[name].replace(':id', id);
        },

        /**
         * JSON request. Returns the response body on success, or null after
         * showing the error (validation errors go to this.errors).
         */
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
                    return data;
                }

                if (response.status === 422 && data.errors) {
                    this.errors = data.errors;
                }
                this.showToast(data.message || `Request failed (${response.status})`, 'error');
            } catch (error) {
                console.error(error);
                this.showToast('Network error. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }

            return null;
        },

        reloadSoon(message) {
            this.showToast(message || 'Saved', 'success');
            setTimeout(() => window.location.reload(), 800);
        },

        isoDate(daysFromToday) {
            const d = new Date();
            d.setDate(d.getDate() + daysFromToday);
            return d.toISOString().split('T')[0];
        },

        formatDisplayDate(value) {
            if (!value) return '';
            return new Date(value + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        },

        showToast(message, type = 'info') {
            document.querySelectorAll('.custom-toast').forEach(t => t.remove());

            const colours = { success: 'bg-success-600', error: 'bg-error-600', warning: 'bg-warning-600', info: 'bg-primary' };
            const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };

            const toast = document.createElement('div');
            toast.className = `custom-toast fixed top-4 right-4 z-[99999] flex items-center gap-2 rounded-lg px-4 py-3 text-sm text-white shadow-lg transition-opacity duration-300 ${colours[type] ?? colours.info}`;
            toast.innerHTML = `<i class="fas ${icons[type] ?? icons.info}"></i>`;
            toast.append(document.createTextNode(message));
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        },
    }));
});
</script>
