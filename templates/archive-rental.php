<?php
/**
 * Archive: Properties
 * Place in: wp-rentals/templates/archive-property.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="main" class="site-main" role="main" style="max-width:1200px;margin:20px auto;padding:0 16px;">
    <header style="display:flex;justify-content:space-between;align-items:center;">
        <h1><?php post_type_archive_title(); ?></h1>
        <div>
            <button id="wpr-toggle-grid" class="button">Grid</button>
            <button id="wpr-toggle-list" class="button">List</button>
        </div>
    </header>

    <div id="wpr-archive-list" class="wpr-archive-list wpr-grid" style="margin-top:18px;"></div>
    <div id="wpr-archive-pagination" style="margin-top:14px;"></div>
</main>

<script>
// Simple initial load via REST search endpoint
(function(){
    var container = document.getElementById('wpr-archive-list');
    var url = '<?php echo esc_url( rest_url( 'wpr/v1/search' ) ); ?>';
    fetch(url).then(function(r){ return r.json(); }).then(function(data){
        // render same as search.js by reusing that script; if search.js exists it will auto-run
        if ( window.renderWPRResults ) window.renderWPRResults( '#wpr-archive-list', data );
    });
})();
</script>

<?php get_footer(); ?>