<?php

namespace SmartySoft\AdjusterForge\Database;

defined('ABSPATH') || exit; // Prevent direct access
use SmartySoft\AdjusterForge\Database\Migrations\AdjusterAvailabilityMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\AdjusterCareerExperienceMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\AdjusterCertificationsMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\AdjusterEquipmentExperienceMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\AdjusterLicencesMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\DcMessagesMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\JobApplicationMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\SubscriptionHistoryMigrator;
use SmartySoft\AdjusterForge\Database\Migrations\UserSubscriptionMigrator;

require_once( ABSPATH.'wp-admin/includes/upgrade.php' );

class DBMigrate
{
    protected static $migrators = [
        UserSubscriptionMigrator::class,
        SubscriptionHistoryMigrator::class,
        DcMessagesMigrator::class,
        JobApplicationMigrator::class,
        AdjusterAvailabilityMigrator::class,
        AdjusterCareerExperienceMigrator::class,
        AdjusterCertificationsMigrator::class,
        AdjusterLicencesMigrator::class,
        AdjusterEquipmentExperienceMigrator::class
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