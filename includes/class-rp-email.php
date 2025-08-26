<?php
// File: includes/class-rp-email.php

if ( ! defined( 'ABSPATH' ) ) exit;

class RP_Email {

    // Send email wrapper
    public static function send_email( $to, $subject, $message, $headers = [] ) {
        $default_headers = ['Content-Type: text/html; charset=UTF-8'];
        $headers = array_merge($default_headers, $headers);

        return wp_mail($to, $subject, $message, $headers);
    }

    // Notify admin of new property
    public static function notify_admin_new_property( $property_id ) {
        $admin_email = get_option('admin_email');
        $property = get_post($property_id);
        $subject = "New Property Submitted: " . $property->post_title;
        $message = "<h3>New Property Listing Submitted</h3>
                    <p><strong>Title:</strong> {$property->post_title}</p>
                    <p><a href='" . admin_url("post.php?post={$property_id}&action=edit") . "'>Review Property</a></p>";
        self::send_email($admin_email, $subject, $message);
    }

    // Notify user on property submission
    public static function notify_user_property_submission( $user_email, $property_id ) {
        $property = get_post($property_id);
        $subject = "Your Property Has Been Submitted!";
        $message = "<p>Hi,</p>
                    <p>Your property <strong>{$property->post_title}</strong> has been submitted successfully.</p>
                    <p>We’ll notify you once it’s reviewed.</p>";
        self::send_email($user_email, $subject, $message);
    }

    // Notify owner on inquiry
    public static function notify_owner_inquiry( $owner_email, $property_id, $inquiry_data ) {
        $property = get_post($property_id);
        $subject = "New Inquiry for {$property->post_title}";
        $message = "<p>You have received a new inquiry:</p>
                    <p><strong>Name:</strong> {$inquiry_data['name']}<br>
                    <strong>Email:</strong> {$inquiry_data['email']}<br>
                    <strong>Message:</strong> {$inquiry_data['message']}</p>";
        self::send_email($owner_email, $subject, $message);
    }

    // Notify user when payment verified/rejected
    public static function notify_payment_status( $user_email, $status ) {
        $subject = "Payment Update - Your Listing";
        $message = $status === 'verified'
            ? "<p>Your payment has been verified. Your listing is now live.</p>"
            : "<p>Your payment was rejected. Please contact support for details.</p>";
        self::send_email($user_email, $subject, $message);
    }
}
