<?php
/**
 * Test script for WP Rentals Plugin
 * Run this script to verify that all modules are loading correctly
 */

// Include WordPress core
define('WP_USE_THEMES', false);
require_once('wp-load.php');

echo "Testing WP Rentals Plugin...\n\n";

// Check if main plugin file exists
if (file_exists('wp-rentals-fixed.php')) {
    echo "✓ Main plugin file exists\n";
} else {
    echo "✗ Main plugin file missing\n";
}

// Check required classes
$classes = [
    'ZK\Rentals\Core',
    'ZK\Rentals\CPT',
    'ZK\Rentals\Metabox',
    'ZK\Rentals\Search',
    'ZK\Rentals\Contact',
    'ZK\Rentals\Favorites',
    'ZK\Rentals\Payment',
    'RP_Email'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ Class $class loaded\n";
    } else {
        echo "✗ Class $class not found\n";
    }
}

// Check if CPT is registered
if (post_type_exists('property')) {
    echo "✓ Property CPT registered\n";
} else {
    echo "✗ Property CPT not registered\n";
}

// Check if taxonomies are registered
$taxonomies = ['property_type', 'property_city', 'property_area'];
foreach ($taxonomies as $taxonomy) {
    if (taxonomy_exists($taxonomy)) {
        echo "✓ Taxonomy $taxonomy registered\n";
    } else {
        echo "✗ Taxonomy $taxonomy not registered\n";
    }
}

// Check if REST API endpoint is registered
$rest_routes = rest_get_server()->get_routes();
$wpr_routes = array_filter($rest_routes, function($key) {
    return strpos($key, 'wpr/v1') !== false;
}, ARRAY_FILTER_USE_KEY);

if (!empty($wpr_routes)) {
    echo "✓ REST API routes registered\n";
} else {
    echo "✗ REST API routes not registered\n";
}

echo "\nTest completed.\n";

// Additional test: Check if admin JS file exists and has content
if (file_exists('assets/js/admin.js') && filesize('assets/js/admin.js') > 0) {
    echo "✓ Admin JS file exists and has content\n";
} else {
    echo "✗ Admin JS file missing or empty\n";
}

// Additional test: Check if CSS files exist
if (file_exists('assets/css/style.css') && filesize('assets/css/style.css') > 0) {
    echo "✓ Main CSS file exists and has content\n";
} else {
    echo "✗ Main CSS file missing or empty\n";
}

if (file_exists('assets/css/admin.css') && filesize('assets/css/admin.css') > 0) {
    echo "✓ Admin CSS file exists and has content\n";
} else {
    echo "✗ Admin CSS file missing or empty\n";
}
