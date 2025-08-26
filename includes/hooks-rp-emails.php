<?php
// File: includes/hooks-rp-emails.php

if ( ! defined( 'ABSPATH' ) ) exit;

// Hook on property submission
add_action('rp_property_submitted', function( $property_id, $user_email ) {
    RP_Email::notify_admin_new_property($property_id);
    RP_Email::notify_user_property_submission($user_email, $property_id);
}, 10, 2);

// Hook on inquiry form submission
add_action('rp_inquiry_submitted', function( $property_id, $owner_email, $inquiry_data ) {
    RP_Email::notify_owner_inquiry($owner_email, $property_id, $inquiry_data);
}, 10, 3);

// Hook on payment verification/rejection
add_action('rp_payment_status_changed', function( $user_email, $status ) {
    RP_Email::notify_payment_status($user_email, $status);
}, 10, 2);
