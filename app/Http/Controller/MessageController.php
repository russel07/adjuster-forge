<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Request\ValidatorHandler;
use SmartySoft\AdjusterForge\Http\Model\JobApplication;
use SmartySoft\AdjusterForge\Http\Controller\BaseController;
use SmartySoft\AdjusterForge\Http\Model\DcMessage;

class MessageController extends BaseController
{
    public function send_message(\WP_REST_Request $request)
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __('You must be logged in to send messages.', 'adjuster-forge'),
                'status'  => 'error',
            ], 401);
        }

        $sender_id = get_current_user_id();
        $application_id = intval($request->get_param('application_id'));
        $message = sanitize_textarea_field( $request->get_param('message') );
        $recipient_id = intval($request->get_param('recipient_id'));
        $job_id = intval($request->get_param('job_id'));

        // Validate inputs
        if ( ! $recipient_id || empty( $message )) {
            return $this->response([
                'message' => __('Recipient and message are required.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }
        $messageModel = new DcMessage();
        // Create message
        $message_id = $messageModel->store([
            'sender_id'      => $sender_id,
            'recipient_id'   => $recipient_id,
            'job_id'         => $job_id,
            'application_id' => $application_id,
            'message'        => $message
        ]);

        if ( ! $message_id ) {
            return $this->response([
                'message' => __('Failed to send message.', 'adjuster-forge'),
                'status'  => 'error',
            ], 500);
        }

        $messages = $messageModel->getConversation($sender_id, $recipient_id);

        foreach ($messages as $message) {
            if ($message->sender_id == $sender_id) {
                $message->is_sender = true;
            } else {
                $message->is_sender = false;
            }
        }

        return $this->response([
            'message' => __('Message sent successfully!', 'adjuster-forge'),
            'data'    => $messages,
            'status'  => 'success',
        ], 200);
    }

    public function get_user_conversations( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __('You must be logged in to view chat partners.', 'adjuster-forge'),
                'status'  => 'error',
            ], 401);
        }
        $user_id = get_current_user_id();
    
        $messageModel = new DcMessage();
        $partners = $messageModel->getChatPartners($user_id);

        return $this->response([
            'data' => $partners,
            'status' => 'success',
        ], 200);
    }

    /**
     * Get conversation for a specific application
     */
    public function get_conversation(\WP_REST_Request $request)
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __('You must be logged in.', 'adjuster-forge'),
                'status'  => 'error',
            ], 401);
        }

        $sender_id = get_current_user_id();
        $recipient_id = intval($request->get_param('partnerId'));

        if ( ! $recipient_id ) {
            return $this->response([
                'message' => __('Partner ID is required.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        $messageModel = new DcMessage();
        $messages = $messageModel->getConversation($sender_id, $recipient_id);

        foreach ($messages as $message) {
            if ($message->sender_id == $sender_id) {
                $message->is_sender = true;
            } else {
                $message->is_sender = false;
            }
        }

        return $this->response([
            'data' => $messages,
            'status' => 'success',
        ], 200);
    }
}