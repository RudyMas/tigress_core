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
        input.setAttribute('data-password-toggle-initialized', 'true');
        input.classList.add('pe-5');

        // Maak de wrapper <div class="position-relative">
        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative';

        // Vervang input met wrapper, en zet input in wrapper
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Maak en configureer de toggle-knop
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-sm btn-link position-absolute end-0";
        button.setAttribute("aria-label", "Toon/verberg wachtwoord");
        button.innerHTML = `<i class="fa-regular fa-eye"></i>`;
        wrapper.appendChild(button);

        // Wacht op een repaint om correcte hoogte te krijgen
        requestAnimationFrame(() => {
            const inputHeight = input.offsetHeight;
            const buttonHeight = button.offsetHeight;
            const topOffset = (inputHeight - buttonHeight) / 2;
            button.style.top = `${topOffset}px`;
            button.style.marginRight = '0.3em';
            button.style.color = 'black';
            button.style.fontSize = '1rem';
        });

        // Toggle functionaliteit
        button.addEventListener("click", () => {
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            const icon = button.querySelector("i");
            icon.classList.toggle("fa-eye", !isPassword);
            icon.classList.toggle("fa-eye-slash", isPassword);
        });
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