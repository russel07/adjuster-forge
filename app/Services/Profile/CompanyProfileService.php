<?php

namespace SmartySoft\AdjusterForge\Services\Profile;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Services\Profile\CompleteProfileService;
use SmartySoft\AdjusterForge\Foundation\AppHelper;
class CompanyProfileService implements CompleteProfileService
{
    use AppHelper;
    public function save(array $data, $user_id): bool
    {
        $settings = self::getOption('adjuster_forge_general_settings', []);

        $free_verification = !empty($settings['allow_free_verification']) && $settings['allow_free_verification'] === 'yes';

        $subscription_data = [
            'profile_completed' => true,
            'profile_completed_at' => current_time('mysql'),
            'paid_subscription_fee' => false,
            'paid_subscription_fee_at' => '',
            'subscription_type' => '',
            'subscription_expire_at' => '',
            'customer_id' => '',
            'transaction_id' => '',
            'account_status' => 'profile_completed',
        ];

        //keep the subscription data
        if ( ! empty( $subscription_data ) ) {
            $profile_data = array_merge($data, $subscription_data);
            update_user_meta( $user_id, 'adjuster_forge_subscription_data', $profile_data );
        }
        // Trigger custom action for profile completion (for hooks/emails/logging)
        do_action('diver_forge_after_complete_profile', (object) $data);
        return true;
    }

    public function update(array $data, $user_id): bool
    {
        // For company updates, we just need to update the user meta
        // since company data is stored in the subscription data
        return true;
    }
}