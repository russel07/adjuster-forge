<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

use wpdb;

class DriverEndorsementsMigrator
{
    protected static $tableName = 'af_driver_endorsements';
    protected static $cacheGroup = 'driver_forge_table_updates';

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
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    endorsement VARCHAR(100) NOT NULL,
                    INDEX(user_id),
                    INDEX(endorsement)
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
        }
    }
}
