<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MDK_Contact_Form
 * Handles contact form submission, emailing, and storage.
 */
class MDK_Contact_Form {

    public function init_hooks() {
        add_action('init', [$this, 'mdk_register_submission_cpt']);
        add_action('template_redirect', [$this, 'mdk_handle_contact_form']);
    }

    /**
     * Register a CPT to store contact submissions.
     */
    public function mdk_register_submission_cpt() {
        register_post_type('mdk_submission', [
            'labels' => [
                'name' => __('Submissions', 'member-directory'),
                'singular_name' => __('Submission', 'member-directory')
            ],
            'public' => false,
            'show_ui' => true, // Show admin UI to view submissions
            'supports' => ['title', 'editor'],
        ]);
    }

    /**
     * Handle form submission on frontend
     */
    public function mdk_handle_contact_form() {
        if (
            !isset($_POST['mdk_submit_contact']) ||
            !isset($_POST['mdk_contact_nonce']) ||
            !wp_verify_nonce($_POST['mdk_contact_nonce'], 'mdk_contact_form')
        ) {
            return;
        }

        $name       = isset($_POST['mdk_full_name']) ? sanitize_text_field($_POST['mdk_full_name']) : '';
        $email      = isset($_POST['mdk_email']) ? sanitize_email($_POST['mdk_email']) : '';
        $message    = isset($_POST['mdk_message']) ? sanitize_textarea_field($_POST['mdk_message']) : '';
        $member_id  = isset($_POST['mdk_member_id']) ? intval($_POST['mdk_member_id']) : 0;

        if (empty($name) || empty($email) || empty($message) || !$member_id) {
            return;
        }

        // Get member email
        $member_email = get_post_meta($member_id, 'mdk_email', true);
        if (!is_email($member_email)) return;

        // Send email
        $subject = 'New message from ' . $name;
        $body = "Name: $name\nEmail: $email\n\n$message";
        $headers = ['Reply-To: ' . $email];

        wp_mail($member_email, $subject, $body, $headers);

        // Save to DB
        wp_insert_post([
            'post_type'    => 'mdk_submission',
            'post_title'   => 'Message from ' . $name,
            'post_content' => $message,
            'post_status'  => 'private',
            'meta_input'   => [
                'mdk_sender_name' => $name,
                'mdk_sender_email' => $email,
                'mdk_member_id' => $member_id,
            ],
        ]);

        // Redirect with success
        wp_redirect(add_query_arg('mdk_sent', '1', get_permalink($member_id)));
        exit;
    }
}
