// resources/js/modal-service.js
window.ModalService = {
    modals: {
        alert: {
            show: false,
            type: 'info',
            title: '',
            message: '',
            confirmText: 'OK',
            onConfirm: null
        },
        confirm: {
            show: false,
            title: 'Confirm Action',
            message: 'Are you sure?',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            confirmButtonClass: 'bg-primary',
            onConfirm: null,
            onCancel: null
        },
        slideover: {
            show: false,
            title: '',
            component: null,
            data: null,
            size: 'lg'
        }
    },

    // Alert methods
    alert(config) {
        this.modals.alert = {
            show: true,
            type: config.type || 'info',
            title: config.title || this.getDefaultTitle(config.type),
            message: config.message,
            confirmText: config.confirmText || 'OK',
            onConfirm: config.onConfirm || (() => this.closeAlert())
        };
    },

    success(message, title = 'Success') {
        this.alert({ type: 'success', title, message });
    },

    error(message, title = 'Error') {
        this.alert({ type: 'error', title, message });
    },

    warning(message, title = 'Warning') {
        this.alert({ type: 'warning', title, message });
    },

    info(message, title = 'Information') {
        this.alert({ type: 'info', title, message });
    },

    closeAlert() {
        this.modals.alert.show = false;
    },

    // Confirm dialog
    confirm(config) {
        this.modals.confirm = {
            show: true,
            title: config.title || 'Confirm Action',
            message: config.message,
            confirmText: config.confirmText || 'Confirm',
            cancelText: config.cancelText || 'Cancel',
            confirmButtonClass: config.confirmButtonClass || 'bg-primary',
            onConfirm: () => {
                config.onConfirm?.();
                this.closeConfirm();
            },
            onCancel: () => {
                config.onCancel?.();
                this.closeConfirm();
            }
        };
    },

    closeConfirm() {
        this.modals.confirm.show = false;
    },

    // Slideover methods
    openSlideover(component, title = '', data = null, size = 'lg') {
        this.modals.slideover = {
            show: true,
            component,
            title,
            data,
            size
        };
    },

    closeSlideover() {
        this.modals.slideover.show = false;
    },

    getDefaultTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };
        return titles[type] || 'Notification';
    }
};

// Initialize Alpine store
document.addEventListener('alpine:init', () => {
    Alpine.store('modals', window.ModalService.modals);
});