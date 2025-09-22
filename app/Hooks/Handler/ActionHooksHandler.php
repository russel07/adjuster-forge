<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;
defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Foundation\AppHelper;
use SmartySoft\AdjusterForge\Services\Mailer;
/**
 * ActionHooksHandler class define all action hooks methods
 */
class ActionHooksHandler
{
    use AppHelper;


    /**
     * Add custom rewrite rules for jobs.
     *
     * This function adds rewrite rules for custom query variables to handle URLs
     * like jobs/brand/samsung and jobs/brand/samsung/new-offers.
     */
    public static function driver_forge_rewrite_rules() {
        add_rewrite_rule(
            '^download/([^/]*)/?',
            'index.php?driver_forge_download_file=$matches[1]',
            'top'
        );
    }

    public static function driver_forge_file_download_handler() {
        if ( get_query_var( 'driver_forge_download_file' ) ) {
            $file = sanitize_text_field( get_query_var( 'driver_forge_download_file' ) );

            if( empty( $file ) ) {
                wp_die( 'Invalid file name.' );
            }

            $file_name =  sanitize_file_name( $file );
            $file_path = WP_CONTENT_DIR . '/uploads/' . DRIVER_FORGE_PLUGIN_ASSET_ID . '/' . $file_name;
          
            if ( $file_path && file_exists( $file_path ) ) {
                $mime_type = mime_content_type( $file_path );
                // Set headers to initiate file download
                header( 'Content-Description: File Transfer' );
                header( 'Content-Type: ' . $mime_type );
                header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
                header( 'Expires: 0' );
                header( 'Cache-Control: must-revalidate' );
                header( 'Pragma: public' );
                header( 'Content-Length: ' . filesize( $file_path ) );
                flush(); // Flush system output buffer
                readfile( $file_path );
                exit;
            } else {
                wp_die( 'File not found.' );
            }
        }
    }

    /**
     * Send user registration confirmation email.
     *
     * This function sends a registration confirmation email to the user after they complete their profile.
     *
     * @param object $data The data object containing user information.
     */
    public static function send_user_registration_confirmation_email( $data ) {

        Mailer::send_registration_confirmation_email([
            'user_name'       => $data->user_name,
            'user_email'      => $data->user_email,
            'user_type'       => $data->user_type,
            'user_password'   => $data->user_password,
            'profile_link'    => esc_url( $data->profile_link ),
        ]);
    }

    /**
     * Send user verification fee paid email.
     *
     * This function sends a verification fee paid confirmation email to the user.
     *
     * @param object $data The data object containing user information.
     */
    public static function send_user_verification_fee_paid_email( $data ) {
        Mailer::send_verification_fee_confirmation_email([
            'user_name'       => $data->user_name,
            'user_email'      => $data->user_email,
            'profile_link'    => esc_url( $data->profile_link ),
        ]);
    }
    /**
     * Send user verification fee paid notification to admin.
     *
     * This function sends a notification to the admin when a user pays the verification fee.
     *
     * @param object $data The data object containing user information.
     */
    public static function send_user_verification_fee_paid_notification_to_admin ($data){
        Mailer::send_user_verification_fee_paid_notification_to_admin([
            'user_name'       => $data->user_name,
            'user_email'      => $data->user_email,
            'payment_id'      => $data->payment_id,
            'order_date'      => $data->order_date
        ]);
    }

    public static function send_user_profile_approved_email($data) {
        Mailer::send_user_profile_approved_email([
            'user_name'       => $data->user_name,
            'user_email'      => $data->user_email,
            'profile_link'    => esc_url( $data->profile_link ),
        ]);
    }

    public static function send_user_profile_rejected_email($data) {
        Mailer::send_user_profile_rejected_email([
            'user_name'       => $data->user_name,
            'user_email'      => $data->user_email,
            'profile_link'    => esc_url( $data->profile_link ),
        ]);
    }
    
    public static function af_redirect_user_to_dashboard() {
        if (!is_user_logged_in()) return;

        // Prevent redirect for AJAX, CRON, REST
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('DOING_CRON') && DOING_CRON) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $user = wp_get_current_user();
        $roles = $user->roles;

        $profile_page = self::getProfilePageUrl();

        if (in_array('driver', $roles) || in_array('company', $roles)) {
            wp_redirect($profile_page);
            exit;
        }
    }
    /**
     * Redirect user to the dashboard after login.
     *
     * This function redirects the user to their profile page after they log in.
     *
     * @param string $redirect_to The URL to redirect to.
     * @param string $requested_redirect_to The requested redirect URL.
     * @param WP_User $user The user object.
     * @return string The URL to redirect to.
     */
    public static function af_redirect_user_to_dashboard_login($redirect_to, $requested_redirect_to, $user) {
        if ( ! ( $user instanceof WP_User) ) {
            return $redirect_to;
        }

        // Redirect to profile page if user has 'driver' or 'company' role
        if ( in_array('driver', $user->roles ) || in_array('company', $user->roles ) ) {
            return self::getProfilePageUrl();
        }

        return $redirect_to;
    }
    /**
     * Redirect user to the login page.
     *
     * This function redirects the user to the login page if they are not logged in.
     * It prevents redirection for AJAX, CRON, and REST requests.
     */
    public static function af_redirect_user_to_login_page() {
        // Prevent redirect for AJAX, CRON, REST
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('DOING_CRON') && DOING_CRON) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        if ( !isset($_GET['action']) || $_GET['action'] !== 'logout' ) {
            $profile_page = self::getProfilePageUrl();
            wp_redirect($profile_page);
            exit;
        }
    }
    /**
     * Hide admin bar for custom roles.
     *
     * This function hides the admin bar for users with custom roles like 'driver' and 'company'.
     */
    public static function af_hide_admin_bar_for_custom_roles() {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $roles = $user->roles;

            // Define custom roles that should not see the admin bar
            $custom_roles = ['driver', 'company'];

            // Check if the user has any of the custom roles
            foreach ($roles as $role) {
                if (in_array($role, $custom_roles)) {
                    show_admin_bar(false);
                    return;
                }
            }
        }
    }

}
