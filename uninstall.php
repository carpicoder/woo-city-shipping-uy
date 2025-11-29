<?php
/**
 * Uninstall script for Woo City Shipping UY
 *
 * Removes all plugin data when the plugin is deleted.
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit;
}

// Delete plugin options
delete_option( 'wc_city_select_shipping_rules' );

// For multisite
if ( is_multisite() ) {
global $wpdb;
$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
foreach ( $blog_ids as $blog_id ) {
switch_to_blog( $blog_id );
delete_option( 'wc_city_select_shipping_rules' );
restore_current_blog();
}
}
