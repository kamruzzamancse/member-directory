<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MDK_CPT_Member
 * Registers the 'member' custom post type
 */
class MDK_CPT_Member {

    public function __construct() {
        add_action('init', [$this, 'mdk_register_member_cpt']);
    }

    /**
     * Register the Member custom post type
     */
    public function mdk_register_member_cpt() {
        $labels = [
            'name'               => __('Members', 'member-directory'),
            'singular_name'      => __('Member', 'member-directory'),
            'add_new'            => __('Add New Member', 'member-directory'),
            'add_new_item'       => __('Add New Member', 'member-directory'),
            'edit_item'          => __('Edit Member', 'member-directory'),
            'new_item'           => __('New Member', 'member-directory'),
            'view_item'          => __('View Member', 'member-directory'),
            'search_items'       => __('Search Members', 'member-directory'),
            'not_found'          => __('No members found', 'member-directory'),
            'not_found_in_trash' => __('No members found in Trash', 'member-directory'),
            'menu_name'          => __('Members', 'member-directory'),
        ];

        $args = [
            'label'               => __('Members', 'member-directory'),
            'labels'              => $labels,
            'public'              => true,
            'has_archive'         => true,
            'rewrite'             => [
                'slug' => 'member',
                'with_front' => false,
            ],
            'show_ui'             => true,
            'show_in_menu'        => true,
            'supports'            => ['title', 'thumbnail', 'editor'],
            'show_in_rest'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-id',
            'capability_type'     => 'post',
        ];

        register_post_type('mdk_member', $args);
    }
}
