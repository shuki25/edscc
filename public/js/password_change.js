$(()=>{
    var verifyPasswordMessage = $('#verify-password-message').text();
    var newPasswordMessage = $('#new-password-message').text();
    var passwordScore = 0;

    $('form').on('keypress', function (e) {
        var key = e.charCode || e.keyCode || 0;
        if(key == 13) {
            e.preventDefault();
        }
    });
    $('#purge-confirm').on('change', function (e) {
        e.preventDefault();
        if($('#purge-confirm').is(':checked')) {
            $('#purge-button').removeAttr('disabled');
        }
        else {
            $('#purge-button').attr('disabled','disabled');
        }
    });

    $('#current-password').on('keyup change', delay(function (e) {
        e.preventDefault();
        $('#verify-password').trigger('change');
    },100));

    $('#verify-password').on('keyup change', delay(function (e) {
        e.preventDefault();

        var vp = $(this).val();
        var cp = $('#current-password').val();
        var button = $('#change-password-button');
        var isMatched = false;

        normalText($('#verify-password-message'));

        if(vp.length == 0) {
            $('#verify-password-message').text(verifyPasswordMessage);
        }
        else if(vp == $('#new-password').val() && vp.length > 0) {
            $('#verify-password-message').text('Passwords match').addClass('text-success');
            isMatched = true;
        }
        else {
            $('#verify-password-message').text('Mismatch Password').addClass('text-danger');
        }

        if(isMatched == true && vp.length >= 8 && passwordScore > 2 && cp.length > 0) {
            button.removeAttr('disabled');
        }
        else {
            button.attr('disabled','disabled');
        }

    },300));

    $('#new-password').on('keyup change', delay(function (e) {
        var np = $(this).val();
        var cp = $('#current-password').val();
        var msg = $('#new-password-message');
        var email = $('#email').val()

        if(np.length == 0) {
            msg.text(newPasswordMessage);
            normalText($('#new-password-message'));
        }
        else {
            $.ajax({
                url: 'ajax/password/strength',
                method: 'POST',
                dataType: 'json',
                data: {
                    'q': np,
                    'cp': cp,
                    'email': email
                },
                success: function (r) {
                    msg.text('');
                    passwordScore = r.score;
                    normalText($('#new-password-message'));
                    if(r.score > 2) {
                        msg.addClass('text-success');
                    }
                    else if(r.score == 2) {
                        msg.addClass('text-warning');
                    }
                    else {
                        msg.addClass('text-danger');
                    }
                    msg.text(r.message);
                }
            });
        }
        $('#verify-password').trigger('change');

    },300));

    $('#reset-button').on('click', function (e) {
        $('#verify-password').trigger('change');
        $('#new-password').trigger('change');
    });
});



function normalText(element) {
    element.removeClass(function (index, className) {
        return (className.match(/(^|\s)text-\S+/g) || []).join(' ');
    });
}

function delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}