<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class License extends Model
{
    protected $tableName = 'af_adjuster_license_classes';

    protected $fillable = [
        'user_id',
        'license_class'
    ];
}