<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Request\ValidatorHandler;
use SmartySoft\AdjusterForge\Http\Model\User;
use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Http\Model\SubscriptionHistory;
use SmartySoft\AdjusterForge\Http\Response\ResponseHandler;
use SmartySoft\AdjusterForge\Http\Controller\BaseController;
use SmartySoft\AdjusterForge\Services\Profile\ProfileServiceFactory as ProfileService;
class UserController extends BaseController
{
    /**
     * Get the profile of the current user
     *
     * @return \WP_REST_Response
     */
    public function get_profile()
    {
        // Get the current user
        $current_user = wp_get_current_user();

        // Check if the user is logged in
        if ( is_user_logged_in() ) {
            $user_data = 
            $user_email         = $current_user->user_email;
            $user_id            = $current_user->ID;
            $first_name         = get_user_meta($user_id, 'af_first_name', true);
            $last_name          = get_user_meta($user_id, 'af_last_name', true);
            $user_type          = get_user_meta($user_id, 'af_user_type', true);
            $subscription_data  = get_user_meta( $user_id, 'adjuster_forge_subscription_data', true );

            $user_status = isset($subscription_data['subscription_canceled']) && $subscription_data['subscription_canceled'] ? 'cancelled' : '';
            // Get the user meta data
            $data = [
                'first_name'        => $first_name,
                'last_name'         => $last_name,
                'name'              => $first_name . ' ' . $last_name,
                'email'             => $user_email,
                'user_type'         => $user_type,
                'city'              => get_user_meta($user_id, 'af_city', true),
                'state'             => get_user_meta($user_id, 'af_state', true),
                'profile_picture'   => get_user_meta($user_id, 'profile_picture', true),
                'phone'             => get_user_meta($user_id, 'af_phone', true),
                'bio'               => get_user_meta($user_id, 'af_bio', true),
                'country'           => get_user_meta($user_id, 'af_country', true),
                'status'            => empty($user_status) ? self::get_user_status($subscription_data) : $user_status,
            ];

            if( 'adjuster' === $user_type ) {
                $service = ProfileService::getProfileService($user_type);
                $additional_data = $service->get_data( $user_id );
                if ( ! is_array($additional_data) ) {
                    $data['adjuster_data'] = [];
                }
                $data['adjuster_data'] = $additional_data;
            } elseif( 'company' === $user_type ) {
                if ( ! is_array($subscription_data) ) {
                    $subscription_data = [];
                }
                $data['company_data'] = $subscription_data;
            } else {
                $user_type = 'unknown';
            }
            $plan_type = isset($subscription_data['plan_type']) ? $subscription_data['plan_type'] : '';

            $data['plan_type'] = $plan_type;
        } else {
            $data = [];
        }

        return $this->response($data, 200);

    }

    /**
     * Update the profile picture of the current user
     *
     * @return \WP_REST_Response
     */
    public function profile_picture()
    {
        if ( is_user_logged_in() ) {
            // Handle event file upload
            $profile_picture = self::handle_file_upload('file');

            if ( is_wp_error( $profile_picture ) ) {
                return $this->response($profile_picture->get_error_message(), 400);
            }

            // Get the user ID
            $user_id = get_current_user_id();

            update_user_meta($user_id, 'profile_picture', $profile_picture);

            return $this->response('Profile picture updated successfully', 200);
        } else {
            return $this->response('You must need to logged in', 400);
        }
    }

    /**
     * Complete the profile of the current user
     *
     * @return \WP_REST_Response
     */
    public function complete_profile(){
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            // Get the user ID
            $user_id = $current_user->ID;
            $user_type = get_user_meta( $user_id, 'af_user_type', true );
            $first_name = get_user_meta( $user_id, 'af_first_name', true );
            $last_name = get_user_meta( $user_id, 'af_last_name', true );
            $user_email     = $current_user->user_email;
            // Prepare user data with consistent key names
            $user_data = [
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'email'        => $user_email,
                'user_type'    => $user_type,
                'profile_link' => self::getProfilePageUrl(),
            ];
            $new_data = [];
            if( $user_type == 'adjuster' ) {
                // Get form data from the request
                $phone              = sanitize_text_field( $this->request->get( 'phone' ) );
                $availability       =  $this->request->get( 'availability' );
                $years_experience   = intval( $this->request->get( 'years_experience' ) );
                $cat_deployments    = intval( $this->request->get( 'cat_deployments' ) );
                $bio                = sanitize_textarea_field( $this->request->get( 'bio' ) );
                $licenses           = sanitize_text_field( $this->request->get( 'licenses' ) );
                $badges             = sanitize_text_field( $this->request->get( 'badges' ) );
                $carrier_experience = $this->request->get( 'carrier_experience' );
                $employers_ia_firms = sanitize_text_field( $this->request->get( 'employers_ia_firms' ) );
                $references_json    = $this->request->get( 'references' );
                $d_agreed           = $this->request->get( 'declaration_agreed', false ) ? true : false;
                $licenses           = stripslashes( $licenses );
                $badges             = stripslashes( $badges );
                $references         = stripslashes( $references_json );
                $license_data       = json_decode( $licenses, true );
                $badge_data         = json_decode( $badges, true );
                $references_data    = json_decode( $references, true );
                // Process license files
                $licenses_with_files = [];
                if (is_array($license_data)) {
                    foreach ($license_data as $index => $license) {
                        $license_entry = [
                            'state' => $license['state'],
                            'number' => $license['number'],
                            'expiration' => $license['expiration'],
                            'has_file' => $license['has_file'],
                            'file_url' => ''
                        ];
                        
                        // Check if there's a corresponding file
                        $file_key = 'license_file_' . $index;
                        if (isset($_FILES[$file_key]) && !empty($_FILES[$file_key]['name'])) {
                            $license_file = self::handle_file_upload($file_key);
                            if (is_wp_error($license_file)) {
                                return $this->response($license_file->get_error_message(), 400);
                            }
                            $license_entry['file_url'] = $license_file;
                        }
                        
                        $licenses_with_files[] = $license_entry;
                    }
                }
                // Process badges with proofs
                $badges_with_proofs = [];
                if (is_array($badge_data)) {
                    foreach ($badge_data as $index => $badge) {
                        $badge_entry = [
                            'badge' => $badge,
                            'proof_file_url' => ''
                        ];
                        
                        // Check if there's a corresponding proof file
                        $proof_key = 'badge_proof_' . $index;
                        if (isset($_FILES[$proof_key]) && !empty($_FILES[$proof_key]['name'])) {
                            $proof_file = self::handle_file_upload($proof_key);
                            if (is_wp_error($proof_file)) {
                                return $this->response($proof_file->get_error_message(), 400);
                            }
                            $badge_entry['proof_file_url'] = $proof_file;
                        }
                        
                        $badges_with_proofs[] = $badge_entry;
                    }
                }

                $work_samples = [];
                // Process carrier experience file
                //check if file exist with key work_sample_ 
                foreach ($_FILES as $key => $file) {
                    if (strpos($key, 'work_sample_') === 0 && !empty($file['name'])) {
                        $uploaded_file = self::handle_file_upload($key);
                        if (is_wp_error($uploaded_file)) {
                            return $this->response($uploaded_file->get_error_message(), 400);
                        } else {
                            $work_samples[] = $uploaded_file;
                        }
                    }
                }

                // Handle event file upload
                $resume = self::handle_file_upload('resume');

                if ( is_wp_error( $resume ) ) {
                    return $this->response($resume->get_error_message(), 400);
                }

                if ( isset($_FILES['w9']) && !empty($_FILES['w9']['name']) ) {
                    $w9 = self::handle_file_upload('w9');
                    if ( is_wp_error( $w9 ) ) {
                        return $this->response($w9->get_error_message(), 400);
                    }
                } else {
                    $w9 = '';
                }

                if ( isset($_FILES['insurance_proof']) && !empty($_FILES['insurance_proof']['name']) ) {
                    $insurance_proof = self::handle_file_upload('insurance_proof');
                    if ( is_wp_error( $insurance_proof ) ) {
                        return $this->response($insurance_proof->get_error_message(), 400);
                    }
                } else {
                    $insurance_proof = '';
                }
                // Update bio separately
                update_user_meta( $user_id, 'af_bio', $bio );

                $new_data = [
                    'phone'             => $phone,
                    'availability'      => $availability,
                    'years_experience'  => $years_experience,
                    'cat_deployments'   => $cat_deployments,
                    'license_data'      => $licenses_with_files,
                    'badges'            => $badges_with_proofs,
                    'carrier_experience'=> $carrier_experience,
                    'employers_ia_firms'=> $employers_ia_firms,
                    'work_samples'      => !empty($work_samples) ? implode( ',', $work_samples ) : null,
                    'resume'            => $resume,
                    'w9'                => $w9,
                    'insurance_proof'   => $insurance_proof,
                    'references'        => $references_data,
                    'declaration_agreed'=> $d_agreed,
                ];
            } else if( $user_type == 'company' ) {
                $phone          = sanitize_text_field( $this->request->get( 'phone' ) );
                $company_name   = sanitize_text_field( $this->request->get( 'company_name' ) );
                $website        = sanitize_text_field( $this->request->get( 'website' ) );
                $dot_mc         = sanitize_text_field( $this->request->get( 'dot_mc' ) );
                $address        = sanitize_text_field( $this->request->get( 'address' ) );
                $about          = sanitize_textarea_field( $this->request->get( 'about' ) );
                $logo           = self::handle_file_upload('logo');
                if ( is_wp_error( $logo ) ) {
                    return $this->response($logo->get_error_message(), 400);
                }

                $new_data = [
                    'phone'         => $phone,
                    'company_name'  => $company_name,
                    'website'       => $website,
                    'dot_mc'        => $dot_mc,
                    'address'       => $address,
                    'logo'          => $logo,
                    'about'         => $about,
                ];
            }
            $profile_data = array_merge($new_data, $user_data);
            $service = ProfileService::getProfileService($user_type);
            $save = $service->save($profile_data, $user_id);

            if( $save ) {
                return $this->response([
                    'message' => 'Profile completed successfully',
                    'status' => 'success',
                ], 200);
            } else {
                return $this->response([
                    'message' => 'Failed to complete profile',
                    'status' => 'error',
                ], 200);
            }            
        } else {
            return $this->response('You must need to logged in', 400);
        }
    }

    public function update_profile()
    {
        if ( ! is_user_logged_in() ) {
            return $this->response('You must be logged in', 400);
        }

        $user_id = get_current_user_id();
        $user_type = get_user_meta( $user_id, 'af_user_type', true );

        // Common profile fields
        $first_name = sanitize_text_field( $this->request->get( 'first_name' ) );
        $last_name  = sanitize_text_field( $this->request->get( 'last_name' ) );
        $phone      = sanitize_text_field( $this->request->get( 'phone' ) );
        $city       = sanitize_text_field( $this->request->get( 'city' ) );
        $state      = sanitize_text_field( $this->request->get( 'state' ) );
        $country    = sanitize_text_field( $this->request->get( 'country' ) );
        $about      = sanitize_textarea_field( $this->request->get( 'about' ) );

        // Update common user meta
        update_user_meta( $user_id, 'af_first_name', $first_name );
        update_user_meta( $user_id, 'af_last_name', $last_name );
        update_user_meta( $user_id, 'af_phone', $phone );
        update_user_meta( $user_id, 'af_city', $city );
        update_user_meta( $user_id, 'af_state', $state );
        update_user_meta( $user_id, 'af_country', $country );

        // Get existing subscription data
        $existing_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);
        if ( ! is_array($existing_data) ) {
            $existing_data = [];
        }

        // Update phone in subscription data
        $existing_data['phone'] = $phone;

        if ( $user_type === 'adjuster' ) {
            update_user_meta( $user_id, 'af_bio', $about );
            
            // Handle adjuster-specific data
            $availability = $this->request->get( 'availability', [] );
            $years_experience = intval( $this->request->get( 'years_experience' ) );
            $cat_deployments = intval( $this->request->get( 'cat_deployments' ) );
            $experience_types = $this->request->get( 'experience_types', [] );
            $badges = $this->request->get( 'badges', [] );
            $carrier_experience = $this->request->get( 'carrier_experience', [] );
            $employers_ia_firms = sanitize_textarea_field( $this->request->get( 'employers_ia_firms' ) );
            $references = $this->request->get( 'references', [] );

            // Handle badge proof files
            $badge_proofs = [];
            if ( is_array($badges) && !empty($badges) ) {
                foreach ( $badges as $badge_id ) {
                    $file_key = 'badge_proof_' . $badge_id;
                    if ( isset($_FILES[$file_key]) && !empty($_FILES[$file_key]['name']) ) {
                        $uploaded_file = self::handle_file_upload($file_key);
                        if ( !is_wp_error($uploaded_file) ) {
                            $badge_proofs[$badge_id] = $uploaded_file;
                        }
                    }
                }
            }

            // Prepare data for ProfileService
            $adjuster_data = [
                'first_name'         => $first_name,
                'last_name'          => $last_name,
                'email'              => wp_get_current_user()->user_email,
                'user_type'          => $user_type,
                'phone'              => $phone,
                'bio'                => $about,
                'availability'       => $availability,
                'years_experience'   => $years_experience,
                'cat_deployments'    => $cat_deployments,
                'experience_types'   => $experience_types,
                'badges'             => $badges,
                'badge_proofs'       => $badge_proofs,
                'carrier_experience' => $carrier_experience,
                'employers_ia_firms' => $employers_ia_firms,
                'references'         => $references,
            ];

            // Update references in subscription data
            $existing_data['references'] = $references;

            // Use ProfileService to update adjuster data
            $service = ProfileService::getProfileService($user_type);
            $save = $service->update($adjuster_data, $user_id);

            if ( ! $save ) {
                return $this->response([
                    'message' => 'Failed to update adjuster profile',
                    'status' => 'error',
                ], 200);
            }

        } elseif ( $user_type === 'company' ) {
            // Handle company-specific data
            $company_name = sanitize_text_field( $this->request->get( 'company_name' ) );
            $website = sanitize_url( $this->request->get( 'website' ) );
            $dot_mc = sanitize_text_field( $this->request->get( 'dot_mc' ) );
            $address = sanitize_textarea_field( $this->request->get( 'address' ) );

            // Update company data in subscription data
            $existing_data['company_name'] = $company_name;
            $existing_data['website'] = $website;
            $existing_data['dot_mc'] = $dot_mc;
            $existing_data['address'] = $address;
            $existing_data['about'] = $about;
        }

        // Update subscription data
        update_user_meta( $user_id, 'adjuster_forge_subscription_data', $existing_data );

        return $this->response([
            'message' => 'Profile updated successfully',
            'status' => 'success',
        ], 200);
    }

    public function change_password()
    {
        // Validate the input fields
        $errors = ValidatorHandler::validateChangePasswordRequest( $this->request );

        if (count($errors) > 0) {
            return $this->response($errors, 400);
        }

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $current_password = sanitize_text_field( $this->request->get( 'current_password' ) );
            $new_password =sanitize_text_field( $this->request->get( 'new_password' ) );

            // Get the current user object
            $user = get_userdata( $user_id );

            // Check if the current password is correct
            if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
                return $this->response('Current password is incorrect', 400);
            }

            // Update the user's password
            wp_set_password( $new_password, $user_id );

            return $this->response('Password changed successfully', 200);
        } else {
            return $this->response('You must need to logged in', 400);
        }
    }

    public function adjusters_list( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $license_classes = sanitize_text_field($request->get_param('license_classes'));
        $endorsements = sanitize_text_field($request->get_param('endorsements'));
        $vehicles = sanitize_text_field($request->get_param('vehicles'));
        $experience = intval($request->get_param('experience'));
        $availability = sanitize_text_field($request->get_param('availability'));

        // Convert comma-separated string to array
        $license_classes_arr = $license_classes !== '' ? explode(',', $license_classes) : [];
        $endorsements_arr = $endorsements !== '' ? explode(',', $endorsements) : [];
        $vehicles_arr = $vehicles !== '' ? explode(',', $vehicles) : [];
        $availability_arr = $availability !== '' ? explode(',', $availability) : [];

        $params = [
            'search'             => sanitize_text_field($request->get_param('search')),
            'city'               => sanitize_text_field($request->get_param('city')),
            'state'              => sanitize_text_field($request->get_param('state')),
            'license_classes'    => $license_classes_arr,
            'endorsements'       => $endorsements_arr,
            'vehicles'           => $vehicles_arr,
            'experience'         => $experience,
            'availability'       => $availability_arr,
        ];

        $user = new User();
        //If request has filters
        $filteredParams = array_filter($params);
        if( ! empty( $filteredParams ) ) {
            $users = $user->filterUsers( $limit, $offset, $filteredParams, 'adjuster' );
            $total_items = count( $users);
        } else {
            $users = $user->getAllByLimit( $limit, $offset,  'adjuster' );
            $total_items = count($user->getAll());
        }

        
        $users_data = [];
        foreach ($users as $key => $item) {
            $profile_data = get_user_meta($item->ID, 'adjuster_forge_subscription_data', true);
            if ( ! is_array($profile_data) ) {
                $profile_data = [];
            }
            $user_status          = self::get_user_status( $profile_data );

            if ( $user_status == 'not_completed' ) {
                $subscription = new Subscription();
                $customer_id = isset($profile_data['customer_id']) ? $profile_data['customer_id'] : '';
                $row = $subscription->getSubscriberByIdandCustomerId($item->ID, $customer_id);
                if ( $row ) {
                    $user_status = $row->status;
                }
            }

            if( $user_status != 'active' ) 
                continue;

            $users_data[] = (object)[
                'user_id'                   => $item->ID,
                'username'                  => $item->user_login,
                'user_email'                => $item->user_email,
                'first_name'                => get_user_meta( $item->ID, 'af_first_name', true ),
                'last_name'                 => get_user_meta( $item->ID, 'af_last_name', true ),
                'city'                      => get_user_meta( $item->ID, 'af_city', true ),
                'state'                     => get_user_meta( $item->ID, 'af_state', true ),
                'profile_picture'           => get_user_meta( $item->ID, 'profile_picture', true ),
                'phone'                     => isset($profile_data['phone']) ? $profile_data['phone'] : '',
                'resume'                    => isset($profile_data['resume']) ? $profile_data['resume'] : '',
                'medical_card'              => isset($profile_data['medical_card']) ? $profile_data['medical_card'] : '',
                'mvr'                       => isset($profile_data['mvr']) ? $profile_data['mvr'] : '',
                'references'                => isset($profile_data['references']) ? $profile_data['references'] : '',
                'profile_completed'         => isset($profile_data['profile_completed']) ? $profile_data['profile_completed'] : false,
                'profile_completed_at'      => isset($profile_data['profile_completed_at']) ? $profile_data['profile_completed_at'] : '',
                'paid_subscription_fee'     => isset($subscription_data['paid_subscription_fee']) ? $subscription_data['paid_subscription_fee'] : false,
                'paid_subscription_fee_at'  => isset($subscription_data['paid_subscription_fee_at']) ? $subscription_data['paid_subscription_fee_at'] : '',
                'subscription_type'         => isset($subscription_data['subscription_type']) ? $subscription_data['subscription_type'] : '',
                'subscription_expire_at'    => isset($subscription_data['subscription_expire_at']) ? $subscription_data['subscription_expire_at'] : '',
                'subscription_canceled'     => isset($subscription_data['subscription_canceled']) ? $subscription_data['subscription_canceled'] : false,
                'subscription_canceled_at'  => isset($subscription_data['subscription_canceled_at']) ? $subscription_data['subscription_canceled_at'] : '',
                'customer_id'               => isset($subscription_data['customer_id']) ? $subscription_data['customer_id'] : '',
                'transaction_id'            => isset($subscription_data['transaction_id']) ? $subscription_data['transaction_id'] : '',
                'status'                    => $user_status,
            ];
        }

        $data = [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $total_items,
            'adjusters_list' => $users_data
        ];

        return $this->response([
            'data' =>  $data,
            'message' => 'adjusters list retrived successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Activate free trial for the user
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function activate_free_trial( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => 'You must be logged in.',
                'status' => 'error',
            ], 401);
        }

        $user_id        = get_current_user_id();
        $user_type      = get_user_meta($user_id, 'af_user_type', true);
        $id             = intval($this->request->get('user_id'));
        $user_email     = sanitize_email($this->request->get('user_email'));
        $name           = sanitize_text_field($this->request->get('name'));
        $customer_id    = sanitize_text_field($this->request->get('customer_id'));

        if( $id != $user_id ) {
            return $this->response([
                'message' => 'You are not authorized to activate free trial for someone else.',
                'status' => 'error',
            ], 403);
        }

        $subscription_model = new Subscription();
        $subscriber = $subscription_model->getSubscriptionByUserId( $user_id );

        if ( ! $subscriber ) {
            // Create a new subscription record
            $id = $subscription_model->store([
                'user_id' => $user_id,
                'customer_id' => $customer_id ? $customer_id : 'free_trial',
                'status' => 'active',
                'subscription_interval' => 'days',
                'paid_date' => current_time('mysql'),
                'expire_at' => date('Y-m-d H:i:s', strtotime('+3 days')),
                'created_at' => current_time('mysql'),
            ]);
            if ( ! $id ) {
                return $this->response([
                    'message' => 'Failed to create subscription record.',
                    'status' => 'error',
                ], 200);
            }
        } else {
            // Update the existing subscription record
            $updatedData = [
                'customer_id' => $subscriber->customer_id ? $subscriber->customer_id : 'free_trial',
                'status' => 'active',
                'paid_date' => current_time('mysql'),
                'expire_at' =>  date('Y-m-d H:i:s', strtotime('+3 days')),
            ];

            $subscription_model->update( $updatedData, $subscriber->id );
        }

        // Store subscription details in database
        (new SubscriptionHistory())->store([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'plan_name' => 'Free Trial',
            'amount' => 0,
            'currency' => 'USD',
            'payment_status' => 'active',
            'customer_id' => $customer_id ?? 'free_trial',
            'transaction_id' => 'free_trial',
            'created_at' => current_time('mysql'),
        ]);

        $existing_data = get_user_meta($user_id, 'adjuster_forge_subscription_data', true);

        $plan_type = 'free_trial';
        if ( ! empty( $existing_data ) ) {
            $existing_data['paid_subscription_fee']     = false;
            $existing_data['paid_subscription_fee_at']  = '';
            $existing_data['transaction_id']            = 'free_trial';
            $existing_data['subscription_type']         = 'Free Trial';
            $existing_data['subscription_expire_at']    = date('Y-m-d H:i:s', strtotime('+3 days'));
            $existing_data['customer_id']               = $customer_id ?? 'free_trial';
            $existing_data['plan_type']                 = $plan_type;
            $existing_data['account_status'] = 'active';
        } else {
            $existing_data = [
                'profile_completed'         => true,
                'profile_completed_at'      => current_time('mysql'),
                'user_type'                 => $user_type,
                'customer_id'               => $customer_id ?? 'free_trial',
                'paid_subscription_fee'     => false,
                'paid_subscription_fee_at'  => current_time('mysql'),
                'transaction_id'            => 'free_trial',
                'subscription_type'         => 'Free Trial',
                'subscription_expire_at'    => date('Y-m-d H:i:s', strtotime('+3 days')),
                'account_status'            => 'active',
                'plan_type'                 => $plan_type,
            ];
        }
        update_user_meta($user_id, 'adjuster_forge_subscription_data', $existing_data);

        return $this->response([
            'message' => 'Free trial activated successfully.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Get payment history
     */
    public function get_payment_history( \WP_REST_Request $request )
    {
        $user_id = get_current_user_id();
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $search = sanitize_text_field($request->get_param('search'));
        $shModel = new SubscriptionHistory();
        $payment_history = $shModel->getHistory( $user_id, $limit, $offset, $search );
        if( ! empty( $search ) ) {
            $total_items = count( $payment_history );
        } else {
            $total_items = count($shModel->getAllHistory($user_id));
        }

        $data = [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $total_items,
            'history' => $payment_history
        ];

        return $this->response([
            'data' =>  $data,
            'message' => 'Payment History retrieved successfully.',
            'status' => 'success',
        ], 200);
    }
}
