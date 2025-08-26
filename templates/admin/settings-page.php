<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Load options with defaults
$main_opts = wp_parse_args( get_option( 'wpr_rentals_options', [] ), [
    'default_currency' => 'PKR',
    'area_unit' => 'Marla',
    'listings_per_page' => 12,
    'default_status' => 'available',
    'enable_frontend_submission' => 0,
    'maps_provider' => 'google',
    'maps_api_key' => '',
] );

$contact_opts = wp_parse_args( get_option( 'wpr_contact_settings', [] ), [
    'notify_admin' => 1,
    'admin_email' => get_option( 'admin_email' ),
    'from_name' => get_bloginfo( 'name' ),
    'from_email' => '',
    'subject_template' => 'New inquiry for {property_title}',
    'save_inquiries' => 1,
    'rate_limit_per_hour' => 20,
] );

$payment_opts = wp_parse_args( get_option( 'wpr_payment_settings', [] ), [
    'enable_paid' => 0,
    'featured_price' => 0,
    'currency' => $main_opts['default_currency'],
    'gateway' => 'manual',
    'paypal_business' => '',
    'paypal_sandbox' => 1,
    'easypaisa_number' => '',
    'jazzcash_number' => '',
    'bank_details' => '',
] );
?>
<div class="wrap wp-rentals-admin">
    <h1><?php esc_html_e( 'WP Rentals Settings', 'wp-rentals' ); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="#wpr-tab-general" class="nav-tab nav-tab-active wpr-tab" data-target="#wpr-tab-general"><?php esc_html_e( 'General', 'wp-rentals' ); ?></a>
        <a href="#wpr-tab-maps" class="nav-tab wpr-tab" data-target="#wpr-tab-maps"><?php esc_html_e( 'Maps', 'wp-rentals' ); ?></a>
        <a href="#wpr-tab-inquiries" class="nav-tab wpr-tab" data-target="#wpr-tab-inquiries"><?php esc_html_e( 'Inquiries', 'wp-rentals' ); ?></a>
        <a href="#wpr-tab-payments" class="nav-tab wpr-tab" data-target="#wpr-tab-payments"><?php esc_html_e( 'Payments', 'wp-rentals' ); ?></a>
        <a href="#wpr-tab-advanced" class="nav-tab wpr-tab" data-target="#wpr-tab-advanced"><?php esc_html_e( 'Advanced', 'wp-rentals' ); ?></a>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'wpr_settings_group' ); ?>

        <div id="wpr-tab-general" class="wpr-tab-panel active" style="padding:16px;background:#fff;border:1px solid #e1e1e1;margin-top:8px;">
            <h2><?php esc_html_e( 'General Settings', 'wp-rentals' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Default Currency', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_rentals_options[default_currency]">
                            <?php $curr = [ 'PKR','USD','EUR','GBP' ]; foreach ( $curr as $c ) : ?>
                                <option value="<?php echo esc_attr( $c ); ?>" <?php selected( $main_opts['default_currency'], $c ); ?>><?php echo esc_html( $c ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Area Unit', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_rentals_options[area_unit]">
                            <?php $units = [ 'Marla','Square Feet','Kanal' ]; foreach ( $units as $u ) : ?>
                                <option value="<?php echo esc_attr( $u ); ?>" <?php selected( $main_opts['area_unit'], $u ); ?>><?php echo esc_html( $u ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Listings Per Page (archive/search)', 'wp-rentals' ); ?></th>
                    <td><input type="number" name="wpr_rentals_options[listings_per_page]" value="<?php echo esc_attr( $main_opts['listings_per_page'] ); ?>" class="small-text" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Default Listing Status', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_rentals_options[default_status]">
                            <option value="available" <?php selected( $main_opts['default_status'], 'available' ); ?>><?php esc_html_e( 'Available', 'wp-rentals' ); ?></option>
                            <option value="rented" <?php selected( $main_opts['default_status'], 'rented' ); ?>><?php esc_html_e( 'Rented', 'wp-rentals' ); ?></option>
                            <option value="pending" <?php selected( $main_opts['default_status'], 'pending' ); ?>><?php esc_html_e( 'Pending', 'wp-rentals' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Enable Frontend Submission', 'wp-rentals' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="wpr_rentals_options[enable_frontend_submission]" value="1" <?php checked( $main_opts['enable_frontend_submission'], 1 ); ?> /> <?php esc_html_e( 'Allow users to submit property listings from the frontend (requires moderation).', 'wp-rentals' ); ?></label>
                    </td>
                </tr>
            </table>
            <p class="submit"><?php submit_button(); ?></p>
        </div>

        <div id="wpr-tab-maps" class="wpr-tab-panel" style="padding:16px;background:#fff;border:1px solid #e1e1e1;margin-top:8px;display:none;">
            <h2><?php esc_html_e( 'Maps & Location', 'wp-rentals' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Maps Provider', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_rentals_options[maps_provider]">
                            <option value="google" <?php selected( $main_opts['maps_provider'], 'google' ); ?>><?php esc_html_e( 'Google Maps', 'wp-rentals' ); ?></option>
                            <option value="leaflet" <?php selected( $main_opts['maps_provider'], 'leaflet' ); ?>><?php esc_html_e( 'Leaflet (OpenStreetMap)', 'wp-rentals' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Google Maps API Key', 'wp-rentals' ); ?></th>
                    <td>
                        <input type="text" name="wpr_rentals_options[maps_api_key]" value="<?php echo esc_attr( $main_opts['maps_api_key'] ); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Provide API key to enable map pinning and map display (for Google Maps).', 'wp-rentals' ); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit"><?php submit_button(); ?></p>
        </div>

        <div id="wpr-tab-inquiries" class="wpr-tab-panel" style="padding:16px;background:#fff;border:1px solid #e1e1e1;margin-top:8px;display:none;">
            <h2><?php esc_html_e( 'Inquiry Settings', 'wp-rentals' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Notify Admin', 'wp-rentals' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="wpr_contact_settings[notify_admin]" value="1" <?php checked( $contact_opts['notify_admin'], 1 ); ?> /> <?php esc_html_e( 'Send a copy of inquiries to the site admin', 'wp-rentals' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Admin Email', 'wp-rentals' ); ?></th>
                    <td><input type="email" name="wpr_contact_settings[admin_email]" value="<?php echo esc_attr( $contact_opts['admin_email'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'From Name / Email', 'wp-rentals' ); ?></th>
                    <td>
                        <input type="text" name="wpr_contact_settings[from_name]" value="<?php echo esc_attr( $contact_opts['from_name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
                        <input type="email" name="wpr_contact_settings[from_email]" value="<?php echo esc_attr( $contact_opts['from_email'] ); ?>" placeholder="optional" />
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Save Inquiries', 'wp-rentals' ); ?></th>
                    <td><label><input type="checkbox" name="wpr_contact_settings[save_inquiries]" value="1" <?php checked( $contact_opts['save_inquiries'], 1 ); ?> /> <?php esc_html_e( 'Store inquiries in the database (Property Inquiries CPT).', 'wp-rentals' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Rate Limit (per IP / hour)', 'wp-rentals' ); ?></th>
                    <td><input type="number" min="1" name="wpr_contact_settings[rate_limit_per_hour]" value="<?php echo esc_attr( $contact_opts['rate_limit_per_hour'] ); ?>" class="small-text" /></td>
                </tr>
            </table>
            <p class="submit"><?php submit_button(); ?></p>
        </div>

        <div id="wpr-tab-payments" class="wpr-tab-panel" style="padding:16px;background:#fff;border:1px solid #e1e1e1;margin-top:8px;display:none;">
            <h2><?php esc_html_e( 'Payments & Monetization', 'wp-rentals' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable Paid Listings', 'wp-rentals' ); ?></th>
                    <td><label><input type="checkbox" name="wpr_payment_settings[enable_paid]" value="1" <?php checked( $payment_opts['enable_paid'], 1 ); ?> /> <?php esc_html_e( 'Charge users to submit listings or to feature listings.', 'wp-rentals' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Featured Listing Price', 'wp-rentals' ); ?></th>
                    <td>
                        <input type="number" name="wpr_payment_settings[featured_price]" value="<?php echo esc_attr( $payment_opts['featured_price'] ); ?>" step="0.01" />
                        <span class="description"><?php esc_html_e( 'Price to make a listing featured (per listing).', 'wp-rentals' ); ?></span>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Payment Currency', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_payment_settings[currency]">
                            <?php foreach ( [ 'PKR','USD','EUR','GBP' ] as $c ) : ?>
                                <option value="<?php echo esc_attr( $c ); ?>" <?php selected( $payment_opts['currency'], $c ); ?>><?php echo esc_html( $c ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Payment Gateway', 'wp-rentals' ); ?></th>
                    <td>
                        <select name="wpr_payment_settings[gateway]">
                            <option value="manual" <?php selected( $payment_opts['gateway'], 'manual' ); ?>><?php esc_html_e( 'Manual / Offline only', 'wp-rentals' ); ?></option>
                            <option value="paypal" <?php selected( $payment_opts['gateway'], 'paypal' ); ?>><?php esc_html_e( 'PayPal (optional)', 'wp-rentals' ); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'PayPal Business Email', 'wp-rentals' ); ?></th>
                    <td><input type="email" name="wpr_payment_settings[paypal_business]" value="<?php echo esc_attr( $payment_opts['paypal_business'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'PayPal Sandbox', 'wp-rentals' ); ?></th>
                    <td><label><input type="checkbox" name="wpr_payment_settings[paypal_sandbox]" value="1" <?php checked( $payment_opts['paypal_sandbox'], 1 ); ?> /> <?php esc_html_e( 'Use PayPal Sandbox for testing', 'wp-rentals' ); ?></label></td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'EasyPaisa Number / QR Info', 'wp-rentals' ); ?></th>
                    <td>
                        <input type="text" name="wpr_payment_settings[easypaisa_number]" value="<?php echo esc_attr( $payment_opts['easypaisa_number'] ); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Enter EasyPaisa mobile number or short QR instructions. If filled it will be shown to customers.', 'wp-rentals' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'JazzCash Number / QR Info', 'wp-rentals' ); ?></th>
                    <td>
                        <input type="text" name="wpr_payment_settings[jazzcash_number]" value="<?php echo esc_attr( $payment_opts['jazzcash_number'] ); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Enter JazzCash mobile number or short QR instructions. If filled it will be shown to customers.', 'wp-rentals' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Bank Details (Account title, IBAN, branch)', 'wp-rentals' ); ?></th>
                    <td>
                        <textarea name="wpr_payment_settings[bank_details]" rows="4" class="large-text"><?php echo esc_textarea( $payment_opts['bank_details'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Provide account title, IBAN, branch, and instructions for bank transfers. If filled it will be shown to customers.', 'wp-rentals' ); ?></p>
                    </td>
                </tr>

            </table>
            <p class="submit"><?php submit_button(); ?></p>
        </div>

        <div id="wpr-tab-advanced" class="wpr-tab-panel" style="padding:16px;background:#fff;border:1px solid #e1e1e1;margin-top:8px;display:none;">
            <h2><?php esc_html_e( 'Advanced', 'wp-rentals' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Development Mode', 'wp-rentals' ); ?></th>
                    <td><label><input type="checkbox" name="wpr_rentals_options[dev_mode]" value="1" <?php checked( $main_opts['dev_mode'] ?? 0, 1 ); ?> /> <?php esc_html_e( 'Enable verbose logging and debug features.', 'wp-rentals' ); ?></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Logs Folder', 'wp-rentals' ); ?></th>
                    <td><input type="text" name="wpr_rentals_options[logs_path]" value="<?php echo esc_attr( $main_opts['logs_path'] ?? WPR_PATH . 'logs/' ); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <p class="submit"><?php submit_button(); ?></p>
        </div>

    </form>
</div>

<style>
.wpr-tab-panel { display:none; }
.wpr-tab-panel.active { display:block; }
</style>

<script>
(function(){
    var tabs = document.querySelectorAll('.wpr-tab');
    tabs.forEach(function(tab){
        tab.addEventListener('click', function(e){
            e.preventDefault();
            document.querySelectorAll('.wpr-tab').forEach(function(t){ t.classList.remove('nav-tab-active'); });
            tab.classList.add('nav-tab-active');
            var target = tab.getAttribute('data-target');
            document.querySelectorAll('.wpr-tab-panel').forEach(function(panel){ panel.style.display = 'none'; panel.classList.remove('active'); });
            var el = document.querySelector(target);
            if ( el ) { el.style.display = 'block'; el.classList.add('active'); }
        });
    });
    // activate first tab
    var first = document.querySelectorAll('.wpr-tab')[0]; if ( first ) first.click();
})();
</script>
