<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

use wpdb;

class SubscriptionHistoryMigrator
{
    protected static $tableName = 'af_subscription_history';
    protected static $cacheGroup = 'adjuster_forge_table_updates';

    public static function migrate()
    {
        global $wpdb;

        // First check cache to see if migration has already been performed
        $cache_key = 'table_exists_' . static::$tableName;
        $table_exists = wp_cache_get($cache_key, static::$cacheGroup);

        if (false === $table_exists) {
            // Get charset and collation
            $charsetCollate = $wpdb->get_charset_collate();

            // Escape table name
            $table = $wpdb->prefix . esc_sql(static::$tableName);

            // Check if table exists
            if ( $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table) ) != $table ) {
                // SQL to create the table
                $sql = "CREATE TABLE {$table} (
                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id BIGINT(20) UNSIGNED NOT NULL,
                    user_type VARCHAR(20) NOT NULL, -- 'adjuster', 'company'
                    plan_name VARCHAR(50) NOT NULL, -- e.g., 'adjuster_init', 'adjuster_monthly', 'company_premium'
                    amount DECIMAL(10,2) NOT NULL, -- e.g., 9.99 or 49.00
                    currency VARCHAR(10) DEFAULT 'USD',
                    payment_status VARCHAR(20) DEFAULT 'paid',-- 'paid', 'failed', 'pending', 'refunded'
                    customer_id VARCHAR(100) DEFAULT NULL,
                    transaction_id VARCHAR(100) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY user_id (user_id),
                    KEY is_active (customer_id),
                    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
        }
    }
}
