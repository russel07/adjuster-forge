<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use \WP_REST_Request;
use \WP_REST_Response;
use SmartySoft\AdjusterForge\Http\Model\OrderHistory;
use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Http\Model\SubscriptionHistory;

class WebhookController extends BaseController
{
    /**
     * Handle webhook events from Stripe and PayPal.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The response object.
     */
    public function handle_webhook_subscribe_stripe( \WP_REST_Request $request )
    {
        $body = $request->get_body();
        $event = json_decode($body, true);

        // Validate JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response([
                'message' => 'Invalid JSON payload'
            ], 400);
        }
        
        // Check event type
        if (!isset($event['type']) || $event['type'] !== 'invoice.payment_succeeded') {
            return $this->response([
                'message' => 'Unsupported event type'
            ], 400);
        }

        $data = [];
        
        // Extract required data
        try {
            $invoice = $event['data']['object'];
            $customer_id = $invoice['customer'] ?? '';
            $transaction_id = $invoice['id'] ?? '';
            $subscription_id = $invoice['parent']['subscription_details']['subscription'] ?? '';
            
            // Get line item data (first item)
            $line_item = $invoice['lines']['data'][0] ?? [];
            
            // Extract metadata from line item instead of invoice
            $user_id = $line_item['metadata']['user_id'] ?? '';
            $user_type = $line_item['metadata']['user_type'] ?? '';
            
            // Extract pricing details
            $price_id = $line_item['pricing']['price_details']['price'] ?? '';
            $product_id = $line_item['pricing']['price_details']['product'] ?? '';
            $amount = ($invoice['amount_paid'] ?? 0) / 100;
            $currency = $invoice['currency'] ?? 'USD';
            
            // Store subscription details in database
            (new SubscriptionHistory())->store([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'plan_name' => $line_item['description'] ?? 'Monthly Plan',
                'amount' => $amount,
                'currency' => $currency,
                'payment_status' => $invoice['status'] ?? '',
                'customer_id' => $customer_id,
                'transaction_id' => $transaction_id,
                'created_at' => current_time('mysql'),
            ]);

            $subscription_model = new Subscription();

            $subscriber = $subscription_model->getSubscriptionByUserId( $user_id );

            if ( ! $subscriber ) {
                // Create a new subscription record
                $subscription_model->store([
                    'user_id' => $user_id,
                    'customer_id' => $customer_id,
                    'subscription_interval' => 'month',
                    'status' => 'active',
                    'paid_date' => current_time('mysql'),
                    'expire_at' => date('Y-m-d H:i:s', strtotime('+1 month')),
                    'created_at' => current_time('mysql'),
                ]);
            } else {
                $interval = $subscriber->subscription_interval;
                // Update the existing subscription record
                $subscription_model->update([
                    'status' => 'active',
                    'paid_date' => current_time('mysql'),
                    'expire_at' => date('Y-m-d H:i:s', strtotime("+1 $interval")),
                ], $subscriber->id);
            }
            
            return $this->response([
                'message' => 'Webhook processed successfully',
                'data' => $data
            ], 200);
            
        } catch (\Exception $e) {
            return $this->response([
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handle_webhook_cancel_subscription_stripe( \WP_REST_Request $request )
    {
        $body = $request->get_body();
        $event = json_decode($body, true);

        // Validate JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response([
                'message' => 'Invalid JSON payload'
            ], 400);
        }

        // Check event type
        if (!isset($event['type']) || $event['type'] !== 'customer.subscription.deleted') {
            return $this->response([
                'message' => 'Unsupported event type'
            ], 400);
        }

        try {
            $subscription = $event['data']['object'];
            $customer_id = $subscription['customer'] ?? '';
            $user_id = $subscription['metadata']['user_id'] ?? '';
            $user_type = $subscription['metadata']['user_type'] ?? '';
            $transaction_id = $subscription['id'] ?? '';
            
            //Store record in subscription history table
            (new SubscriptionHistory())->store([
                'user_id'           => $user_id,
                'user_type'         => $user_type,
                'plan_name'         => 'Cancelled Subscription',
                'amount'            => 0,
                'currency'          => 'USD',
                'payment_status'    => 'Cancelled',
                'transaction_id'    => $transaction_id,
                'customer_id'       => $customer_id,
                'created_at'        => current_time('mysql'),
            ]); 

            $existing_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
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

            update_user_meta($user_id, 'adjuster_forge_subscription_data', $existing_data);

            return $this->response([
                'message' => 'Subscription cancelled, user meta updated',
                'data' => [
                    'customer_id' => $customer_id,
                    'user_id' => $user_id,
                    'user_type' => $user_type
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->response([
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle webhook events from Stripe and PayPal.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The response object.
     */
    public function handle_webhook_refund_stripe( \WP_REST_Request $request )
    {
        $body = $request->get_body();
        $event = json_decode($body, true);

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return $this->response([
                'message' => 'Invalid JSON'
            ], 400);
        }

        $subscription_model = new Subscription();

        // Extract subscription object
        $subscription = $event['data']['object'];
        $customer_id = $subscription['customer'] ?? '';
        $transaction_id = $subscription['id'] ?? '';
        $user_id = $subscription['metadata']['user_id'] ?? '';
        $user_type = $subscription['metadata']['user_type'] ?? '';
        $currency = $subscription['currency'] ?? 'USD';

        if ( ! empty( $user_id ) ) {
            $subscriber = $subscription_model->getSubscriptionByUserId($user_id);
        } else {
            $subscriber = $subscription_model->getSubscriptionByCustomerId($customer_id);
        }

        
        if ($subscriber) {
            $user_id = $subscriber->user_id;
            $update_data = [
                'status' => 'refunded',
                'expire_at' => current_time('mysql'), //As refund expire from now
                'created_at' => current_time('mysql'),
                'customer_id' => $customer_id,
            ];
            $subscription_model->update($update_data, $subscriber->id);
        }

        if( $user_id ) {
            $user_type = get_user_meta($user_id, 'af_user_type', true);
            // Store record in subscription history table
            (new SubscriptionHistory())->store([
                'user_id'           => $user_id,
                'user_type'         => $user_type,
                'plan_name'         => 'Refunded Subscription',
                'amount'            => 0,
                'currency'          => $currency,
                'payment_status'    => 'Refunded',
                'transaction_id'    => $transaction_id,
                'customer_id'       => $customer_id,
                'created_at'        => current_time('mysql'),
            ]); 

            $existing_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
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

            update_user_meta($user_id, 'adjuster_forge_subscription_data', $existing_data);
        }

        return $this->response([
            'data' => [
                'customer_id' => $customer_id,
                'user_id' => $user_id,
                'user_type' => $user_type,
            ],
            'message' => 'Refund processed successfully'
        ], 200);
    }
}
