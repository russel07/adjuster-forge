<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;

defined('ABSPATH') || exit; // Prevent direct access

/**
 * Class CleanupHandler
 *
 * Handles plugin scheduled tasks.
 */

use SmartySoft\AdjusterForge\Http\Model\Subscription;
class CleanupHandler
{
    public static function maybe_expire_subscriptions()
    {
        // Get all users with active subscriptions
        $users = self::get_active_subscribed_users();

        foreach ($users as $user) {
            // Check if the subscription is expired
            if (self::is_subscription_expired($user)) {
                $subscription_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
                if ($subscription_data) {
                    $subscription_data['account_status'] = 'expired';
                    update_user_meta($user_id, 'adjuster_forge_subscription_data', $subscription_data);
                }

                (new Subscription())->update(['status' => 'expired'],  $user->ID);
            }
        }
    }

    public static function is_subscription_expired( $user )
    {
        if (empty($user->expire_at)) {
            // If expire_at is not set, treat as expired
            return true;
        }

        $expire_timestamp = strtotime($user->expire_at);
        // Use current time for accuracy, not just date
        $now_timestamp = time();

        // If expire_at is in the past, subscription is expired
        return $expire_timestamp < $now_timestamp;
    } 

    public static function get_active_subscribed_users()
    {
        $model = new Subscription();
        return $model->getActiveSubscriptions();
    }
}