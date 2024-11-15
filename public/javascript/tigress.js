$(function () {
    $('select').select2({
        theme: 'bootstrap-5',
        placeholder: $(this).data('placeholder'),
    });
});