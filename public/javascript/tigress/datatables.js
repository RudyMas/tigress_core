/**
 * Tigress JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

import { __ } from './translations.js';

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
            language: window.tigress.languageDatatables,
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

// Backwards-compatible globals
window.initDatatablesTranslations = initDatatablesTranslations;
window.initGebruikersTable = initGebruikersTable;
window.resetButtonDatatables = resetButtonDatatables;
window.resetDataTables = resetDataTables;

export {
    initDatatablesTranslations,
    initGebruikersTable,
    resetButtonDatatables,
    resetDataTables
};
