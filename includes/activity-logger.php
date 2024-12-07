<?php

function log_suspicious_activity($message) {
    $file = SATPAM_WP_PLUGIN_DIR . 'logs/suspicious_activity.log';

    if ( ! file_exists(dirname($file)) ) {
        mkdir(dirname($file), 0755, true);
    }

    $entry = date('Y-m-d H:i:s') . " - {$message}\n";

    $log_file = fopen($file, 'ab'); 
    fwrite($log_file, $entry);
    fclose($log_file); 
}

add_filter('authenticate', function ($user, $username, $password) {
    if (is_wp_error($user)) {
        log_suspicious_activity("Failed login attempt for username: {$username} from IP: {$_SERVER['REMOTE_ADDR']}");
    }
    return $user;
}, 30, 3);
