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

// Initialiseer de gebruikers datatable
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

// Initialiseer wachtwoord toggle knoppen
function initPasswordToggles(scope = document) {
    const passwordFields = scope.querySelectorAll('input[type="password"]:not([data-password-toggle-initialized])');

    passwordFields.forEach((input) => {
        // Markeer als geïnitialiseerd
        input.setAttribute('data-password-toggle-initialized', 'true');

        // Zorg dat de ouder een juiste wrapper heeft
        const wrapper = input.closest('.position-relative') || input.parentElement;
        wrapper.classList.add('position-relative');

        // Padding zodat oogje niet overlapt
        input.classList.add('pe-5');

        // Maak de toggleknop
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-sm btn-link position-absolute end-0 top-0";
        button.style.marginTop = '2.55em';
        button.style.marginRight = '1em';
        button.style.color = 'darkgray';
        button.setAttribute("aria-label", "Toon/verberg wachtwoord");
        button.innerHTML = `<i class="fa-regular fa-eye"></i>`;

        // Toggle logica
        button.addEventListener("click", () => {
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            const icon = button.querySelector("i");
            icon.classList.toggle("fa-eye", !isPassword);
            icon.classList.toggle("fa-eye-slash", isPassword);
        });

        // Voeg knop toe aan de wrapper
        wrapper.appendChild(button);
    });
}


// Automatisch opstarten bij DOM klaar
document.addEventListener('DOMContentLoaded', function () {
    initAutoGrow();
    initTooltips();
    initGebruikersTable();
    initPasswordToggles();
});

window.initTooltips = initTooltips;
window.autoResize = autoResize;
window.initAutoGrow = initAutoGrow;