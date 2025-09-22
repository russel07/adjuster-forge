<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Model\Availability;
use SmartySoft\AdjusterForge\Http\Model\License;
use SmartySoft\AdjusterForge\Http\Model\Endorsement;
use SmartySoft\AdjusterForge\Http\Model\Experience;

class User extends Model
{
    protected $tableName = 'users';

    public function getAll( $date =  null, $role = 'driver')
    {
        if( $date ){
            $date = date( 'Y-m', strtotime( $date ) );
            $query = "SELECT u.ID, u.user_login, u.user_email 
                    FROM {$this->tableName} u
                    INNER JOIN {$this->_wpdb->usermeta} um ON u.ID = um.user_id
                    WHERE um.meta_key = %s AND um.meta_value LIKE %s AND DATE_FORMAT(u.user_registered, %s) = %s";
            // Prepare the query with the date and role
            $prepared_query = $this->_wpdb->prepare($query, $this->_wpdb->prefix . 'capabilities', '%'.$role.'%', '%Y-%m', $date);

            $results = $this->_wpdb->get_results( $prepared_query );
        } else {
            $results = $this->_wpdb->get_results("SELECT u.ID, u.user_login, u.user_email FROM {$this->tableName} u
                INNER JOIN {$this->_wpdb->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = '{$this->_wpdb->prefix}capabilities'AND um.meta_value LIKE '%{$role}%'"
            );
        }
        
        return $results ? $results : [];
    }

    public function getAllByLimit( $limit, $offset, $role = 'driver' ) {
        $query = "SELECT u.ID, u.user_login, u.user_email 
                FROM {$this->tableName} u
                INNER JOIN {$this->_wpdb->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = %s AND um.meta_value LIKE %s
                LIMIT %d OFFSET %d";
                
        $prepared_query = $this->_wpdb->prepare($query, $this->_wpdb->prefix . 'capabilities', '%'.$role.'%', $limit, $offset);

        return $this->_wpdb->get_results($prepared_query);
    }


    public function search($limit, $offset, $query, $role = 'driver')
    {
        $like_query = '%' . $this->_wpdb->esc_like($query) . '%';
        $query_string = "SELECT * FROM {$this->tableName} WHERE (user_login LIKE %s OR user_email LIKE %s) AND ID IN (SELECT user_id FROM {$this->_wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s) LIMIT %d OFFSET %d";
        $prepared_query = $this->_wpdb->prepare($query_string, $like_query, $like_query, $this->_wpdb->prefix . 'capabilities', '%' . $role . '%', $limit, $offset);

        $results = $this->_wpdb->get_results($prepared_query);

        return $results ? $results : [];
    }

    public function filterUsers( $limit, $offset, $params, $role = 'driver')
    {
        $where = [];
        $query_args = [];

        // Role filter (always present)
        $where[] = "ID IN (SELECT user_id FROM {$this->_wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s)";
        $query_args[] = $this->_wpdb->prefix . 'capabilities';
        $query_args[] = '%' . $role . '%';

        // Search (user_login or user_email)
        if (!empty($params['search'])) {
            $where[] = "(user_login LIKE %s OR user_email LIKE %s)";
            $search = '%' . $this->_wpdb->esc_like($params['search']) . '%';
            $query_args[] = $search;
            $query_args[] = $search;
        }

        // City
        if (!empty($params['city'])) {
            $where[] = "ID IN (SELECT user_id FROM {$this->_wpdb->usermeta} WHERE meta_key = 'af_city' AND meta_value LIKE %s)";
            $query_args[] = '%' . $this->_wpdb->esc_like($params['city']) . '%';
        }

        // State
        if (!empty($params['state'])) {
            $where[] = "ID IN (SELECT user_id FROM {$this->_wpdb->usermeta} WHERE meta_key = 'af_state' AND meta_value LIKE %s)";
            $query_args[] = '%' . $this->_wpdb->esc_like($params['state']) . '%';
        }

        // License Classes (array) - use License model table
        if (!empty($params['license_classes']) && is_array($params['license_classes'])) {
            $license_where = [];
            foreach ($params['license_classes'] as $class) {
                $license_where[] = "license_class = %s";
                $query_args[] = $class;
            }
            $license_table = (new License())->getTableName();
            $where[] = "ID IN (SELECT user_id FROM {$license_table} WHERE " . implode(' OR ', $license_where) . ")";
        }

        // Endorsements (array) - use Endorsement model table
        if (!empty($params['endorsements']) && is_array($params['endorsements'])) {
            $endorse_where = [];
            foreach ($params['endorsements'] as $endorse) {
                $endorse_where[] = "endorsement = %s";
                $query_args[] = $endorse;
            }
            $endorsement_table = (new Endorsement())->getTableName();
            $where[] = "ID IN (SELECT user_id FROM {$endorsement_table} WHERE " . implode(' OR ', $endorse_where) . ")";
        }

        // Vehicles (array) and Experience (min years) - use Experience model table
        $experience_table = (new Experience())->getTableName();
        if (!empty($params['vehicles']) && is_array($params['vehicles'])) {
            $vehicle_where = [];
            foreach ($params['vehicles'] as $vehicle) {
                $vehicle_where[] = "equipment_type = %s";
                $query_args[] = $vehicle;
            }
            // If experience filter is also set, combine with years
            if (!empty($params['experience'])) {
                $vehicle_exp_where = [];
                foreach ($params['vehicles'] as $vehicle) {
                    $vehicle_exp_where[] = "(equipment_type = %s AND years >= %d)";
                    $query_args[] = $vehicle;
                    $query_args[] = intval($params['experience']);
                }
                $where[] = "ID IN (SELECT user_id FROM {$experience_table} WHERE " . implode(' OR ', $vehicle_exp_where) . ")";
            } else {
                $where[] = "ID IN (SELECT user_id FROM {$experience_table} WHERE " . implode(' OR ', $vehicle_where) . ")";
            }
        } else if (!empty($params['experience'])) {
            // Only experience filter (any vehicle with min years)
            $where[] = "ID IN (SELECT user_id FROM {$experience_table} WHERE years >= %d)";
            $query_args[] = intval($params['experience']);
        }

        // Availability (array) - use Availability model table
        if (!empty($params['availability']) && is_array($params['availability'])) {
            $avail_where = [];
            foreach ($params['availability'] as $avail) {
                $avail_where[] = "availability = %s";
                $query_args[] = $avail;
            }
            $availability_table = (new Availability())->getTableName();
            $where[] = "ID IN (SELECT user_id FROM {$availability_table} WHERE " . implode(' OR ', $avail_where) . ")";
        }

        // Build the final query
        $query_string = "SELECT * FROM {$this->tableName}";
        if (!empty($where)) {
            $query_string .= " WHERE " . implode(' AND ', $where);
        }
        $query_string .= " LIMIT %d OFFSET %d";
        $query_args[] = $limit;
        $query_args[] = $offset;
        $prepared_query = $this->_wpdb->prepare($query_string, ...$query_args);
        $results = $this->_wpdb->get_results($prepared_query);

        return $results ? $results : [];
    }
}