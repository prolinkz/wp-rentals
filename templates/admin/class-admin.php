<?php
/**
 * Canvas 8 — Admin Panel & Settings
 * Files:
 *  - admin/class-admin.php
 *  - admin/settings-page.php
 *
 * Paste admin/class-admin.php into: wp-rentals/admin/class-admin.php
 * Paste admin/settings-page.php into: wp-rentals/admin/settings-page.php
 */

// -----------------------------
// admin/class-admin.php
// -----------------------------

namespace ZK\Rentals\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {
    const MENU_SLUG = 'wpr-settings';

    public static function init() {
        $inst = new self();
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );

        // load the settings page template when needed
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function add_menu() {
        add_menu_page(
            __( 'WP Rentals', 'wp-rentals' ),
            __( 'WP Rentals', 'wp-rentals' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_page' ],
            'dashicons-building',
            56
        );

        add_submenu_page( self::MENU_SLUG, __( 'Settings', 'wp-rentals' ), __( 'Settings', 'wp-rentals' ), 'manage_options', self::MENU_SLUG );
    }

    public function enqueue_assets( $hook ) {
        // Only load on plugin settings page
        if ( isset( $_GET['page'] ) && $_GET['page'] === self::MENU_SLUG ) {
            wp_enqueue_style( 'wpr-admin' );
            wp_enqueue_script( 'wpr-admin' );
        }
    }

    public function register_settings() {
        // Main plugin options (single option array)
        register_setting( 'wpr_settings_group', 'wpr_rentals_options', [ 'sanitize_callback' => [ $this, 'sanitize_main_options' ] ] );

        // Ensure contact settings registered (Contact module may register as well)
        register_setting( 'wpr_settings_group', 'wpr_contact_settings' );

        // Payment settings
        register_setting( 'wpr_settings_group', 'wpr_payment_settings' );

        // Add settings sections and fields programmatically if desired (we'll render manual fields in template)
    }

    public function sanitize_main_options( $input ) {
        $out = [];
        $out['default_currency'] = isset( $input['default_currency'] ) ? sanitize_text_field( $input['default_currency'] ) : 'PKR';
        $out['area_unit'] = isset( $input['area_unit'] ) ? sanitize_text_field( $input['area_unit'] ) : 'Marla';
        $out['listings_per_page'] = isset( $input['listings_per_page'] ) ? intval( $input['listings_per_page'] ) : 12;
        $out['default_status'] = in_array( $input['default_status'] ?? 'available', [ 'available', 'rented', 'pending' ], true ) ? $input['default_status'] : 'available';
        $out['enable_frontend_submission'] = ! empty( $input['enable_frontend_submission'] ) ? 1 : 0;
        $out['maps_provider'] = isset( $input['maps_provider'] ) ? sanitize_text_field( $input['maps_provider'] ) : 'google';
        $out['maps_api_key'] = isset( $input['maps_api_key'] ) ? sanitize_text_field( $input['maps_api_key'] ) : '';
        return $out;
    }

    public function render_page() {
        // load the settings page template
        $tpl = WPR_PATH . 'admin/settings-page.php';
        if ( file_exists( $tpl ) ) {
            include $tpl;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'WP Rentals Settings', 'wp-rentals' ) . '</h1><p>Settings template missing.</p></div>';
        }
    }
}

// Initialize admin
Admin::init();
