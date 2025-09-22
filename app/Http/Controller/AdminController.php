<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Request\ValidatorHandler;
use SmartySoft\AdjusterForge\Http\Model\User;
use SmartySoft\AdjusterForge\Http\Model\OrderHistory;
use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Services\CsvWriter;
use SmartySoft\AdjusterForge\Services\UserStatusService;
use SmartySoft\AdjusterForge\Services\UserDataTransformer;

class AdminController extends BaseController
{
    private $statusService;
    private $dataTransformer;

    public function __construct()
    {
        parent::__construct();
        $this->statusService = UserStatusService::getInstance();
        $this->dataTransformer = new UserDataTransformer();
    }

    /**
     * Retrieve general settings for the admin.
     * 
     * @return mixed The response containing the settings or an error message.
     */
    public function general_settings()
    {
        $defaults = [
            'auth_page_id'                  => '',
            'profile_page_id'               => '',
            'driver_subscription_amount'    => 9.99,
            'driver_subscription_id'        => '',
            'driver_yearly_subscription_amount' => 99.99,
            'driver_yearly_subscription_id' => '',
            'standard_membership_amount'    => 59.0,
            'standard_membership_id'        => '',
            'standard_yearly_membership_amount' => 599.0,
            'standard_yearly_membership_id' => '',
            'premium_membership_amount'     => 99.0,
            'premium_membership_id'         => '',
            'premium_yearly_membership_amount' => 999.0,
            'premium_yearly_membership_id'  => '',
            'allow_free_verification'       => 'no',
            'wp_pages'                      => self::getWPPages(),
            'currencies'                    => self::getAllowedCurrency(),
            'selected_currency'             => 'USD',
        ];

        $existingSettings = self::getOption('driver_forge_general_settings', []);

        if ( !$existingSettings ) {
            return $this->response([
                'settings' => $defaults,
                'status' => 'success',
            ], 200);
        }

        $settings = wp_parse_args($existingSettings, $defaults);

        return $this->response([
            'settings' => $settings,
            'status' => 'success',
        ], 200);
    }

    /**
     * Update general settings and store them in the options table.
     * 
     * @return mixed The response of the update action, either errors or success message.
     */
    public function update_settings()
    {
        // Validate the input data using the ValidatorHandler class.
        $errors = ValidatorHandler::validateGeneralSettings($this->request);

        // If there are validation errors, return the errors with a 403 status code.
        if (count($errors) > 0) {
            return $this->response($errors, 403);
        }

        $existingSettings = self::getOption('driver_forge_general_settings', []);


        // Prepare the data array to store the settings.
        $data = array(
            'auth_page_id'                  => intval($this->request->get('auth_page_id')),
            'profile_page_id'               => intval($this->request->get('profile_page_id')),
            'driver_subscription_amount'    => floatval($this->request->get('driver_subscription_amount')),
            'driver_subscription_id'        => sanitize_text_field($this->request->get('driver_subscription_id')),
            'driver_yearly_subscription_amount' => floatval($this->request->get('driver_yearly_subscription_amount')),
            'driver_yearly_subscription_id' => sanitize_text_field($this->request->get('driver_yearly_subscription_id')),
            'standard_membership_amount'    => floatval($this->request->get('standard_membership_amount')),
            'standard_membership_id'        => sanitize_text_field($this->request->get('standard_membership_id')),
            'standard_yearly_membership_amount' => floatval($this->request->get('standard_yearly_membership_amount')),
            'standard_yearly_membership_id' => sanitize_text_field($this->request->get('standard_yearly_membership_id')),
            'premium_membership_amount'     => floatval($this->request->get('premium_membership_amount')),
            'premium_membership_id'         => sanitize_text_field($this->request->get('premium_membership_id')),
            'premium_yearly_membership_amount' => floatval($this->request->get('premium_yearly_membership_amount')),
            'premium_yearly_membership_id'  => sanitize_text_field($this->request->get('premium_yearly_membership_id')),
            'allow_free_verification'       => $this->request->get('allow_free_verification') === 'true' ? 'yes' : 'no',
            'selected_currency'             => sanitize_text_field($this->request->get('selected_currency')),
        );

        // Update or insert the settings in the wp_options table.
        self::updateOption('driver_forge_general_settings', $data);

        $settings = $data;
        $settings['wp_pages'] = self::getWPPages();

        return $this->response([
            'settings' => $settings,
            'message' => 'Settings updated successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Get the list of drivers.
     *
     * @param \WP_REST_Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function drivers_list( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $search = sanitize_text_field($request->get_param('search'));
        $user = new User();

        if( ! empty( $search ) ) {
            $users = $user->search( $limit, $offset, $search, 'driver' );
            $total_items = count( $users);
        } else {
            $users = $user->getAllByLimit( $limit, $offset,  'driver' );
            $total_items = count($user->getAll());
        }

        // Use the new transformer service
        $data = $this->dataTransformer->transformPaginatedResponse(
            $users, 
            $page, 
            $limit, 
            $total_items, 
            'drivers_list'
        );

        return $this->response([
            'data' =>  $data,
            'message' => 'drivers list retrieved successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Retrieve details of a specific driver by user ID.
     * @param \WP_REST_Request $request The request object containing the user ID.
     * @return mixed The response containing driver details or an error message.
     * */
    public function driver_details( \WP_REST_Request $request )
    {
        $user_id = intval($request->get_param('user_id'));
        $driver = User::find($user_id);

        if ( ! $driver ) {
            return $this->response([
                'message' => 'Driver not found.',
                'status' => 'error',
            ], 404);
        }

        // Use the new transformer service
        $data = $this->dataTransformer->transformDriverDetails($driver);

        return $this->response([
            'data' =>  $data,
            'message' => 'Driver details retrieved successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Toggle the driver's status (approve or reject).
     * @param \WP_REST_Request $request The request object containing the user ID and action.
     * @return mixed The response containing the status update or an error message.
     * */
    public function toggle_driver( \WP_REST_Request $request )
    {
        $user_id = intval($request->get_param('user_id'));
        $action = sanitize_text_field($request->get_param('action'));

        if ( ! in_array( $action, ['approved', 'rejected'], true ) ) {
            return $this->response([
                'message' => 'Invalid action.',
                'status' => 'error',
            ], 400);
        }

        $user = User::find($user_id);
        if ( ! $user ) {
            return $this->response([
                'message' => 'User not found.',
                'status' => 'error',
            ], 404);
        }

        $first_name = get_user_meta( $user_id, 'af_first_name', true );
        $user_email = $user->user_email;
        
        // Use the new status service to update user status
        $status = $action === 'approved' ? UserStatusService::STATUS_APPROVED : UserStatusService::STATUS_REJECTED;
        $additional_data = [
            'approve_status_at' => current_time('mysql'),
        ];

        $result = $this->statusService->updateUserStatus($user_id, $status, $additional_data);

        if ( $result ) {
            do_action('diver_forge_after_driver_profile_'. $action, (object)[
                'user_id' => $user_id,
                'user_name' => $first_name,
                'user_email' => $user_email,
                'action' => $action,
            ]);

            return $this->response([
                'message' => 'Driver status updated successfully.',
                'status' => 'success',
            ], 200);
        }

        return $this->response([
            'message' => 'Failed to update driver status.',
            'status' => 'error',
        ], 500);
    }
    /**
     * Retrieve a list of companies with pagination and optional search.
     * @param \WP_REST_Request $request The request object containing pagination and search parameters.
     * @return mixed The response containing the list of companies or an error message.
     */
    public function companies_list( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $search = sanitize_text_field($request->get_param('search'));
        $user = new User();

        if( ! empty( $search ) ) {
            $users = $user->search( $limit, $offset, $search, 'company' );
            $total_items = count( $users);
        } else {
            $users = $user->getAllByLimit( $limit, $offset,  'company' );
            $total_items = count($user->getAll());
        }

        // Use the new transformer service
        $data = $this->dataTransformer->transformPaginatedResponse(
            $users, 
            $page, 
            $limit, 
            $total_items, 
            'companies'
        );

        return $this->response([
            'data' =>  $data,
            'message' => 'companies list retrieved successfully.',
            'status' => 'success',
        ], 200);
    }
    
    /**
     * Search for drivers based on pagination and search parameters.
     * @param \WP_REST_Request $request The request object containing pagination and search parameters.
     * @return mixed The response containing the list of drivers or an error message.
     * */
    public function search_drivers_list( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $search = sanitize_text_field($request->get_param('search'));
        $user = new User();
        $users = $user->search( $limit, $offset, $search, 'driver' );
        $total_items = count($user->getAll());
        $users_data = [];
        foreach ($users as $key => $item) {
            $membership         = get_user_meta( $item->ID, 'diverge_forge_membership', true );
            $membership_data    = unserialize( $membership );
            $is_subscribed      = isset( $membership_data['_is_purchased'] ) && 'yes' == $membership_data['_is_purchased'] ? true : false;
            $last_order         = [];

            $users_data[] = (object)[
                'user_id'        => $item->ID,
                'username'       => $item->user_login,
                'user_email'     => $item->user_email,
                'first_name'     => get_user_meta( $item->ID, 'first_name', true ),
                'last_name'      => get_user_meta( $item->ID, 'last_name', true ),
                'active'         => $is_subscribed,
            ];

            if ($last_order) {
                $users_data[$key]->payment_status = $last_order->payment_status;
                $users_data[$key]->payment_date = $last_order->created_at;
                $users_data[$key]->refund_status = $last_order->refund_status;
                $users_data[$key]->refund_date = $last_order->refund_date;
            } else {
                $users_data[$key]->payment_status = '';
                $users_data[$key]->payment_date = '';
                $users_data[$key]->refund_status = '';
                $users_data[$key]->refund_date = '';
            }
        }

        $data = [
            'current_page' => 1,
            'per_page' => $limit,
            'total_items' => $total_items,
            'drivers_list' => $users_data
        ];

        return $this->response([
            'data' =>  $data,
            'message' => 'drivers list retrived successfully.',
            'status' => 'success',
        ], 200);
    }
    /**
     * Search for companies based on pagination and search parameters.
     * @param \WP_REST_Request $request The request object containing pagination and search parameters.
     * @return mixed The response containing the list of companies or an error message.
     */
    public function search_companies_list( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $search = sanitize_text_field($request->get_param('search'));
        $user = new User();
        $users = $user->search( $limit, $offset, $search, 'company' );
        $total_items = count($user->getAll());
        $users_data = [];
        foreach ($users as $key => $item) {
            $membership         = get_user_meta( $item->ID, 'diverge_forge_membership', true );
            $membership_data    = unserialize( $membership );
            $is_subscribed      = isset( $membership_data['_is_purchased'] ) && 'yes' == $membership_data['_is_purchased'] ? true : false;
            $last_order         = [];

            $users_data[] = (object)[
                'user_id'        => $item->ID,
                'username'       => $item->user_login,
                'user_email'     => $item->user_email,
                'first_name'     => get_user_meta( $item->ID, 'first_name', true ),
                'last_name'      => get_user_meta( $item->ID, 'last_name', true ),
                'active'         => $is_subscribed,
            ];

            if ($last_order) {
                $users_data[$key]->payment_status = $last_order->payment_status;
                $users_data[$key]->payment_date = $last_order->created_at;
                $users_data[$key]->refund_status = $last_order->refund_status;
                $users_data[$key]->refund_date = $last_order->refund_date;
            } else {
                $users_data[$key]->payment_status = '';
                $users_data[$key]->payment_date = '';
                $users_data[$key]->refund_status = '';
                $users_data[$key]->refund_date = '';
            }
        }

        $data = [
            'current_page' => 1,
            'per_page' => $limit,
            'total_items' => $total_items,
            'companies' => $users_data
        ];

        return $this->response([
            'data' =>  $data,
            'message' => 'companies list retrieved successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Permit user access by updating their subscription data.
     * 
     * @return mixed The response of the permit action, either errors or success message.
     */
    public function permit_user_access()
    {
        $user_id  = intval( $this->request->get( 'id' ) );

        // Validate user ID
        if ( ! $user_id || ! get_userdata( $user_id ) ) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Invalid user ID'
            ], 400);
        }

        // Use the new status service to update user status
        $additional_data = [
            'subscription_expire_at' => date('Y-m-d', strtotime('+1 year')),
            'account_status_at' => current_time('mysql'),
        ];

        $result = $this->statusService->updateUserStatus($user_id, UserStatusService::STATUS_ACTIVE, $additional_data);

        if ($result) {
            return $this->response([
                'status'  => 'success',
                'message' => 'Permit access successfully'
            ], 200);
        }

        return $this->response([
            'status'  => 'error',
            'message' => 'Failed to permit access'
        ], 500);
    }

    /**
     * Revoke user access by updating their subscription data.
     * 
     * @return mixed The response of the revoke action, either errors or success message.
     */
    public function revoke_user_access()
    {
        $user_id  = intval( $this->request->get( 'id' ) );

        // Validate user ID
        if ( ! $user_id || ! get_userdata( $user_id ) ) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Invalid user ID'
            ], 400);
        }

        // Use the new status service to update user status
        $additional_data = [
            'subscription_expire_at' => date('Y-m-d H:i:s'),
            'account_status_at' => current_time('mysql'),
        ];

        $result = $this->statusService->updateUserStatus($user_id, UserStatusService::STATUS_INACTIVE, $additional_data);

        if ($result) {
            return $this->response([
                'status'  => 'success',
                'message' => 'Revoked access successfully'
            ], 200);
        }

        return $this->response([
            'status'  => 'error',
            'message' => 'Failed to revoke access'
        ], 500);
    }
}
