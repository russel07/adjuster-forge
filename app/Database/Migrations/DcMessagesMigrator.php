<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

class DcMessagesMigrator
{
    protected static $tableName = 'af_dc_messages';
    protected static $cacheGroup = 'adjuster_forge_table_updates';
    
    /**
     * Migrate the database to create the dc_messages table for one-to-one chat.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        // First check cache to see if migration has already been performed
        $cache_key = 'table_exists_' . static::$tableName;
        $table_exists = wp_cache_get($cache_key, static::$cacheGroup);

        if (false === $table_exists) {
            // Get charset and collation for the table
            $charsetCollate = $wpdb->get_charset_collate();

            // Escape the table name properly
            $table = $wpdb->prefix . esc_sql(static::$tableName);

            // Check if table exists
            $table_exists_query = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
            
            if ($table_exists_query != $table) {
                // SQL query to create the simple chat table
                $sql = "CREATE TABLE $table (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `sender_id` BIGINT(20) UNSIGNED NOT NULL,
                    `recipient_id` BIGINT(20) UNSIGNED NOT NULL,
                    `job_id` BIGINT(20) UNSIGNED NULL,
                    `application_id` BIGINT(20) UNSIGNED NULL,
                    `message` TEXT NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_sender` (`sender_id`),
                    KEY `idx_recipient` (`recipient_id`),
                    KEY `idx_job` (`job_id`),
                    KEY `idx_application` (`application_id`),
                    KEY `idx_conversation` (`application_id`, `created_at`),
                    KEY `idx_user_conversations` (`sender_id`, `recipient_id`, `created_at`)
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
            
            // Cache that table exists to avoid repeated checks
            wp_cache_set($cache_key, true, static::$cacheGroup, HOUR_IN_SECONDS);
        }
    }
}