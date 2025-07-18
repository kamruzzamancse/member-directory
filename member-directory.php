<?php
/**
 * Plugin Name: Member Directory
 * Description: A custom plugin for managing members and teams, featuring a frontend directory and contact form.
 * Version: 1.0
 * Author: Md. Kamruzzaman
 * Text Domain: member-directory
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access to the plugin file
}

// Define plugin version and path
define('MDK_PLUGIN_VERSION', '1.0');
define('MDK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MDK_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include Loader class
require_once MDK_PLUGIN_DIR . 'includes/class-mdk-loader.php';

// Initialize
function mdk_init_plugin() {
    $loader = new MDK_Loader();
    $loader->run();
}
add_action('plugins_loaded', 'mdk_init_plugin');

register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

