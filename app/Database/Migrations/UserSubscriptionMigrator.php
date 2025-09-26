<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

use wpdb;

class UserSubscriptionMigrator
{
    protected static $tableName = 'af_user_subscriptions';
    protected static $cacheGroup = 'adjuster_forge_table_updates';

    public static function migrate()
    {
        global $wpdb;

        // Check cache first
        $cache_key = 'table_exists_' . static::$tableName;
        $table_exists = wp_cache_get($cache_key, static::$cacheGroup);

        if (false === $table_exists) {
            $charsetCollate = $wpdb->get_charset_collate();
            $table = $wpdb->prefix . esc_sql(static::$tableName);

            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) {
                $sql = "CREATE TABLE {$table} (
                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id BIGINT(20) UNSIGNED NOT NULL,
                    customer_id VARCHAR(100) NOT NULL,
                    status VARCHAR(20) DEFAULT 'pending', -- paid, canceled, failed, etc.
                    subscription_interval VARCHAR(20) DEFAULT NULL,
                    paid_date DATETIME DEFAULT NULL,
                    expire_at DATETIME DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY user_id (user_id),
                    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
        }
    }
}
