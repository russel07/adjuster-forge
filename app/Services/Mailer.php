<?php

namespace SmartySoft\AdjusterForge\Services;

defined( 'ABSPATH' ) || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Foundation\AppHelper;

/**
 * Class Mailer
 *
 * This class is responsible for sending emails within the application.
 * It uses the AppHelper trait to leverage common application functionalities.
 *
 * @package SmartySoft\AdjusterForge\Services
 */
class Mailer {
    use AppHelper;

    /**
     * Sends a registration confirmation email to the user.
     *
     * This function takes an array of user data, sanitizes the input, and sends a registration
     * confirmation email to the user with their registration details.
     *
     * @param array $user_data Associative array containing user data. Expected keys:
     * - 'user_name' (string): Name of the user registering.
     * - 'user_email' (string): Email address of the user.
     * - 'user_password' (string): Password of the user.
     * - 'profile_link' (string): URL to the user's profile.
     *
     * @return void
     */
    public static function send_registration_confirmation_email( $user_data ) {
        // Sanitize incoming data
        $user_name    = sanitize_text_field( $user_data['user_name'] );
        $user_email   = sanitize_email( $user_data['user_email'] );
        $user_type    = sanitize_text_field( $user_data['user_type'] );
        $user_password = sanitize_text_field( $user_data['user_password'] );
        $profile_link = esc_url( $user_data['profile_link'] );

        // Start output buffering and include the email template
        ob_start();
        if( 'adjuster' === $user_type ) {
            // Set email subject
            $subject = 'Welcome to I Will Drive 4 U – Next Steps';
            include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/adjuster-registration.php' );
        } elseif( 'company' === $user_type ) {
            // Set email subject
            $subject = 'Welcome to I Will Drive 4 U – Your Account is Ready';
            include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/company-registration.php' );
        } 
        // Capture the output
        $message = ob_get_clean();

        

        // Get headers for the email
        $headers = self::get_email_headers();

        // Attempt to send the email
        wp_mail( $user_email, $subject, $message, $headers );
    }

    public static function send_user_profile_approved_email($data) {
        // Sanitize incoming data
        $user_name   = sanitize_text_field( $data['user_name'] );
        $user_email  = sanitize_email( $data['user_email'] );

        // Start output buffering and include the email template
        ob_start();
        include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/adjuster-approved.php' );
        $message = ob_get_clean();

        // Set email subject
        $subject = 'You\’re Approved – Your Profile is Now Live';

        // Get headers for the email
        $headers = self::get_email_headers();

        // Attempt to send the email
        wp_mail( $user_email, $subject, $message, $headers );
    }

    public static function send_user_profile_rejected_email($data) {
        // Sanitize incoming data
        $user_name   = sanitize_text_field( $data['user_name'] );
        $user_email  = sanitize_email( $data['user_email'] );

        // Start output buffering and include the email template
        ob_start();
        include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/adjuster-rejected.php' );
        $message = ob_get_clean();

        // Set email subject
        $subject = 'Update on Your Verification Status';

        // Get headers for the email
        $headers = self::get_email_headers();

        // Attempt to send the email
        wp_mail( $user_email, $subject, $message, $headers );
    }

    /**
     * Get the email headers.
     *
     * @return array The email headers.
     */
    private static function get_email_headers() {
        $admin_mail = get_option('admin_email');
        return array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: Admin <' . $admin_mail . '>',
            'From: Admin <' . $admin_mail . '>',
        );
    }
}