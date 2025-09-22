<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class DcMessage extends Model
{
    protected $tableName = 'af_dc_messages';

    public function getConversation( $sender_id, $receiver_id )
    {
        $model = static::getInstance();
        
        // Get the full table name with prefix
        $table_name = $model->tableName;
        
        $query = $model->_wpdb->prepare("
            SELECT * FROM {$table_name}
            WHERE (sender_id = %d AND recipient_id = %d)
            OR (sender_id = %d AND recipient_id = %d)
            ORDER BY created_at ASC LIMIT 100
        ", $sender_id, $receiver_id, $receiver_id, $sender_id);
        
        return $model->_wpdb->get_results($query);
    }

    /**
     * Get all unique chat partners for a user
     * Returns list of unique people the user has chatted with
     * 
     * @param int $user_id - The user ID (can be sender or receiver)
     * @return array - Array of unique chat partners with their info
     */
    public function getChatPartners($user_id)
    {
        $model = static::getInstance();
        
        // Get the full table name with prefix
        $table_name =  $model->tableName;
        $users_table = $model->_wpdb->users;
        
        $query = $model->_wpdb->prepare("
            SELECT 
                chat.partner_id,
                u.display_name AS partner_name,
                u.user_email AS partner_email,
                m.message AS last_message,
                m.created_at AS last_message_time
            FROM (
                SELECT 
                    CASE 
                        WHEN sender_id = %d THEN recipient_id
                        ELSE sender_id
                    END AS partner_id,
                    MAX(id) AS last_message_id
                FROM {$table_name}
                WHERE sender_id = %d OR recipient_id = %d
                GROUP BY partner_id
            ) chat
            INNER JOIN {$table_name} AS m ON m.id = chat.last_message_id
            LEFT JOIN {$users_table} AS u ON chat.partner_id = u.ID
            ORDER BY m.created_at DESC
        ", $user_id, $user_id, $user_id);
        
        return $model->_wpdb->get_results($query);
    }
}