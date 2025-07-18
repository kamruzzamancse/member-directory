<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class MDK_CPT_Team
 * Registers the 'mdk_team' custom post type.
 */
class MDK_CPT_Team {

    public function __construct() {
        add_action('init', [$this, 'mdk_register_team_cpt']);
    }

    /**
     * Register the Team custom post type
     */
    public function mdk_register_team_cpt() {
        $labels = [
            'name'                  => __('Teams', 'member-directory'),
            'singular_name'         => __('Team', 'member-directory'),
            'add_new'               => __('Add New Team', 'member-directory'),
            'add_new_item'          => __('Add New Team', 'member-directory'),
            'edit_item'             => __('Edit Team', 'member-directory'),
            'new_item'              => __('New Team', 'member-directory'),
            'view_item'             => __('View Team', 'member-directory'),
            'search_items'          => __('Search Teams', 'member-directory'),
            'not_found'             => __('No teams found', 'member-directory'),
            'not_found_in_trash'    => __('No teams found in Trash', 'member-directory'),
            'menu_name'             => __('Teams', 'member-directory'),
        ];

        $args = [
            'label'                 => __('Teams', 'member-directory'),
            'labels'                => $labels,
            'public'                => true,
            'has_archive'           => true,
            'rewrite'               => [
                'slug'       => 'teams',
                'with_front' => false
            ],
            'show_ui'               => true,
            'show_in_menu'          => true,
            'supports'              => ['title', 'editor', 'thumbnail'],
            'show_in_rest'          => true,
            'menu_position'         => 26,
            'menu_icon'             => 'dashicons-groups',
            'capability_type'       => 'post',
        ];

        register_post_type('mdk_team', $args);
    }
}
