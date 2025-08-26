<?php
// File: uninstall.php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Delete options
delete_option( 're_listings_settings' );

// Optionally delete posts & meta
$properties = get_posts([
    'post_type' => 'property',
    'numberposts' => -1,
    'post_status' => 'any'
]);

foreach ( $properties as $property ) {
    wp_delete_post( $property->ID, true );
}
