/**
 * Tigress_legacy.js - Fallback for legacy Tigress features that are not yet migrated to the new JS structure.
 * This file should be removed once all legacy features have been migrated.
 *
 * Note: This file is included in the base template and should only contain code that is necessary for legacy features.
 *
 * Mostly because a package like select2 uses jQuery and we don't want to include jQuery in the new JS structure just
 * for select2. Once select2 is migrated to the new JS structure, this file can be removed.
 *
 * @version 2026.02.11.0
 */

function initSelect2(root = document) {
    $(root).find('select').each(function () {
        const $select = $(this);
        if ($select.data('select2')) return; // already initialized

        const parentModal = $select.closest('.modal');
        const emptyOptText = $select.find('option[value=""]').first().text() || '';

        $select.select2({
            theme: 'bootstrap-5',
            placeholder: emptyOptText,
            dropdownParent: parentModal.length ? parentModal : $(document.body)
        });
    });
}

$(function () {
    initSelect2(document);
});
