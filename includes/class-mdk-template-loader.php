<?php
if (!defined('ABSPATH')) exit;

/**
 * Class MDK_Template_Loader
 * Handles custom template loading for member directory.
 */
class MDK_Template_Loader {

    public function init_hooks() {
        add_filter('template_include', [$this, 'mdk_load_member_template']);
    }

    public function mdk_load_member_template($template) {
        if (is_singular('mdk_member')) {
            $plugin_template = MDK_PLUGIN_DIR . 'templates/single-mdk_member.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
}
