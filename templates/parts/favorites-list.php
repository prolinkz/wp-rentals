// ------------------ templates/parts/favorites-list.php ------------------
<?php
/**
 * Simple favorites list part (used by the [wpr_favorites] shortcode if template part exists)
 */
if ( ! isset( $post ) && ! have_posts() ) return;
?>
<div class="wpr-favorites">
    <?php while ( have_posts() ) : the_post(); ?>
        <div class="wpr-fav-item">
            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
            <div><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></div>
            <p><a href="#" class="wpr-fav-btn" data-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php echo Favorites::is_favorited( get_the_ID() ) ? esc_html__( 'Saved', 'wp-rentals' ) : esc_html__( 'Save to Favorites', 'wp-rentals' ); ?></a></p>
        </div>
    <?php endwhile; wp_reset_postdata(); ?>
</div>
