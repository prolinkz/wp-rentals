// === FILE: wp-rentals/wp-rentals.php ===
<?php
/**
 * Plugin Name: WP Rentals – Property Rental Listings
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
if ( ! defined( 'WPR_VERSION' ) )        define( 'WPR_VERSION', '1.0.0' );
if ( ! defined( 'WPR_FILE' ) )           define( 'WPR_FILE', __FILE__ );
if ( ! defined( 'WPR_PATH' ) )           define( 'WPR_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'WPR_URL' ) )            define( 'WPR_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'WPR_BASENAME' ) )       define( 'WPR_BASENAME', plugin_basename( __FILE__ ) );

// -----------------------------------------------------------------------------
// Simple PSR-4-like autoloader for ZK\Rentals namespace
// Files expected under includes/ with class-<kebab>.php names
// -----------------------------------------------------------------------------
spl_autoload_register( function( $class ) {
    $prefix = 'ZK\\Rentals\\';
    $base_dir = WPR_PATH . 'includes/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) return;

    $relative_class = substr( $class, $len );
    $relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class );
    $file = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_path ) ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
});

// -----------------------------------------------------------------------------
// Activation / Deactivation
// -----------------------------------------------------------------------------
register_activation_hook( __FILE__, function() {
    // allow Core::activate to create CPTs/roles when available
    if ( class_exists( 'ZK\\Rentals\\Core' ) ) {
        ZK\\Rentals\\Core::activate();
    }
    flush_rewrite_rules();
});

register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
});

// -----------------------------------------------------------------------------
// Bootstrap plugin after plugins_loaded
// -----------------------------------------------------------------------------
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'wp-rentals', false, dirname( WPR_BASENAME ) . '/languages' );

    if ( class_exists( 'ZK\\Rentals\\Core' ) ) {
        ZK\\Rentals\\Core::instance();
    }
});

// Defensive admin notice for minimum requirements
add_action( 'admin_init', function(){
    $min_php = '7.4';
    $min_wp  = '5.8';
    if ( version_compare( PHP_VERSION, $min_php, '<' ) || version_compare( get_bloginfo( 'version' ), $min_wp, '<' ) ) {
        add_action( 'admin_notices', function(){
            echo '<div class="notice notice-error"><p>' . esc_html__( 'WP Rentals requires at least PHP 7.4 and WordPress 5.8.', 'wp-rentals' ) . '</p></div>';
        } );
    }
});

// === END FILE ===


