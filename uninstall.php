<?php
/**
 * Uninstall handler.
 *
 * @package WP_Builder
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'wp_builder_provider' );
delete_option( 'wp_builder_parts' );

// Remove builder meta from pages (kept minimal: only our own keys).
delete_post_meta_by_key( '_wpb_active' );
delete_post_meta_by_key( '_wpb_data' );
delete_post_meta_by_key( '_wpb_part_type' );

// Clean up chat session transients.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpb_chat_%' OR option_name LIKE '_transient_timeout_wpb_chat_%'" );
