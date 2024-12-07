<?php

add_action('init', function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_REFERER'])) {
        wp_die('Suspicious activity detected.');
    }
});
