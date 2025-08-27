<?php
/**
 * Plugin Name: WP Rentals – Property Rental Listings (Fixed)
 * Plugin URI:  https://example.com/wp-rentals
 * Description: Property listing plugin for landlords and tenants. Custom Post Type, frontend submission, search, favorites, and inquiries.
 * Version: 1.0.0
 * Author: ZK Enterprises
 * Text Domain: wp-rentals
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// -----------------------------------------------------------------------------
// Constants
// -----------------------------------------------------------------------------
define( 'WPR_VERSION', '1.0.0' );
define( 'WPR_FILE', __FILE__ );
define( 'WPR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPR_URL', plugin_dir_url( __FILE__ ) );
define( 'WPR_BASENAME', plugin_basename( __FILE__ ) );

// -----------------------------------------------------------------------------
// Include required files
// -----------------------------------------------------------------------------
require_once WPR_PATH . 'includes/class-core.php';
require_once WPR_PATH . 'includes/class-cpt.php';
require_once WPR_PATH . 'includes/class-metabox.php';
require_once WPR_PATH . 'includes/class-search.php';
require_once WPR_PATH . 'includes/class-rentals-contact.php';
require_once WPR_PATH . 'includes/class-rentals-favorites.php';
require_once WPR_PATH . 'includes/class-rentals-payment.php';
require_once WPR_PATH . 'includes/class-rp-email.php';
require_once WPR_PATH . 'includes/hooks-rp-emails.php';

// -----------------------------------------------------------------------------
// Activation / Deactivation
// -----------------------------------------------------------------------------
register_activation_hook( __FILE__, 'wpr_activate_plugin' );
register_deactivation_hook( __FILE__, 'wpr_deactivate_plugin' );

function wpr_activate_plugin() {
    // Create default options
    $defaults = [
        'default_currency' => 'PKR',
        'area_unit'        => 'Marla',
        'maps_api'         => '',
    ];
    $existing = get_option( 'wpr_rentals_options', [] );
    if ( ! is_array( $existing ) ) $existing = [];
    update_option( 'wpr_rentals_options', array_merge( $defaults, $existing ) );
    
    // Register CPT and taxonomies
    if ( class_exists( 'ZK\Rentals\CPT' ) ) {
        $cpt = new ZK\Rentals\CPT();
        $cpt->register_post_type();
        $cpt->register_taxonomies();
    }
    
    flush_rewrite_rules();
}

function wpr_deactivate_plugin() {
    flush_rewrite_rules();
}

// -----------------------------------------------------------------------------
// Initialize plugin
// -----------------------------------------------------------------------------
add_action( 'plugins_loaded', 'wpr_init_plugin' );

function wpr_init_plugin() {
    load_plugin_textdomain( 'wp-rentals', false, dirname( WPR_BASENAME ) . '/languages' );
    
    // Initialize core
    if ( class_exists( 'ZK\Rentals\Core' ) ) {
        ZK\Rentals\Core::instance();
    }
    
    // Initialize classes
    if ( class_exists( 'ZK\Rentals\CPT' ) ) {
        new ZK\Rentals\CPT();
    }
    
    if ( class_exists( 'ZK\Rentals\Metabox' ) ) {
        new ZK\Rentals\Metabox();
    }
    
    if ( class_exists( 'ZK\Rentals\Search' ) ) {
        new ZK\Rentals\Search();
    }
    
    if ( class_exists( 'ZK\Rentals\Contact' ) ) {
        ZK\Rentals\Contact::init();
    }
    
    if ( class_exists( 'ZK\Rentals\Favorites' ) ) {
        ZK\Rentals\Favorites::init();
    }
    
    if ( class_exists( 'ZK\Rentals\Payment' ) ) {
        ZK\Rentals\Payment::init();
    }
}

// Defensive admin notice for minimum requirements
add_action( 'admin_init', 'wpr_check_requirements' );

function wpr_check_requirements() {
    $min_php = '7.4';
    $min_wp  = '5.8';
    if ( version_compare( PHP_VERSION, $min_php, '<' ) || version_compare( get_bloginfo( 'version' ), $min_wp, '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'WP Rentals requires at least PHP 7.4 and WordPress 5.8.', 'wp-rentals' ) . '</p></div>';
        } );
    }
}

