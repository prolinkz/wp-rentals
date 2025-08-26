<?php
/**
 * Canvas 4 — Frontend Search & Filtering
 * File: includes/class-search.php
 *
 * Provides a [wpr_search] shortcode and a REST API endpoint for AJAX-powered searching.
 */

namespace ZK\Rentals;

if ( ! defined( 'ABSPATH' ) ) exit;

class Search {

    const REST_NAMESPACE = 'wpr/v1';
    const REST_ROUTE     = 'search';

    public static function init() {
        $inst = new self();
    }

    public function __construct() {
        add_shortcode( 'wpr_search', [ $this, 'shortcode_search_form' ] );
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets() {
        // ensure core registered assets exist (Core registers wpr-main)
        if ( wp_script_is( 'wpr-main', 'registered' ) ) {
            wp_enqueue_script( 'wpr-main' );
        }

        // register our search script
        wp_register_script( 'wpr-search', WPR_URL . 'assets/js/search.js', [ 'jquery' ], WPR_VERSION, true );
        wp_enqueue_script( 'wpr-search' );

        wp_localize_script( 'wpr-search', 'WPR_SEARCH', [
            'rest_url' => esc_url_raw( rest_url( self::REST_NAMESPACE . '/' . self::REST_ROUTE ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
        ] );

        // basic styles (core style already enqueued)
    }

    /**
     * Shortcode output: search form + results container
     */
    public function shortcode_search_form( $atts ) {
        $atts = shortcode_atts( [
            'per_page' => 9,
            'layout'   => 'grid',
        ], $atts, 'wpr_search' );

        ob_start();

        // Fetch taxonomy terms for filters
        $types = get_terms( [ 'taxonomy' => 'property_type', 'hide_empty' => false ] );
        $cities = get_terms( [ 'taxonomy' => 'property_city', 'hide_empty' => false ] );
        $areas = get_terms( [ 'taxonomy' => 'property_area', 'hide_empty' => false ] );

        ?>
        <div class="wpr-search-widget">
            <form id="wpr-search-form" class="wpr-search-form" onsubmit="return false;">
                <div class="wpr-row" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" name="s" id="wpr_s" placeholder="<?php esc_attr_e( 'Search by keyword, title or locality', 'wp-rentals' ); ?>" style="flex:1;min-width:180px;padding:8px;" />

                    <select name="property_type" id="wpr_property_type">
                        <option value=""><?php esc_html_e( 'Any Type', 'wp-rentals' ); ?></option>
                        <?php foreach ( $types as $t ) : ?>
                            <option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="property_city" id="wpr_property_city">
                        <option value=""><?php esc_html_e( 'Any City', 'wp-rentals' ); ?></option>
                        <?php foreach ( $cities as $c ) : ?>
                            <option value="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="property_area" id="wpr_property_area">
                        <option value=""><?php esc_html_e( 'Any Area', 'wp-rentals' ); ?></option>
                        <?php foreach ( $areas as $a ) : ?>
                            <option value="<?php echo esc_attr( $a->slug ); ?>"><?php echo esc_html( $a->name ); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="number" name="min_price" id="wpr_min_price" placeholder="<?php esc_attr_e( 'Min Price', 'wp-rentals' ); ?>" style="width:120px;" />
                    <input type="number" name="max_price" id="wpr_max_price" placeholder="<?php esc_attr_e( 'Max Price', 'wp-rentals' ); ?>" style="width:120px;" />

                    <select name="bedrooms" id="wpr_bedrooms">
                        <option value=""><?php esc_html_e( 'Any Beds', 'wp-rentals' ); ?></option>
                        <?php for ( $i=1; $i<=6; $i++ ) { $label = $i===6? '6+': $i; echo '<option value="'.$i.'">'.$label.'</option>'; } ?>
                    </select>

                    <select name="bathrooms" id="wpr_bathrooms">
                        <option value=""><?php esc_html_e( 'Any Baths', 'wp-rentals' ); ?></option>
                        <?php for ( $i=1; $i<=6; $i++ ) { $label = $i===6? '6+': $i; echo '<option value="'.$i.'">'.$label.'</option>'; } ?>
                    </select>

                    <select name="sort" id="wpr_sort">
                        <option value="date_desc"><?php esc_html_e( 'Newest', 'wp-rentals' ); ?></option>
                        <option value="date_asc"><?php esc_html_e( 'Oldest', 'wp-rentals' ); ?></option>
                        <option value="price_asc"><?php esc_html_e( 'Price: Low to High', 'wp-rentals' ); ?></option>
                        <option value="price_desc"><?php esc_html_e( 'Price: High to Low', 'wp-rentals' ); ?></option>
                    </select>

                    <button class="button button-primary" id="wpr_search_btn"><?php esc_html_e( 'Search', 'wp-rentals' ); ?></button>
                </div>
            </form>

            <div id="wpr-search-results" class="wpr-search-results" style="margin-top:16px;"></div>
            <div id="wpr-search-pagination" style="margin-top:12px;"></div>
        </div>
        <?php

        // initial config data
        echo '<script>window.WPR_SEARCH_CFG = ' . wp_json_encode( [ 'per_page' => (int) $atts['per_page'] ] ) . ';</script>';

        return ob_get_clean();
    }

    public function register_routes() {
        register_rest_route( self::REST_NAMESPACE, self::REST_ROUTE, [
            'methods'  => 'GET',
            'callback' => [ $this, 'rest_search_callback' ],
            'args'     => [
                's' => [ 'required' => false ],
                'property_type' => [ 'required' => false ],
                'property_city' => [ 'required' => false ],
                'property_area' => [ 'required' => false ],
                'min_price' => [ 'required' => false, 'validate_callback' => 'is_numeric' ],
                'max_price' => [ 'required' => false, 'validate_callback' => 'is_numeric' ],
                'bedrooms' => [ 'required' => false ],
                'bathrooms' => [ 'required' => false ],
                'amenities' => [ 'required' => false ],
                'sort' => [ 'required' => false ],
                'page' => [ 'required' => false ],
                'per_page' => [ 'required' => false ],
            ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function rest_search_callback( 
        \WP_REST_Request $request
    ) {
        $params = $request->get_query_params();

        $paged = isset( $params['page'] ) ? max( 1, intval( $params['page'] ) ) : 1;
        $per_page = isset( $params['per_page'] ) ? intval( $params['per_page'] ) : ( WPR_DATA['per_page'] ?? 9 );
        if ( $per_page <= 0 ) $per_page = 9;

        $args = [
            'post_type' => 'property',
            'post_status' => 'publish',
            'paged' => $paged,
            'posts_per_page' => $per_page,
        ];

        // Keyword search
        if ( ! empty( $params['s'] ) ) {
            $args['s'] = sanitize_text_field( $params['s'] );
        }

        // Taxonomies
        $tax_query = [];
        foreach ( [ 'property_type', 'property_city', 'property_area' ] as $tax ) {
            if ( ! empty( $params[ $tax ] ) ) {
                $tax_query[] = [
                    'taxonomy' => $tax,
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $params[ $tax ] ),
                ];
            }
        }
        if ( $tax_query ) $args['tax_query'] = $tax_query;

        // Meta queries
        $meta_query = [ 'relation' => 'AND' ];

        if ( isset( $params['min_price'] ) || isset( $params['max_price'] ) ) {
            $mq = [ 'key' => '_wpr_price', 'type' => 'NUMERIC' ];
            if ( isset( $params['min_price'] ) && $params['min_price'] !== '' ) {
                $mq['value'] = [ floatval( $params['min_price'] ), ( isset( $params['max_price'] ) && $params['max_price'] !== '' ) ? floatval( $params['max_price'] ) : PHP_INT_MAX ];
                $mq['compare'] = 'BETWEEN';
            } elseif ( isset( $params['max_price'] ) && $params['max_price'] !== '' ) {
                $mq['value'] = floatval( $params['max_price'] );
                $mq['compare'] = '<=';
            }
            $meta_query[] = $mq;
        }

        if ( ! empty( $params['bedrooms'] ) ) {
            $meta_query[] = [ 'key' => '_wpr_bedrooms', 'value' => intval( $params['bedrooms'] ), 'compare' => '>=' , 'type' => 'NUMERIC' ];
        }
        if ( ! empty( $params['bathrooms'] ) ) {
            $meta_query[] = [ 'key' => '_wpr_bathrooms', 'value' => intval( $params['bathrooms'] ), 'compare' => '>=' , 'type' => 'NUMERIC' ];
        }

        // Amenities - expect comma separated keys e.g. gas,electric
        if ( ! empty( $params['amenities'] ) ) {
            $amen_list = array_map( 'sanitize_text_field', explode( ',', $params['amenities'] ) );
            foreach ( $amen_list as $amen ) {
                $meta_query[] = [ 'key' => '_wpr_amen_' . $amen, 'value' => '1', 'compare' => '=' ];
            }
        }

        if ( count( $meta_query ) > 1 ) $args['meta_query'] = $meta_query;

        // Sorting
        if ( ! empty( $params['sort'] ) ) {
            switch ( $params['sort'] ) {
                case 'price_asc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = '_wpr_price';
                    $args['order'] = 'ASC';
                    break;
                case 'price_desc':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = '_wpr_price';
                    $args['order'] = 'DESC';
                    break;
                case 'date_asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                case 'date_desc':
                default:
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
            }
        }

        $q = new \WP_Query( $args );

        $results = [];
        if ( $q->have_posts() ) {
            while ( $q->have_posts() ) {
                $q->the_post();
                $id = get_the_ID();
                $price = get_post_meta( $id, '_wpr_price', true );
                $beds = get_post_meta( $id, '_wpr_bedrooms', true );
                $baths = get_post_meta( $id, '_wpr_bathrooms', true );
                $thumb = get_the_post_thumbnail_url( $id, 'medium' );

                $results[] = [
                    'id' => $id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'price' => $price !== '' ? floatval( $price ) : null,
                    'bedrooms' => intval( $beds ),
                    'bathrooms' => intval( $baths ),
                    'thumbnail' => $thumb,
                    'excerpt' => get_the_excerpt(),
                ];
            }
            wp_reset_postdata();
        }

        $response = [
            'total' => (int) $q->found_posts,
            'pages' => (int) $q->max_num_pages,
            'page'  => (int) $paged,
            'per_page' => (int) $per_page,
            'results' => $results,
        ];

        return rest_ensure_response( $response );
    }
}

// Register module (Core will include and call init if present)
Search::init();
