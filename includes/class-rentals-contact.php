<?php
/**
* Canvas 6 — Inquiry / Contact Form + Email Notifications
* Files:
* - includes/class-contact.php
* - assets/js/contact.js
*
* Paste the PHP into: wp-rentals/includes/class-contact.php
* Paste the JS into: wp-rentals/assets/js/contact.js
*/


namespace ZK\Rentals;


if ( ! defined( 'ABSPATH' ) ) exit;


class Contact {


const OPTION_KEY = 'wpr_contact_settings';
const NONCE = 'wpr_inquiry_nonce';


public static function init() {
$c = new self();
}


public function __construct() {
// Shortcode
add_shortcode( 'wpr_inquiry', [ $this, 'shortcode_inquiry' ] );


// AJAX handlers
add_action( 'wp_ajax_nopriv_wpr_submit_inquiry', [ $this, 'handle_ajax_submit' ] );
add_action( 'wp_ajax_wpr_submit_inquiry', [ $this, 'handle_ajax_submit' ] );


// Fallback admin_post (non-AJAX form submit)
add_action( 'admin_post_nopriv_wpr_submit_inquiry', [ $this, 'handle_post_submit' ] );
add_action( 'admin_post_wpr_submit_inquiry', [ $this, 'handle_post_submit' ] );


// Register settings for this module
add_action( 'admin_init', [ $this, 'register_settings' ] );


// Register CPT to store inquiries
add_action( 'init', [ $this, 'register_inquiry_cpt' ] );


// Enqueue frontend script
add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
}


public function register_settings() {
register_setting( 'wpr_settings_group', self::OPTION_KEY, [
'type' => 'array',
'sanitize_callback' => [ $this, 'sanitize_settings' ],
'default' => [
'notify_admin' => 1,
'admin_email' => get_option( 'admin_email' ),
'from_name' => get_bloginfo( 'name' ),
'from_email' => '', // if empty WP will use default
'subject_template' => 'New inquiry for {property_title}',
'save_inquiries' => 1,
'rate_limit_per_hour' => 20,
]
] );
}


public function sanitize_settings( $input ) {
$out = [];
$out['notify_admin'] = ! empty( $input['notify_admin'] ) ? 1 : 0;
$out['admin_email'] = isset( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : get_option( 'admin_email' );
$out['from_name'] = isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : get_bloginfo( 'name' );
$out['from_email'] = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';
$out['subject_template'] = isset( $input['subject_template'] ) ? sanitize_text_field( $input['subject_template'] ) : 'New inquiry';
$out['save_inquiries'] = ! empty( $input['save_inquiries'] ) ? 1 : 0;
$out['rate_limit_per_hour'] = isset( $input['rate_limit_per_hour'] ) ? intval( $input['rate_limit_per_hour'] ) : 20;
return $out;
}
Contact::init();