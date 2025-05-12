$(function () {
    $('select').each(function () {
        const $select = $(this);
        const parentModal = $select.closest('.modal');

        $select.select2({
            theme: 'bootstrap-5',
            placeholder: $select.find('option[value=""]').text(),
            dropdownParent: parentModal.length ? parentModal : $(document.body)
        });
    });
});
