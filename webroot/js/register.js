$('#confirm_password').on('keyup', function () {
    if ($(this).val() == $('#password').val()) {
        $('#message').html('matchar').css('color', 'green');
    } else $('#message').html('matchar inte').css('color', 'red');
});
