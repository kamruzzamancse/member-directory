<?php
if (!defined('ABSPATH')) {
    exit;
}

class MDK_Frontend {

    public function init_hooks() {
        add_filter('post_type_link', [$this, 'mdk_custom_member_permalink'], 10, 2);
        add_action('init', [$this, 'mdk_add_member_rewrite_rule']);
        add_action('template_redirect', [$this, 'mdk_intercept_member_url']);
    }

    /**
     * Create custom permalink structure like /first-last
     */
    public function mdk_custom_member_permalink($permalink, $post) {
        if ($post->post_type === 'mdk_member') {
            $first = get_post_meta($post->ID, 'mdk_first_name', true);
            $last  = get_post_meta($post->ID, 'mdk_last_name', true);
            return home_url('/' . sanitize_title($first . '_' . $last));
        }
        return $permalink;
    }

    /**
     * Add a rewrite rule for /first-last
     */
    public function mdk_add_member_rewrite_rule() {
        add_rewrite_tag('%mdk_member_name%', '([^&]+)');
        add_rewrite_rule('^([^/]+_[^/]+)/?$', 'index.php?mdk_member_name=$matches[1]', 'top');
    }

    /**
     * Intercept and load custom single member template
     */
    public function mdk_intercept_member_url() {
        global $wp_query;

        if (get_query_var('mdk_member_name')) {
            $slug = sanitize_title(get_query_var('mdk_member_name'));

            // Reverse the slug into first + last
            $slug_parts = explode('_', $slug);
            if (count($slug_parts) !== 2) {
                $wp_query->set_404();
                return;
            }

            $args = [
                'post_type'  => 'mdk_member',
                'post_status'=> 'publish',
                'meta_query' => [
                    ['key' => 'mdk_status', 'value' => 'active'],
                    ['key' => 'mdk_first_name', 'value' => $slug_parts[0], 'compare' => 'LIKE'],
                    ['key' => 'mdk_last_name', 'value' => $slug_parts[1], 'compare' => 'LIKE'],
                ],
                'posts_per_page' => 1
            ];

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                $post = $query->posts[0];
                setup_postdata($post);
                include MDK_PLUGIN_DIR . 'templates/single-member.php';
                exit;
            } else {
                $wp_query->set_404();
            }
        }
    }
}
