<?php
/**
 * Class Version_Switcher.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

/**
 * Class handling plugin updates.
 *
 * @since 1.0.0
 */
trait Version_Switcher {

	/**
	 * Update the plugin to a specific version by zip URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url    The plugin update URL.
	 * @param string $branch The branch being used.
	 * @return array|false An array of results indexed by plugin file. False if the user has insufficient permissions, update lock is set, or unable to connect to the filesystem.
	 */
	public function switch_version( $url, $branch ) {
		static $switching_lock_key = 'amp_qa_tester_switching_lock';

		// Ensure user can perform plugin upgrades.
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		$switching_locked = get_transient( $switching_lock_key );
		if ( $switching_locked ) {
			return false;
		}

		// Lock updating. Lock always expires after 15 seconds.
		set_transient( $switching_lock_key, true, 15 );

		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php' );

		$current     = get_site_transient( 'update_plugins' );
		$original    = $current;
		$plugin_name = Plugin::PLUGIN_SLUG . '/' . Plugin::PLUGIN_SLUG . '.php';

		// Remove plugin from the no_update list if present.
		unset( $current->no_update[ $plugin_name ] );

		// Set the plugin to update from our custom URL.
		$current->response[ $plugin_name ]->package     = $url;
		$current->response[ $plugin_name ]->new_version = Plugin::PLUGIN_SLUG . '@' . $branch;

		// Temporarily replace the site plugin upgrade info and upgrade the plugin.
		set_site_transient( 'update_plugins', $current );
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );

		// Run the upgrade.
		$result = $upgrader->bulk_upgrade( [ $plugin_name ] );

		// Restore the site plugin upgrade info.
		set_site_transient( 'update_plugins', $original );

		// Unlock updating.
		delete_transient( $switching_lock_key );

		return $result;
	}
}
