<?php
/**
 * Canvas 3 — Property Listing Fields (Metaboxes)
 * File: includes/class-metabox.php
 *
 * Adds a comprehensive metabox for the `property` post type capturing pricing,
 * specs, location, amenities, media (gallery) and more.
 *
 * NOTE: Core loader (class-core.php) will include this file automatically if present.
 */

namespace ZK\Rentals;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Metabox {

    /** Prefix for meta keys */
    const META_PREFIX = '_wpr_';

    /** Amenities list */
    private $amenities = [
        'gas'      => 'Gas Connection',
        'electric' => 'Electricity',
        'water'    => 'Water Supply',
        'lawn'     => 'Lawn / Garden',
        'servant'  => 'Servant Quarter',
        'furnished'=> 'Furnished',
        'internet' => 'Internet Access',
        'ac'       => 'Air Conditioning',
        'heating'  => 'Heating',
    ];

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_meta' ], 10, 2 );

        // Admin scripts to support media uploader & map (uses core registered 'wpr-admin')
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
    }

    public function add_meta_box() {
        add_meta_box(
            'wpr_property_details',
            __( 'Property Details', 'wp-rentals' ),
            [ $this, 'render_meta_box' ],
            'property',
            'normal',
            'high'
        );
    }

    public function admin_assets( $hook ) {
        global $post_type;
        if ( $post_type !== 'property' ) return;

        // WordPress media library
        wp_enqueue_media();
        // our admin script (registered by Core: 'wpr-admin')
        if ( wp_script_is( 'wpr-admin', 'registered' ) ) {
            wp_enqueue_script( 'wpr-admin' );
        }

        // Localize data for media uploader and map
        $data = [
            'nonce' => wp_create_nonce( 'wpr_metabox_nonce' ),
            'i18n'  => [
                'choose_images' => __( 'Choose Images', 'wp-rentals' ),
                'add_images'    => __( 'Add to Gallery', 'wp-rentals' ),
                'remove'        => __( 'Remove', 'wp-rentals' ),
            ],
        ];
        wp_localize_script( 'wpr-admin', 'WPR_Metabox', $data );

        // Admin styles
        if ( wp_style_is( 'wpr-admin', 'registered' ) ) {
            wp_enqueue_style( 'wpr-admin' );
        }
    }

    public function render_meta_box( $post ) {
        // Nonce
        wp_nonce_field( 'wpr_save_property', 'wpr_property_nonce' );

        // Load existing values
        $get = function( $key, $default = '' ) use ( $post ) {
            return get_post_meta( $post->ID, self::META_PREFIX . $key, true ) ?: $default;
        };

        $price         = $get( 'price', '' );
        $currency      = $get( 'currency', get_option( 'wpr_rentals_options', [] )['default_currency'] ?? 'PKR' );
        $advance       = $get( 'advance', '' );
        $rent_period   = $get( 'rent_period', 'Monthly' );
        $additional    = $get( 'additional_costs', '' );

        $address       = $get( 'address', '' );
        $latitude      = $get( 'lat', '' );
        $longitude     = $get( 'lng', '' );

        $bedrooms      = $get( 'bedrooms', '' );
        $bathrooms     = $get( 'bathrooms', '' );
        $area_value    = $get( 'area_value', '' );
        $area_unit     = $get( 'area_unit', 'Marla' );
        $parking       = $get( 'parking', '' );
        $year_built    = $get( 'year_built', '' );

        $gallery       = $get( 'gallery', '' ); // comma separated IDs
        $gallery_ids   = $gallery ? explode( ',', $gallery ) : [];

        $video_url     = $get( 'video_url', '' );
        $status        = $get( 'status', 'available' );
        $property_type = $get( 'property_type', '' );

        ?>
        <div class="wpr-metabox">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="wpr_price"><?php esc_html_e( 'Monthly Rent', 'wp-rentals' ); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="wpr_price" id="wpr_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text" />
                            &nbsp;
                            <select name="wpr_currency">
                                <?php $currencies = [ 'PKR', 'USD', 'EUR', 'GBP' ];
                                foreach ( $currencies as $cur ) : ?>
                                    <option value="<?php echo esc_attr( $cur ); ?>" <?php selected( $currency, $cur ); ?>><?php echo esc_html( $cur ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpr_advance"><?php esc_html_e( 'Advance Amount', 'wp-rentals' ); ?></label></th>
                        <td><input type="number" step="0.01" name="wpr_advance" id="wpr_advance" value="<?php echo esc_attr( $advance ); ?>" class="regular-text" /></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_rent_period"><?php esc_html_e( 'Rent Period', 'wp-rentals' ); ?></label></th>
                        <td>
                            <select name="wpr_rent_period" id="wpr_rent_period">
                                <option value="Monthly" <?php selected( $rent_period, 'Monthly' ); ?>><?php esc_html_e( 'Monthly', 'wp-rentals' ); ?></option>
                                <option value="Quarterly" <?php selected( $rent_period, 'Quarterly' ); ?>><?php esc_html_e( 'Quarterly', 'wp-rentals' ); ?></option>
                                <option value="Yearly" <?php selected( $rent_period, 'Yearly' ); ?>><?php esc_html_e( 'Yearly', 'wp-rentals' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpr_additional_costs"><?php esc_html_e( 'Additional Costs', 'wp-rentals' ); ?></label></th>
                        <td><textarea name="wpr_additional_costs" id="wpr_additional_costs" rows="3" class="large-text"><?php echo esc_textarea( $additional ); ?></textarea></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_address"><?php esc_html_e( 'Address', 'wp-rentals' ); ?></label></th>
                        <td>
                            <input type="text" name="wpr_address" id="wpr_address" value="<?php echo esc_attr( $address ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Full street address. You may fill latitude/longitude below or use the map (requires API key in settings).', 'wp-rentals' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Map Location', 'wp-rentals' ); ?></th>
                        <td>
                            <input type="text" name="wpr_lat" id="wpr_lat" value="<?php echo esc_attr( $latitude ); ?>" placeholder="Latitude" style="width:140px;" />
                            <input type="text" name="wpr_lng" id="wpr_lng" value="<?php echo esc_attr( $longitude ); ?>" placeholder="Longitude" style="width:140px; margin-left:8px;" />
                            <div id="wpr_map_canvas" style="width:100%;height:300px;margin-top:8px;background:#f5f5f5;display:none;">Map preview (enable API key in settings)</div>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpr_bedrooms"><?php esc_html_e( 'Bedrooms', 'wp-rentals' ); ?></label></th>
                        <td><input type="number" min="0" name="wpr_bedrooms" id="wpr_bedrooms" value="<?php echo esc_attr( $bedrooms ); ?>" style="width:100px;" /></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_bathrooms"><?php esc_html_e( 'Bathrooms', 'wp-rentals' ); ?></label></th>
                        <td><input type="number" min="0" name="wpr_bathrooms" id="wpr_bathrooms" value="<?php echo esc_attr( $bathrooms ); ?>" style="width:100px;" /></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_area_value"><?php esc_html_e( 'Total Area', 'wp-rentals' ); ?></label></th>
                        <td>
                            <input type="number" name="wpr_area_value" id="wpr_area_value" value="<?php echo esc_attr( $area_value ); ?>" style="width:120px;" />
                            <select name="wpr_area_unit">
                                <?php $units = [ 'Marla', 'Square Feet', 'Kanal' ]; foreach ( $units as $u ) : ?>
                                    <option value="<?php echo esc_attr( $u ); ?>" <?php selected( $area_unit, $u ); ?>><?php echo esc_html( $u ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpr_parking"><?php esc_html_e( 'Car Porch / Parking', 'wp-rentals' ); ?></label></th>
                        <td><input type="number" min="0" name="wpr_parking" id="wpr_parking" value="<?php echo esc_attr( $parking ); ?>" style="width:100px;" /></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_year_built"><?php esc_html_e( 'Year Built', 'wp-rentals' ); ?></label></th>
                        <td><input type="number" min="1800" max="2099" step="1" name="wpr_year_built" id="wpr_year_built" value="<?php echo esc_attr( $year_built ); ?>" style="width:120px;" /></td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Amenities & Features', 'wp-rentals' ); ?></th>
                        <td>
                            <?php foreach ( $this->amenities as $key => $label ) :
                                $val = get_post_meta( $post->ID, self::META_PREFIX . 'amen_' . $key, true ); ?>
                                <label style="display:inline-block;width:200px;">
                                    <input type="checkbox" name="wpr_amen[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $val, 1 ); ?> /> <?php echo esc_html( $label ); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Image Gallery', 'wp-rentals' ); ?></th>
                        <td>
                            <div>
                                <button type="button" class="button wpr-add-gallery-images"><?php esc_html_e( 'Add / Edit Gallery', 'wp-rentals' ); ?></button>
                                <input type="hidden" id="wpr_gallery" name="wpr_gallery" value="<?php echo esc_attr( $gallery ); ?>" />
                            </div>
                            <div id="wpr_gallery_preview" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                                <?php
                                foreach ( $gallery_ids as $aid ) {
                                    $src = wp_get_attachment_image_src( (int) $aid, 'thumbnail' );
                                    if ( $src ) {
                                        echo '<div class="wpr-gallery-item" data-id="' . esc_attr( $aid ) . '" style="width:90px;">';
                                        echo '<img src="' . esc_url( $src[0] ) . '" style="width:100%;height:auto;display:block;border:1px solid #ddd;padding:2px;border-radius:4px;" />';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="wpr_video_url"><?php esc_html_e( 'Video Link (YouTube/Vimeo)', 'wp-rentals' ); ?></label></th>
                        <td><input type="url" name="wpr_video_url" id="wpr_video_url" value="<?php echo esc_attr( $video_url ); ?>" class="regular-text" /></td>
                    </tr>

                    <tr>
                        <th><label for="wpr_status"><?php esc_html_e( 'Status', 'wp-rentals' ); ?></label></th>
                        <td>
                            <select name="wpr_status" id="wpr_status">
                                <option value="available" <?php selected( $status, 'available' ); ?>><?php esc_html_e( 'Available', 'wp-rentals' ); ?></option>
                                <option value="rented" <?php selected( $status, 'rented' ); ?>><?php esc_html_e( 'Rented', 'wp-rentals' ); ?></option>
                                <option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'wp-rentals' ); ?></option>
                            </select>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        <?php
    }

    public function save_meta( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['wpr_property_nonce'] ) || ! wp_verify_nonce( $_POST['wpr_property_nonce'], 'wpr_save_property' ) ) {
            return;
        }

        // Autosave check
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // Permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Only for our post type
        if ( $post->post_type !== 'property' ) return;

        // Sanitize and update simple fields
        $map = [
            'price'           => 'wpr_price',
            'currency'        => 'wpr_currency',
            'advance'         => 'wpr_advance',
            'rent_period'     => 'wpr_rent_period',
            'additional_costs'=> 'wpr_additional_costs',
            'address'         => 'wpr_address',
            'lat'             => 'wpr_lat',
            'lng'             => 'wpr_lng',
            'bedrooms'        => 'wpr_bedrooms',
            'bathrooms'       => 'wpr_bathrooms',
            'area_value'      => 'wpr_area_value',
            'area_unit'       => 'wpr_area_unit',
            'parking'         => 'wpr_parking',
            'year_built'      => 'wpr_year_built',
            'video_url'       => 'wpr_video_url',
            'status'          => 'wpr_status',
        ];

        foreach ( $map as $meta_key => $input ) {
            if ( isset( $_POST[ $input ] ) ) {
                $val = $_POST[ $input ];
                if ( in_array( $meta_key, [ 'price', 'advance' ], true ) ) {
                    $val = floatval( $val );
                } elseif ( in_array( $meta_key, [ 'bedrooms', 'bathrooms', 'parking', 'year_built' ], true ) ) {
                    $val = intval( $val );
                } else {
                    $val = sanitize_text_field( $val );
                }
                update_post_meta( $post_id, self::META_PREFIX . $meta_key, $val );
            } else {
                // For unchecked/empty fields we might want to delete meta
                // leave as is to avoid accidental deletion
            }
        }

        // Save amenities
        if ( isset( $_POST['wpr_amen'] ) && is_array( $_POST['wpr_amen'] ) ) {
            foreach ( $this->amenities as $a_key => $label ) {
                $save = isset( $_POST['wpr_amen'][ $a_key ] ) ? 1 : 0;
                update_post_meta( $post_id, self::META_PREFIX . 'amen_' . $a_key, $save );
            }
        } else {
            // ensure amenities cleared when none
            foreach ( $this->amenities as $a_key => $label ) {
                update_post_meta( $post_id, self::META_PREFIX . 'amen_' . $a_key, 0 );
            }
        }

        // Save gallery (CSV of attachment IDs)
        if ( isset( $_POST['wpr_gallery'] ) ) {
            $raw = sanitize_text_field( $_POST['wpr_gallery'] );
            // sanitize list of integers
            $ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
            $csv = implode( ',', $ids );
            update_post_meta( $post_id, self::META_PREFIX . 'gallery', $csv );
        }

        // Optionally save property_type as meta (we rely on taxonomy) - no action here
    }

    // Convenience helper: return amenities array for frontend display
    public static function get_amenities( $post_id ) {
        $inst = new self();
        $out = [];
        foreach ( $inst->amenities as $k => $label ) {
            $val = get_post_meta( $post_id, self::META_PREFIX . 'amen_' . $k, true );
            if ( $val ) $out[ $k ] = $label;
        }
        return $out;
    }

} // end class

// Instantiate
new Metabox();
