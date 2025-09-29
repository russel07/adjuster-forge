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

    public function findByUserId($user_id)
    {
        $model = static::getInstance();
        return $model->_wpdb->get_results("SELECT * FROM $model->tableName WHERE adjuster_id = $user_id");
    }
}