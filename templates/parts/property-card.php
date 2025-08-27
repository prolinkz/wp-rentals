
<?php
/**
 * Template part: property card
 * Usage: get_template_part( 'wp-rentals/templates/parts/property-card' )
 * 
 * ## 3) Partial: `templates/parts/property-card.php`
 * 
 * Path: `wp-rentals/templates/parts/property-card.php`

 */ 
if ( ! isset( $post ) ) return;
$id = get_the_ID();
$price = get_post_meta( $id, '_wpr_price', true );
$currency = get_post_meta( $id, '_wpr_currency', true ) ?: 'PKR';
$thumb = get_the_post_thumbnail_url( $id, 'medium' );
$excerpt = get_the_excerpt();

// Get additional meta information
$bedrooms = get_post_meta( $id, '_wpr_bedrooms', true );
$bathrooms = get_post_meta( $id, '_wpr_bathrooms', true );
$area_value = get_post_meta( $id, '_wpr_area_value', true );
$area_unit = get_post_meta( $id, '_wpr_area_unit', true ) ?: 'Marla';
$parking = get_post_meta( $id, '_wpr_parking', true );

// Get taxonomy terms
$property_types = get_the_terms( $id, 'property_type' );
$cities = get_the_terms( $id, 'property_city' );
$areas = get_the_terms( $id, 'property_area' );

// Limit excerpt to 60-70 words
$limited_excerpt = wp_trim_words( $excerpt, 65, '...' );
?>
<div class="wpr-card">
    <?php if ( $thumb ) : ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="wpr-card-image" />
        </a>
    <?php endif; ?>
    
    <div class="wpr-card-content">
        <h3 class="wpr-card-title">
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a>
        </h3>
        
        <div class="wpr-card-price">
            <?php echo esc_html( $currency . ' ' . number_format_i18n( floatval( $price ) ) ); ?> 
            <span class="wpr-rent-period"><?php esc_html_e( 'per month', 'wp-rentals' ); ?></span>
        </div>
        
        <!-- Two-column meta information -->
        <div class="wpr-card-meta">
            <div class="wpr-meta-column">
                <?php if ( $bedrooms ) : ?>
                    <div class="wpr-meta-item">
                        <span class="wpr-meta-label"><?php esc_html_e( 'Bedrooms:', 'wp-rentals' ); ?></span>
                        <span class="wpr-meta-value"><?php echo esc_html( $bedrooms ); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ( $bathrooms ) : ?>
                    <div class="wpr-meta-item">
                        <span class="wpr-meta-label"><?php esc_html_e( 'Bathrooms:', 'wp-rentals' ); ?></span>
                        <span class="wpr-meta-value"><?php echo esc_html( $bathrooms ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="wpr-meta-column">
                <?php if ( $area_value ) : ?>
                    <div class="wpr-meta-item">
                        <span class="wpr-meta-label"><?php esc_html_e( 'Area:', 'wp-rentals' ); ?></span>
                        <span class="wpr-meta-value"><?php echo esc_html( $area_value . ' ' . $area_unit ); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ( $parking ) : ?>
                    <div class="wpr-meta-item">
                        <span class="wpr-meta-label"><?php esc_html_e( 'Parking:', 'wp-rentals' ); ?></span>
                        <span class="wpr-meta-value"><?php echo esc_html( $parking ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Location information -->
        <div class="wpr-card-location">
            <?php if ( $cities && ! is_wp_error( $cities ) ) : ?>
                <span class="wpr-location-item">
                    <?php echo esc_html( implode( ', ', wp_list_pluck( $cities, 'name' ) ) ); ?>
                </span>
            <?php endif; ?>
            
            <?php if ( $areas && ! is_wp_error( $areas ) ) : ?>
                <span class="wpr-location-item">
                    <?php echo esc_html( implode( ', ', wp_list_pluck( $areas, 'name' ) ) ); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Limited description -->
        <div class="wpr-card-description">
            <?php echo wp_kses_post( $limited_excerpt ); ?>
        </div>
        
        <div class="wpr-card-actions">
            <a class="wpr-button" href="<?php echo esc_url( get_permalink() ); ?>">
                <?php esc_html_e( 'View Details', 'wp-rentals' ); ?>
            </a>
        </div>
    </div>
</div>
