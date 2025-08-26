<?php
/**
 * Admin Payments Manager
 * Path: wp-rentals/admin/class-payments-admin.php
 */

namespace ZK\Rentals\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Payments_Admin {
    public static function init() {
        $inst = new self();
    }

    public function __construct() {
        // Columns and row actions for wpr_payment CPT
        add_filter( 'manage_edit-wpr_payment_columns', [ $this, 'columns' ] );
        add_action( 'manage_wpr_payment_posts_custom_column', [ $this, 'render_column' ], 10, 2 );
        add_filter( 'post_row_actions', [ $this, 'row_actions' ], 10, 2 );

        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Make sure list table supports sortable date
        add_filter( 'manage_edit-wpr_payment_sortable_columns', [ $this, 'sortable_columns' ] );
    }

    public function columns( $cols ) {
        $new = [];
        $new['cb'] = $cols['cb'];
        $new['title'] = __( 'Title', 'wp-rentals' );
        $new['amount'] = __( 'Amount', 'wp-rentals' );
        $new['method'] = __( 'Method', 'wp-rentals' );
        $new['status'] = __( 'Status', 'wp-rentals' );
        $new['proof'] = __( 'Proof', 'wp-rentals' );
        $new['date'] = __( 'Date', 'wp-rentals' );
        return $new;
    }

    public function render_column( $column, $post_id ) {
        switch ( $column ) {
            case 'amount':
                $amt = get_post_meta( $post_id, 'wpr_pay_amount', true );
                $currency = get_post_meta( $post_id, 'wpr_pay_currency', true ) ?: ( get_option( 'wpr_payment_settings' )['currency'] ?? 'PKR' );
                echo $amt !== '' ? esc_html( $currency . ' ' . number_format_i18n( floatval( $amt ), 2 ) ) : '&mdash;';
                break;
            case 'method':
                $method = get_post_meta( $post_id, 'wpr_pay_method', true );
                if ( ! $method ) {
                    $raw = get_post_meta( $post_id, 'wpr_pay_raw', true );
                    if ( $raw ) {
                        $maybe = maybe_unserialize( $raw );
                        if ( is_array( $maybe ) && ! empty( $maybe['method'] ) ) $method = $maybe['method'];
                    }
                }
                echo $method ? esc_html( ucfirst( $method ) ) : '&mdash;';
                break;
            case 'status':
                $st = get_post_meta( $post_id, 'wpr_pay_status', true ) ?: 'pending_verification';
                $label = ucfirst( str_replace( '_', ' ', $st ) );
                echo '<span class="wpr-pay-status" data-id="' . esc_attr( $post_id ) . '">' . esc_html( $label ) . '</span>';
                break;
            case 'proof':
                $attach = get_post_meta( $post_id, 'wpr_pay_proof_id', true );
                if ( $attach ) {
                    $url = wp_get_attachment_url( $attach );
                    echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html__( 'View', 'wp-rentals' ) . '</a>';
                } else {
                    echo '&mdash;';
                }
                break;
            case 'date':
                echo get_the_date( '', $post_id );
                break;
        }
    }

    public function row_actions( $actions, $post ) {
        if ( $post->post_type !== 'wpr_payment' ) return $actions;
        if ( ! current_user_can( 'manage_options' ) ) return $actions;

        $id = $post->ID;
        $status = get_post_meta( $id, 'wpr_pay_status', true ) ?: 'pending_verification';

        if ( 'pending_verification' === $status ) {
            $verify = '<a href="#" class="wpr-pay-action wpr-verify-btn" data-id="' . esc_attr( $id ) . '">' . esc_html__( 'Verify', 'wp-rentals' ) . '</a>';
            $reject = '<a href="#" class="wpr-pay-action wpr-reject-btn" data-id="' . esc_attr( $id ) . '">' . esc_html__( 'Reject', 'wp-rentals' ) . '</a>';
            $actions['wpr_verify'] = $verify;
            $actions['wpr_reject'] = $reject;
        }
        return $actions;
    }

    public function sortable_columns( $cols ) {
        $cols['date'] = 'date';
        return $cols;
    }

    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        if ( $screen && $screen->post_type === 'wpr_payment' ) {
            wp_enqueue_script( 'wpr-payments-admin', WPR_URL . 'admin/js/payments-admin.js', [ 'jquery' ], WPR_VERSION, true );
            wp_localize_script( 'wpr-payments-admin', 'WPR_PAY_ADMIN', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wpr_verify_nonce' ),
                'i18n' => [ 'verifying' => __( 'Verifying...', 'wp-rentals' ), 'rejecting' => __( 'Rejecting...', 'wp-rentals' ) ]
            ] );
            wp_enqueue_style( 'wpr-admin' );
        }
    }
}

Payments_Admin::init();
