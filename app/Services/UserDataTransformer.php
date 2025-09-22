<?php

namespace SmartySoft\AdjusterForge\Services;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Services\UserStatusService;

/**
 * User Data Transformer Service
 * Handles consistent user data transformation for APIs and lists
 */
class UserDataTransformer
{
    private $statusService;
    
    public function __construct()
    {
        $this->statusService = UserStatusService::getInstance();
    }

    /**
     * Transform single user to standard format
     */
    public function transformUser($user, $include_sensitive = false)
    {
        if (!$user || !isset($user->ID)) {
            return null;
        }

        $user_id = $user->ID;
        $status_data = $this->statusService->getUserStatus($user_id);
        $subscription_data = $status_data['subscription_data'];

        $base_data = [
            'user_id' => $user_id,
            'username' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => get_user_meta($user_id, 'af_first_name', true),
            'last_name' => get_user_meta($user_id, 'af_last_name', true),
            'city' => get_user_meta($user_id, 'af_city', true),
            'state' => get_user_meta($user_id, 'af_state', true),
            'profile_picture' => get_user_meta($user_id, 'profile_picture', true),
            'status' => $status_data['status'],
            'is_active' => $status_data['is_active'],
            'is_expired' => $status_data['is_expired'],
            'expires_at' => $status_data['expires_at'],
            'days_remaining' => $status_data['days_remaining'],
            'user_type' => get_user_meta($user_id, 'af_user_type', true),
        ];

        // Add subscription information if requested
        if ($include_sensitive) {
            $base_data = array_merge($base_data, [
                'phone' => $subscription_data['phone'] ?? '',
                'profile_completed' => $subscription_data['profile_completed'] ?? false,
                'profile_completed_at' => $subscription_data['profile_completed_at'] ?? '',
                'paid_verification_fee' => $subscription_data['paid_verification_fee'] ?? false,
                'paid_verification_fee_at' => $subscription_data['paid_verification_fee_at'] ?? '',
                'paid_subscription_fee' => $subscription_data['paid_subscription_fee'] ?? false,
                'paid_subscription_fee_at' => $subscription_data['paid_subscription_fee_at'] ?? '',
                'subscription_type' => $subscription_data['subscription_type'] ?? '',
                'subscription_expire_at' => $subscription_data['expire_at'] ?? '',
                'subscription_canceled' => $subscription_data['subscription_canceled'] ?? false,
                'subscription_canceled_at' => $subscription_data['subscription_canceled_at'] ?? '',
                'customer_id' => $subscription_data['customer_id'] ?? '',
                'transaction_id' => $subscription_data['transaction_id'] ?? '',
                'plan_type' => $subscription_data['plan_type'] ?? '',
            ]);
        }

        return (object) $base_data;
    }

    /**
     * Transform user list with optimized bulk operations
     */
    public function transformUserList(array $users, $include_sensitive = false)
    {
        if (empty($users)) {
            return [];
        }

        $user_ids = array_map(function($user) {
            return $user->ID;
        }, $users);

        // Get all status data in bulk
        $bulk_statuses = $this->statusService->getBulkUserStatuses($user_ids);

        // Get all meta data in bulk
        $bulk_meta = $this->getBulkUserMeta($user_ids, [
            'af_first_name',
            'af_last_name', 
            'af_city',
            'af_state',
            'profile_picture',
            'af_user_type'
        ]);

        $transformed_users = [];
        foreach ($users as $user) {
            $user_id = $user->ID;
            $status_data = $bulk_statuses[$user_id] ?? [];
            $meta_data = $bulk_meta[$user_id] ?? [];
            $subscription_data = $status_data['subscription_data'] ?? [];

            $base_data = [
                'user_id' => $user_id,
                'username' => $user->user_login,
                'user_email' => $user->user_email,
                'first_name' => $meta_data['af_first_name'] ?? '',
                'last_name' => $meta_data['af_last_name'] ?? '',
                'city' => $meta_data['af_city'] ?? '',
                'state' => $meta_data['af_state'] ?? '',
                'profile_picture' => $meta_data['profile_picture'] ?? '',
                'status' => $status_data['status'] ?? UserStatusService::STATUS_NOT_COMPLETED,
                'expires_at' => $status_data['expires_at'] ?? null,
                'user_type' => $meta_data['af_user_type'] ?? '',
            ];

            // Add sensitive data if requested
            if ($include_sensitive) {
                $base_data = array_merge($base_data, [
                    'phone' => $subscription_data['phone'] ?? '',
                    'profile_completed' => $subscription_data['profile_completed'] ?? false,
                    'profile_completed_at' => $subscription_data['profile_completed_at'] ?? '',
                    'paid_verification_fee' => $subscription_data['paid_verification_fee'] ?? false,
                    'paid_verification_fee_at' => $subscription_data['paid_verification_fee_at'] ?? '',
                    'paid_subscription_fee' => $subscription_data['paid_subscription_fee'] ?? false,
                    'paid_subscription_fee_at' => $subscription_data['paid_subscription_fee_at'] ?? '',
                    'subscription_type' => $subscription_data['subscription_type'] ?? '',
                    'subscription_expire_at' => $subscription_data['expire_at'] ?? '',
                    'subscription_canceled' => $subscription_data['subscription_canceled'] ?? false,
                    'subscription_canceled_at' => $subscription_data['subscription_canceled_at'] ?? '',
                    'customer_id' => $subscription_data['customer_id'] ?? '',
                    'transaction_id' => $subscription_data['transaction_id'] ?? '',
                    'plan_type' => $subscription_data['plan_type'] ?? '',
                ]);
            }

            $transformed_users[] = (object) $base_data;
        }

        return $transformed_users;
    }

    /**
     * Transform driver with driver-specific data
     */
    public function transformDriverDetails($user)
    {
        $base_data = $this->transformUser($user, true);
        if (!$base_data) {
            return null;
        }

        $user_id = $user->ID;
        $subscription_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
        
        if (!is_array($subscription_data)) {
            $subscription_data = [];
        }

        // Add driver-specific fields
        $driver_data = [
            'resume' => $subscription_data['resume'] ?? '',
            'medical_card' => $subscription_data['medical_card'] ?? '',
            'mvr' => $subscription_data['mvr'] ?? '',
            'references' => $subscription_data['references'] ?? '',
            'license_classes' => $subscription_data['license_classes'] ?? [],
            'endorsements' => $subscription_data['endorsements'] ?? [],
            'equipment_experience' => $subscription_data['equipment_experience'] ?? [],
            'availability' => $subscription_data['availability'] ?? [],
        ];

        return (object) array_merge((array) $base_data, $driver_data);
    }

    /**
     * Transform company with company-specific data
     */
    public function transformCompanyDetails($user)
    {
        $base_data = $this->transformUser($user, true);
        if (!$base_data) {
            return null;
        }

        $user_id = $user->ID;
        $subscription_data = get_user_meta($user_id, 'driver_forge_subscription_data', true);
        
        if (!is_array($subscription_data)) {
            $subscription_data = [];
        }

        // Add company-specific fields
        $company_data = [
            'company_name' => $subscription_data['company_name'] ?? '',
            'website' => $subscription_data['website'] ?? '',
            'dot_mc' => $subscription_data['dot_mc'] ?? '',
            'address' => $subscription_data['address'] ?? '',
            'about' => $subscription_data['about'] ?? '',
        ];

        return (object) array_merge((array) $base_data, $company_data);
    }

    /**
     * Get bulk user meta data efficiently
     */
    private function getBulkUserMeta(array $user_ids, array $meta_keys)
    {
        if (empty($user_ids) || empty($meta_keys)) {
            return [];
        }

        global $wpdb;
        
        $user_placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
        $key_placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));
        
        $query = $wpdb->prepare(
            "SELECT user_id, meta_key, meta_value 
             FROM {$wpdb->usermeta} 
             WHERE user_id IN ({$user_placeholders}) 
             AND meta_key IN ({$key_placeholders})",
            array_merge($user_ids, $meta_keys)
        );
        
        $results = $wpdb->get_results($query);
        
        // Organize by user_id
        $organized = [];
        foreach ($results as $row) {
            $organized[$row->user_id][$row->meta_key] = $row->meta_value;
        }
        
        return $organized;
    }

    /**
     * Transform for legacy compatibility
     */
    public function transformForLegacy($user)
    {
        // For backward compatibility with existing code
        return $this->transformUser($user, true);
    }

    /**
     * Transform pagination response
     */
    public function transformPaginatedResponse(array $users, $page, $per_page, $total_items, $list_type = 'users')
    {
        return [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_items' => $total_items,
            $list_type => $this->transformUserList($users, true)
        ];
    }
}
