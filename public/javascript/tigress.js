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
}

function initGebruikersTable() {
    const datatableElement = document.getElementById('datatableTigress');

    if (!datatableElement) return;

    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable === 'function') {
        jQuery(datatableElement).DataTable({
            stateSave: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Alle"]],
            language: {
                url: '/node_modules/datatables.net-plugins/i18n/nl-NL.json'
            },
        });
    } else {
        console.warn('DataTables vereist nog jQuery – kan niet initialiseren zonder jQuery.');
    }

    const modal = document.getElementById('ModalGebruikerVerwijderen');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-id');
            const input = modal.querySelector('#VerwijderGebruiker');
            if (input) {
                input.value = itemId;
            }
        });
    }
}

// Automatisch opstarten bij DOM klaar
document.addEventListener('DOMContentLoaded', function () {
    initAutoGrow();
    initTooltips();
    // initModals();
    initGebruikersTable();
});

window.initTooltips = initTooltips;
window.autoResize = autoResize;
window.initAutoGrow = initAutoGrow;
// window.initModals = initModals;