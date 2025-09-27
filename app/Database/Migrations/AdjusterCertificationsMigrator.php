<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

use wpdb;

class AdjusterCertificationsMigrator
{
    protected static $tableName = 'af_adjuster_certifications';
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
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    adjuster_id BIGINT UNSIGNED NOT NULL,
                    certification VARCHAR(50) NOT NULL,
                    issued_date DATE NULL,
                    certificate_file VARCHAR(255),
                    FOREIGN KEY (adjuster_id) REFERENCES {$wpdb->prefix}users(id) ON DELETE CASCADE
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
        }
    }
}
