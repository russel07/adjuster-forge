<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Badge extends Model
{
    protected $tableName = 'af_adjuster_badges';

    protected $fillable = [
        'adjuster_id',
        'badge',
        'proof_file'
    ];
}