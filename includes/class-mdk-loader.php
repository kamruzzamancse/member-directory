<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main Loader Class
 * Loads all dependencies and registers plugin hooks.
 */
class MDK_Loader {

    /**
     * Run the plugin by loading all hooks and features.
     */
    public function run() {
        $this->mdk_load_dependencies();
        $this->mdk_register_cpt_classes();
        $this->mdk_define_admin_hooks();
        $this->mdk_define_public_hooks();
        $this->mdk_define_template_hooks();
    }

    /**
     * Load all required class files.
     */
    private function mdk_load_dependencies() {
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-cpt-member.php';
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-cpt-team.php';
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-contact-form.php';
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-admin.php';
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-frontend.php';
        require_once MDK_PLUGIN_DIR . 'includes/class-mdk-template-loader.php';
    }

    /**
     * Register Custom Post Types (CPTs).
     */
    private function mdk_register_cpt_classes() {
        new MDK_CPT_Member();
        new MDK_CPT_Team();
    }

    /**
     * Define hooks related to admin-side functionality.
     */
    private function mdk_define_admin_hooks() {
        $admin = new MDK_Admin();
        $admin->init_hooks();
    }

    /**
     * Define hooks related to frontend/public functionality.
     */
    private function mdk_define_public_hooks() {
        $frontend = new MDK_Frontend();
        $frontend->init_hooks();

        $contact = new MDK_Contact_Form();
        $contact->init_hooks();
    }

    /**
     * Load custom template files for CPTs.
     */
    private function mdk_define_template_hooks() {
        $template_loader = new MDK_Template_Loader();
        $template_loader->init_hooks();
    }
}
