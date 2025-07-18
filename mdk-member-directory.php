<?php
/**
 * Plugin Name: MDK Member Directory
 * Update URI: false
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


/**
 * Add Teams meta box to 'mdk_member' CPT
 */
function mdk_add_teams_metabox() {
    add_meta_box(
        'mdk_teams_metabox',
        __('Assign Teams', 'member-directory'),
        'mdk_render_teams_metabox',
        'mdk_member',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'mdk_add_teams_metabox');

/**
 * Render the teams checkbox list inside the meta box
 */
function mdk_render_teams_metabox($post) {
    // Security nonce
    wp_nonce_field('mdk_save_teams', 'mdk_teams_nonce');

    // Get saved teams (array of post IDs)
    $saved_teams = get_post_meta($post->ID, 'mdk_teams', true);
    if (!is_array($saved_teams)) {
        $saved_teams = [];
    }

    // Get all teams
    $teams = get_posts([
        'post_type' => 'mdk_team',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    if (empty($teams)) {
        echo '<p>' . __('No teams found.', 'member-directory') . '</p>';
        return;
    }

    echo '<ul style="list-style:none; padding-left:0; max-height:200px; overflow:auto;">';
    foreach ($teams as $team) {
        $checked = in_array($team->ID, $saved_teams) ? 'checked' : '';
        echo '<li><label>';
        echo '<input type="checkbox" name="mdk_teams[]" value="' . esc_attr($team->ID) . '" ' . $checked . '> ';
        echo esc_html($team->post_title);
        echo '</label></li>';
    }
    echo '</ul>';
}

/**
 * Save the selected teams when saving the member post
 */
function mdk_save_teams_meta($post_id) {
    // Verify nonce
    if (!isset($_POST['mdk_teams_nonce']) || !wp_verify_nonce($_POST['mdk_teams_nonce'], 'mdk_save_teams')) {
        return;
    }

    // Avoid autosave, revisions, or insufficient permissions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save teams array or delete meta if none
    if (isset($_POST['mdk_teams']) && is_array($_POST['mdk_teams'])) {
        $team_ids = array_map('intval', $_POST['mdk_teams']);
        update_post_meta($post_id, 'mdk_teams', $team_ids);
    } else {
        delete_post_meta($post_id, 'mdk_teams');
    }
}
add_action('save_post_mdk_member', 'mdk_save_teams_meta');
