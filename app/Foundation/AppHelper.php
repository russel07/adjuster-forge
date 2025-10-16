<?php

namespace SmartySoft\AdjusterForge\Foundation;

use SmartySoft\AdjusterForge\Http\Model\Subscription;
use SmartySoft\AdjusterForge\Services\UserStatusService;
trait AppHelper
{
    public static function adjuster_forge_app_vars()
    {
        $af_general_settings = self::getOption('adjuster_forge_general_settings', []);
        $home_url           = home_url();
        $upload_dir         = wp_upload_dir();
        $uploadUrl          = esc_url($upload_dir['baseurl']) . '/' . ADJUSTER_FORGE_PLUGIN_ASSET_ID;
        $lost_password_url  = wp_lostpassword_url();
        $logout_url         = wp_logout_url();
        $profile_page_id    = isset($af_general_settings['profile_page_id']) ? $af_general_settings['profile_page_id'] : '';
        $auth_page_id       = isset($af_general_settings['auth_page_id']) ? $af_general_settings['auth_page_id'] : '';
        $profile_page_url   = get_permalink($profile_page_id);
        $auth_page_url      = get_permalink($auth_page_id);
        $t_c_page_url       = esc_url($home_url . '/terms-and-conditions');
        $currency           = isset($af_general_settings['selected_currency']) ? $af_general_settings['selected_currency'] : 'USD';
        $expire_at          = date('Y-m-d');
        $user_data          = [];
        $subscription_data  = [];
        $user_status        = '';
        $plan_type          = '';
        $app_logo           = ADJUSTER_FORGE_PLUGIN_URL . 'assets/img/logo.svg';
        if ( is_user_logged_in() ) {
            $current_user       = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_type = get_user_meta($user_id, 'af_user_type', true);
            $firest_name = get_user_meta($user_id, 'af_first_name', true);
            $last_name = get_user_meta($user_id, 'af_last_name', true);
            $user_name = $firest_name . ' ' . $last_name;
            $user_email = $current_user->user_email;
            $user_data = [
                'user_id' => $user_id,
                'user_type' => $user_type,
                'user_name' => $user_name,
                'user_email' => $user_email,
            ];
            $job_this_month = self::get_user_job_count_for_current_month($user_id);
            
            // Use the new UserStatusService
            $statusService = UserStatusService::getInstance();
            $user_status_data = $statusService->getUserStatus($user_id);
            
            $user_status = $user_status_data['status'];
            $subscription_data = $user_status_data['subscription_data'];
            $expire_at = $user_status_data['expires_at'] ?? date('Y-m-d');
            
            if (isset($subscription_data['plan_type'])) {
                $plan_type = $subscription_data['plan_type'];
            } elseif ($user_type === 'adjuster') {
                $plan_type = '';
            } else {
                $plan_type = 'no_plan';
            }
            if( ! is_date($expire_at) ) {
                $expire_at = date('Y-m-d');
            }
        }
        
        $expire_timestamp  = strtotime($expire_at);
        $now_timestamp     = strtotime(date('Y-m-d'));
        $is_expired        = $expire_timestamp < $now_timestamp ? true : false;
        $day_remaining     = floor(($expire_timestamp - $now_timestamp) / (60 * 60 * 24));
        $expire_date       = is_date($expire_at) ? date_i18n(get_option('date_format'), $expire_timestamp) : date_i18n(get_option('date_format'), $now_timestamp);
        $payment_settings  = self::getOption('adjuster_forge_payment_settings', []);
        $stripe_public_key = isset($payment_settings['stripe_public_key']) ? $payment_settings['stripe_public_key'] : '';
        
        if( $user_status === "cancelled" && ! $is_expired ) {
            $user_status = 'active';
        } 

        return array(
            'home_url'          => $home_url,
            'ajax_url'          => admin_url('admin-ajax.php'),
            'slug'              => 'adjuster-forge',
            'plugin_assets'     => ADJUSTER_FORGE_PLUGIN_URL . 'assets/',
            'image_url'         => $uploadUrl,
            'is_logged_in'      => is_user_logged_in(),
            'user_data'         => $user_data,
            'subscription_data' => $subscription_data,
            'lost_password'     => $lost_password_url,
            'logout_url'        => $logout_url,
            'auth_page'         => $auth_page_url,
            'profile_page'      => $profile_page_url,
            't_c_page'          => $t_c_page_url,
            'remaining_days'    => $day_remaining,
            'stripe_public_key' => $stripe_public_key,
            'is_expired'        => $is_expired,
            'user_status'       => $user_status,
            'expire_date'       => $expire_date,
            'plan_type'         => $plan_type,
            'job_this_month'    => isset($job_this_month) ? $job_this_month : 0,
            'rest_info'         => array (
                'base_url'      => esc_url_raw( rest_url() ),
                'rest_url'      => rest_url('adjuster-forge' . '/v2'),
                'nonce'         => wp_create_nonce('wp_rest'),
                'namespace'     => 'adjuster-forge',
                'version'       => 'v2',
            ),
            'app_logo'          => $app_logo,
        );
    }


    public static function get_user_job_count_for_current_month($author_id) 
    {
        global $wpdb;
        $table = $wpdb->posts;
        $start_of_month = date('Y-m-01 00:00:00');
        $now = current_time('mysql');
        $job_count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE post_type = 'job' AND post_status = 'publish' AND post_author = %d AND post_date >= %s AND post_date <= %s",
            $author_id, $start_of_month, $now
        ));

        return $job_count ? intval($job_count) : 0;
    }

    /**
     * getOption method will return settings using key
     * This method will get a key as parameter, fetch data from the database, and return it.
     *
     * @param string $key The option key to retrieve.
     * @param mixed $default The default value to return if the option is not found.
     * @return mixed The option value or the default value.
     */
    public static function getOption($key, $default = '')
    {
        // Get settings from options table using the key
        $data = get_option($key);

        // If the data exists, return it; otherwise, return the default value
        return $data !== false ? maybe_unserialize($data) : $default;
    }

    /**
     * Update or insert an option in the wp_options table.
     * This method serializes the data if necessary.
     *
     * @param string $key   The option key to update.
     * @param mixed  $value The value to store.
     * @return bool True if the value was updated successfully, false otherwise.
     */
    public static function updateOption($key, $value)
    {
        // Serialize the value if it's an array or object.
        return update_option($key, maybe_serialize($value));
    }

    /**
     * Retrieve all published WordPress pages and format them as an array.
     *
     * This function queries the WordPress database to retrieve all pages
     * with a post type of 'page' and a post status of 'publish'.
     * It returns an array of formatted pages containing their ID and title.
     *
     * @return array $formattedPages An array of formatted pages with 'id' and 'title'.
     */
    public static function getWPPages()
    {
        global $wpdb;

        // Query to get all published pages
        $pages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title
                FROM {$wpdb->prefix}posts
                WHERE post_type = %s
                AND post_status = %s
                ORDER BY ID DESC",
                'page',
                'publish'
            )
        );

        // Initialize the formatted pages array
        $formattedPages = [];

        // Loop through each page and format the output
        foreach ($pages as $page) {
            $formattedPages[] = [
                'id'    => strval($page->ID),   // Convert ID to string as per requirement
                'title' => $page->post_title
            ];
        }

        return $formattedPages;
    }

    public static function getAllowedCurrency()
    {
        return apply_filters('adjuster_forge_allowed_currency', array(
            'USD' => 'USD',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
        ));
    }

    public static function getAllowedCurrencySymble()
    {
        return apply_filters('adjuster_forge_allowed_currency_symble', array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ));
    }

    /**
     * getProfilePageUrl will get the Profile Page id and return link of the page
     * @return mixed
     */
    public static function getProfilePageUrl()
    {
        $settings = self::getOption('adjuster_forge_general_settings', []);

        if (!empty($settings) && isset($settings['profile_page_id'])) {
            $page_id = $settings['profile_page_id'];
            $baseUrl = get_permalink($page_id);
            $baseUrl = rtrim($baseUrl, '/\\');
        } else {
            $baseUrl = '';
        }

        return apply_filters('adjuster_forge_profile_page_url', $baseUrl);
    }

    /**
     * Handle file uploads.
     *
     * @param string $file_key
     * @return string|WP_Error The file name of the uploaded file, or WP_Error on failure
     */
    public static function handle_file_upload( $file_key, $upload_directory = ADJUSTER_FORGE_PLUGIN_ASSET_ID )
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        // Check if the file was uploaded successfully
        //Nonce already validated in the calling function
        if (!isset($_FILES[$file_key]) || !isset($_FILES[$file_key]['error']) || $_FILES[$file_key]['error'] != UPLOAD_ERR_OK) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
            return new \WP_Error('file_upload_error', 'File upload failed.');
        }

        // Get the WordPress upload directory
        $upload_dir = wp_upload_dir();
        $custom_upload_dir = $upload_dir['basedir'] . '/' . $upload_directory;

        // Ensure the directory exists, if not, create it
        if ( ! file_exists( $custom_upload_dir ) ) {
            wp_mkdir_p( $custom_upload_dir );
        }

        // Temporarily change the upload directory
        add_filter('upload_dir', function ($dirs) use ($custom_upload_dir, $upload_dir, $upload_directory) {
            $dirs['path'] = $custom_upload_dir;
            $dirs['url'] = $upload_dir['baseurl'] . '/' . $upload_directory;
            $dirs['subdir'] = '';
            return $dirs;
        });

        // Handle the file upload
        //nonce already validated in the calling function
        $uploaded_file = wp_handle_upload($_FILES[$file_key], ['test_form' => false]); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        // Remove the filter to restore default upload directory
        remove_filter('upload_dir', '__return_empty_array');

        if (isset($uploaded_file['error'])) {
            return new \WP_Error('upload_error', $uploaded_file['error']);
        }

        // File renaming with a unique prefix
        $file_path = $uploaded_file['file'];
        $file_info = pathinfo($file_path);
        $new_file_name = uniqid('dfsd_', true) . '.' . $file_info['extension'];
        $new_file_path = $file_info['dirname'] . '/' . $new_file_name;

        // Initialize the WordPress filesystem
        global $wp_filesystem;
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        WP_Filesystem();

        if (!$wp_filesystem->move($file_path, $new_file_path)) {
            return new WP_Error('move_error', 'Failed to move the uploaded file.');
        }

        return $new_file_name;
    }


    /**
     * Generate a unique card number.
     *
     * This function generates a unique card number consisting of three groups of random numbers.
     * The first group contains 6 digits, and the second and third groups contain 4 digits each.
     * The groups are concatenated with spaces.
     *
     * @return string The generated unique card number.
     */
    public static function you_generate_unique_number() {
        // Generate three groups of random numbers
        $group1 = mt_rand(100000, 999999); // 6 digits
        $group2 = mt_rand(1000, 9999);     // 4 digits
        $group3 = mt_rand(1000, 9999);     // 4 digits

        // Concatenate the groups with spaces
        $unique_number = sprintf('%06d %04d %04d', $group1, $group2, $group3);

        return $unique_number;
    }

    /**
     * Get the user status based on subscription data.
     *
     * @param array $status_data The subscription status data.
     * @return string The user status (active, inactive, expired, or canceled).
     */
    public static function get_user_status( $status_data ) {
        if( $status_data === null || !is_array($status_data) ) {
            return 'not_completed';
        }

        if( isset($status_data['status']) && !empty($status_data['status']) ) {
            return $status_data['status'];
        }

        if( isset($status_data['account_status']) && !empty($status_data['account_status']) ) {
            return $status_data['account_status'];
        }

        if( isset($status_data['subscription_canceled']) && $status_data['subscription_canceled'] ) {
            return 'canceled';
        } else if( isset($status_data['subscription_expire_at']) && !empty($status_data['subscription_expire_at']) ) {
            $expire_date = strtotime($status_data['subscription_expire_at']);
            if( $expire_date < time() ) {
                return 'expired';
            }
            return 'active';
        } elseif( isset($status_data['paid_subscription_fee']) && $status_data['paid_subscription_fee'] ) {
            return 'pending_review';
        }
        return 'not_completed';
    }

    private static function get_company_status($meta)
    {
        if (empty($meta['paid_subscription_fee'])) {
            return 'due_subscription';
        }
        
        return self::check_subscription_expiry($meta);
    }

    private static function get_adjuster_status($meta)
    {
        // Check verification fee first
        if (empty($meta['paid_verification_fee'])) {
            return 'due_verification';
        }
        
        // Check subscription fee
        if (empty($meta['paid_subscription_fee'])) {
            return self::check_approval_status($meta);
        }
        
        return self::check_subscription_expiry($meta);
    }

    private static function check_approval_status($meta)
    {
        $approve_status = $meta['approve_status'] ?? '';
        
        if ($approve_status === 'approved') {
            return 'due_subscription';
        } elseif ($approve_status === 'rejected') {
            return 'rejected';
        }
        
        return 'submitted';
    }

    private static function check_subscription_expiry($meta)
    {
        $is_cancelled = !empty($meta['subscription_canceled']) ? $meta['subscription_canceled'] : false;
        if ($is_cancelled) {
            return 'cancelled';
        }
        $last_paid = !empty($meta['paid_subscription_fee_at']) ? strtotime($meta['paid_subscription_fee_at']) : 0;
        $expire_at = !empty($meta['subscription_expire_at']) ? strtotime($meta['subscription_expire_at']) : 0;
        $today = strtotime(date('Y-m-d'));
        
        // Invalid dates or expire_at is before last_paid
        if ($last_paid <= 0 || $expire_at <= 0 || $expire_at <= $last_paid) {
            return 'expired';
        }
        
        return ($expire_at >= $today) ? 'active' : 'expired';
    }

    public static function adjusters_stripe_plan( $settings = [] ) {
        return apply_filters('adjuster_forge_adjusters_stripe_plan', [
            'id' => '1',
            'name' => 'Free Trial',
            'price' => 0.00,
            'interval' => 'Monthly',
            'description' => 'Free Trial for new companies',
            'monthly_id' => isset($settings['adjuster_subscription_id']) ? $settings['adjuster_subscription_id'] : '',
            'monthly_name' => 'Adjuster Monthly Fee',
            'monthly_price' => isset($settings['adjuster_subscription_amount']) ? floatval($settings['adjuster_subscription_amount']) : 9.99,
            'monthly_interval' => 'Monthly',
            'yearly_id' => isset($settings['adjuster_yearly_subscription_id']) ? $settings['adjuster_yearly_subscription_id'] : '',
            'yearly_name' => 'adjuster Yearly Fee',
            'yearly_price' => isset($settings['adjuster_yearly_subscription_amount']) ? floatval($settings['adjuster_yearly_subscription_amount']) : 99.99,
            'yearly_interval' => 'Yearly',
            'description' => 'Essential features for new adjusters',
            'features' => [
                [
                    'icon' => 'check',
                    'title' => 'View up to 3 premium adjuster profiles (with limited visibility)',
                ],
                [
                    'icon' => 'cross',
                    'title' => 'No messaging',
                ],
                [
                    'icon' => 'cross',
                    'title' => 'No job posting',
                ],
                [
                    'icon' => 'check',
                    'title' => 'Designed as a teaser to show value and encourage upgrade',
                ],
            ]
        ]);
    }

    public static function companies_stripe_plan( $settings = [] ) {
        $free_trial = [
            'id' => 'free_trial',
            'name' => 'Free Trial',
            'price' => 0.00,
            'interval' => 'Monthly',
            'description' => 'Free Trial for new companies',
            'features' => [
                [
                    'icon' => 'check',
                    'title' => 'View up to 3 premium adjuster profiles (with limited visibility)',
                ],
                [
                    'icon' => 'cross',
                    'title' => 'No messaging',
                ],
                [
                    'icon' => 'cross',
                    'title' => 'No job posting',
                ],
                [
                    'icon' => 'check',
                    'title' => 'Designed as a teaser to show value and encourage upgrade',
                ],
            ]
        ];

        $paid_plans = [
            [
                'monthly_id' => isset($settings['standard_membership_id']) ? $settings['standard_membership_id'] : '2',
                'monthly_name' => 'Standard Company',
                'monthly_price' => isset($settings['standard_membership_amount']) ? floatval($settings['standard_membership_amount']) : 59.0,
                'monthly_interval' => 'Monthly',
                'yearly_id' => isset($settings['standard_yearly_membership_id']) ? $settings['standard_yearly_membership_id'] : '2',
                'yearly_name' => 'Standard Company',
                'yearly_price' => isset($settings['standard_yearly_membership_amount']) ? floatval($settings['standard_yearly_membership_amount']) : 599.0,
                'yearly_interval' => 'Yearly',
                'description' => 'Standard Company',
                'features' => [
                    [
                        'icon' => 'check',
                        'title' => 'Unlimited viewing of premium adjuster profiles',
                    ],
                    [
                        'icon' => 'check',
                        'title' => ' Unlimited direct messaging with adjusters',
                    ],
                    [
                        'icon' => 'cross',
                        'title' => 'No job posting included',
                    ],
                    [
                        'icon' => 'check',
                        'title' => 'Ideal for recruiters who already manage posting externally',
                    ],
                ]
            ],
            [
                'monthly_id' => isset($settings['premium_membership_id']) ? $settings['premium_membership_id'] : '3',
                'monthly_name' => 'Premium Company',
                'monthly_price' => isset($settings['premium_membership_amount']) ? floatval($settings['premium_membership_amount']) : 99.0,
                'monthly_interval' => 'Monthly',
                'yearly_id' => isset($settings['premium_yearly_membership_id']) ? $settings['premium_yearly_membership_id'] : '3',
                'yearly_name' => 'Premium Company',
                'yearly_price' => isset($settings['premium_yearly_membership_amount']) ? floatval($settings['premium_yearly_membership_amount']) : 999.0,
                'yearly_interval' => 'Yearly',
                'description' => 'Premium Company',
                'features' => [
                    [
                        'icon' => 'check',
                        'title' => 'Unlimited viewing and messaging of premium adjusters',
                    ],
                    [
                        'icon' => 'check',
                        'title' => '1 SEO-optimized job post per month',
                    ],
                    [
                        'icon' => 'check',
                        'title' => 'Schedule interviews directly with adjusters',
                    ],
                    [
                        'icon' => 'check',
                        'title' => 'Priority access to newly verified adjusters',
                    ],
                    [
                        'icon' => 'check',
                        'title' => 'Add-ons available (boosted posts, featured email, etc.)',
                    ]
                ]
            ]
        ];
        $user_id = get_current_user_id();
        $subscriptionModel = new Subscription();
        $row = $subscriptionModel->getSubscriptionByUserId($user_id);

        if( $row && $row->customer_id === 'free_trial') {
            $plans = $paid_plans;
        } else {
            $plans = array_merge([$free_trial], $paid_plans);
        }
        return apply_filters('adjuster_forge_companies_stripe_plan', $plans);
    }

    /**
     * Get the plan type based on the plan ID.
     * @param string $plan_id The plan ID to check.
     * @return string The plan type ('free_trial', 'standard', 'premium', or 'adjuster').
     */
    public static function get_plan_type( $plan_id ) {
        $settings = self::getOption('adjuster_forge_general_settings', []);
        if ( empty( $settings ) || empty( $plan_id ) ) {
            return 'free_trial';
        }

        $id = isset($settings['standard_membership_id']) ? $settings['standard_membership_id'] : '2';
        $yearly_id = isset($settings['standard_yearly_membership_id']) ? $settings['standard_yearly_membership_id'] : '2';
        if ($plan_id === $id || $plan_id === $yearly_id) {
            return 'standard';
        } else {
            $id = isset($settings['premium_membership_id']) ? $settings['premium_membership_id'] : '3';
            $yearly_id = isset($settings['premium_yearly_membership_id']) ? $settings['premium_yearly_membership_id'] : '3';
            if ($plan_id === $yearly_id || $plan_id === $id) {
                return 'premium';
            }
        }
        return 'adjuster';
    }
}
