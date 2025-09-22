<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Experience extends Model
{
    protected $tableName = 'af_driver_equipment_experience';

    protected $fillable = [
        'user_id',
        'equipment_type',
        'years'
    ];
}