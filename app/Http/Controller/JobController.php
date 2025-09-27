<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Http\Request\ValidatorHandler;
use SmartySoft\AdjusterForge\Http\Model\JobApplication;
use SmartySoft\AdjusterForge\Http\Controller\BaseController;

class JobController extends BaseController
{
    /**
     * Get jobs from the 'job' custom post type.
     * Returns an array of jobs with basic fields.
     */
    public function get_jobs( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'You must be logged in to get job list.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 401);
        }

        $user = wp_get_current_user();
        if ( in_array( 'adjuster', (array) $user->roles ) ) {
            return $this->response([
                'message' => __( 'You do not have permission to get job list.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }

        $author_id = $user->ID;
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $args = [
            'post_type'      => 'job',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'     => '_job_author',
                    'value'   => $author_id,
                    'compare' => '='
                ]
            ],
        ];

        $query = new \WP_Query($args);

        $jobs = [];
        foreach ($query->posts as $post) {
            $thumbnail = get_post_meta($post->ID, '_job_attachment', true);
            $applicant_count = get_post_meta($post->ID, '_job_applicant_count', true);
            $jobs[] = [
                'ID'        => $post->ID,
                'title'     => $post->post_title,
                'content'   => $post->post_content,
                'author'    => $post->post_author,
                'date'      => $post->post_date,
                'thumbnail' => $thumbnail ? $thumbnail : '',
                'applicant' => $applicant_count ? intval($applicant_count) : 0,
            ];
        }
        $total_items = $query->found_posts;

        wp_reset_postdata();

        $data = [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $total_items,
            'jobs' => $jobs
        ];

        return $this->response([
            'data' => $data,
            'status' => 'success',
        ], 200);
    }

    public function create_post()
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'You must be logged in to create a job.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 401);
        }
        $user = wp_get_current_user();
        $author_id = $user->ID;
        $user_type = get_user_meta($author_id, 'af_user_type', true);
        $user_roles = (array) $user->roles;
        $plan_type = '';
        $subscription_data = get_user_meta($author_id, 'adjuster_forge_subscription_data', true);
        if ( is_array($subscription_data ) && isset($subscription_data['plan_type'])) {
            $plan_type = $subscription_data['plan_type'];
        }

        // Only allow company and premium company to post jobs, but restrict premium company to 1 job/month
        if ($user_type === 'company') {
            // Check if this user is premium
            $is_premium = $plan_type === 'premium';
            if ($is_premium) {
                // Check if this premium user already posted a job this month
                $job_count = self::get_user_job_count_for_current_month($author_id);
                if ($job_count >= 1) {
                    return $this->response([
                        'message' => __( 'Premium users can only post one job per month.', 'adjuster-forge' ),
                        'status'  => 'error',
                    ], 200);
                }
            } else {
                return $this->response([
                    'message' => __( 'Only premium company users can create jobs.', 'adjuster-forge' ),
                    'status'  => 'error',
                ], 200);
            }
        } else {
            // Not a company or adjuster, block
            return $this->response([
                'message' => __( 'You do not have permission to create a job.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 200);
        }

        $title = sanitize_text_field( $this->request->get( 'title' ) );
        $content = sanitize_textarea_field( $this->request->get( 'content' ) );
        if ( empty( $title ) || empty( $content ) ) {
            return $this->response([
                'message' => __( 'Title and content are required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 400);
        }

        $post_id = wp_insert_post( [
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'job',
            'post_status'  => 'publish',
            'post_author'  => $author_id,
            'meta_input'   => [
                '_job_author' => $author_id,
            ],
        ] );

        if ( is_wp_error( $post_id ) ) {
            return $this->response([
                'message' => $post_id->get_error_message(),
                'status'  => 'error',
            ], 400);
        }

        if( isset($_FILES['job_attachment']) || !empty($_FILES['job_attachment']['name']) ) {
           // Handle event file upload
            $job_attachment = self::handle_file_upload('job_attachment');

            if ( is_wp_error( $job_attachment ) ) {
                return $this->response($job_attachment->get_error_message(), 400);
            }

            if ( ! empty( $job_attachment ) ) {
                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                // Use wp_upload_dir to get the upload directory
                $upload_dir = wp_upload_dir();
                $job_attachment_url = trailingslashit($upload_dir['baseurl']) . ADJUSTER_FORGE_PLUGIN_ASSET_ID . '/' . ltrim($job_attachment, '/');
                // Update the post meta with the job attachment URL
                update_post_meta( $post_id, '_job_attachment', $job_attachment_url );
            }
        }
        
        return $this->response([
            'data'   => $post_id,
            'status' => 'success',
        ], 201);
    }

    public function get_public_jobs( \WP_REST_Request $request )
    {
        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $applied_job_ids = (new JobApplication())->getAllAppliedJobId();
        $args = [
            'post_type'      => 'job',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if( ! empty( $applied_job_ids ) ) {
            $args['post__not_in'] = $applied_job_ids;
        }

        $query = new \WP_Query($args);

        $thumbnail      = ADJUSTER_FORGE_PLUGIN_URL . 'assets/img/thumbnail.jpg';
        $jobs = [];
        foreach ($query->posts as $post) {
            $attachment = get_post_meta($post->ID, '_job_attachment', true);
            $applicant_count = get_post_meta($post->ID, '_job_applicant_count', true);
            $author_id = $post->post_author;
            $author_meta = get_userdata($author_id);
            $applied = (new JobApplication())->getByJobId($post->ID);
            $jobs[] = [
                'ID'      => $post->ID,
                'title'   => $post->post_title,
                'content' => $post->post_content,
                'author'  => $author_meta ? $author_meta->display_name : '',
                'date'    => $post->post_date,
                'thumbnail' => $thumbnail,
                'attachment' => $attachment ? $attachment : '',
                'applicant'  => $applicant_count ? intval($applicant_count) : 0,
                'is_applied' => $applied ? true : false,
            ];
        }
        $total_items = $query->found_posts;

        wp_reset_postdata();

        return $this->response([
            'data' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => $total_items,
                'jobs'         => $jobs
            ],
            'status' => 'success',
        ], 200);
    }

    public function get_applied_jobs( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'You must be logged in to get applied jobs.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 401);
        }

        $page   = intval($request->get_param('page') );
        $limit  = intval($request->get_param('per_page') );
        $offset = ($page - 1) * $limit;
        $applied_job_ids = (new JobApplication())->getAllAppliedJobId();

        if( empty( $applied_job_ids ) ) {
            return $this->response([
                'data' => [
                    'current_page' => $page,
                    'per_page'     => $limit,
                    'total_items'  => 0,
                    'jobs'         => []
                ],
                'status' => 'success',
            ], 200);
        }
        $args = [
            'post_type'      => 'job',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__in'       => $applied_job_ids,
        ];

        $query = new \WP_Query($args);

        $thumbnail      = ADJUSTER_FORGE_PLUGIN_URL . 'assets/img/thumbnail.jpg';
        $jobs = [];
        foreach ($query->posts as $post) {
            $attachment = get_post_meta($post->ID, '_job_attachment', true);
            $applicant_count = get_post_meta($post->ID, '_job_applicant_count', true);
            $author_id = $post->post_author;
            $author_meta = get_userdata($author_id);
            $application = (new JobApplication())->getByJobId($post->ID);
            $jobs[] = [
                'ID'      => $post->ID,
                'title'   => $post->post_title,
                'content' => $post->post_content,
                'author'  => $author_meta ? $author_meta->display_name : '',
                'date'    => $post->post_date,
                'thumbnail' => $thumbnail,
                'attachment' => $attachment ? $attachment : '',
                'applicant'  => $applicant_count ? intval($applicant_count) : 0,
                'is_applied' => $application ? true : false,
                'application' => $application,
            ];
        }
        $total_items = $query->found_posts;

        wp_reset_postdata();

        return $this->response([
            'data' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => $total_items,
                'jobs'         => $jobs
            ],
            'status' => 'success',
        ], 200);

    }

    public function get_job_details( \WP_REST_Request $request )
    {
        $job_id = intval($request->get_param('id'));
        
        if (!$job_id) {
            return $this->response([
                'message' => __('Job ID is required.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        $post = get_post($job_id);
        
        if (!$post || $post->post_type !== 'job' || $post->post_status !== 'publish') {
            return $this->response([
                'message' => __('Job not found.', 'adjuster-forge'),
                'status'  => 'error',
            ], 404);
        }

        $attachment         = get_post_meta($post->ID, '_job_attachment', true);
        $applicant_count    = get_post_meta($post->ID, '_job_applicant_count', true);
        $author_meta        = get_userdata($post->post_author);
        $thumbnail          = $attachment ? $attachment : ADJUSTER_FORGE_PLUGIN_URL . 'assets/img/thumbnail.jpg';
        $existing_application = (new JobApplication())->getByJobId($job_id);
        $application        = (new JobApplication())->getByJobId($post->ID);
        $author_id          = $post->post_author;
        $subscription_data  = get_user_meta($author_id, 'adjuster_forge_subscription_data', true);
        if ( ! is_array( $subscription_data ) ) {
            $subscription_data = [];
        }
        $job_details = [
            'ID'         => $post->ID,
            'title'      => $post->post_title,
            'content'    => $post->post_content,
            'author'     => $author_meta ? $author_meta->display_name : '',
            'author_id'  => $post->post_author,
            'date'       => $post->post_date,
            'thumbnail'  => $thumbnail,
            'attachment' => $attachment ? $attachment : '',
            'applicant'  => $applicant_count ? intval($applicant_count) : 0,
            'is_applied' => $existing_application ? true : false,
            'application' => $application,
            'company_data' => [
                'company_name' => $subscription_data['company_name'] ?? '',
                'website'      => $subscription_data['website'] ?? '',
                'address'      => $subscription_data['address'] ?? '',
                'phone'        => $subscription_data['phone'] ?? '',
                'dot_mc'       => $subscription_data['dot_mc'] ?? '',
                'logo'         => get_user_meta($author_id, 'profile_picture', true),
            ]
        ];

        return $this->response([
            'data'   => $job_details,
            'status' => 'success',
        ], 200);
    }

    public function apply_job( \WP_REST_Request $request )
    {
        if (!is_user_logged_in()) {
            return $this->response([
                'message' => __('You must be logged in to apply for a job.', 'adjuster-forge'),
                'status'  => 'error',
            ], 401);
        }

        $user = wp_get_current_user();
        $job_id = intval($request->get_param('job_id'));
        $cover_letter = sanitize_textarea_field($request->get_param('cover_letter'));

        if ( ! $job_id ) {
            return $this->response([
                'message' => __('Job ID is required.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        // Check if job exists
        $job = get_post($job_id);
        if ( !$job || $job->post_type !== 'job' ) {
            return $this->response([
                'message' => __('Job not found.', 'adjuster-forge'),
                'status'  => 'error',
            ], 404);
        }

        $application  = new JobApplication();

        // Check if user already applied
        $existing_application = $application->getByJobId( $job_id );

        if ( ! empty( $existing_application ) ) {
            return $this->response([
                'message' => __('You have already applied for this job.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        // Create job application
        $data = [
            'job_id' => $job_id,
            'applicant_id' => $user->ID,
            'cover_letter' => $cover_letter,
            'status' => 'pending',
            'application_date' => current_time('mysql'),
        ];

        $application_id = $application->store($data);

        $applicant_count = get_post_meta($job_id, '_job_applicant_count', true);
        $applicant_count = $applicant_count ? intval($applicant_count) + 1 : 1;
        update_post_meta( $job_id, '_job_applicant_count', $applicant_count );

        if ($application_id === false) {
            return $this->response([
                'message' => __('Failed to submit application.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        return $this->response([
            'message' => __('Application submitted successfully!', 'adjuster-forge'),
            'data'    => $application_id,
            'status'  => 'success',
        ], 201);
    }

    public function get_job_applicants( \WP_REST_Request $request )
    {
        $job_id = intval($request->get_param('jobId'));
        $page   = intval($request->get_param('page') ) ? intval($request->get_param('page') ) : 1;
        $limit  = intval($request->get_param('per_page') ) ? intval($request->get_param('per_page') ) : 10;
        $offset = ($page - 1) * $limit;

        if (!$job_id) {
            return $this->response([
                'message' => __('Job ID is required.', 'adjuster-forge'),
                'status'  => 'error',
            ], 400);
        }

        $post = get_post($job_id);
        
        if (!$post || $post->post_type !== 'job' || $post->post_status !== 'publish') {
            return $this->response([
                'message' => __('Job not found.', 'adjuster-forge'),
                'status'  => 'error',
            ], 404);
        }
        $jobDetails = [
            'ID'         => $post->ID,
            'title'      => $post->post_title,
            'content'    => $post->post_content,
            'author'     => get_userdata($post->post_author)->display_name,
            'date'       => $post->post_date,
            'thumbnail'  => get_post_meta($post->ID, '_job_attachment', true) ?: ADJUSTER_FORGE_PLUGIN_URL . 'assets/img/thumbnail.jpg',
            'applicant'  => $applicant_count ? intval($applicant_count) : 0,
        ];
        $applicant_count = get_post_meta($post->ID, '_job_applicant_count', true);
        $applicants = [];
        if (! $applicant_count || $applicant_count <= 0) {
            return $this->response([
                'message' => __('No applicants found for this job.', 'adjuster-forge'),
                'status'  => 'success',
                'data'    => [
                    'jobDetails' => $jobDetails,
                    'applicants' => [],
                ],
            ], 200);
        }
        // Get applicants for the job
        $applicants = ( new JobApplication() )->getApplicantsByJobId($job_id, $limit, $offset);

        if (empty($applicants)) {
            return $this->response([
                'message' => __('No applicants found for this job.', 'adjuster-forge'),
                'status'  => 'success',
                'data'    => [
                    'jobDetails' => $jobDetails,
                    'applicants' => [],
                ],
            ], 200);
        }

        return $this->response([
            'data'   => [
                'jobDetails' => $jobDetails,
                'applicants' => $applicants,
            ],
            'status' => 'success',
        ], 200);
    }

    public function add_applicant_note( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'You must be logged in to add a note.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 401);
        }

        $applicant_id = intval($request->get_param('applicant_id'));
        $note = sanitize_textarea_field($request->get_param('note'));

        if ( ! $applicant_id || empty($note) ) {
            return $this->response([
                'message' => __( 'Applicant ID and Note are required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 400);
        }

        $application =  (new JobApplication() )->find( $applicant_id );

        if( ! $application ) {
            return $this->response([
                'message' => __( 'Application not found.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 404);
        }

        // Add note to the job application
        $model = JobApplication::getInstance();
        $model->update([
            'employer_notes' => $note,
            'updated_at' => current_time('mysql'),
        ], $applicant_id);
        
        return $this->response([
            'message' => __( 'Note added successfully.', 'adjuster-forge' ),
            'status'  => 'success',
        ], 200);
    }

    public function update_applicant_status( \WP_REST_Request $request )
    {
        if ( ! is_user_logged_in() ) {
            return $this->response([
                'message' => __( 'You must be logged in to update applicant status.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 401);
        }

        $applicant_id = intval($request->get_param('applicant_id'));
        $status = sanitize_text_field($request->get_param('status'));

        if ( ! $applicant_id || empty($status) ) {
            return $this->response([
                'message' => __( 'Applicant ID and Status are required.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 400);
        }

        $application = (new JobApplication())->find($applicant_id);

        if ( ! $application ) {
            return $this->response([
                'message' => __( 'Application not found.', 'adjuster-forge' ),
                'status'  => 'error',
            ], 404);
        }

        // Update the status of the job application
        $model = JobApplication::getInstance();
        $model->update([
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ], $applicant_id);

        return $this->response([
            'message' => __( 'Applicant status updated successfully.', 'adjuster-forge' ),
            'status'  => 'success',
        ], 200);
    }
}