<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

class AuthController extends BaseController
{ 
    /**
     * Handles user login.
     *
     * This method retrieves and sanitizes the email, password, and remember_me inputs from the request.
     * It then authenticates the user using WordPress's wp_authenticate function. If authentication is
     * successful, the user is logged in and a success response is returned. If authentication fails,
     * an error response is returned.
     *
     * @return \WP_REST_Response The response object containing the login status and user data.
     */
    public function login() {
        // Check if the user is already logged in
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            return $this->response([
                'message' => __( 'User is already logged in.', 'adjuster-forge' ),
                'data'    => [
                    'id'       => $current_user->ID,
                    'username' => $current_user->user_login,
                    'email'    => $current_user->user_email,
                    'name'     => $current_user->display_name,
                ],
                'status'  => 'success',
            ], 200);
        }

        // Retrieve and sanitize inputs
        $email = sanitize_email( $this->request->get( 'email' ) );
        $password = sanitize_text_field( $this->request->get( 'password' ) );
        $remember_me = sanitize_text_field( $this->request->get( 'remember_me' ) );

        // Check if required fields are provided
        if ( empty( $email ) || empty( $password ) ) {
            return $this->response([
                'message' => __( 'Email and password are required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }

        // Authenticate the user
        $user = wp_authenticate( $email, $password );

        // Handle authentication errors
        if ( is_wp_error( $user ) ) {
            $message = $user->get_error_message();
            return $this->response([
                'message' => $message,
                'status'  => 'error',
            ], 200);
        }

        // Log the user in
        wp_set_current_user( $user->ID, $user->user_login );
        wp_set_auth_cookie( $user->ID, $remember_me === 'true' );

        // Prepare success response
        return $this->response([
            'message' => __( 'Login successful.', 'adjuster-forge' ),
            'data'    => [
                'id'       => $user->ID,
                'username' => $user->user_login,
                'email'    => $user->user_email,
                'name'     => $user->display_name,
            ],
            'status'  => 'success',
        ], 200);
    }

    /**
     * Handles user Registration.
     *
     **/
    public function register() {
        // Retrieve and sanitize inputs
        $email          = sanitize_email( $this->request->get( 'email' ) );
        $password       = sanitize_text_field( $this->request->get( 'password' ) );
        $first_name     = sanitize_text_field( $this->request->get( 'first_name' ) );
        $last_name      = sanitize_text_field( $this->request->get( 'last_name' ) );
        $user_type      = sanitize_text_field( $this->request->get( 'user_type' ) );
        $city           = sanitize_text_field( $this->request->get( 'city' ) );
        $state          = sanitize_text_field( $this->request->get( 'state' ) );
        $t_condition    = sanitize_text_field( $this->request->get( 'term_condition' ) );

        // Check if email already exists
        if ( email_exists( $email ) ) {
            return $this->response( [
                'message' => 'Email is already registered.',
                'status'  => 'error',
            ], 200 );
        }



        // Check if username can be generated from full name
        $first_name     = ucfirst( $first_name );
        $last_name      = ucfirst( $last_name );
        $username       = sanitize_user( strtolower($first_name) );

        if ( username_exists( $username ) ) {
            $username = $username . rand( 100, 999 ); // Append random numbers to avoid conflicts
        }

        // Create the user
        $user_id = wp_create_user( $username, $password, $email );

        // Check if user creation was successful
        if ( is_wp_error( $user_id ) ) {
            return $this->response( [
                'message' => $user_id->get_error_message(),
                'status'  => 'error',
            ], 200 );
        }

        // Set user role based on user type
        wp_update_user( [
            'ID'          => $user_id,
            'name'        => $first_name .' '. $last_name,
            'role'       => $user_type,
        ] );

        // Send user registration confirmation email
        do_action( 'adjuster_forge_after_registration', (Object)[
            'user_name'     => $first_name,
            'user_email'    => $email,
            'user_type'     => $user_type,
            'user_password' => $password,
            'profile_link'  => self::getProfilePageUrl(),
        ] );

        // Update additional user metadata
        update_user_meta( $user_id, 'af_first_name', $first_name );
        update_user_meta( $user_id, 'af_last_name', $last_name );
        update_user_meta( $user_id, 'af_user_type', $user_type );
        update_user_meta( $user_id, 'af_city', $city );
        update_user_meta( $user_id, 'af_state', $state );
        update_user_meta( $user_id, 'af_term_condition', $t_condition );

        // Optionally, log in the user immediately after registration
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        // Return success response
        return $this->response( [
            'message' => 'Registration successful.',
            'status'  => 'success',
            'data'    => [
                'user_id'       => $user_id,
                'email'         => $email,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'name'          => $first_name . ' ' . $last_name,
                'user_type'     => $user_type,
                'city'          => $city,
                'state'         => $state,
                'profile_link'  => self::getProfilePageUrl(),
            ],
        ], 200 );
    }

    /**
     * Handles user logout.
     *
     * This method logs out the current user and returns a success response.
     *
     * @return \WP_REST_Response The response object containing the logout status.
     */
    public function logout() {
        // Check if the user is logged in
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'User is not logged in.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }
        // Log out the user
        wp_logout();
        // Return success response
        return $this->response([
            'message' => __( 'Logout successful.', 'adjuster-forge' ),
            'status'  => 'success',
        ], 200);
    }

    /**
     * Handles user password reset.
     *
     * This method retrieves the email from the request, checks if the user exists,
     * and sends a password reset link if the user is found. It returns a success
     * or error response based on the outcome.
     *
     * @return \WP_REST_Response The response object containing the password reset status.
     */
    public function forgot_password() {
        $general_settings   = self::getOption('adjuster_forge_general_settings', []);
        $auth_page_id       = $general_settings['auth_page_id'] ? $general_settings['auth_page_id'] : '';
        $auth_page_url      = get_permalink($auth_page_id);
        $email              = sanitize_email( $this->request->get( 'email' ) );

        // Check if the email is provided
        if ( empty( $email ) ) {
            return $this->response([
                'message' => __( 'Email is required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }
        // Check if the user exists
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            return $this->response([
                'message' => __( 'No user found with this email address.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }
        // Send password reset email
        $reset_key = get_password_reset_key( $user );
        if ( is_wp_error( $reset_key ) ) {
            return $this->response([
                'message' => $reset_key->get_error_message(),
                'status'  => 'error',
            ], 200);
        }
        // now make the reset URL use the auth page URL
        // get username
        $user_login = $user->user_login;

        $reset_url = $auth_page_url . '#/reset-password?token=' . urlencode($reset_key) . '&login=' . urlencode($user_login);

        // Prepare the email content
        $subject = __( 'Password Reset Request', 'adjuster-forge' );
        $message = sprintf(
            __( 'Hello %s, you requested a password reset. Click the link below to reset your password: %s', 'adjuster-forge' ),
            $user->display_name,
            esc_url( $reset_url )
        );
        // Send the email
        $sent = wp_mail( $email, $subject, $message );
        if ( ! $sent ) {
            return $this->response([
                'message' => __( 'Failed to send password reset email.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200); }
        // Return success response
        return $this->response([
            'message' => __( 'Password reset email sent successfully.', 'adjuster-forge' ),
            'status'  => 'success',
            'url' =>$auth_page_url
        ], 200);
    }

    /*
     * Handles user password reset.
     *
     * This method retrieves the token and new password from the request, verifies the reset key,
     * and resets the user's password if the verification is successful. It returns a success or
     * error response based on the outcome.
     *
     * @return \WP_REST_Response The response object containing the password reset status.
     */

    public function reset_password() {
        // Retrieve and sanitize inputs
        $token          = sanitize_text_field( $this->request->get( 'token' ) );
        $new_password   = sanitize_text_field( $this->request->get( 'password' ) );
        $login          = sanitize_text_field( $this->request->get( 'login' ) );

        // Check if token and new password are provided
        if ( empty( $token ) || empty( $new_password ) ) {
            return $this->response([
                'message' => __( 'Token and new password are required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }


        // Verify the reset key
        // Verify the reset key and retrieve the user
        $user = check_password_reset_key($token, $login);

        if ( is_wp_error( $user ) ) {
            return $this->response([
                'message' => $user->get_error_message(),
                'status'  => 'error',
            ], 200);
        }

        // Reset the password
        reset_password( $user, $new_password );

        // Return success response
        return $this->response([
            'message' => __( 'Password has been reset successfully.', 'adjuster-forge' ),
            'status'  => 'success',
        ], 200);
    }
}
