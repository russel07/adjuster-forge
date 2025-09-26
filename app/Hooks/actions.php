<?php
    //Prevent direct access
    defined('ABSPATH') || exit;

    use SmartySoft\AdjusterForge\Hooks\Handler\ActionHooksHandler;
    use SmartySoft\AdjusterForge\Hooks\Handler\CleanupHandler;

    //Register all action hooks

    //Add rewrite rules for custom query vars
    add_action( 'init', [ ActionHooksHandler::class, 'adjuster_forge_rewrite_rules'] );
    add_action( 'template_redirect', [ ActionHooksHandler::class, 'adjuster_forge_file_download_handler'] );

    //Send email to user after registration
    add_action('diver_forge_after_registration', [ ActionHooksHandler::class, 'send_user_registration_confirmation_email'] );
    //Send email to user after paid verification fee
    add_action('diver_forge_after_verification_fee_paid', [ ActionHooksHandler::class, 'send_user_verification_fee_paid_email'] );
    //Send email to admin after user paid verification fee
    add_action('diver_forge_after_verification_fee_paid', [ ActionHooksHandler::class, 'send_user_verification_fee_paid_notification_to_admin'] );
    //Send email to user after profile approved
    add_action('diver_forge_after_adjuster_profile_approved', [ ActionHooksHandler::class, 'send_user_profile_approved_email'] );
    //Send email to user after profile rejected
    add_action('diver_forge_after_adjuster_profile_rejected', [ ActionHooksHandler::class, 'send_user_profile_rejected_email'] );

    //Hooks to daily task
    add_action('adjuster_forge_daily_tasks', [ CleanupHandler::class, 'maybe_expire_subscriptions']);

    //Redirect user to the dashboard after login
    add_action('admin_init', [ ActionHooksHandler::class, 'af_redirect_user_to_dashboard']);
    add_action('login_redirect', [ ActionHooksHandler::class, 'af_redirect_user_to_dashboard_login'], 10, 3);
    add_action('login_init', [ ActionHooksHandler::class, 'af_redirect_user_to_login_page'] );

    //Hide admin bar for custom roles
    add_action('after_setup_theme', [ ActionHooksHandler::class, 'af_hide_admin_bar_for_custom_roles'] );

    add_action(
        'wp_ajax_adjuster_forge_renew_rest_nonce',
        function () {
            wp_send_json(
                array(
                    'nonce' => wp_create_nonce('wp_rest'),
                ),
                200
            );
        }
    );