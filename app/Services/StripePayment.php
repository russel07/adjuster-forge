<?php
namespace SmartySoft\AdjusterForge\Services;

defined('ABSPATH') || exit; // Prevent direct access

class StripePayment
{
    protected $secret_key;

    public function __construct($secret_key)
    {
        $this->secret_key = $secret_key;
    }

    /**
     * Create a customer in Stripe
     *
     * @param string $email Customer's email address
     * @param string $name Customer's name
     * @return string Stripe customer ID
     * @throws \Exception
     */
    public function createCustomer( $email, $name )
    {
        $response = wp_remote_post('https://api.stripe.com/v1/customers', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'email' => sanitize_email($email),
                'name' => sanitize_text_field($name),
            ],
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) {
            return $data['id'];
        } else {
            throw new \Exception('Failed to create customer: ' . $data['error']['message']);
        }
    }

    /**
     * Create a PaymentIntent for processing payments
     *
     * @param int $amount Amount in cents
     * @param string $currency Currency code (e.g., 'usd')
     * @param string $customer_id Stripe customer ID
     * @return array PaymentIntent data
     * @throws \Exception
     */
    public function createPaymentIntent( $amount, $currency, $customer_id )
    {
        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'amount' => intval($amount),
                'currency' => sanitize_text_field($currency),
                'customer' => sanitize_text_field($customer_id),
                'automatic_payment_methods[enabled]' => 'true',
            ],
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ( isset($data['id']) ) {
            return $data;
        } else {
            throw new \Exception('Failed to create payment intent: ' . $data['error']['message']);
        }
    }

    /**
     * Create a refund for a payment intent
     *
     * @param string $payment_intent_id Stripe payment intent ID
     * @param int|null $amount Amount to refund in cents (optional)
     * @return array Refund data
     * @throws \Exception
     */
    public function createRefund($payment_intent_id, $amount = null) {
        $body = [
            'payment_intent' => sanitize_text_field($payment_intent_id),
        ];

        if ($amount !== null) {
            $body['amount'] = intval($amount);
        }

      
        $response = wp_remote_post('https://api.stripe.com/v1/refunds', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) {
            return $data;
        } else {
            throw new \Exception('Failed to create refund: ' . $data['error']['message']);
        }
    }

    /**
     * Create a subscription for a customer
     *
     * @param string $customer_id Stripe customer ID
     * @param string $price_id Stripe price ID
     * @param string $payment_method_id Stripe payment method ID
     * @return array
     * @throws \Exception
     */
    public function createSubscription($customer_id, $price_id, $payment_method_id, $user_id, $user_type)
    {
        // First attach the payment method to the customer
        $this->attachPaymentMethod($payment_method_id, $customer_id);

        // Create the subscription
        $response = wp_remote_post('https://api.stripe.com/v1/subscriptions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'customer' => sanitize_text_field($customer_id),
                'items' => [['price' => sanitize_text_field($price_id)]],
                'default_payment_method' => sanitize_text_field($payment_method_id),
                'expand[0]' => 'latest_invoice.payment_intent',
                'metadata[user_id]' => sanitize_text_field($user_id),
                'metadata[user_type]' => sanitize_text_field($user_type),
            ],
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) {
            return $data;
        } else {
            throw new \Exception('Failed to create subscription: ' . $data['error']['message']);
        }
    }

    /**
     * Attach a payment method to a customer
     *
     * @param string $payment_method_id
     * @param string $customer_id
     * @throws \Exception
     */
    private function attachPaymentMethod($payment_method_id, $customer_id)
    {
        $response = wp_remote_post("https://api.stripe.com/v1/payment_methods/{$payment_method_id}/attach", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'customer' => sanitize_text_field($customer_id),
            ],
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['id'])) {
            throw new \Exception('Failed to attach payment method: ' . $data['error']['message']);
        }
    }

    /**
     * Cancel a subscription
     *
     * @param string $subscription_id
     * @return array
     * @throws \Exception
     */
    public function cancelSubscription($subscription_id)
    {
        $response = wp_remote_request("https://api.stripe.com/v1/subscriptions/{$subscription_id}", [
            'method' => 'DELETE',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['id'])) {
            return $data;
        } else {
            throw new \Exception('Failed to cancel subscription: ' . $data['error']['message']);
        }
    }
}