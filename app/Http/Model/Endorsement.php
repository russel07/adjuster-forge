<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Endorsement extends Model
{
    protected $tableName = 'af_driver_endorsements';

    protected $fillable = [
        'user_id',
        'endorsement'
    ];
}