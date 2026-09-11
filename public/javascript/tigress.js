/**
 * Tigress.js - Main entry point
 * @version 2026.09.11.1
 */

import { initTooltips, initAutoGrow } from './tigress/core.js';

import {
    initDatatablesTranslations,
    initGebruikersTable
} from './tigress/datatables.js';

import {
    initPasswordToggles,
    warnUnsavedChanges
} from './tigress/forms.js';

import './tigress/translations.js';
import './tigress/popup.js';
import './tigress/lock-pages.js';
import './tigress/email-validation.js';
import './tigress/download.js';

initDatatablesTranslations();

document.addEventListener('DOMContentLoaded', function () {
    initAutoGrow();
    initTooltips();
    initGebruikersTable();
    initPasswordToggles();
    warnUnsavedChanges();

    if (window.lucide) {
        window.lucide.createIcons();
    }
});