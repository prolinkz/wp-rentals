<?php
/**
 * Template: Single Property
 * Place in: wp-rentals/templates/single-property.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
    $id = get_the_ID();
    $price = get_post_meta( $id, '_wpr_price', true );
    $currency = get_post_meta( $id, '_wpr_currency', true ) ?: 'PKR';
    $beds = get_post_meta( $id, '_wpr_bedrooms', true );
    $baths = get_post_meta( $id, '_wpr_bathrooms', true );
    $area_value = get_post_meta( $id, '_wpr_area_value', true );
    $area_unit = get_post_meta( $id, '_wpr_area_unit', true );
    $address = get_post_meta( $id, '_wpr_address', true );
    $gallery_csv = get_post_meta( $id, '_wpr_gallery', true );
    $gallery = $gallery_csv ? array_map( 'intval', explode( ',', $gallery_csv ) ) : [];
    $video = get_post_meta( $id, '_wpr_video_url', true );
    $status = get_post_meta( $id, '_wpr_status', true );
    $amenities = ZK\Rentals\Metabox::get_amenities( $id );
    ?>

    <main id="main" class="site-main wpr-single-property" role="main" style="max-width:1100px;margin:20px auto;padding:0 16px;">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >

            <header class="wpr-property-header" style="display:flex;gap:20px;align-items:flex-start;">
                <div style="flex:1;">
                    <h1><?php the_title(); ?></h1>
                    <p style="color:#666;margin-top:6px;"><?php echo esc_html( $address ); ?></p>
                    <div style="margin-top:12px;font-size:20px;font-weight:700;"><?php echo esc_html( $currency . ' ' . number_format_i18n( floatval( $price ) ) ); ?> <span style="font-weight:400;font-size:14px;color:#777;">/ month</span></div>
                </div>
                <div style="width:240px;text-align:right;">
                    <div style="margin-bottom:8px;"><strong>Status:</strong> <?php echo esc_html( ucfirst( $status ) ); ?></div>
                    <div style="margin-bottom:8px;">
                        <a href="#wpr-contact" class="button button-primary">Inquire</a>
                    </div>
                    <div>
                        <button class="button wpr-fav-btn" data-id="<?php echo esc_attr( $id ); ?>" aria-pressed="false"><?php esc_html_e( 'Save to Favorites', 'wp-rentals' ); ?></button>
                    </div>
                </div>
            </header>

            <section class="wpr-media" style="margin-top:18px;">
                <?php if ( $gallery ) : ?>
                    <div class="wpr-gallery" style="display:flex;gap:8px;overflow:auto;margin-bottom:12px;">
                        <?php foreach ( $gallery as $aid ) : $src = wp_get_attachment_image_url( $aid, 'large' ); if ( $src ) : ?>
                            <div style="flex:0 0 320px;"><img src="<?php echo esc_url( $src ); ?>" style="width:100%;height:auto;border-radius:6px;" alt="" /></div>
                        <?php endif; endforeach; ?>
                    </div>
                <?php elseif ( has_post_thumbnail() ) : ?>
                    <div><?php the_post_thumbnail( 'large' ); ?></div>
                <?php endif; ?>

                <?php if ( $video ) : ?>
                    <div style="margin-top:12px;">
                        <?php echo wp_oembed_get( esc_url( $video ) ); ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="wpr-content" style="display:flex;gap:24px;margin-top:18px;">
                <div style="flex:2;">
                    <h2><?php esc_html_e( 'Property Description', 'wp-rentals' ); ?></h2>
                    <div class="wpr-description"><?php the_content(); ?></div>

                    <h3 style="margin-top:18px;"><?php esc_html_e( 'Specifications', 'wp-rentals' ); ?></h3>
                    <ul>
                        <li><strong><?php esc_html_e( 'Bedrooms:', 'wp-rentals' ); ?></strong> <?php echo esc_html( $beds ); ?></li>
                        <li><strong><?php esc_html_e( 'Bathrooms:', 'wp-rentals' ); ?></strong> <?php echo esc_html( $baths ); ?></li>
                        <li><strong><?php esc_html_e( 'Area:', 'wp-rentals' ); ?></strong> <?php echo esc_html( $area_value . ' ' . $area_unit ); ?></li>
                        <li><strong><?php esc_html_e( 'Parking:', 'wp-rentals' ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_wpr_parking', true ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Year Built:', 'wp-rentals' ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_wpr_year_built', true ) ); ?></li>
                    </ul>

                    <?php if ( $amenities ) : ?>
                        <h3><?php esc_html_e( 'Amenities', 'wp-rentals' ); ?></h3>
                        <ul class="wpr-amenities">
                            <?php foreach ( $amenities as $k => $label ) : ?>
                                <li><?php echo esc_html( $label ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                </div>

                <aside style="flex:1;">
                    <div style="border:1px solid #eee;padding:12px;border-radius:6px;background:#fff;">
                        <h4><?php esc_html_e( 'Contact Owner', 'wp-rentals' ); ?></h4>
                        <p><?php esc_html_e( 'Use the form below to send an inquiry. The owner will get an email.', 'wp-rentals' ); ?></p>
                        <?php echo do_shortcode( '[wpr_inquiry]' ); // Canvas 6 will implement this shortcode ?>
                    </div>

                    <?php if ( get_post_meta( $id, '_wpr_lat', true ) && get_post_meta( $id, '_wpr_lng', true ) ) : ?>
                        <div style="margin-top:12px;border:1px solid #eee;padding:8px;border-radius:6px;background:#fff;">
                            <h4><?php esc_html_e( 'Location', 'wp-rentals' ); ?></h4>
                            <div id="wpr-single-map" style="width:100%;height:200px;background:#f5f5f5;"></div>
                            <script>
                                (function(){
                                    var lat = <?php echo json_encode( floatval( get_post_meta( $id, '_wpr_lat', true ) ) ); ?>;
                                    var lng = <?php echo json_encode( floatval( get_post_meta( $id, '_wpr_lng', true ) ) ); ?>;
                                    window.WPR_MAP_INIT = window.WPR_MAP_INIT || [];
                                    window.WPR_MAP_INIT.push({ el:'#wpr-single-map', lat:lat, lng:lng, zoom:15 });
                                })();
                            </script>
                        </div>
                    <?php endif; ?>
                </aside>
            </section>

        </article>
    </main>

<?php
endwhile;
get_footer();




## 6) Important: Template Loader Patch

Your Core class `template_loader()` (in `includes/class-core.php`) currently looks for `rental_property` templates. Ensure it checks the actual post type `property`. Replace the method with this snippet:

```php
public function template_loader( $template ) {
    if ( is_singular( 'property' ) ) {
        $custom = $this->locate_template( 'single-property.php' );
        if ( $custom ) return $custom;
    }
    if ( is_post_type_archive( 'property' ) ) {
        $custom = $this->locate_template( 'archive-property.php' );
        if ( $custom ) return $custom;
    }
    return $template;
}
