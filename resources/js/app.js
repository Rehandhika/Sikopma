import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

// Register Alpine plugins
Alpine.plugin(focus);
Alpine.plugin(collapse);
Alpine.plugin(intersect);

// Global Alpine stores
Alpine.store('sidebar', {
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; }
});

Alpine.store('notifications', {
    items: [],
    add(message, type = 'info') {
        const id = Date.now();
        this.items.push({ id, message, type });
        setTimeout(() => this.remove(id), 5000);
    },
    remove(id) { this.items = this.items.filter(i => i.id !== id); }
});

// Expose Alpine
window.Alpine = Alpine;

// Start Alpine after defining components
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close(focusAfter) { if (!this.open) return; this.open = false; if (focusAfter) focusAfter.focus(); }
    }));

    Alpine.data('modal', (initialOpen = false) => ({
        open: initialOpen,
        show() { this.open = true; document.body.style.overflow = 'hidden'; },
        hide() { this.open = false; document.body.style.overflow = ''; }
    }));

    Alpine.data('tabs', (defaultTab = 0) => ({
        activeTab: defaultTab,
        setActive(i) { this.activeTab = i; },
        isActive(i) { return this.activeTab === i; }
    }));

    Alpine.data('toast', () => ({
        show: false,
        message: '',
        type: 'info',
        display(message, type = 'info', duration = 3000) {
            this.message = message; this.type = type; this.show = true;
            setTimeout(() => { this.show = false; }, duration);
        }
    }));

    Alpine.data('confirmDialog', () => ({
        open: false,
        title: '',
        message: '',
        onConfirm: null,
        show(title, message, callback) { this.title = title; this.message = message; this.onConfirm = callback; this.open = true; },
        confirm() { if (this.onConfirm) this.onConfirm(); this.open = false; },
        cancel() { this.open = false; }
    }));
});

Alpine.start();

// Feature modules
import './charts';
import './flatpickr-config';
import './tom-select-config';
import './sortable-config';
import './filepond-config';
import './utils';
