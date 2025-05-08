/**
 * Tigress.js - Moderne UI-hulpfuncties zonder jQuery
 * Tooltip-init, auto-grow textareas, modals
 */

// Initialiseer Bootstrap-tooltips (ook voor modalknoppen als ze een title hebben)
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

// Automatisch textarea laten meegroeien bij input
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

// Initialiseer modale dialoogknoppen (bv. ID zetten bij verwijderen)
function initModals() {
    const modalVerwijder = document.getElementById('ModalRfdVerwijderen');
    if (modalVerwijder) {
        modalVerwijder.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-id');
            modalVerwijder.querySelector('#VerwijderRfd').value = itemId;
        });
    }
}

// Automatisch opstarten bij DOM klaar
document.addEventListener('DOMContentLoaded', function () {
    initAutoGrow();
    initTooltips();
    initModals();
});

window.initTooltips = initTooltips;
window.autoResize = autoResize;
window.initAutoGrow = initAutoGrow;
window.initModals = initModals;