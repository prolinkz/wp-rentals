<?php
/**
 * Canvas 7 — Favorites System
 * Files:
 *  - includes/class-favorites.php
 *  - assets/js/favorites.js
 *  - templates/parts/favorites-list.php
 *
 * Paste PHP into: wp-rentals/includes/class-favorites.php
 * Paste JS into: wp-rentals/assets/js/favorites.js
 * Paste template into: wp-rentals/templates/parts/favorites-list.php
 */

namespace ZK\Rentals;

if ( ! defined( 'ABSPATH' ) ) exit;

class Favorites {
    const NONCE = 'wpr_fav_nonce';
    const COOKIE = 'wpr_favorites';

    public static function init() {
        $inst = new self();
    }

    public function __construct() {
        add_action( 'wp_ajax_wpr_toggle_favorite', [ $this, 'ajax_toggle_favorite' ] );
        add_action( 'wp_ajax_nopriv_wpr_toggle_favorite', [ $this, 'ajax_toggle_favorite' ] );

        add_shortcode( 'wpr_favorites', [ $this, 'shortcode_favorites_list' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Expose helper to other modules
        if ( ! function_exists( 'wpr_is_favorited' ) ) {
            function wpr_is_favorited( $post_id ) {
                return Favorites::is_favorited( $post_id );
            }
        }
    }

    public function enqueue_assets() {
        wp_register_script( 'wpr-favorites', WPR_URL . 'assets/js/favorites.js', [ 'jquery' ], WPR_VERSION, true );
        wp_enqueue_script( 'wpr-favorites' );
        wp_localize_script( 'wpr-favorites', 'WPR_FAV', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( self::NONCE ),
            'logged_in' => is_user_logged_in() ? 1 : 0,
        ] );
    }

    /** AJAX toggle handler */
    public function ajax_toggle_favorite() {
        check_ajax_referer( self::NONCE, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( ! $post_id || get_post_type( $post_id ) !== 'property' ) {
            wp_send_json_error( [ 'message' => __( 'Invalid property', 'wp-rentals' ) ], 400 );
        }

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $favorites = get_user_meta( $user_id, 'wpr_favorites', true );
            if ( ! is_array( $favorites ) ) $favorites = [];

            if ( in_array( $post_id, $favorites, true ) ) {
                // remove
                $favorites = array_values( array_diff( $favorites, [ $post_id ] ) );
                update_user_meta( $user_id, 'wpr_favorites', $favorites );
                $action = 'removed';
            } else {
                // add
                $favorites[] = $post_id;
                update_user_meta( $user_id, 'wpr_favorites', $favorites );
                $action = 'added';
            }

            $count = count( $favorites );

            wp_send_json_success( [ 'action' => $action, 'count' => $count, 'favorites' => $favorites ] );
        } else {
            // Guests: use cookie storing CSV of IDs
            $cookie = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( $_COOKIE[ self::COOKIE ] ) : '';
            $ids = $cookie ? array_filter( array_map( 'absint', explode( ',', $cookie ) ) ) : [];
            if ( in_array( $post_id, $ids, true ) ) {
                $ids = array_values( array_diff( $ids, [ $post_id ] ) );
                $action = 'removed';
            } else {
                $ids[] = $post_id;
                $action = 'added';
            }
            $csv = implode( ',', $ids );
            // set cookie for 30 days
            setcookie( self::COOKIE, $csv, time() + (30 * DAY_IN_SECONDS), COOKIEPATH ? COOKIEPATH : '/' );
            // Also send header for caching clients
            wp_send_json_success( [ 'action' => $action, 'count' => count( $ids ), 'favorites' => $ids ] );
        }
    }

    /** Return favorites array for current user or guest cookie */
    public static function get_favorites() {
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $favorites = get_user_meta( $user_id, 'wpr_favorites', true );
            if ( ! is_array( $favorites ) ) $favorites = [];
            return $favorites;
        } else {
            $cookie = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( $_COOKIE[ self::COOKIE ] ) : '';
            $ids = $cookie ? array_filter( array_map( 'absint', explode( ',', $cookie ) ) ) : [];
            return $ids;
        }
    }

    public static function is_favorited( $post_id ) {
        $favorites = self::get_favorites();
        return in_array( intval( $post_id ), $favorites, true );
    }

    /** Shortcode to list favorites: [wpr_favorites] */
    public function shortcode_favorites_list( $atts ) {
        $atts = shortcode_atts( [ 'per_page' => 12 ], $atts, 'wpr_favorites' );
        $fav = self::get_favorites();
        ob_start();
        echo '<div class="wpr-favorites-list">';
        if ( empty( $fav ) ) {
            echo '<p>' . esc_html__( 'No saved properties yet.', 'wp-rentals' ) . '</p>';
        } else {
            $args = [
                'post_type' => 'property',
                'post__in' => $fav,
                'posts_per_page' => intval( $atts['per_page'] ),
            ];
            $q = new \WP_Query( $args );
            if ( $q->have_posts() ) {
                echo '<div class="wpr-grid">';
                while ( $q->have_posts() ) { $q->the_post();
                    // try to load template part if exists
                    $part = WPR_PATH . 'templates/parts/property-card.php';
                    if ( file_exists( $part ) ) {
                        include $part;
                    } else {
                        echo '<div>' . get_the_title() . '</div>';
                    }
                }
                echo '</div>';
                wp_reset_postdata();
            } else {
                echo '<p>' . esc_html__( 'No saved properties found.', 'wp-rentals' ) . '</p>';
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

} // end class

Favorites::init();