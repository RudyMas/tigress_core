/**
 * Tigress JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

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

// Backwards-compatible globals
window.showPopup = showPopup;
window.hidePopup = hidePopup;

export { showPopup, hidePopup };
