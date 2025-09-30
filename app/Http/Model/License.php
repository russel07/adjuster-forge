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