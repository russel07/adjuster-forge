<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class License extends Model
{
    protected $tableName = 'af_adjuster_licenses';

    protected $fillable = [
        'adjuster_id',
        'state',
        'license_number',
        'expiration_date',
        'license_file'
    ];
}