<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class CarrierExperience extends Model
{
    protected $tableName = 'af_adjuster_carrier_experience';

    protected $fillable = [
        'adjuster_id',
        'carrier_name',
        'description',
        'proof_file'
    ];
}