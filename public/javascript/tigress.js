function autoResize($el) {
    $el.css('height', 'auto');
    $el.css('height', $el.prop('scrollHeight') + 'px');
}

$(function () {
    $('select').select2({
        theme: 'bootstrap-5',
        placeholder: $(this).data('placeholder'),
    });

    $('.auto-grow').each(function () {
        autoResize($(this));
    });

    $(document).on('input', '.auto-grow', function () {
        autoResize($(this));
    });

    $('[data-toggle="tooltip"]').tooltip({
        boundary: 'window',
        trigger: 'hover'
    });
});