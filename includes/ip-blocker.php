<?php

add_action('init', function () {
    $blocked_ips = get_option('blocked_ips', []);
    $ip = $_SERVER['REMOTE_ADDR'];

    if (in_array($ip, $blocked_ips)) {
        wp_die('Your IP has been blocked due to suspicious activity.');
    }
});
