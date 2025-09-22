<?php

namespace SmartySoft\AdjusterForge\Database;

defined('ABSPATH') || exit; // Prevent direct access
use SmartySoft\AdjusterForge\Database\Migrations\UserSubscriptionMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\SubscriptionHistoryMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DcMessagesMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\JobApplicationMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DriverAvailabilityMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DriverLicencesMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DriverEndorsementsMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DriverEquipmentExperienceMigrator;

require_once( ABSPATH.'wp-admin/includes/upgrade.php' );

class DBMigrate
{
    protected static $migrators = [
        UserSubscriptionMigrator::class,
        SubscriptionHistoryMigrator::class,
        DcMessagesMigrator::class,
        JobApplicationMigrator::class,
        DriverAvailabilityMigrator::class,
        DriverLicencesMigrator::class,
        DriverEndorsementsMigrator::class,
        DriverEquipmentExperienceMigrator::class
    ];

    public static function run(){
        self::migrate();
    }

    public static function migrate()
    {
        foreach ( self::$migrators as $class ) {
            $class::migrate();
        }
    }
}