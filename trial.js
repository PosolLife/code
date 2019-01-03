
var capch_data = '';
var verifyCallback = function(response) {
    capch_data = response;
};
var onloadCallback = function() {
    grecaptcha.render('trial_captch', {
        'sitekey' : '6xxxxxxxxxxxxxxt',
        'callback' : verifyCallback,
        'theme' : 'light'
    });
};

jQuery(document).ready(function () {
    // Selectors
    var _selectors = {
        email           : jQuery("#email"),
        /
        first_name      : jQuery("#f_name"),
        last_name       : jQuery("#l_name"),
        captcha_value   : jQuery("#trial_captch"),
    };
    // Messages
    var messages = {
        email       : "Please enter valid email!",
        captcha     : "Symbols doesn't match!",
        country     : "You should select the country!",
        industry    : "You should select the Industry!",
        first_name  : "Enter First Name!",
        last_name   : "Enter Last Name!",

    };
    // Regexp for email check
    var email_reg = /[A-Z0-9._%+-]+@[A-Z0-9.-]+.[A-Za-z]/igm;
    // Validating
    var validation = {
        form : function(data){
            var output = true;

            if(data.email == '' && !email_reg.test(data.email)) {
                _selectors.email.parent().addClass('has-error');
                _selectors.email.parent().append('<div class="fill_this_field  warning-msg">'+messages.email+'</div>');
                output = false;
            }
            if(data.first_name == '') {
                _selectors.first_name.parent().addClass('has-error');
                _selectors.first_name.parent().append('<div class="fill_this_field warning-msg">'+messages.first_name+'</div>');
                output = false;
            }

            if(data.last_name == '') {
                _selectors.last_name.parent().addClass('has-error');
                _selectors.last_name.parent().append('<div class="fill_this_field warning-msg">'+messages.last_name+'</div>');
                output = false;
            }
            if(data.captcha_value == '') {
                _selectors.captcha_value.parent().append('<div class="fill_this_field warning-msg">'+messages.captcha+'</div>');
                output = false;
            }
            return output;
        }
    };
    // Removing error classes
    var remove = {
        errors : function(){
            jQuery("#confirm,#email,#f_name,#l_name,#captcha").parent().removeClass('has-error');
            jQuery(".fill_this_field").remove();
            jQuery("text-red").html("");
        }
    };
    // Live disabling/enabling button
    jQuery("#email,#f_name,#l_name").on("input change", function() {
        if( _selectors.email.val()          != ''  &&
            _selectors.first_name.val()     != ""  &&
            _selectors.last_name.val()      != ""
        ) {
            jQuery("#start_trial").removeAttr("disabled");
        } else {
            jQuery("#start_trial").attr("disabled", "disabled");
        }
    });
    // Start trial button click
    jQuery("#start_trial").click(function () {
        remove.errors();
        var _vars = {
            email           : _selectors.email.val(),
            first_name      : _selectors.first_name.val(),
            last_name       : _selectors.last_name.val(),
            captcha_value   : capch_data,
        };
        if(validation.form(_vars)) {
            jQuery.ajax({
                "method": "POST",
                "url"   : "/register-trial",
                "data"  : jQuery("#form-trial").serialize()
            }).done(function (data) {
                console.log(data);
                if(data == 'Email already exists!') {
                    jQuery(".text-red").html("Email already exists!");
                    return false;
                } else {
                    document.location.href = '/report'
                }
            });
        }
    });

});
