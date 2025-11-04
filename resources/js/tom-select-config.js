import TomSelect from 'tom-select';

const defaultConfig = {
    create: false,
    sortField: { field: 'text', direction: 'asc' },
    maxOptions: null,
    placeholder: 'Pilih...',
    closeAfterSelect: true,
    plugins: ['remove_button', 'clear_button'],
    render: {
        no_results: function(data, escape) {
            return '<div class="no-results">Tidak ada hasil untuk "' + escape(data.input) + '"</div>';
        },
    }
};

window.initSelect = function(selector, options = {}) {
    return new TomSelect(selector, { ...defaultConfig, ...options });
};

window.initMultiSelect = function(selector, options = {}) {
    return new TomSelect(selector, {
        ...defaultConfig,
        plugins: ['remove_button', 'clear_button', 'checkbox_options'],
        maxItems: null,
        ...options
    });
};

window.initAjaxSelect = function(selector, url, options = {}) {
    return new TomSelect(selector, {
        ...defaultConfig,
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(url + '?search=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(json => { callback(json.data || json); })
                .catch(() => { callback(); });
        },
        ...options
    });
};

window.initUserSelect = function(selector, options = {}) {
    return initAjaxSelect(selector, '/api/users/search', {
        valueField: 'id',
        labelField: 'name',
        searchField: ['name', 'email', 'nim'],
        render: {
            option: function(data, escape) {
                return `<div class="py-2">
                    <div class="font-medium">${escape(data.name)}</div>
                    <div class="text-xs text-gray-500">${escape(data.email || '')} ${data.nim ? '- ' + escape(data.nim) : ''}</div>
                </div>`;
            },
            item: function(data, escape) { return `<div>${escape(data.name)}</div>`; }
        },
        ...options
    });
};

window.initProductSelect = function(selector, options = {}) {
    return initAjaxSelect(selector, '/api/products/search', {
        valueField: 'id',
        labelField: 'name',
        searchField: ['name', 'sku'],
        render: {
            option: function(data, escape) {
                return `<div class="py-2">
                    <div class="font-medium">${escape(data.name)}</div>
                    <div class="text-xs text-gray-500">SKU: ${escape(data.sku || '-') } | Harga: Rp ${escape(data.price || 0)}</div>
                </div>`;
            },
            item: function(data, escape) { return `<div>${escape(data.name)}</div>`; }
        },
        ...options
    });
};

// Auto-init
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-select]').forEach(el => { initSelect(el); });
    document.querySelectorAll('[data-multiselect]').forEach(el => { initMultiSelect(el); });
    document.querySelectorAll('[data-user-select]').forEach(el => { initUserSelect(el); });
    document.querySelectorAll('[data-product-select]').forEach(el => { initProductSelect(el); });
});

document.addEventListener('livewire:navigated', function() {
    document.querySelectorAll('[data-select]').forEach(el => { if (!el.tomselect) initSelect(el); });
});

export { TomSelect };
