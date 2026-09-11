/**
 * Tigress JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

import { __ } from './translations.js';

// Initialise password toggle buttons for password fields
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

// Lock a button on form submit to prevent multiple submissions
function lockOnSubmit(buttonId, text = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + __('In progress...')) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;

    const form = btn.closest('form');
    form.addEventListener('submit', () => {
        btn.disabled = true;
        if (btn.tagName === 'INPUT') {
            btn.value = text;
        } else {
            btn.innerHTML = text;
        }
    });
}

// add lock-submit class to buttons that should be locked on submit
document.addEventListener('submit', function (event) {
    const button = event.submitter;

    if (!button || !button.classList.contains('lock-submit')) {
        return;
    }

    button.disabled = true;

    const text = button.dataset.lockText || '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + __('In progress...');

    if (button.tagName === 'INPUT') {
        button.value = text;
    } else {
        button.innerHTML = text;
    }
});

/**
 * @version 2026.02.11.0
 * Warn users about unsaved changes when they attempt to leave the page.
 * Tracks changes in forms and shows a confirmation dialog if there are unsaved changes.
 *
 * @param {string} formSelector - CSS selector to identify forms to track (default: 'form')
 * @param {string} warningText - Custom warning message for unsaved changes (default: 'You have unsaved changes. Are you sure you want to leave?')
 * @param {object} options - Additional configuration options:
 *   - ignoreSelector: CSS selector for fields to ignore when tracking changes (default: '[data-ignore-dirty="1"]')
 *   - ignoreDisabled: Whether to ignore disabled fields (default: true)
 *   - enableSelect2: Whether to add special handling for Select2 fields (default: true)
 *
 * @returns {object} An object with methods to check dirty state, mark dirty, reset, and destroy the handler.
 */
function warnUnsavedChanges(
    formSelector = 'form',
    warningText = __('You have unsaved changes. Are you sure you want to leave?'),
    options = {}
) {
    const cfg = {
        ignoreSelector: '[data-ignore-dirty="1"]',
        ignoreDisabled: true,
        enableSelect2: true,
        ...options
    };
    let bypassOnce = false;

    const forms = Array.from(document.querySelectorAll(formSelector));
    const dirtyMap = new WeakMap();

    const setDirty = (form, value = true) => dirtyMap.set(form, !!value);
    const isDirtyForm = (form) => dirtyMap.get(form) === true;
    const anyDirty = () => forms.some(isDirtyForm);

    forms.forEach(f => setDirty(f, false));

    const resolveFormFromEl = (el) => el?.closest?.(formSelector) || null;

    const isTrackableField = (el) => {
        if (!el || el.nodeType !== 1) return false;
        if (cfg.ignoreSelector && el.closest(cfg.ignoreSelector)) return false;
        if (cfg.ignoreDisabled && (el.disabled || el.closest('fieldset[disabled]'))) return false;

        // ignore buttons and hidden inputs
        if (el.matches('button, [type="button"], [type="submit"], [type="reset"], input[type="hidden"]')) return false;

        // normal fields
        if (el.matches('input, textarea, select')) return true;

        // contenteditable
        if (el.isContentEditable) return true;

        return false;
    };

    const markDirtyFromEvent = (e) => {
        const target = e.target;
        if (!isTrackableField(target)) return;

        const form = resolveFormFromEl(target);
        if (form) setDirty(form, true);
    };

    // Generic DOM events
    const eventTypes = ['input', 'change', 'keyup', 'paste', 'cut'];
    eventTypes.forEach(type => document.addEventListener(type, markDirtyFromEvent, true));

    // Reset on submit
    const onSubmit = (e) => {
        const form = e.target?.closest?.(formSelector);
        if (form) setDirty(form, false);
    };
    document.addEventListener('submit', onSubmit, true);

    // beforeunload
    const onBeforeUnload = (e) => {
        if (!anyDirty()) return;
        if (bypassOnce) return;

        e.preventDefault();
        e.returnValue = warningText; // browsers ignore custom text, but this helps trigger the dialog
        return warningText;
    };
    window.addEventListener('beforeunload', onBeforeUnload);

    // ---- Select2 safety net (optional, requires jQuery + Select2) ----
    let select2HandlerAttached = false;
    const initSelect2Support = () => {
        if (!cfg.enableSelect2) return;

        const $ = window.jQuery || window.$;
        if (!$ || !$.fn || !$.fn.select2) return;
        if (select2HandlerAttached) return;
        select2HandlerAttached = true;

        $(document).on('select2:select select2:unselect select2:clear select2:close', function (ev) {
            const el = ev.target; // original <select>
            if (!el) return;
            if (cfg.ignoreSelector && el.closest(cfg.ignoreSelector)) return;

            const form = resolveFormFromEl(el);
            if (form) setDirty(form, true);
        });
    };
    initSelect2Support();

    // ---- TinyMCE hook helper ----
    // Call controller.bindTinyMCE(editor) from tinymce.init({ setup(editor) { ... }})
    const bindTinyMCE = (editor) => {
        if (!editor) return;

        const mark = () => {
            // original textarea element
            const el = editor.getElement ? editor.getElement() : null;
            if (!el) return;

            if (cfg.ignoreSelector && el.closest(cfg.ignoreSelector)) return;

            const form = resolveFormFromEl(el);
            if (form) setDirty(form, true);
        };

        // Real user edits
        editor.on('input change undo redo keyup', mark);

        // Only treat SetContent as dirty if it's not the initial load
        editor.on('SetContent', (e) => {
            // TinyMCE often sets e.initial = true on initial content load
            // Also ignore programmatic sets (when present)
            if (e?.initial) return;
            if (e?.set === true) return;          // some versions use this for programmatic sets
            if (e?.format === "raw") return;      // optional extra filter, can remove
            mark();
        });
    };

    return {
        // TinyMCE integration point:
        bindTinyMCE,

        isDirty: (formOrSelector) => {
            if (!formOrSelector) return anyDirty();
            const form = resolveForm(formOrSelector);
            return form ? isDirtyForm(form) : false;
        },
        markDirty: (formOrSelector) => {
            const form = resolveForm(formOrSelector);
            if (form) setDirty(form, true);
        },
        reset: (formOrSelector) => {
            if (!formOrSelector) {
                forms.forEach(f => setDirty(f, false));
                return;
            }
            const form = resolveForm(formOrSelector);
            if (form) setDirty(form, false);
        },
        destroy: () => {
            eventTypes.forEach(type => document.removeEventListener(type, markDirtyFromEvent, true));
            document.removeEventListener('submit', onSubmit, true);
            window.removeEventListener('beforeunload', onBeforeUnload);
        }
    };

    function resolveForm(formOrSelector) {
        if (typeof formOrSelector === 'string') return document.querySelector(formOrSelector);
        return formOrSelector;
    }
}

// Backwards-compatible globals
window.initPasswordToggles = initPasswordToggles;
window.lockOnSubmit = lockOnSubmit;
window.warnUnsavedChanges = warnUnsavedChanges;

export { initPasswordToggles, lockOnSubmit, warnUnsavedChanges };
