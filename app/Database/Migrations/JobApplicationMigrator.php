<?php

namespace SmartySoft\AdjusterForge\Database\Migrations;

use wpdb;

class JobApplicationMigrator
{
    protected static $tableName = 'af_job_applications';
    protected static $cacheGroup = 'driver_forge_table_updates';

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
                    job_id BIGINT(20) UNSIGNED NOT NULL,
                    applicant_id BIGINT(20) UNSIGNED NOT NULL,
                    cover_letter LONGTEXT,
                    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'pending',
                    employer_notes LONGTEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_application (job_id, applicant_id),
                    KEY job_id (job_id),
                    KEY applicant_id (applicant_id),
                    KEY status (status),
                    KEY application_date (application_date)
                ) $charsetCollate;";

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta($sql);
            }
        }
    }
}