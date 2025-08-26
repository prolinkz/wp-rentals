
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
?>
<div class="wpr-card">
    <?php if ( $thumb ) : ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>"><img src="<?php echo esc_url( $thumb ); ?>" alt="" /></a>
    <?php endif; ?>
    <h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h3>
    <div class="wpr-card-price"><?php echo esc_html( $currency . ' ' . number_format_i18n( floatval( $price ) ) ); ?></div>
    <p class="wpr-card-excerpt"><?php echo wp_kses_post( wp_trim_words( $excerpt, 18 ) ); ?></p>
    <p><a class="button" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'View Details', 'wp-rentals' ); ?></a></p>
</div>