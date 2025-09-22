<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class SubscriptionHistory extends Model
{
    protected $tableName = 'af_subscription_history';

    public function getAllHistory( $user_id ) {
        return $this->_wpdb->get_results(
            $this->_wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    public function getHistory($user_id, $limit = 10, $offset = 0, $search = '')
    {
        $query = "SELECT * FROM {$this->tableName} WHERE user_id = %d";
        $query_params = [$user_id];

        if ( ! empty( $search ) ) {
            $query .= " AND (plan_name LIKE %s OR payment_status LIKE %s OR transaction_id LIKE %s)";
            $query_params[] = '%' . $this->_wpdb->esc_like( $search ) . '%';
            $query_params[] = '%' . $this->_wpdb->esc_like( $search ) . '%';
            $query_params[] = '%' . $this->_wpdb->esc_like( $search ) . '%';
        }

        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_params[] = $limit;
        $query_params[] = $offset;

        $results = $this->_wpdb->get_results( $this->_wpdb->prepare( $query, $query_params ) );
        return $results ? $results : [];
    }

    /**
     * Get subscription by subscription ID
     */
    public function get_subscription_by_id($transaction_id)
    {
        $result = $this->_wpdb->get_row(
            $this->_wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE transaction_id = %s ORDER BY created_at DESC LIMIT 1",
                $transaction_id
            )
        );

        return $result ? $result : null;
    }
}