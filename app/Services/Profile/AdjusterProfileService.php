<?php

namespace SmartySoft\AdjusterForge\Services\Profile;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Services\Profile\CompleteProfileService;
use SmartySoft\AdjusterForge\Foundation\AppHelper;
use SmartySoft\AdjusterForge\Http\Model\Availability;
use SmartySoft\AdjusterForge\Http\Model\License;
use SmartySoft\AdjusterForge\Http\Model\Badge;
use SmartySoft\AdjusterForge\Http\Model\CarrierExperience;
use SmartySoft\AdjusterForge\Http\Model\Experience;
use SmartySoft\AdjusterForge\Http\Model\Endorsement;

class AdjusterProfileService implements CompleteProfileService
{
    use AppHelper;
    public function save(array $data, $user_id): bool
    {
        $user_data = [
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'user_type'         => $data['user_type'],
            'profile_link'      => $data['profile_link'],
            'phone'             => $data['phone'],
            'years_experience'  => $data['years_experience'],
            'cat_deployments'   => $data['cat_deployments'],
            'bio'               => $data['bio'],
            'resume'            => $data['resume'],
            'w9'                => $data['w9'],
            'insurance_proof'   => $data['insurance_proof'],
        ];
        //Store availability
        $availability = $data['availability'];
        $data_arr = json_decode( wp_unslash( $availability ), true );
        foreach ($data_arr as $value) {
            if ( ! empty( $value ) ) {
                (new Availability())->store([
                    'adjuster_id' => $user_id, 'availability' => $value
                ]);
            }
        }
        //Store license classes
        $licenses = $data['license_data'];
        foreach ($licenses as $license) {
            if ( ! empty( $license ) ) {
                (new License())->store([
                    'adjuster_id' => $user_id, 'state' => $license['state'], 'license_number' => $license['number'],
                    'expiration_date' => $license['expiration'], 'license_file' => $license['file_url']
                ]);
            }
        }
        //Store badges
        $badges = $data['badges'];
        foreach ($badges as $badge) {
            if ( ! empty( $badge ) ) {
                (new Badge())->store([
                    'adjuster_id' => $user_id, 'badge' => $badge['badge'], 'proof_file' => $badge['proof_file_url']
                ]);
            }
        }
        
        //Store carrier experience
        $carrier_experience = $data['carrier_experience'];
        $employers_ia_firms = $data['employers_ia_firms'];
        $work_samples = $data['work_samples'];
        
        (new CarrierExperience())->store([
            'adjuster_id' => $user_id,
            'carrier_name' => $carrier_experience,
            'description' => $employers_ia_firms,
            'proof_file' => $work_samples
        ]);

        //Store references
        $references_arr = $data['references'];
        $user_data['references'] = $references_arr;
        $settings = self::getOption('adjuster_forge_general_settings', []);

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
            update_user_meta( $user_id, 'adjuster_forge_subscription_data', $profile_data );
        } else {
            update_user_meta( $user_id, 'adjuster_forge_subscription_data', $user_data );
        }
        // Trigger custom action for profile completion (for hooks/emails/logging)
        do_action('adjuster_forge_after_complete_profile', (object) $user_data);
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
                        'adjuster_id' => $user_id, 
                        'availability' => $value
                    ]);
                }
            }
        }

        // Store experience types (using License model for now - may need separate model)
        if (isset($data['experience_types']) && is_array($data['experience_types'])) {
            foreach ($data['experience_types'] as $value) {
                if (!empty($value)) {
                    (new License())->store([
                        'adjuster_id' => $user_id, 
                        'license_class' => $value
                    ]);
                }
            }
        }

        // Store badges with proof files
        if (isset($data['badges']) && is_array($data['badges'])) {
            foreach ($data['badges'] as $badge_id) {
                if (!empty($badge_id)) {
                    $proof_file = '';
                    
                    // Check if there's a proof file for this badge
                    if (isset($data['badge_proofs'][$badge_id])) {
                        $proof_file = $data['badge_proofs'][$badge_id];
                    }
                    
                    (new Badge())->store([
                        'adjuster_id' => $user_id,
                        'badge' => $badge_id,
                        'proof_file' => $proof_file
                    ]);
                }
            }
        }

        // Store carrier experience
        if (isset($data['carrier_experience']) || isset($data['employers_ia_firms'])) {
            $carrier_experience = isset($data['carrier_experience']) && is_array($data['carrier_experience']) 
                ? implode(',', $data['carrier_experience']) 
                : (isset($data['carrier_experience']) ? $data['carrier_experience'] : '');
                
            $employers_ia_firms = isset($data['employers_ia_firms']) ? $data['employers_ia_firms'] : '';
            
            (new CarrierExperience())->store([
                'adjuster_id' => $user_id,
                'carrier_name' => $carrier_experience,
                'description' => $employers_ia_firms,
                'proof_file' => '' // No work samples in update
            ]);
        }

        // Update user meta with basic profile data and references
        $user_data = [
            'first_name'        => $data['first_name'] ?? '',
            'last_name'         => $data['last_name'] ?? '',
            'email'             => $data['email'] ?? '',
            'user_type'         => $data['user_type'] ?? '',
            'phone'             => $data['phone'] ?? '',
            'bio'               => $data['bio'] ?? '',
            'years_experience'  => $data['years_experience'] ?? 0,
            'cat_deployments'   => $data['cat_deployments'] ?? 0,
            'references'        => $data['references'] ?? [],
        ];

        // Get existing subscription data and merge with updates
        $existing_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
        if (!is_array($existing_data)) {
            $existing_data = [];
        }

        // Merge user data with existing subscription data
        $updated_data = array_merge($existing_data, $user_data);
        update_user_meta($user_id, 'adjuster_forge_subscription_data', $updated_data);

        return true;
    }

    /**
     * Clear existing adjuster-specific data before updating
     */
    private function clearExistingData($user_id)
    {
        // Delete existing records
        (new Availability())->deleteByUserId($user_id);
        (new License())->deleteByUserId($user_id);
        (new Badge())->deleteByUserId($user_id);
        (new CarrierExperience())->deleteByUserId($user_id);
        (new Experience())->deleteByUserId($user_id);
    }

    public function get_data( $user_id ) 
    {
        $user_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
        $availability = (new Availability())->findByUserId($user_id);
        $availability = array_map(function($item) {
            return $item->availability;
        }, $availability);
        $licenses = (new License())->findByUserId($user_id);
        $licenses = array_map(function($item) {
            return [
                'state' => $item->state,
                'number' => $item->license_number,
                'expiration' => $item->expiration_date,
                'has_file' => !empty($item->license_file),
                'file_url' => $item->license_file
            ];
        }, $licenses);
        $badges = (new Badge())->findByUserId($user_id);
        $badges = array_map(function($item) {
            return [
                'badge' => $item->badge,
                'has_proof_file' => !empty($item->proof_file),
                'proof_file_url' => $item->proof_file
            ];
        }, $badges);
        return [
            'user_data' => $user_data,
            'availability' => $availability,
            'licenses' => $licenses,
            'badges' => $badges
        ];
    }
}