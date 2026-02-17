$(function () {
    $('#postcode').on('input', function () {
        let value = $(this).val().replace(/[^0-9]/g, '');

        value = value.substring(0, 7);

        if (value.length > 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }

        $(this).val(value);
    });
});