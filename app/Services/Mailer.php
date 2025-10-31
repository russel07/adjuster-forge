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
            $subject = 'Your Adjusters On Demand Profile Is Under Review';
            include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/adjuster-registration.php' );
        } elseif( 'company' === $user_type ) {
            // Set email subject
            $subject = 'Welcome to Adjusters On Demand — Let’s Get You Connected';
            include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/company-registration.php' );
        } 
        // Capture the output
        $message = ob_get_clean();

        

        // Get headers for the email
        $headers = self::get_email_headers();

        // Attempt to send the email
        wp_mail( $user_email, $subject, $message, $headers );
    }

    public static function send_verification_fee_confirmation_email( $data ) {
        // Sanitize incoming data
        $user_name   = sanitize_text_field( $data['user_name'] );
        $user_email  = sanitize_email( $data['user_email'] );
        $profile_link = esc_url( $data['profile_link'] );

        // Start output buffering and include the email template
        ob_start();
        include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/verification-payment-confirmation.php' );
        $message = ob_get_clean();

        // Set email subject
        $subject = 'Your Verification is Underway';

        // Get headers for the email
        $headers = self::get_email_headers();

        // Attempt to send the email
        wp_mail( $user_email, $subject, $message, $headers );
    }

    /**
     * Sends an invoice notification email to the admin after a successful purchase.
     *
     * This function takes an array of invoice data, sanitizes the input, and sends an email to the admin
     * with the details of the purchase, including the user's name, email, payment ID, item purchased,
     * payment method, and order date.
     *
     * @param array $invoice_data Associative array containing invoice data. Expected keys:
     * - 'user_name' (string): Name of the user making the purchase.
     * - 'user_email' (string): Email address of the user.
     * - 'payment_id' (string): Payment ID for the transaction.
     * - 'order_date' (string): Date the order was placed.
     *
     * @return void
     */
    public static function send_user_verification_fee_paid_notification_to_admin( $invoice_data ) {
        // Sanitize incoming data to ensure safe use
        $user_name      = sanitize_text_field( $invoice_data['user_name'] );
        $user_email     = sanitize_email( $invoice_data['user_email'] );
        $payment_id     = sanitize_text_field( $invoice_data['payment_id'] );
        $order_date     = sanitize_text_field( $invoice_data['order_date'] );

        // Start output buffering and include the email template
        ob_start();
        include_once( ADJUSTER_FORGE_PLUGIN_DIR . '/app/Mail/admin-notification.php' );
        $message = ob_get_clean();

        // Set email subject and recipient
        $subject    = 'New Driver Verification Fee Payment Received';
        $admin_mail = get_option('admin_email');

        // Get the headers for the email (e.g., from, content-type)
        $headers = self::get_email_headers();

        // Attempt to send the email to the admin
        wp_mail( $admin_mail, $subject, $message, $headers );
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
        $subject = ' Congratulations — You’re Officially a Verified Adjuster!';

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
        $subject = 'Update on Your Adjusters On Demand Application';

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