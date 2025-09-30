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

    public function findByUserId($user_id)
    {
        $model = static::getInstance();
        return $model->_wpdb->get_results("SELECT * FROM $model->tableName WHERE adjuster_id = $user_id");
    }

    public function deleteByUserId($user_id)
    {
        $model = static::getInstance();
        return $model->_wpdb->delete($model->tableName, array('adjuster_id' => $user_id));
    }
}