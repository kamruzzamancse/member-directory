<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class MDK_Admin
 * Handles admin functionality like meta boxes, saving, and UI enhancements.
 */
class MDK_Admin {

    public function init_hooks() {
        add_action('add_meta_boxes', [$this, 'mdk_register_member_meta_box']);
        add_action('add_meta_boxes', [$this, 'mdk_register_submission_metabox']);
        add_action('save_post_mdk_member', [$this, 'mdk_save_member_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'mdk_enqueue_color_picker']);
        add_action('admin_notices', [$this, 'mdk_show_admin_notices']);
        add_filter('manage_mdk_member_posts_columns', [$this, 'mdk_add_submissions_column']);
        add_action('manage_mdk_member_posts_custom_column', [$this, 'mdk_render_submissions_column'], 10, 2);
    }

    public function mdk_register_member_meta_box() {
        add_meta_box(
            'mdk_member_meta',
            __('Member Details', 'member-directory'),
            [$this, 'mdk_render_member_meta_box'],
            'mdk_member',
            'normal',
            'default'
        );
    }

    public function mdk_render_member_meta_box($post) {
        wp_nonce_field('mdk_save_member_meta', 'mdk_member_nonce');

        $fields = [
            'first_name'     => __('First Name', 'member-directory'),
            'last_name'      => __('Last Name', 'member-directory'),
            'email'          => __('Email', 'member-directory'),
            'address'        => __('Address', 'member-directory'),
            'favorite_color' => __('Favorite Color', 'member-directory'),
            'status'         => __('Status', 'member-directory'),
        ];

        $meta = [];
        foreach ($fields as $key => $label) {
            $meta[$key] = get_post_meta($post->ID, 'mdk_' . $key, true);
        }
        ?>
        <p>
            <label><?php echo esc_html($fields['first_name']); ?></label><br>
            <input type="text" name="mdk_first_name" class="widefat" value="<?php echo esc_attr($meta['first_name']); ?>">
        </p>
        <p>
            <label><?php echo esc_html($fields['last_name']); ?></label><br>
            <input type="text" name="mdk_last_name" class="widefat" value="<?php echo esc_attr($meta['last_name']); ?>">
        </p>
        <p>
            <label><?php echo esc_html($fields['email']); ?></label><br>
            <input type="email" name="mdk_email" class="widefat" value="<?php echo esc_attr($meta['email']); ?>">
        </p>
        <p>
            <label><?php echo esc_html($fields['address']); ?></label><br>
            <textarea name="mdk_address" class="widefat"><?php echo esc_textarea($meta['address']); ?></textarea>
        </p>
        <p>
            <label><?php echo esc_html($fields['favorite_color']); ?></label><br>
            <input type="text" name="mdk_favorite_color" class="widefat mdk-color-field" value="<?php echo esc_attr($meta['favorite_color']); ?>">
        </p>
        <p>
            <label><?php echo esc_html($fields['status']); ?></label><br>
            <select name="mdk_status" class="widefat">
                <option value="active" <?php selected($meta['status'], 'active'); ?>>Active</option>
                <option value="draft" <?php selected($meta['status'], 'draft'); ?>>Draft</option>
            </select>
        </p>
        <?php
        // Teams relationship meta box
        $selected_teams = get_post_meta($post->ID, 'mdk_teams', true);
        $selected_teams = is_array($selected_teams) ? $selected_teams : [];

        $teams = get_posts([
            'post_type'   => 'mdk_team',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby'     => 'title',
            'order'       => 'ASC',
        ]);
        ?>
        <p>
            <label><?php _e('Teams', 'member-directory'); ?></label><br>
            <select name="mdk_teams[]" multiple class="widefat" size="5">
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo esc_attr($team->ID); ?>" <?php selected(in_array($team->ID, $selected_teams)); ?>>
                        <?php echo esc_html($team->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function mdk_save_member_meta($post_id, $post) {
        if (!isset($_POST['mdk_member_nonce']) || !wp_verify_nonce($_POST['mdk_member_nonce'], 'mdk_save_member_meta')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) return;
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

        $email = sanitize_email($_POST['mdk_email'] ?? '');
        if ($email) {
            $duplicate = get_posts([
                'post_type'  => 'mdk_member',
                'meta_key'   => 'mdk_email',
                'meta_value' => $email,
                'exclude'    => [$post_id],
                'fields'     => 'ids',
            ]);

            if (!empty($duplicate)) {
                add_filter('redirect_post_location', function ($location) {
                    return add_query_arg('mdk_email_duplicate', '1', $location);
                });
                return;
            }
        }

        $fields = ['first_name', 'last_name', 'email', 'address', 'favorite_color', 'status'];
        foreach ($fields as $field) {
            update_post_meta($post_id, 'mdk_' . $field, sanitize_text_field($_POST['mdk_' . $field] ?? ''));
        }

        $teams = isset($_POST['mdk_teams']) ? array_map('intval', $_POST['mdk_teams']) : [];
        update_post_meta($post_id, 'mdk_teams', $teams);
    }

    public function mdk_enqueue_color_picker($hook) {
        if (in_array($hook, ['post-new.php', 'post.php']) && get_post_type() === 'mdk_member') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('mdk-color-picker', MDK_PLUGIN_URL . 'assets/js/mdk-color-picker.js', ['wp-color-picker'], MDK_PLUGIN_VERSION, true);
        }
    }

    public function mdk_show_admin_notices() {
        if (!empty($_GET['mdk_email_duplicate'])) {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> Email already in use by another member.</p></div>';
        }
    }

    public function mdk_add_submissions_column($columns) {
        $columns['mdk_submissions'] = __('Messages Received', 'member-directory');
        return $columns;
    }

    public function mdk_render_submissions_column($column, $post_id) {
        if ($column === 'mdk_submissions') {
            echo intval($this->mdk_get_submission_count($post_id));
        }
    }

    private function mdk_get_submission_count($member_id) {
        $query = new WP_Query([
            'post_type'   => 'mdk_submission',
            'meta_key'    => 'mdk_member_id',
            'meta_value'  => $member_id,
            'post_status' => 'publish',
            'fields'      => 'ids',
        ]);
        return $query->found_posts;
    }

    public function mdk_register_submission_metabox() {
        add_meta_box(
            'mdk_submissions_box',
            __('Contact Messages', 'member-directory'),
            [$this, 'mdk_render_submission_metabox'],
            'mdk_member',
            'normal',
            'default'
        );
    }

    public function mdk_render_submission_metabox($post) {
        $messages = get_posts([
            'post_type'   => 'mdk_submission',
            'meta_key'    => 'mdk_member_id',
            'meta_value'  => $post->ID,
            'post_status' => 'publish',
            'orderby'    => 'date',
            'order'      => 'DESC',
        ]);

        if (empty($messages)) {
            echo '<p>' . __('No messages yet.', 'member-directory') . '</p>';
            return;
        }

        echo '<ul>';
        foreach ($messages as $msg) {
            $name  = get_post_meta($msg->ID, 'mdk_full_name', true);
            $email = get_post_meta($msg->ID, 'mdk_email', true);
            $body  = get_the_content(null, false, $msg);
            echo '<li><strong>' . esc_html($name) . '</strong> (' . esc_html($email) . ')<br>';
            echo '<em>' . esc_html($body) . '</em></li><br>';
        }
        echo '</ul>';
    }
}
