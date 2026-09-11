/**
 * Tigress Email Validation JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

import { __ } from './translations.js';

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

// Backwards-compatible globals
window.tigressEmailRules = tigressEmailRules;
window.validateEmailType = validateEmailType;

export { tigressEmailRules, validateEmailType };
