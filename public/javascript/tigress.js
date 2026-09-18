/**
 * Tigress.js - Main entry point
 * @version 2026.09.18.0
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

import { initCarousel } from './tigress/carousel.js';

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
    initCarousel();

    if (window.lucide) {
        window.lucide.createIcons();
    }
});