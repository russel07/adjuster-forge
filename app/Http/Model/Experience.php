<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class Experience extends Model
{
    protected $tableName = 'af_adjuster_equipment_experience';

    protected $fillable = [
        'user_id',
        'equipment_type',
        'years'
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