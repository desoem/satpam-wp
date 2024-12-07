<?php

add_action('login_form', function () {
    ?>
    <p>
        <label for="2fa_code">2FA Code<br>
            <input type="text" name="2fa_code" id="2fa_code" class="input" size="20" required>
        </label>
    </p>
    <?php
});

add_filter('authenticate', function ($user, $username, $password) {
    if (is_wp_error($user)) {
        return $user;
    }

    $user_id = $user->ID;
    $secret = get_user_meta($user_id, 'google_authenticator_secret', true);

    if ($secret) {
        require_once __DIR__ . '/../vendor/autoload.php'; // Pastikan path benar
        $ga = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        $otp = sanitize_text_field($_POST['2fa_code']);

        // Cek jika kode 2FA tidak kosong
        if (empty($otp)) {
            return new WP_Error('missing_2fa_code', __('Please enter the 2FA code.'));
        }

        // Verifikasi kode 2FA
        if (!$ga->checkCode($secret, $otp)) {
            return new WP_Error('2fa_failed', __('Invalid 2FA code. Please check and try again.'));
        }
    }

    return $user;
}, 30, 3);
