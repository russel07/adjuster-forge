<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Services\StripePayment;
use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Http\Model\SubscriptionHistory;

class PaymentController extends BaseController
{
    /**
     * Get payment settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_payment_settings()
    {
        $defaults = [
            'payment_gateway'        => 'stripe',
            'stripe_publishable_key' => '',
            'stripe_secret_key'      => '',
            'paypal_client_id'       => '',
            'paypal_secret_key'      => '',
        ];

        $existingSettings = self::getOption('driver_forge_payment_settings', []);

        if ( !$existingSettings ) {
            return $this->response([
                'data' => $defaults,
                'status' => 'success',
            ], 200);
        } else {
            $settings = wp_parse_args($existingSettings, $defaults);

            return $this->response([
                'data' => $settings,
                'status' => 'success',
            ], 200);
        }
    }

    /**
     * Save payment settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function save_payment_settings()
    {
        $data = array(
            'payment_gateway'        => sanitize_text_field($this->request->get('payment_gateway')),
            'stripe_public_key'      => sanitize_text_field($this->request->get('stripe_public_key')),
            'stripe_secret_key'      => sanitize_text_field($this->request->get('stripe_secret_key')),
            'paypal_client_id'       => sanitize_text_field($this->request->get('paypal_client_id')),
            'paypal_secret_key'      => sanitize_text_field($this->request->get('paypal_secret_key')),
        );

        self::updateOption('driver_forge_payment_settings', $data);

        return $this->response([
            'message' => 'Payment settings updated successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Handles user Registration.
     *
     **/
    public function make_payment_intents() {
        // Retrieve and sanitize inputs
        $amount         = intval($this->request->get('amount'));
        $currency       = sanitize_text_field($this->request->get('currency'));
        $customer_email = sanitize_email( $this->request->get( 'email' ) );
        $customer_name  = sanitize_text_field( $this->request->get( 'name' ) );

        $setings = self::getOption('driver_forge_payment_settings', []);

        if ( empty( $setings ) ) {
            return $this->response([
                'message' => 'Payment settings not found.',
                'status' => 'error',
            ], 400);
        }

        if( !isset( $setings['stripe_secret_key'] ) || empty($setings['stripe_secret_key'] ) ) {
            return $this->response([
                'message' => 'Stripe secret key not set.',
                'status' => 'error',
            ], 400);
        }

        $secret_key = $setings['stripe_secret_key'];

        try {
            $stripe = new StripePayment($secret_key);

            // Step 1: Create a Customer
            $customer_id = $stripe->createCustomer($customer_email, $customer_name);

            // Step 2: Create a Payment Intent with the Customer
            $payment_intent_data = $stripe->createPaymentIntent($amount, $currency, $customer_id);

            return $this->response([
                'customer_id' => $customer_id,
                'client_secret' => $payment_intent_data['client_secret'],
                'message' => 'Registration and payment intent created successfully.',
                'status' => 'success',
            ], 200);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Create a subscription with Stripe
     */
    public function create_subscription()
    {
        // Get current user
        $current_user = wp_get_current_user();
        if (!is_user_logged_in()) {
            return $this->response([
                'message' => 'You must be logged in to create a subscription.',
                'status' => 'error',
            ], 401);
        }

        // Retrieve and sanitize inputs
        $plan_id = sanitize_text_field($this->request->get('plan_id'));
        $customer_email = sanitize_email($this->request->get('email'));
        $customer_name = sanitize_text_field($this->request->get('name'));
        $payment_method_id = sanitize_text_field($this->request->get('payment_method_id'));
        $customer_id = sanitize_text_field($this->request->get('customer_id'));
        $interval = sanitize_text_field($this->request->get('interval'));

        // Validate required fields
        if (empty($plan_id) || empty($customer_email) || empty($customer_name) || empty($payment_method_id)) {
            return $this->response([
                'message' => 'All fields are required.',
                'status' => 'error',
            ], 400);
        }

        // Get payment settings
        $settings = self::getOption('driver_forge_payment_settings', []);
        if (empty($settings) || !isset($settings['stripe_secret_key']) || empty($settings['stripe_secret_key'])) {
            return $this->response([
                'message' => 'Stripe secret key not configured.',
                'status' => 'error',
            ], 400);
        }

        $secret_key = $settings['stripe_secret_key'];

        try {
            $stripe = new StripePayment($secret_key);

            // Create or get customer
            if( empty( $customer_id ) ) {
                $customer_id = $stripe->createCustomer($customer_email, $customer_name);
            }
            $user_id = $current_user->ID;
            $user_type = get_user_meta($user_id, 'af_user_type', true);
            // Create subscription
            $subscription_data = $stripe->createSubscription($customer_id, $plan_id, $payment_method_id, $user_id, $user_type);

            // Store subscription details in database
            (new SubscriptionHistory())->store([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'plan_name' => $subscription_data['items']['data'][0]['price']['nickname'] ?? 'Monthly Plan',
                'amount' => $subscription_data['items']['data'][0]['price']['unit_amount'] / 100,
                'currency' => $subscription_data['items']['data'][0]['price']['currency'],
                'payment_status' => $subscription_data['status'],
                'customer_id' => $customer_id,
                'transaction_id' => $subscription_data['id'],
                'created_at' => current_time('mysql'),
            ]);

            $subscription_model = new Subscription();
            $subscriber = $subscription_model->getSubscriptionByUserId( $user_id );

            if ( ! $subscriber ) {
                // Create a new subscription record
                $subscription_model->store([
                    'user_id' => $user_id,
                    'customer_id' => $customer_id,
                    'subscription_interval' => $interval,
                    'status' => 'active',
                    'paid_date' => current_time('mysql'),
                    'expire_at' => date('Y-m-d H:i:s', strtotime("+1 $interval")),
                    'created_at' => current_time('mysql'),
                ]);
            } else {
                // Update the existing subscription record
                $updatedData = [
                    'subscription_interval' => $interval,
                    'status' => 'active',
                    'paid_date' => current_time('mysql'),
                    'expire_at' => date('Y-m-d H:i:s', strtotime("+1 $interval")),
                    'created_at' => current_time('mysql'),
                ];

                $subscription_model->update( $updatedData, $subscriber->id );
            }

            $existing_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
            $plan_type = self::get_plan_type( $plan_id );
            if ( ! empty( $existing_data ) ) {
                $existing_data['paid_subscription_fee'] = true;
                $existing_data['paid_subscription_fee_at'] = current_time('mysql');
                $existing_data['transaction_id']            = $subscription_data['id'];
                $existing_data['subscription_type']         = $subscription_data['items']['data'][0]['price']['nickname'] ?? 'Monthly Plan';
                $existing_data['subscription_expire_at']    = date('Y-m-d H:i:s', strtotime("+1 $interval"));
                $existing_data['customer_id']               = $customer_id;
                $existing_data['plan_type']                 = $plan_type;
                $existing_data['subscription_status']       = 'active';
                $existing_data['account_status']            = 'active';
            } else {
                $existing_data = [
                    'profile_completed' => true,
                    'profile_completed_at' => current_time('mysql'),
                    'user_type' => $user_type,
                    'customer_id'               => $customer_id,
                    'paid_subscription_fee'     => true,
                    'paid_subscription_fee_at'  => current_time('mysql'),
                    'transaction_id'            => $subscription_data['id'],
                    'subscription_type'         => $subscription_data['items']['data'][0]['price']['nickname'] ?? 'Monthly Plan',
                    'subscription_expire_at'    => date('Y-m-d H:i:s', strtotime("+1 $interval")),
                    'account_status'            => 'active',
                    'plan_type'                 => $plan_type,
                ];
            }
            update_user_meta($user_id, 'driver_forge_subscription_data', $existing_data );

        return $this->response([
            'subscription_id' => $subscription_data['id'],
            'customer_id' => $customer_id,
                'status' => $subscription_data['status'],
                'message' => 'Subscription created successfully.',
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            return $this->response([
                'message' => 'Error creating subscription: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancel_subscription()
    {
        // Get current user
        $current_user = wp_get_current_user();
        if (!is_user_logged_in()) {
            return $this->response([
                'message' => 'You must be logged in.',
                'status' => 'error',
            ], 401);
        }

        $subscription_id = sanitize_text_field($this->request->get('subscription_id'));
        $user_id = $current_user->ID;
        $user_type = get_user_meta($user_id, 'af_user_type', true);
        //if subscription id is empty
        if ( empty( $subscription_id ) ) {
            return $this->response([
                'message' => 'Subscription ID is required.',
                'status' => 'error',
            ], 400);
        }

        $subscriptionHModel = new SubscriptionHistory();
        $row = $subscriptionHModel->get_subscription_by_id($subscription_id);
        //if subscription not found
        if ( !$row ) {
            return $this->response([
                'message' => 'Subscription not found.',
                'status' => 'error',
            ], 404);
        }

        // Get payment settings
        $settings = self::getOption('driver_forge_payment_settings', []);
        //if settings are not found
        if (empty($settings) || !isset($settings['stripe_secret_key']) || empty($settings['stripe_secret_key'])) {
            return $this->response([
                'message' => 'Stripe secret key not configured.',
                'status' => 'error',
            ], 400);
        }

        $secret_key = $settings['stripe_secret_key'];

        try {
            $stripe = new StripePayment($secret_key);
            $cancelled_subscription = $stripe->cancelSubscription($subscription_id);

            $existing_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
            if ( ! empty( $existing_data ) ) {
                $existing_data['subscription_canceled'] = true;
                $existing_data['subscription_canceled_at'] = current_time('mysql');
            } else {
                $existing_data = [
                    'profile_completed' => true,
                    'profile_completed_at' => current_time('mysql'),
                    'subscription_canceled' => true,
                    'subscription_canceled_at' => current_time('mysql'),
                ];
            }

            update_user_meta($user_id, 'driver_forge_subscription_data', $existing_data);
            // Store cancellation in subscription history
            $subscriptionHModel->store([
                'user_id' => $user_id,
                'customer_id' => $row->customer_id,
                'subscription_interval' => $row->subscription_interval,
                'status' => 'cancelled',
                'paid_date' => current_time('mysql'),
                'expire_at' => $row->expire_at,
                'created_at' => current_time('mysql'),
            ]);

            return $this->response([
                'message' => 'Subscription cancelled successfully.',
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            return $this->response([
                'message' => 'Error cancelling subscription: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Get subscription plans (demo data)
     */
    public function get_subscription_plans()
    {
        // Get current user
        $current_user = wp_get_current_user();
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => 'You must be logged in.',
                'status' => 'error',
            ], 401);
        }

        $user_email         = $current_user->user_email;
        $user_id            = $current_user->ID;
        $user_type          = get_user_meta($user_id, 'af_user_type', true);

        $subscription_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
        if ( ! empty( $subscription_data ) ) {
            $customer_id = isset($subscription_data['customer_id']) ? $subscription_data['customer_id'] : '';
        } else {
            $customer_id = '';
        }

        $settings = self::getOption('driver_forge_general_settings', []);
        if ( empty( $settings ) ) {
            return $this->response([
                'message' => 'Subscription plans not configured.',
                'status' => 'error',
            ], 400);
        }
        // Define subscription plans
        $driver_plan = self::drivers_stripe_plan( $settings );

        $company_plans = self::companies_stripe_plan( $settings );

        $data = [
            'user_data' => [
                'user_name' => $current_user->display_name,
                'user_email' => $user_email,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'customer_id' => $customer_id,
            ],
            'plans' => $user_type === 'driver' ? [$driver_plan] : $company_plans,
        ];

        return $this->response([
            'data' => $data,
            'status' => 'success',
        ], 200);
    }
}
