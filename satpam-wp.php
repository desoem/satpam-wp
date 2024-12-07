<?php
/**
Plugin Name: Satpam WP
Plugin URI: https://github.com/desoem/satpam-wp
Description: Plugin untuk melindungi situs WordPress dari serangan DDOS, brute force, dan spam.
Version: 1.0
Author: Ridwan Sumantri
Author URI: https://github.com/desoem
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('SATPAM_WP_VERSION', '2.0');
define('SATPAM_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SATPAM_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once SATPAM_WP_PLUGIN_DIR . 'includes/admin-menu.php'; // Admin menu and settings
require_once SATPAM_WP_PLUGIN_DIR . 'includes/login-limiter.php'; // Login limit
require_once SATPAM_WP_PLUGIN_DIR . 'includes/recaptcha.php'; // reCAPTCHA integration
require_once SATPAM_WP_PLUGIN_DIR . 'includes/two-factor-auth.php'; // Two-Factor Authentication
require_once SATPAM_WP_PLUGIN_DIR . 'includes/ip-blocker.php'; // IP blocking
require_once SATPAM_WP_PLUGIN_DIR . 'includes/firewall.php'; // Firewall rules
require_once SATPAM_WP_PLUGIN_DIR . 'includes/activity-logger.php'; // Logging activity

// Activation hook
register_activation_hook(__FILE__, 'SATPAM_WP_activate');
function satpam_wp_activate() {
    if (!get_option('blocked_ips')) {
        update_option('blocked_ips', []);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'SATPAM_WP_deactivate');
function satpam_wp_deactivate() {
    delete_option('blocked_ips');
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('satpam-wp-style', SATPAM_WP_PLUGIN_URL . 'assets/css/style.css', [], SATPAM_WP_VERSION);
    wp_enqueue_script('satpam-wp-script', SATPAM_WP_PLUGIN_URL . 'assets/js/script.js', ['jquery'], SATPAM_WP_VERSION, true);
});
