$(function () {
    $('select').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            placeholder: $(this).data('placeholder') || ''
        });
    });
});
