// === FILE: wp-rentals/includes/class-core.php ===
<?php
namespace ZK\Rentals;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Core {
    /** @var Core */
    private static $instance;

    public static function instance() : Core {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function activate() : void {
        // Pre-create default options
        $defaults = [
            'default_currency' => 'PKR',
            'area_unit'        => 'Marla',
            'maps_api'         => '',
        ];
        $existing = get_option( 'wpr_rentals_options', [] );
        if ( ! is_array( $existing ) ) $existing = [];
        update_option( 'wpr_rentals_options', array_merge( $defaults, $existing ) );

        // Register CPT/taxonomies now if class exists
        if ( class_exists( '\\ZK\\Rentals\\CPT' ) ) {
            CPT::register();
        }
    }

    private function __construct() {
        // Register assets and base hooks
        add_action( 'init', [ $this, 'register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );

        // Load modules if present
        $this->maybe_init_module( 'CPT' );
        $this->maybe_init_module( 'Metabox' );
        $this->maybe_init_module( 'Search' );
        $this->maybe_init_module( 'Display' );
        $this->maybe_init_module( 'Contact' );
        $this->maybe_init_module( 'Favorites' );
        $this->maybe_init_module( 'Admin' );
        $this->maybe_init_module( 'Payment' );

        // Template override support
        add_filter( 'template_include', [ $this, 'template_loader' ] );
    }

    private function maybe_init_module( $module ) {
        $class = "ZK\\\\Rentals\\\\" . $module;
        $file = WPR_PATH . 'includes/class-' . strtolower( str_replace( '_', '-', $module ) ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
            $fqn = "ZK\\\\Rentals\\\\" . $module;
            if ( class_exists( $fqn ) ) {
                // If module has static init method use it, else instantiate
                if ( method_exists( $fqn, 'init' ) ) {
                    call_user_func( [ $fqn, 'init' ] );
                } else {
                    new $fqn();
                }
            }
        }
    }

    public function register_assets() : void {
        // Public
        wp_register_style( 'wpr-style', WPR_URL . 'assets/css/style.css', [], WPR_VERSION );
        wp_register_script( 'wpr-main',  WPR_URL . 'assets/js/main.js', [ 'jquery' ], WPR_VERSION, true );

        // Admin
        wp_register_style( 'wpr-admin', WPR_URL . 'assets/css/admin.css', [], WPR_VERSION );
        wp_register_script( 'wpr-admin', WPR_URL . 'assets/js/admin.js', [ 'jquery' ], WPR_VERSION, true );
    }

    public function enqueue_public() : void {
        // Enqueue base public style
        wp_enqueue_style( 'wpr-style' );
        wp_enqueue_script( 'wpr-main' );

        // Localize minimal data for frontend scripts
        wp_localize_script( 'wpr-main', 'WPR_DATA', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpr_ajax_nonce' ),
        ] );
    }

    public function enqueue_admin( $hook ) : void {
        // Load admin assets on our plugin pages (admin module will register slugs)
        wp_enqueue_style( 'wpr-admin' );
        wp_enqueue_script( 'wpr-admin' );
    }

    public function template_loader( $template ) {
        if ( is_singular( 'rental_property' ) ) {
            $custom = $this->locate_template( 'single-rental_property.php' );
            if ( $custom ) return $custom;
        }
        if ( is_post_type_archive( 'rental_property' ) ) {
            $custom = $this->locate_template( 'archive-rental_property.php' );
            if ( $custom ) return $custom;
        }
        return $template;
    }

    private function locate_template( string $file ) : string {
        $theme_path = trailingslashit( get_stylesheet_directory() ) . 'wp-rentals/' . $file;
        if ( file_exists( $theme_path ) ) return $theme_path;

        $plugin_path = WPR_PATH . 'templates/' . $file;
        if ( file_exists( $plugin_path ) ) return $plugin_path;

        return '';
    }
}

// Initialize core when file is loaded by autoloader requirement
// Note: Core::instance() is invoked from main plugin file on plugins_loaded

// === END FILE ===
