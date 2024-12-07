<?php

add_filter('authenticate', function ($user, $username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $attempts = get_transient("login_attempts_{$ip}") ?: 0;

    if ($attempts >= 5) {
        return new WP_Error('too_many_attempts', __('Too many login attempts. Please try again later.'));
    }

    if (is_wp_error($user)) {
        set_transient("login_attempts_{$ip}", $attempts + 1, 30 * MINUTE_IN_SECONDS);
        return $user;
    }

    delete_transient("login_attempts_{$ip}");
    return $user;
}, 30, 3);
