<?php
/**
 * Trait Version_Switcher.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use WP_Error;
use WP_Filesystem_Base;

/**
 * Trait housing logic related to plugin updates.
 *
 * @since 1.0.0
 */
trait Version_Switcher {

	/**
	 * Update the plugin to a specific version by zip URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url          The plugin update URL.
	 * @param string $build_id     The build ID being used.
	 * @param string $build_origin The origin of the build.
	 * @return array|false|WP_Error An array of results indexed by plugin file.
	 *                             False if the user has insufficient permissions, update lock is set, or unable to connect to the filesystem.
	 *                             Otherwise, a WP_Error.
	 */
	public function switch_version( $url, $build_id, $build_origin ) {
		$switching_lock_key = 'amp_qa_tester_switching_lock';

		// Ensure plugin build for develop branch or PR exists before attempting to switch.
		if ( 'release' !== $build_origin ) {
			$request = wp_safe_remote_head( $url );

			if ( 200 !== $request['response']['code'] ) {
				$name = 'branch' === $build_origin ? $build_id . ' branch' : 'PR #' . $build_id;
				return new WP_Error(
					'build_not_found',
					sprintf(
					/* translators: %s: Build name */
						__( 'The build for %s could not be retrieved', 'amp-qa-tester' ),
						$name
					),
					[ 'status' => 400 ]
				);
			}
		}

		// Ensure user can perform plugin upgrades.
		if ( ! current_user_can( 'update_plugins' ) ) {
			return new WP_Error(
				'insufficient_permissions',
				__( 'User does not have the permission to update plugins', 'amp-qa-tester' ),
				[ 'status' => 403 ]
			);
		}

		$switching_locked = get_transient( $switching_lock_key );
		if ( $switching_locked ) {
			return new WP_Error(
				'switching_locked',
				__( 'The plugin is in the process of being updated', 'amp-qa-tester' ),
				[ 'status' => 400 ]
			);
		}

		// Lock updating. Lock always expires after 15 seconds.
		set_transient( $switching_lock_key, true, 15 );

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

		$current     = get_site_transient( 'update_plugins' );
		$original    = $current;
		$plugin_name = Plugin::PLUGIN_SLUG . '/' . Plugin::PLUGIN_SLUG . '.php';

		// Remove plugin from the no_update list if present.
		unset( $current->no_update[ $plugin_name ] );

		// Set the plugin to update from our custom URL.
		$current->response[ $plugin_name ] = (object) [
			'id'          => str_replace( '.php', '', $plugin_name ),
			'slug'        => Plugin::PLUGIN_SLUG,
			'plugin'      => $plugin_name,
			'new_version' => Plugin::PLUGIN_SLUG . '@' . $build_id,
			'url'         => 'https://wordpress.org/plugins/' . Plugin::PLUGIN_SLUG,
			'package'     => $url,
		];

		// Temporarily replace the site plugin upgrade info and upgrade the plugin.
		set_site_transient( 'update_plugins', $current );
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );

		// Ensure the plugin folder has the correct name before installing.
		add_filter( 'upgrader_source_selection', [ $this, 'ensure_correct_folder' ] );

		// Run the upgrade.
		$result = $upgrader->bulk_upgrade( [ $plugin_name ] );

		remove_filter( 'upgrader_source_selection', [ $this, 'ensure_correct_folder' ] );

		// Restore the site plugin upgrade info.
		set_site_transient( 'update_plugins', $original );

		// Unlock updating.
		delete_transient( $switching_lock_key );

		return $result;
	}

	/**
	 * Ensure the folder name of the AMP plugin is set to its plugin slug.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 *
	 * @param string $source File source location.
	 * @return string|WP_Error New file source location, or WP_Error if the folder could not be renamed.
	 */
	public function ensure_correct_folder( $source ) {
		global $wp_filesystem;

		$folder_name = basename( $source );

		if ( Plugin::PLUGIN_SLUG === $folder_name ) {
			return $source;
		}

		$new_source = trailingslashit( trailingslashit( dirname( $source ) ) . Plugin::PLUGIN_SLUG );
		$moved      = $wp_filesystem->move( $source, $new_source );

		if ( ! $moved ) {
			return new WP_Error(
				'amp_plugin_folder_not_correct',
				__( 'Failed to rename the AMP plugin folder before installing the plugin.', 'amp-qa-tester' ),
				[
					'old_source' => $source,
					'new_source' => $new_source,
				]
			);
		}

		return $new_source;
	}
}
