<?php

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Router\Router;

//Setup settings for admin
Router::get('general-settings', 'SettingsController@index');
//User login and register
Router::post('register', 'AuthController@register');
Router::post('login', 'AuthController@login');
Router::post('forgot-password', 'AuthController@forgot_password');
Router::post('reset-password', 'AuthController@reset_password');
//Membership purchase and payment
Router::get('get-membership-settings', 'SettingsController@get_membership_data');
Router::post('create-payment-intent', 'PaymentController@make_payment_intents');
Router::post('create-payment-refund', 'PaymentController@make_payment_intents_refund');
Router::post('save-membership', 'PaymentController@save_membership');

// Subscription management
Router::get('subscription-plans', 'PaymentController@get_subscription_plans');
Router::post('create-subscription', 'PaymentController@create_subscription');
Router::post('cancel-subscription', 'PaymentController@cancel_subscription');
Router::post('activate-free-trial', 'UserController@activate_free_trial');

//Adjuster and Company Profile functionality
Router::get('get-profile', 'UserController@get_profile');
Router::post('profile-picture', 'UserController@profile_picture');
Router::post('complete-profile', 'UserController@complete_profile');
Router::post('update-profile', 'UserController@update_profile');
Router::post('save-profile', 'UserController@save_profile');
Router::get('get-inbox', 'UserController@get_my');
Router::post('change-password', 'UserController@change_password');

Router::get('get-saved-offers', 'UserController@get_saved_offers');
Router::post('save-favourite/{postId}', 'UserController@save_favourite');
Router::post('remove-favourite/{id}', 'UserController@remove_favourite');

//Adjuster and Company Post functionality
Router::get('jobs', 'JobController@get_public_jobs');
Router::get('get-jobs', 'JobController@get_jobs');
Router::post('create-job', 'JobController@create_post');
Router::get('/job-details/{id}', 'JobController@get_job_details');
Router::post('/apply-job', 'JobController@apply_job');
Router::get('applied-jobs', 'JobController@get_applied_jobs');
Router::get('job-applicants/{jobId}', 'JobController@get_job_applicants');
Router::post('add-applicant-note', 'JobController@add_applicant_note');
Router::post('update-applicant-status', 'JobController@update_applicant_status');
Router::post('send-message', 'MessageController@send_message');
Router::get('get-user-conversations', 'MessageController@get_user_conversations');
Router::get('get-conversation/{partnerId}', 'MessageController@get_conversation');
Router::get('active-adjusters', 'UserController@adjusters_list');
Router::get('payment-history', 'UserController@get_payment_history');

//Admin Features
Router::get('admin-settings', 'AdminController@general_settings');
Router::post('update-settings', 'AdminController@update_settings');
Router::post('update-free-access-settings', 'AdminController@update_free_access_settings');
Router::post('update-manual-payment', 'AdminController@update_manual_payment');
Router::get('get-payment-settings', 'PaymentController@get_payment_settings');
Router::post('save-payment-settings', 'PaymentController@save_payment_settings');
Router::get('purchased-list', 'AdminController@purchased_list');
Router::get('search-purchased', 'AdminController@search_purchased_list');
Router::get('adjusters', 'AdminController@adjusters_list');
Router::get('adjuster/{user_id}', 'AdminController@adjuster_details');
Router::post('toggle-adjuster', 'AdminController@toggle_adjuster');
Router::get('search-adjusters', 'AdminController@search_adjusters_list');
Router::get('companies', 'AdminController@companies_list');
Router::get('search-companies', 'AdminController@search_companies_list');
Router::post('permit-access', 'AdminController@permit_user_access');
Router::post('revoke-access', 'AdminController@revoke_user_access');

//Webhook
Router::publicPost('webhook-subscribe-to-stripe', 'WebhookController@handle_webhook_subscribe_stripe');
Router::publicPost('webhook-cancel-subscription-from-stripe', 'WebhookController@handle_webhook_cancel_subscription_stripe');
Router::publicPost('webhook-refund-from-stripe', 'WebhookController@handle_webhook_refund_stripe');