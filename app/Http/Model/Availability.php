<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Availability extends Model
{
    protected $tableName = 'af_adjuster_availability';

    protected $fillable = [
        'user_id',
        'availability'
    ];
}