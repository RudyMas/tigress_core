/**
 * Tigress JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

// Initialise Bootstrap tooltips for elements with data-bs-toggle="tooltip", data-toggle="tooltip", or data-bs-toggle="modal"
function initTooltips(scope = document) {
    const tooltipTriggerList = scope.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"], [data-bs-toggle="modal"]');
    tooltipTriggerList.forEach(el => {
        if (!el._tooltipInstance) {
            el._tooltipInstance = new bootstrap.Tooltip(el, {
                boundary: 'window',
                trigger: 'hover'
            });
        }
    });
}

// Automatically resize textareas based on content
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
}

function initAutoGrow(scope = document) {
    scope.querySelectorAll('.auto-grow').forEach(el => autoResize(el));

    scope.addEventListener('input', function (e) {
        if (e.target.matches('.auto-grow')) {
            autoResize(e.target);
        }
    });
}

// Backwards-compatible globals
window.initTooltips = initTooltips;
window.autoResize = autoResize;
window.initAutoGrow = initAutoGrow;

export { initTooltips, autoResize, initAutoGrow };
