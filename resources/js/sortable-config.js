import Sortable from 'sortablejs';

const defaultConfig = {
    animation: 150,
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    chosenClass: 'sortable-chosen',
    handle: '.sortable-handle',
    fallbackOnBody: true,
    swapThreshold: 0.65,
};

window.initSortable = function(selector, options = {}) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return null;

    return Sortable.create(element, { ...defaultConfig, ...options });
};

window.initSortableLivewire = function(selector, component, method, options = {}) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return null;

    return Sortable.create(element, {
        ...defaultConfig,
        onEnd: function() {
            const itemIds = Array.from(element.children).map(el => el.dataset.id);
            component.call(method, itemIds);
        },
        ...options
    });
};

window.initScheduleSortable = function(selector, component, options = {}) {
    const containers = document.querySelectorAll(selector);
    containers.forEach(container => {
        Sortable.create(container, {
            ...defaultConfig,
            group: 'schedule',
            onEnd: function(evt) {
                const userId = evt.item.dataset.userId;
                const fromSession = evt.from.dataset.session;
                const toSession = evt.to.dataset.session;
                const fromDay = evt.from.dataset.day;
                const toDay = evt.to.dataset.day;
                component.call('moveAssignment', {
                    userId,
                    fromDay,
                    fromSession,
                    toDay,
                    toSession,
                });
            },
            ...options
        });
    });
};

document.addEventListener('livewire:navigated', function() {
    document.querySelectorAll('[data-sortable]').forEach(element => {
        if (!element.sortable) {
            initSortable(element);
        }
    });
});

export { Sortable };
