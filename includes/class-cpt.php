<?php
/**
 * Class CPT
 * Registers Property Custom Post Type and Taxonomies
 */

namespace ZK\Rentals;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
    }

    /**
     * Register Property Post Type
     */
    public function register_post_type() {
        $labels = [
            'name'               => __( 'Properties', 'wp-rentals' ),
            'singular_name'      => __( 'Property', 'wp-rentals' ),
            'add_new'            => __( 'Add New', 'wp-rentals' ),
            'add_new_item'       => __( 'Add New Property', 'wp-rentals' ),
            'edit_item'          => __( 'Edit Property', 'wp-rentals' ),
            'new_item'           => __( 'New Property', 'wp-rentals' ),
            'view_item'          => __( 'View Property', 'wp-rentals' ),
            'search_items'       => __( 'Search Properties', 'wp-rentals' ),
            'not_found'          => __( 'No properties found', 'wp-rentals' ),
            'not_found_in_trash' => __( 'No properties found in Trash', 'wp-rentals' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'properties' ],
            'menu_icon'          => 'dashicons-building',
            'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'show_in_rest'       => true,
        ];

        register_post_type( 'property', $args );
    }

    /**
     * Register Taxonomies (Property Type, City, Area)
     */
    public function register_taxonomies() {
        // Property Type
        register_taxonomy(
            'property_type',
            'property',
            [
                'labels' => [
                    'name'          => __( 'Property Types', 'wp-rentals' ),
                    'singular_name' => __( 'Property Type', 'wp-rentals' ),
                ],
                'hierarchical' => true,
                'public'       => true,
                'rewrite'      => [ 'slug' => 'property-type' ],
                'show_in_rest' => true,
            ]
        );

        // City
        register_taxonomy(
            'property_city',
            'property',
            [
                'labels' => [
                    'name'          => __( 'Cities', 'wp-rentals' ),
                    'singular_name' => __( 'City', 'wp-rentals' ),
                ],
                'hierarchical' => true,
                'public'       => true,
                'rewrite'      => [ 'slug' => 'city' ],
                'show_in_rest' => true,
            ]
        );

        // Area
        register_taxonomy(
            'property_area',
            'property',
            [
                'labels' => [
                    'name'          => __( 'Areas', 'wp-rentals' ),
                    'singular_name' => __( 'Area', 'wp-rentals' ),
                ],
                'hierarchical' => true,
                'public'       => true,
                'rewrite'      => [ 'slug' => 'area' ],
                'show_in_rest' => true,
            ]
        );
    }
}
