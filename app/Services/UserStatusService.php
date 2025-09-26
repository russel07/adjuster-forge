<?php

namespace SmartySoft\AdjusterForge\Services;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Foundation\AppHelper;

/**
 * Centralized User Status Management Service
 * Handles all user status determination and subscription tracking
 */
class UserStatusService
{
    use AppHelper;

    private static $instance = null;
    private static $statusCache = [];
    
    // Status constants for consistency
    const STATUS_NOT_COMPLETED = 'not_completed';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELED = 'canceled';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DUE_SUBSCRIPTION = 'due_subscription';
    const STATUS_FREE_TRIAL = 'free_trial';

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get comprehensive user status information
     * 
     * @param int $user_id
     * @param bool $use_cache
     * @return array
     */
    public function getUserStatus($user_id, $use_cache = true)
    {
        $cache_key = "user_status_{$user_id}";
        
        if ($use_cache && isset(self::$statusCache[$cache_key])) {
            return self::$statusCache[$cache_key];
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return $this->getDefaultStatus();
        }

        $user_type = get_user_meta($user_id, 'af_user_type', true);
        $subscription_meta = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
        $subscription_record = $this->getSubscriptionRecord($user_id);

        $status_data = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'status' => $this->determineStatus($subscription_meta, $subscription_record),
            'subscription_data' => $this->normalizeSubscriptionData($subscription_meta, $subscription_record),
            'permissions' => $this->getUserPermissions($user_type, $subscription_meta, $subscription_record),
            'expires_at' => $this->getExpirationDate($subscription_meta, $subscription_record),
            'is_active' => false,
            'is_expired' => false,
            'is_trial' => false,
            'days_remaining' => 0
        ];

        // Set boolean flags for easier checking
        $status_data['is_active'] = in_array($status_data['status'], [
            self::STATUS_ACTIVE, 
            self::STATUS_APPROVED, 
            self::STATUS_FREE_TRIAL
        ]);
        
        $status_data['is_expired'] = ($status_data['status'] === self::STATUS_EXPIRED);
        $status_data['is_trial'] = ($status_data['status'] === self::STATUS_FREE_TRIAL);
        
        if ($status_data['expires_at']) {
            $expires_timestamp = strtotime($status_data['expires_at']);
            $status_data['days_remaining'] = max(0, ceil(($expires_timestamp - time()) / DAY_IN_SECONDS));
        }

        // Cache the result
        self::$statusCache[$cache_key] = $status_data;
        
        return $status_data;
    }

    /**
     * Determine user status based on all available data
     */
    private function determineStatus($meta_data, $subscription_record)
    {
        // Normalize meta data
        if (!is_array($meta_data)) {
            $meta_data = [];
        }

        // Priority 1: Check subscription record status (most authoritative)
        if ($subscription_record && !empty($subscription_record->status)) {
            // Check if subscription is expired based on date
            if ($this->isSubscriptionExpired($subscription_record)) {
                return self::STATUS_EXPIRED;
            }
            return $subscription_record->status;
        }

        // Priority 2: Check meta data statuses
        if (isset($meta_data['account_status']) && !empty($meta_data['account_status'])) {
            return $meta_data['account_status'];
        }

        if (isset($meta_data['status']) && !empty($meta_data['status'])) {
            return $meta_data['status'];
        }

        // Priority 3: Check for cancellation
        if (isset($meta_data['subscription_canceled']) && $meta_data['subscription_canceled']) {
            return self::STATUS_CANCELED;
        }

        // Priority 4: Check expiration from meta
        if (isset($meta_data['subscription_expire_at']) && !empty($meta_data['subscription_expire_at'])) {
            $expire_date = strtotime($meta_data['subscription_expire_at']);
            if ($expire_date < time()) {
                return self::STATUS_EXPIRED;
            }
            return self::STATUS_ACTIVE;
        }

        // Priority 5: Check if paid subscription fee
        if (isset($meta_data['paid_subscription_fee']) && $meta_data['paid_subscription_fee']) {
            return self::STATUS_PENDING_REVIEW;
        }

        return self::STATUS_NOT_COMPLETED;
    }

    /**
     * Get subscription record from database
     */
    private function getSubscriptionRecord($user_id)
    {
        $subscription_model = new Subscription();
        return $subscription_model->getSubscriptionByUserId($user_id);
    }

    /**
     * Check if subscription is expired based on expire_at date
     */
    private function isSubscriptionExpired($subscription_record)
    {
        if (!$subscription_record || empty($subscription_record->expire_at)) {
            return false;
        }

        $expire_timestamp = strtotime($subscription_record->expire_at);
        return $expire_timestamp < time();
    }

    /**
     * Normalize subscription data from both sources
     */
    private function normalizeSubscriptionData($meta_data, $subscription_record)
    {
        $normalized = [
            'subscription_type' => '',
            'customer_id' => '',
            'transaction_id' => '',
            'plan_type' => '',
            'subscription_interval' => 'month',
            'paid_date' => '',
            'expire_at' => '',
            'created_at' => '',
            'profile_completed' => false,
            'paid_verification_fee' => false,
            'paid_subscription_fee' => false,
        ];

        // Merge meta data
        if (is_array($meta_data)) {
            $normalized = array_merge($normalized, $meta_data);
        }

        // Override with subscription record data (more authoritative)
        if ($subscription_record) {
            $normalized['customer_id'] = $subscription_record->customer_id ?? $normalized['customer_id'];
            $normalized['subscription_interval'] = $subscription_record->subscription_interval ?? $normalized['subscription_interval'];
            $normalized['paid_date'] = $subscription_record->paid_date ?? $normalized['paid_date'];
            $normalized['expire_at'] = $subscription_record->expire_at ?? $normalized['expire_at'];
            $normalized['created_at'] = $subscription_record->created_at ?? $normalized['created_at'];
        }

        return $normalized;
    }

    /**
     * Get user permissions based on status and type
     */
    private function getUserPermissions($user_type, $meta_data, $subscription_record)
    {
        $status = $this->determineStatus($meta_data, $subscription_record);
        
        $base_permissions = [
            'can_view_profile' => true,
            'can_edit_profile' => true,
            'can_view_jobs' => false,
            'can_apply_jobs' => false,
            'can_post_jobs' => false,
            'can_message' => false,
            'can_view_premium_profiles' => false,
        ];

        // Active users get most permissions
        if (in_array($status, [self::STATUS_ACTIVE, self::STATUS_APPROVED, self::STATUS_FREE_TRIAL])) {
            if ($user_type === 'adjuster') {
                $base_permissions['can_view_jobs'] = true;
                $base_permissions['can_apply_jobs'] = true;
                $base_permissions['can_message'] = true;
            } elseif ($user_type === 'company') {
                $base_permissions['can_post_jobs'] = true;
                $base_permissions['can_view_premium_profiles'] = true;
                $base_permissions['can_message'] = true;
            }
        }

        return $base_permissions;
    }

    /**
     * Get expiration date from best available source
     */
    private function getExpirationDate($meta_data, $subscription_record)
    {
        // Subscription record is more authoritative
        if ($subscription_record && !empty($subscription_record->expire_at)) {
            return $subscription_record->expire_at;
        }

        // Fall back to meta data
        if (is_array($meta_data) && isset($meta_data['subscription_expire_at'])) {
            return $meta_data['subscription_expire_at'];
        }

        return null;
    }

    /**
     * Update user status and sync between meta and subscription table
     */
    public function updateUserStatus($user_id, $status, $additional_data = [])
    {
        // Clear cache first
        $this->clearUserCache($user_id);

        $subscription_model = new Subscription();
        $existing_subscription = $subscription_model->getSubscriptionByUserId($user_id);
        $meta_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
        
        if (!is_array($meta_data)) {
            $meta_data = [];
        }

        // Update meta data
        $meta_data['status'] = $status;
        $meta_data['account_status'] = $status;
        $meta_data['account_status_at'] = current_time('mysql');
        $meta_data = array_merge($meta_data, $additional_data);

        update_user_meta($user_id, 'adjuster_forge_subscription_data', $meta_data);

        // Update or create subscription record
        $subscription_data = [
            'status' => $status,
            'paid_date' => current_time('mysql'),
        ];

        // Set expiration based on status
        if ($status === self::STATUS_ACTIVE || $status === self::STATUS_APPROVED) {
            $interval = $existing_subscription->subscription_interval ?? 'month';
            $subscription_data['expire_at'] = date('Y-m-d H:i:s', strtotime("+1 $interval"));
        } elseif ($status === self::STATUS_FREE_TRIAL) {
            $subscription_data['expire_at'] = date('Y-m-d H:i:s', strtotime('+3 days'));
        } else {
            $subscription_data['expire_at'] = current_time('mysql');
        }

        if ($existing_subscription) {
            $subscription_model->update($subscription_data, $existing_subscription->id);
        } else {
            $subscription_data['user_id'] = $user_id;
            $subscription_data['customer_id'] = $additional_data['customer_id'] ?? 'TEMP_' . uniqid();
            $subscription_data['subscription_interval'] = $additional_data['subscription_interval'] ?? 'month';
            $subscription_data['created_at'] = current_time('mysql');
            $subscription_model->store($subscription_data);
        }

        return true;
    }

    /**
     * Bulk get user statuses for lists (optimized)
     */
    public function getBulkUserStatuses(array $user_ids)
    {
        if (empty($user_ids)) {
            return [];
        }

        // Get all subscription records at once
        $subscription_model = new Subscription();
        global $wpdb;
        $table = $subscription_model->getTableName();
        $placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id IN ({$placeholders})",
            ...$user_ids
        );
        
        $subscriptions = $wpdb->get_results($query);
        $subscription_by_user = [];
        foreach ($subscriptions as $sub) {
            $subscription_by_user[$sub->user_id] = $sub;
        }

        // Get all meta data at once
        $meta_query = $wpdb->prepare(
            "SELECT user_id, meta_value FROM {$wpdb->usermeta} 
             WHERE meta_key = 'adjuster_forge_subscription_data' 
             AND user_id IN ({$placeholders})",
            ...$user_ids
        );
        
        $meta_results = $wpdb->get_results($meta_query);
        $meta_by_user = [];
        foreach ($meta_results as $meta) {
            $meta_by_user[$meta->user_id] = maybe_unserialize($meta->meta_value);
        }

        // Build status for each user
        $statuses = [];
        foreach ($user_ids as $user_id) {
            $meta_data = $meta_by_user[$user_id] ?? [];
            $subscription_record = $subscription_by_user[$user_id] ?? null;
            
            $statuses[$user_id] = [
                'user_id' => $user_id,
                'status' => $this->determineStatus($meta_data, $subscription_record),
                'subscription_data' => $this->normalizeSubscriptionData($meta_data, $subscription_record),
                'expires_at' => $this->getExpirationDate($meta_data, $subscription_record),
            ];
        }

        return $statuses;
    }

    /**
     * Clear user cache
     */
    public function clearUserCache($user_id)
    {
        $cache_key = "user_status_{$user_id}";
        unset(self::$statusCache[$cache_key]);
        wp_cache_delete($cache_key, 'adjuster_forge_user_status');
    }

    /**
     * Clear all cache
     */
    public function clearAllCache()
    {
        self::$statusCache = [];
        wp_cache_flush_group('adjuster_forge_user_status');
    }

    /**
     * Get default status structure
     */
    private function getDefaultStatus()
    {
        return [
            'user_id' => 0,
            'user_type' => '',
            'status' => self::STATUS_NOT_COMPLETED,
            'subscription_data' => [],
            'permissions' => [],
            'expires_at' => null,
            'is_active' => false,
            'is_expired' => false,
            'is_trial' => false,
            'days_remaining' => 0
        ];
    }

    /**
     * Check if user can perform action
     */
    public function canUserPerform($user_id, $action)
    {
        $status_data = $this->getUserStatus($user_id);
        return $status_data['permissions'][$action] ?? false;
    }

    /**
     * Get users expiring soon (for notifications)
     */
    public function getUsersExpiringSoon($days = 3)
    {
        $subscription_model = new Subscription();
        global $wpdb;
        $table = $subscription_model->getTableName();
        
        $future_date = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $now = current_time('mysql');
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE status = 'active' 
             AND expire_at BETWEEN %s AND %s",
            $now,
            $future_date
        );
        
        return $wpdb->get_results($query);
    }
}
