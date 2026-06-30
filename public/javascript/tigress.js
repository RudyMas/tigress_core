/**
 * Tigress.js - Moderne UI-hulpfuncties zonder jQuery
 * @version 2026.06.30.0
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

// Initialise DataTables translations based on the document language
function initDatatablesTranslations() {
    window.tigress = window.tigress || {};

    const htmlLang = document.documentElement.lang.toLowerCase() || navigator.language.toLowerCase() || 'en';
    const shortLang = htmlLang.substring(0, 2);
    window.tigress.shortLang = shortLang;

    const languageFiles = {
        nl: '/node_modules/datatables.net-plugins/i18n/nl-NL.json',
        fr: '/node_modules/datatables.net-plugins/i18n/fr-FR.json',
        de: '/node_modules/datatables.net-plugins/i18n/de-DE.json',
        es: '/node_modules/datatables.net-plugins/i18n/es-ES.json',
        it: '/node_modules/datatables.net-plugins/i18n/it-IT.json',
        sv: '/node_modules/datatables.net-plugins/i18n/sv-SE.json',
        // no entry for en-US → use default
        // en: '/node_modules/datatables.net-plugins/i18n/en-GB.json',
    };

    const languageTinymce = {
        nl: {url: '/node_modules/tinymce-i18n/langs7/nl_BE.js', lang: 'nl_BE'},
        fr: {url: '/node_modules/tinymce-i18n/langs7/fr_FR.js', lang: 'fr_FR'},
        de: {url: '/node_modules/tinymce-i18n/langs7/de.js', lang: 'de'},
        es: {url: '/node_modules/tinymce-i18n/langs7/es.js', lang: 'es'},
        it: {url: '/node_modules/tinymce-i18n/langs7/it.js', lang: 'it'},
        sv: {url: '/node_modules/tinymce-i18n/langs7/sv_SE.js', lang: 'sv_SE'},
        // no entry for English → use default
    }

    window.tigress.languageDatatables = languageFiles[shortLang] ? {url: languageFiles[shortLang]} : {};
    window.tigress.languageTinymce = languageTinymce[shortLang] ? languageTinymce[shortLang] : '';
}

// Initialise the user datatable with DataTables if jQuery is available
function initGebruikersTable() {
    const datatableElement = document.getElementById('datatableTigress');

    if (!datatableElement) return;

    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable === 'function') {
        jQuery(datatableElement).DataTable({
            stateSave: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Alle"]],
            responsive: true,
            scrollX: false,
            language: tigress.languageDatatables,
        });
    } else {
        console.warn('DataTables requires jQuery – cannot initialize without jQuery.');
    }

    const modal = document.getElementById('ModalRemoveUser');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-id');
            const input = modal.querySelector('#RemoveUser');
            if (input) {
                input.value = itemId;
            }
        });
    }
}

// Create a reset-button for the saveState
function resetButtonDatatables(tableName) {
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm btn-secondary';
    btn.innerText = __('Reset table');
    btn.addEventListener('click', function () {
        if (confirm(__('Reset this table?'))) {
            resetDataTables(tableName);
        }
    });

    return btn;
}

// Reset the saveState of a DataTables
function resetDataTables(tableName) {
    if (!window.localStorage) {
        return;
    }

    const path = window.location.pathname;
    const key = 'DataTables_' + tableName + '_' + path;

    localStorage.removeItem(key);
    location.reload();
}

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


// Show popup
function showPopup(popupWindow) {
    popupWindow.classList.remove('hidden');
    requestAnimationFrame(() => popupWindow.classList.add('show'));
}

// Hide popup
function hidePopup(popupWindow, timeout = 300) {
    popupWindow.classList.remove('show');
    setTimeout(() => popupWindow.classList.add('hidden'), timeout);
}

// Handle clicks on popup elements
document.addEventListener('click', function (event) {
    const popupElement = event.target.closest('.popup');

    if (!popupElement) {
        return;
    }

    // Is dit een submit-knop binnen een form?
    if (
        popupElement.matches('button[type="submit"], input[type="submit"]') &&
        popupElement.form &&
        !popupElement.form.checkValidity()
    ) {
        return;
    }

    const popup = document.getElementById('loadingPopup');
    showPopup(popup);
});


// Lock a button on form submit to prevent multiple submissions
function lockOnSubmit(buttonId, text = __('In progress...')) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;

    const form = btn.closest('form');
    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerText = text;
    });
}

// add lock-submit class to buttons that should be locked on submit
document.addEventListener('submit', function (event) {
    const button = event.submitter;

    if (!button || !button.classList.contains('lock-submit')) {
        return;
    }

    button.disabled = true;

    const text = button.dataset.lockText || __('In progress...');

    if (button.tagName === 'INPUT') {
        button.value = text;
    } else {
        button.innerText = text;
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

// Automatically initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    initAutoGrow();
    initTooltips();
    initDatatablesTranslations();
    initGebruikersTable();
    initPasswordToggles();
    warnUnsavedChanges();
    lucide.createIcons();
});

window.initTooltips = initTooltips;
window.autoResize = autoResize;
window.initAutoGrow = initAutoGrow;

/**
 * @version 2026.01.08.0
 * Universele tigress translation loader and __-functie
 * Load 1 or more translation files and combine them smartly.
 * You can use __('Welkom') to get the translation for "Welkom" in the current language.
 */
(function () {
    let TRANSLATIONS = {};
    let LANG = (document.documentElement.lang || navigator.language || 'en').toLowerCase().substring(0, 2);

    /**
     * Load translation data, either from file(s) or a given object.
     * @param {Array<string>|object} input - Array of URLs to translation JSON files, or an object with translations.
     * @returns {Promise<void>}
     */
    function loadTranslations(input) {
        // Direct object toevoegen, geen fetch
        if (typeof input === "object" && !Array.isArray(input)) {
            for (const [lang, translations] of Object.entries(input)) {
                if (!TRANSLATIONS[lang]) TRANSLATIONS[lang] = {};
                Object.assign(TRANSLATIONS[lang], translations);
            }
            return Promise.resolve(); // interface consistent houden
        }

        // Array van bestanden, zoals vroeger
        return Promise.all(
            input.map(file =>
                fetch(file)
                    .then(r => {
                        if (!r.ok) throw new Error(`Can not load translation file: ${file}`);
                        return r.json();
                    })
                    .catch(e => {
                        console.warn(e);
                        return {};
                    })
            )
        ).then(jsons => {
            TRANSLATIONS = {};
            for (const json of jsons) {
                for (const [lang, translations] of Object.entries(json)) {
                    if (!TRANSLATIONS[lang]) TRANSLATIONS[lang] = {};
                    Object.assign(TRANSLATIONS[lang], translations);
                }
            }
        });
    }

    function __(text) {
        const lang = (window.LANG || LANG);
        if (TRANSLATIONS && TRANSLATIONS[lang] && TRANSLATIONS[lang][text]) {
            return TRANSLATIONS[lang][text];
        }
        return text;
    }

    window.tigress = window.tigress || {};
    window.tigress.loadTranslations = loadTranslations;
    window.__ = __;
})();

/**
 * Lock-pages heartbeat (resource/resourceId)
 * @version 2026.01.08.0
 *
 * Server contract (JSON):
 *  - success: { ok: true, expires_at?: "YYYY-MM-DD HH:MM:SS" }
 *  - locked by other: { ok: false, locked: true, locked_by_user_id?: number }
 *  - no lock: { ok: false, reason: "no_lock" }  (optioneel)
 */
(function () {
    window.tigress = window.tigress || {};

    let timer = null;
    let cfg = null;

    const DEFAULTS = {
        intervalMs: 2 * 60 * 1000, // 2 min (expiry=5 min -> safe)
        refreshUrl: '/lock-pages/refresh',
        releaseUrl: '/lock-pages/release',
        refreshOnVisibility: true,
        releaseOnUnload: true,

        // callbacks
        onOk: null,        // function(data) {}
        onLocked: null,    // function(data) {}
        onError: null      // function(error) {}
    };

    async function refresh() {
        if (!cfg) return;

        try {
            const res = await fetch(cfg.refreshUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    resource: cfg.resource,
                    resourceId: cfg.resourceId
                })
            });

            if (!res.ok) return;

            const data = await res.json();

            if (data && data.ok) {
                if (typeof cfg.onOk === 'function') cfg.onOk(data);
                return;
            }

            // If locked by someone else (or we lost lock)
            if (data && data.locked) {
                stopInternal();

                if (typeof cfg.onLocked === 'function') {
                    cfg.onLocked(data);
                } else {
                    alert(__('This page is currently being edited by someone else. Please try again later.'));
                }
            }
        } catch (e) {
            if (typeof cfg?.onError === 'function') cfg.onError(e);
        }
    }

    function visibilityHandler() {
        if (!cfg) return;
        if (!document.hidden) refresh();
    }

    function stopInternal() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        if (cfg?.refreshOnVisibility) {
            document.removeEventListener('visibilitychange', visibilityHandler);
        }
        cfg = null;
    }

    function start(resource, resourceId, options = {}) {
        // stop previous heartbeat if any
        stopInternal();

        cfg = Object.assign({}, DEFAULTS, options, {
            resource,
            resourceId
        });

        // Start interval + immediate refresh
        refresh();
        timer = setInterval(refresh, cfg.intervalMs);

        if (cfg.refreshOnVisibility) {
            document.addEventListener('visibilitychange', visibilityHandler);
        }
    }

    function stop() {
        stopInternal();
    }

    // Release lock on unload (best-effort)
    window.addEventListener('beforeunload', () => {
        if (!cfg || !cfg.releaseOnUnload) return;

        try {
            const res = fetch(cfg.releaseUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    resource: cfg.resource,
                    resourceId: cfg.resourceId
                })
            });
        } catch (e) {
            // ignore
        }
    });

    // Expose API
    window.tigress.lockPages = {
        start,
        stop,
        refresh
    };
})();

/**
 * Tigress email validation based on domain rules.
 * Usage: Add data-email-type="school" or data-email-type="business" to input fields.
 * Optionally, add data-email-message="Custom message" for a custom validation message.
 * @type {{school: string[], business: string[]}}
 */
const tigressEmailRules = {
    school: [
        'gmail.com',
        'hotmail.com',
        'outlook.com',
        'live.com',
        'msn.com',
        'yahoo.com',
        'icloud.com',
        'proton.me',
        'protonmail.com',
        'telenet.be',
        'proximus.be'
    ],
    business: [
        'gmail.com',
        'hotmail.com',
        'outlook.com',
        'live.com',
        'msn.com',
        'yahoo.com',
        'icloud.com',
        'proton.me',
        'protonmail.com'
    ]
};

function validateEmailType(input) {

    const email = input.value.trim().toLowerCase();
    const type = input.dataset.emailType;
    let message = input.dataset.emailMessage ||
        'This email address is not allowed.';
    message = __(message);

    const blockedDomains = tigressEmailRules[type] || [];

    input.setCustomValidity('');

    if (email === '') {
        return;
    }

    if (!email.includes('@')) {
        return;
    }

    const domain = email.split('@').pop();

    if (blockedDomains.includes(domain)) {
        input.setCustomValidity(message);
    }
}

// input + blur afhandelen
document.addEventListener('input', function (event) {
    if (event.target.matches('input[data-email-type]')) {
        validateEmailType(event.target);
    }
});

document.addEventListener('blur', function (event) {
    if (event.target.matches('input[data-email-type]')) {
        validateEmailType(event.target);
    }
}, true);

// formulier-validatie
document.addEventListener('submit', function (event) {

    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    form.querySelectorAll('input[data-email-type]')
        .forEach(validateEmailType);

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
    }

}, true);

// Showing download progress on the website
const TigressDownload = (() => {
    const getFilenameFromDisposition = (disposition, fallback) => {
        if (!disposition) return fallback;

        const utf8Match = disposition.match(/filename\*=UTF-8''([^;\n]+)/i);
        if (utf8Match?.[1]) {
            return decodeURIComponent(utf8Match[1]);
        }

        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i);
        return match?.[1]?.replace(/['"]/g, '') || fallback;
    };

    const resolveModal = (modal, modalId) => {
        if (modal) return modal;

        if (!modalId || typeof bootstrap === 'undefined') {
            return null;
        }

        const modalElement = document.getElementById(modalId);
        return modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    };

    const resolveElement = (element, id) => {
        if (element) return element;
        return id ? document.getElementById(id) : null;
    };

    const updateProgress = (bar, text, percent, loadedBytes, totalBytes) => {
        if (bar) {
            bar.style.width = `${percent}%`;
            bar.setAttribute('aria-valuenow', String(percent));
            bar.textContent = `${percent}%`;
        }

        if (text && totalBytes) {
            text.textContent =
                `${__('Busy downloading')}: ${(loadedBytes / 1024).toFixed(0)} KB / ${(totalBytes / 1024).toFixed(0)} KB...`;
        }
    };

    const download = async (url, options = {}) => {
        let {
            method = 'GET',
            filename = 'download',
            fetcher = window.fetch.bind(window),
            fetchOptions = {},

            modal = null,
            modalId = null,

            progressBar = null,
            progressBarId = 'downloadProgressBar',

            progressText = null,
            progressTextId = 'downloadProgressText',

            messages = {}
        } = options;

        modal = resolveModal(modal, modalId);
        progressBar = resolveElement(progressBar, progressBarId);
        progressText = resolveElement(progressText, progressTextId);

        const msg = {
            connecting: __('Connecting...'),
            downloading: __('Downloading file...'),
            saving: __('Saving file...'),
            error: __('Error downloading: '),
            ...messages
        };

        try {
            if (progressBar) {
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', '0');
                progressBar.textContent = '0%';
            }

            if (progressText) progressText.textContent = msg.connecting;
            if (modal) modal.show();

            const response = await fetcher(url, {
                method,
                credentials: 'same-origin',
                ...fetchOptions
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const disposition = response.headers.get('content-disposition');
            const finalFilename = getFilenameFromDisposition(disposition, filename);

            const contentLength = response.headers.get('content-length');

            let blob;

            if (!contentLength || !response.body) {
                if (progressText) progressText.textContent = msg.downloading;
                blob = await response.blob();
            } else {
                const totalBytes = parseInt(contentLength, 10);
                let loadedBytes = 0;

                const reader = response.body.getReader();

                const stream = new ReadableStream({
                    async start(controller) {
                        try {
                            while (true) {
                                const { done, value } = await reader.read();

                                if (done) {
                                    controller.close();
                                    break;
                                }

                                loadedBytes += value.byteLength;

                                const percent = Math.round((loadedBytes / totalBytes) * 100);
                                updateProgress(progressBar, progressText, percent, loadedBytes, totalBytes);

                                controller.enqueue(value);
                            }
                        } catch (error) {
                            controller.error(error);
                        }
                    }
                });

                blob = await new Response(stream).blob();
            }

            if (progressText) progressText.textContent = msg.saving;

            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            a.style.display = 'none';
            a.href = blobUrl;
            a.download = finalFilename;

            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(blobUrl);
            document.body.removeChild(a);

            return true;
        } catch (error) {
            alert(msg.error + error.message);
            return false;
        } finally {
            setTimeout(() => {
                if (modal) modal.hide();
            }, 400);
        }
    };

    return {
        download
    };
})();

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-download-file');

    if (!button) {
        return;
    }

    event.preventDefault();

    TigressDownload.download(button.dataset.url, {
        filename: button.dataset.filename || 'download.pdf',
        modalId: button.dataset.modalId || 'downloadProgressModal'
    });
});
