<?php

require_once plugin_dir_path(__FILE__) . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// Tambahkan menu pengaturan plugin di admin
add_action('admin_menu', function () {
    add_menu_page(
        'Satpam WP',
        'Satpam WP',
        'manage_options',
        '2fa-settings',
        'satpam_wp_settings_page',
        'dashicons-shield-alt',
        90
    );

    add_submenu_page('2fa-settings', '2FA Settings', '2FA Settings', 'manage_options', '2fa-settings', 'satpam_wp_settings_page');
    add_submenu_page('2fa-settings', 'Monitor Suspicious IPs', 'Monitor Suspicious IPs', 'manage_options', 'monitor-ip', 'display_monitor_ip_page');
    add_submenu_page('2fa-settings', 'reCAPTCHA Settings', 'reCAPTCHA Settings', 'manage_options', 'recaptcha-settings', 'anti_ddos_settings_page');
});

// Fungsi untuk menghasilkan QR Code
function generate_qr_code($user_id, $secret) {
    $site_name = urlencode(get_bloginfo('name'));
    $user_email = urlencode(wp_get_current_user()->user_email);
    $issuer = $site_name;
    $uri = "otpauth://totp/{$site_name}:{$user_email}?secret={$secret}&issuer={$issuer}";

    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/2fa-qr-codes';
    if (!file_exists($temp_dir) && !wp_mkdir_p($temp_dir)) {
        wp_die('Failed to create directory for 2FA QR codes. Check permissions.');
    }

    $file_path = "{$temp_dir}/2fa_{$user_id}.png";

    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($uri)
        ->build();

    $result->saveToFile($file_path);

    return "{$upload_dir['baseurl']}/2fa-qr-codes/2fa_{$user_id}.png";
}

// Fungsi untuk halaman pengaturan 2FA
function satpam_wp_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $current_user = wp_get_current_user();
    $secret = get_user_meta($current_user->ID, 'google_authenticator_secret', true);
    $is_2fa_verified = get_user_meta($current_user->ID, '2fa_verified', false);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['disable_2fa'])) {
            delete_user_meta($current_user->ID, 'google_authenticator_secret');
            delete_user_meta($current_user->ID, '2fa_verified');
            $is_2fa_verified = false;
            $secret = ''; // Reset secret to regenerate later
            echo '<div class="updated"><p>Two-Factor Authentication has been disabled.</p></div>';
        } elseif (isset($_POST['generate_qr'])) {
            $ga = new GoogleAuthenticator();
            $secret = $ga->generateSecret();
            update_user_meta($current_user->ID, 'google_authenticator_secret', $secret);
            echo '<div class="updated"><p>QR Code generated. Scan it to activate 2FA.</p></div>';
        } elseif (isset($_POST['activate_2fa'])) {
            $otp_code = sanitize_text_field($_POST['otp_code'] ?? '');
            $ga = new GoogleAuthenticator();
            if (!empty($otp_code) && $ga->checkCode($secret, $otp_code)) {
                update_user_meta($current_user->ID, '2fa_verified', true);
                $is_2fa_verified = true;
                echo '<div class="updated"><p>2FA successfully activated!</p></div>';
            } else {
                echo '<div class="error"><p>Invalid OTP Code. Please try again.</p></div>';
            }
        }
    }

    ?>
    <div class="wrap">
        <h1>Two-Factor Authentication (2FA) Settings</h1>
        <form method="post">
            <?php if ($is_2fa_verified): ?>
                <!-- Jika 2FA aktif -->
                <p>Two-Factor Authentication is currently <strong>enabled</strong>.</p>
                <p>To disable 2FA, click the button below:</p>
                <button type="submit" name="disable_2fa" class="button button-secondary">Disable 2FA</button>
            <?php else: ?>
                <!-- Jika 2FA nonaktif -->
                <p>Two-Factor Authentication is currently <strong>disabled</strong>.</p>
                <?php if (empty($secret)): ?>
                    <!-- Generate QR jika secret belum ada -->
                    <button type="submit" name="generate_qr" class="button button-primary">Generate QR Code</button>
                <?php else: ?>
                    <p>Scan this QR Code with your authenticator app:</p>
                    <img src="<?php echo esc_url(generate_qr_code($current_user->ID, $secret)); ?>" alt="QR Code">
                    <p>Or use this secret key:</p>
                    <code><?php echo esc_html($secret); ?></code>
                    <p>Enter OTP Code to activate:</p>
                    <input type="text" name="otp_code" class="regular-text" required>
                    <br><br>
                    <input type="submit" name="activate_2fa" value="Activate 2FA" class="button button-primary">
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
    <?php
}


// Fungsi untuk halaman pengaturan reCAPTCHA
function anti_ddos_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['recaptcha_site_key']) && isset($_POST['recaptcha_secret_key'])) {
            update_option('recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
            update_option('recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }
    }

    $recaptcha_site_key = get_option('recaptcha_site_key');
    $recaptcha_secret_key = get_option('recaptcha_secret_key');
    ?>
    <div class="wrap">
        <h1>reCAPTCHA Settings</h1>
        <form method="post" class="recaptcha-settings-form">
            <h2>Google reCAPTCHA Settings</h2>
            <div class="form-field">
                <label for="recaptcha_site_key">Site Key:</label>
                <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr($recaptcha_site_key); ?>" class="regular-text">
            </div>
            <div class="form-field">
                <label for="recaptcha_secret_key">Secret Key:</label>
                <input type="text" name="recaptcha_secret_key" value="<?php echo esc_attr($recaptcha_secret_key); ?>" class="regular-text">
            </div>
            <input type="submit" value="Save Settings" class="button button-primary">
        </form>
    </div>
	<style>
        /* Membatasi lebar input field */
        .recaptcha-settings-form .form-field {
            margin-bottom: 15px;
        }

        .recaptcha-settings-form .form-field label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .recaptcha-settings-form .form-field input {
            width: 50%;  /* Mengatur lebar input menjadi 50% */
            max-width: 400px; /* Lebar maksimal 400px */
            padding: 8px;
            font-size: 14px;
        }
    </style>
    <?php
}

// Log IP termasuk negara menggunakan API geolokasi
add_action('wp_login_failed', 'log_failed_login_ip');
function log_failed_login_ip($username) {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $time = current_time('mysql');
    $country = get_country_from_ip($user_ip);

    $ip_access_log = get_option('ip_access_log', []);
    $ip_access_log[] = [
        'ip' => $user_ip,
        'time' => $time,
        'country' => $country,
    ];

    update_option('ip_access_log', $ip_access_log);
}

// Fungsi untuk mendapatkan negara dari IP menggunakan API ip-api
function get_country_from_ip($ip) {
    $response = wp_remote_get("http://ip-api.com/json/{$ip}");
    if (is_wp_error($response)) {
        return 'Unknown';
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    return isset($data['country']) ? $data['country'] : 'Unknown';
}


function display_monitor_ip_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Ambil daftar IP yang diblokir dari opsi
    $blocked_ips = get_option('blocked_ips', []);
    $access_log = get_option('ip_access_log', []);

    // Proses blokir dan unblock IP
    if (isset($_GET['block_ip'])) {
        $ip_to_block = sanitize_text_field($_GET['block_ip']);
        if (!in_array($ip_to_block, $blocked_ips)) {
            $blocked_ips[] = $ip_to_block;
            update_option('blocked_ips', $blocked_ips);
        }
    }

    if (isset($_GET['unblock_ip'])) {
        $ip_to_unblock = sanitize_text_field($_GET['unblock_ip']);
        $blocked_ips = array_diff($blocked_ips, [$ip_to_unblock]);
        update_option('blocked_ips', $blocked_ips);
    }

    // Proses form input manual untuk menambah IP ke blokir
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_ip_manual'])) {
        $manual_ip = sanitize_text_field($_POST['manual_ip']);
        if (!empty($manual_ip) && !in_array($manual_ip, $blocked_ips)) {
            $blocked_ips[] = $manual_ip;
            update_option('blocked_ips', $blocked_ips);
        }
    }

    ?>
    <div class="wrap">
        <h1>Monitor Suspicious IPs</h1>

        <!-- Kolom Input Manual IP -->
        <h2>Add IP to Block</h2>
        <form method="post">
            <input type="text" name="manual_ip" class="regular-text" placeholder="Enter IP to block" required>
            <input type="submit" name="block_ip_manual" class="button button-primary" value="Block IP">
        </form>

        <br>

        <!-- Daftar IP yang mengakses website -->
        <h3>IP Access Log</h3>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>Access Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($access_log as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['ip']); ?></td>
                        <td><?php echo esc_html($log['country']); ?></td>
                        <td><?php echo esc_html($log['time']); ?></td>
                        <td>
                            <?php if (in_array($log['ip'], $blocked_ips)): ?>
                                <span class="button button-secondary" style="pointer-events: none;">Blocked</span>
                            <?php else: ?>
                                <a href="?page=monitor-ip&block_ip=<?php echo esc_attr($log['ip']); ?>" class="button button-primary">Block</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>

        <!-- Daftar IP yang sudah diblokir -->
        <h3>Blocked IPs</h3>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blocked_ips as $blocked_ip): ?>
                    <tr>
                        <td><?php echo esc_html($blocked_ip); ?></td>
                        <td>
                            <a href="?page=monitor-ip&unblock_ip=<?php echo esc_attr($blocked_ip); ?>" class="button button-secondary">Unblock</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>