import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

flatpickr.localize(Indonesian);

const defaultConfig = {
    dateFormat: 'd M Y',
    altInput: true,
    altFormat: 'd M Y',
    allowInput: true,
    locale: Indonesian,
};

window.initDatePicker = function(selector, options = {}) {
    return flatpickr(selector, { ...defaultConfig, ...options });
};

window.initTimePicker = function(selector, options = {}) {
    return flatpickr(selector, {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        ...options,
    });
};

window.initDateTimePicker = function(selector, options = {}) {
    return flatpickr(selector, {
        ...defaultConfig,
        enableTime: true,
        dateFormat: 'd M Y H:i',
        altFormat: 'd M Y H:i',
        time_24hr: true,
        ...options,
    });
};

window.initDateRangePicker = function(selector, options = {}) {
    return flatpickr(selector, {
        ...defaultConfig,
        mode: 'range',
        ...options,
    });
};

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-datepicker]').forEach(el => initDatePicker(el));
    document.querySelectorAll('[data-timepicker]').forEach(el => initTimePicker(el));
    document.querySelectorAll('[data-datetimepicker]').forEach(el => initDateTimePicker(el));
    document.querySelectorAll('[data-daterange]').forEach(el => initDateRangePicker(el));
});

// Livewire navigation re-init
document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-datepicker]').forEach(el => { if (!el._flatpickr) initDatePicker(el); });
});

export { flatpickr };
