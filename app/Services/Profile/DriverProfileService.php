<?php

namespace SmartySoft\AdjusterForge\Services\Profile;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Services\Profile\CompleteProfileService;
use SmartySoft\AdjusterForge\Foundation\AppHelper;
use SmartySoft\AdjusterForge\Http\Model\Availability;
use SmartySoft\AdjusterForge\Http\Model\License;
use SmartySoft\AdjusterForge\Http\Model\Endorsement;
use SmartySoft\AdjusterForge\Http\Model\Experience;

class DriverProfileService implements CompleteProfileService
{
    use AppHelper;
    public function save(array $data, $user_id): bool
    {
        $user_data = [
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'user_type'     => $data['user_type'],
            'profile_link'  => $data['profile_link'],
            'phone'         => $data['phone'],
            'resume'       => $data['resume'],
            'medical_card' => $data['medical_card'],
            'mvr'          => $data['mvr']
        ];
        //Store availability
        $availability = $data['availability'];
        $data_arr = json_decode( wp_unslash( $availability ), true );
        foreach ($data_arr as $value) {
            if ( ! empty( $value ) ) {
                (new Availability())->store([
                    'user_id' => $user_id, 'availability' => $value
                ]);
            }
        }
        //Store license classes
        $license_classes = $data['license_classes'];
        $licenses = json_decode( wp_unslash( $license_classes ), true );
        foreach ($licenses as $value) {
            if ( ! empty( $value ) ) {
                (new License())->store([
                    'user_id' => $user_id, 'license_class' => $value
                ]);
            }
        }
        //Store endorsements
        $endorsements = $data['endorsements'];
        $endorsements_arr = json_decode( wp_unslash( $endorsements ), true );
        foreach ($endorsements_arr as $value) {
            if ( ! empty( $value ) ) {
                (new Endorsement())->store([
                    'user_id' => $user_id, 'endorsement' => $value
                ]);
            }
        }
        //Store experience
        $experience_json = $data['experience'];
        $experience_arr = json_decode( wp_unslash( $experience_json ), true );
        foreach ($experience_arr as $experience) {
            if ( ! empty( $experience ) ) {
                (new Experience())->store([
                    'user_id' => $user_id, 
                    'equipment_type' => $experience['type'] ?? '',
                    'years' => $experience['years'] ?? 0
                ]);
            }
        }
        //Store references
        $references_json = $data['references'];
        $references_arr = json_decode( wp_unslash( $references_json ), true );
        $user_data['references'] = $references_arr;
        $settings = self::getOption('driver_forge_general_settings', []);

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
            $profile_data = array_merge($user_data, $subscription_data);
            update_user_meta( $user_id, 'driver_forge_subscription_data', $profile_data );
        } else {
            update_user_meta( $user_id, 'driver_forge_subscription_data', $user_data );
        }
        // Trigger custom action for profile completion (for hooks/emails/logging)
        do_action('diver_forge_after_complete_profile', (object) $user_data);
        return true;
    }

    public function update(array $data, $user_id): bool
    {
        // Clear existing data before updating
        $this->clearExistingData($user_id);

        // Store availability
        if (isset($data['availability']) && is_array($data['availability'])) {
            foreach ($data['availability'] as $value) {
                if (!empty($value)) {
                    (new Availability())->store([
                        'user_id' => $user_id, 
                        'availability' => $value
                    ]);
                }
            }
        }

        // Store license classes
        if (isset($data['license_classes']) && is_array($data['license_classes'])) {
            foreach ($data['license_classes'] as $value) {
                if (!empty($value)) {
                    (new License())->store([
                        'user_id' => $user_id, 
                        'license_class' => $value
                    ]);
                }
            }
        }

        // Store endorsements
        if (isset($data['endorsements']) && is_array($data['endorsements'])) {
            foreach ($data['endorsements'] as $value) {
                if (!empty($value)) {
                    (new Endorsement())->store([
                        'user_id' => $user_id, 
                        'endorsement' => $value
                    ]);
                }
            }
        }

        // Store experience
        if (isset($data['experience']) && is_array($data['experience'])) {
            foreach ($data['experience'] as $experience) {
                if (!empty($experience) && isset($experience['type']) && isset($experience['years'])) {
                    (new Experience())->store([
                        'user_id' => $user_id, 
                        'equipment_type' => $experience['type'],
                        'years' => intval($experience['years'])
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Clear existing driver-specific data before updating
     */
    private function clearExistingData($user_id)
    {
        // Delete existing records
        (new Availability())->deleteByUserId($user_id);
        (new License())->deleteByUserId($user_id);
        (new Endorsement())->deleteByUserId($user_id);
        (new Experience())->deleteByUserId($user_id);
    }

    public function get_data( $user_id ) 
    {
        $user_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
        $availability = (new Availability())->findByUserId($user_id);
        $availability = array_map(function($item) {
            return $item->availability;
        }, $availability);
        $licenses = (new License())->findByUserId($user_id);
        $licenses = array_map(function($item) {
            return $item->license_class;
        }, $licenses);
        $endorsements = (new Endorsement())->findByUserId($user_id);
        $endorsements = array_map(function($item) {
            return $item->endorsement;
        }, $endorsements);
        $experience = (new Experience())->findByUserId($user_id);
        $experience = array_map(function($item) {
            return [
                'type' => $item->equipment_type,
                'years' => $item->years
            ];
        }, $experience);
        return [
            'user_data' => $user_data,
            'availability' => $availability,
            'licenses' => $licenses,
            'endorsements' => $endorsements,
            'experience' => $experience
        ];
    }
}