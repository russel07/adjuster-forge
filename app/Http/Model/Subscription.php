<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Subscription extends Model
{
    /**
     * @var string The name of the database table.
     */
    protected $tableName = 'af_user_subscriptions';

    /**
     * @var array Fields that can be mass assigned
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'subscription_interval',
        'status',
        'paid_date',
        'expire_at',
        'created_at'
    ];

    /**
     * Get the database table name
     *
     * @return string
     */
    public static function get_table_name()
    {
        return 'af_user_subscriptions';
    }

    /**
     * Get a subscriber by user ID and customer ID
     *
     * @param int $user_id
     * @param string $customer_id
     * @return object|null
     */
    public static function getSubscriberByIdandCustomerId($user_id, $customer_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::get_table_name();

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND customer_id = %s",
            $user_id,
            $customer_id
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Get subscription by user ID
     * @param int $user_id
     */
    public function getSubscriptionByUserId($user_id)
    {
        $query = $this->_wpdb->prepare(
            "SELECT * FROM $this->tableName WHERE user_id = %d",
            $user_id
        );

        return $this->_wpdb->get_row($query);
    }

    public function getSubscriptionByCustomerId($customer_id)
    {
        $query = $this->_wpdb->prepare(
            "SELECT * FROM $this->tableName WHERE customer_id = %s",
            $customer_id
        );

        return $this->_wpdb->get_row($query);
    }

    /**
     * Get active subscription by user ID
     * @param int $user_id
     */
    public function getActiveSubscriptionByUserId($user_id)
    {
        $query = $this->_wpdb->prepare(
            "SELECT * FROM $this->tableName WHERE user_id = %d AND status = 'active'",
            $user_id
        );

        return $this->_wpdb->get_row($query);
    }

    /**
     * Get expired subscription by user ID
     * @param int $user_id
     */
    public function getExpiredSubscriptionByUserId($user_id)
    {
        $query = $this->_wpdb->prepare(
            "SELECT * FROM $this->tableName WHERE user_id = %d AND status = 'expired'",
            $user_id
        );

        return $this->_wpdb->get_row($query);
    }

    /**
     * Get all active subscriptions
     */
    public function getActiveSubscriptions()
    {
        $query = "SELECT * FROM $this->tableName WHERE status = 'active'";
        return $this->_wpdb->get_results($query);
    }
}