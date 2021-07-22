<?php
/**
 * Plugin uninstall file.
 *
 * @package AMP
 */

// If uninstall.php is not called by WordPress, then die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once AMP__DIR__ . '/includes/uninstall-functions.php';

if ( is_multisite() ) {
	$site_ids = get_sites(
		[
			'fields'                 => 'ids',
			'number'                 => '',
			'update_site_cache'      => false,
			'update_site_meta_cache' => false,
		]
	);
	$site_ids = ( ! empty( $site_ids ) && is_array( $site_ids ) ) ? $site_ids : [];

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		AmpProject\AmpWP\remove_plugin_data();
	}
	restore_current_blog();
} else {
	AmpProject\AmpWP\remove_plugin_data();
}
