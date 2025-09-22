<?php

namespace SmartySoft\AdjusterForge\Cpt;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Jobs
 *
 * Registers the Job custom post type with only basic fields.
 */
class Jobs {

    public function __construct() {
        add_action( 'init', [ $this, 'register_job_cpt' ] );
        // Flush rewrite rules on activation/deactivation
        register_activation_hook( __FILE__, [ $this, 'flush_rewrite_rules' ] );
        register_deactivation_hook( __FILE__, [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Registers the Job custom post type.
     */
    public function register_job_cpt() {
        $labels = [
            'name'               => __( 'Jobs', 'driver-forge' ),
            'singular_name'      => __( 'Job', 'driver-forge' ),
            'add_new'            => __( 'Add New', 'driver-forge' ),
            'add_new_item'       => __( 'Add New Job', 'driver-forge' ),
            'edit_item'          => __( 'Edit Job', 'driver-forge' ),
            'new_item'           => __( 'New Job', 'driver-forge' ),
            'view_item'          => __( 'View Job', 'driver-forge' ),
            'search_items'       => __( 'Search Jobs', 'driver-forge' ),
            'not_found'          => __( 'No Jobs found', 'driver-forge' ),
            'not_found_in_trash' => __( 'No Jobs found in trash', 'driver-forge' ),
            'menu_name'          => __( 'Jobs', 'driver-forge' ),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'has_archive'         => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'query_var'           => true,
            'menu_icon'           => 'dashicons-megaphone',
            'capability_type'     => 'post',
            'capabilities' => [
                'create_posts' => 'create_jobs',
                'edit_post' => 'edit_job',
                'edit_posts' => 'edit_jobs',
                'delete_post' => 'delete_job',
                'delete_posts' => 'delete_jobs',
            ],
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor' ],
            'rewrite'             => [ 'slug' => 'job', 'with_front' => false ],
            'show_in_rest'        => true,
        ];

        register_post_type( 'job', $args );
    }

    /**
     * Flushes rewrite rules.
     */
    public function flush_rewrite_rules() {
        $this->register_job_cpt();
        flush_rewrite_rules();
    }
}