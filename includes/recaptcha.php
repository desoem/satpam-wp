<?php

add_action('login_enqueue_scripts', function () {
    $site_key = get_option('recaptcha_site_key', '');

    if ( ! $site_key ) {
        return;
    }

    // Menyisipkan skrip reCAPTCHA
    echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($site_key) . '"></script>';
    echo '<script>
        grecaptcha.ready(function () {
            grecaptcha.execute("' . esc_attr($site_key) . '", { action: "login" }).then(function (token) {
                var recaptchaInput = document.createElement("input");
                recaptchaInput.type = "hidden";
                recaptchaInput.name = "g-recaptcha-response";
                recaptchaInput.value = token;
                document.getElementById("loginform").appendChild(recaptchaInput);
            });
        });
    </script>';
});
