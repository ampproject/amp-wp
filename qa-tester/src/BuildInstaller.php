<?php
/**
 * Class BuildInstaller.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use WP_Ajax_Upgrader_Skin;
use WP_Error;

/**
 * Class housing logic related to installing plugin builds.
 *
 * @since 1.0.0
 */
class BuildInstaller {

	const INSTALL_LOCK_KEY = 'amp_qa_tester_switching_lock';

	/**
	 * Build ID.
	 *
	 * @var string
	 */
	protected $build_id;

	/**
	 * Origin of build. Whether from a tagged release, a branch or a PR.
	 *
	 * @var string
	 */
	protected $build_origin;

	/**
	 * Constructor.
	 *
	 * @param string $build_id     Build ID.
	 * @param string $build_origin Build origin.
	 */
	public function __construct( $build_id, $build_origin ) {
		$this->build_id     = $build_id;
		$this->build_origin = $build_origin;
	}

	/**
	 * Installs the build from the specified URL.
	 *
	 * @param string $url URL for build zip.
	 *
	 * @return bool|WP_Error Whether the install was successful or not, otherwise a `WP_Error` object.
	 */
	public function install( $url ) {
		$can_install = $this->can_install( $url );

		if ( ! $can_install || is_wp_error( $can_install ) ) {
			return $can_install;
		}

		// Lock updating. Lock always expires after 15 seconds.
		set_transient( self::INSTALL_LOCK_KEY, true, 15 );

		$plugin_upgrader = $this->get_upgrader();

		add_filter( 'upgrader_source_selection', [ $this, 'ensure_correct_plugin_dir' ] );

		$result = $plugin_upgrader->install( $url );

		remove_filter( 'upgrader_source_selection', [ $this, 'ensure_correct_plugin_dir' ] );

		// Unlock updating.
		delete_transient( self::INSTALL_LOCK_KEY );

		return $result;
	}

	/**
	 * Ensure the build is installed in the correct directory.
	 *
	 * @param string $source File source location.
	 *
	 * @return string|string[]|WP_Error
	 */
	public function ensure_correct_plugin_dir( $source ) {
		$slug_dir   = Plugin::PLUGIN_SLUG;
		$source_dir = basename( $source );

		if ( $slug_dir === $source_dir ) {
			return $source;
		}

		$new_source = substr_replace( $source, $slug_dir, strrpos( $source, $source_dir ), strlen( $source_dir ) );

		if ( $GLOBALS['wp_filesystem']->move( $source, $new_source ) ) {
			return $new_source;
		}

		return new WP_Error(
			'amp_plugin_install',
			__( 'Failed to move the AMP plugin to the correct directory.', 'amp-qa-tester' ),
			[
				'old_source' => $source,
				'new_source' => $new_source,
			]
		);
	}

	/**
	 * Determine if the build can be installed.
	 *
	 * @param string $url URL for build zip.
	 *
	 * @return bool|WP_Error Whether the install was successful or not, otherwise a `WP_Error` object.
	 */
	protected function can_install( $url ) {
		// Ensure user can perform plugin upgrades.
		if ( ! current_user_can( 'update_plugins' ) ) {
			return new WP_Error(
				'insufficient_permissions',
				__( 'User does not have the permission to update plugins', 'amp-qa-tester' ),
				[ 'status' => 403 ]
			);
		}

		// Ensure plugin build for the branch or PR exists before attempting to install.
		if ( 'branch' === $this->build_origin || 'pr' === $this->build_origin ) {
			$request = wp_safe_remote_head( $url );

			if ( 200 !== $request['response']['code'] ) {
				$error_message = 'branch' === $this->build_origin
					/* translators: %s: Branch name */
					? __( 'The build for the %s branch could not be retrieved', 'amp-qa-tester' )
					/* translators: %s: Pull request ID */
					: __( 'The build for PR #%s could not be retrieved', 'amp-qa-tester' );

				return new WP_Error(
					'build_not_found',
					sprintf( $error_message, $this->build_id ),
					[ 'status' => 400 ]
				);
			}
		}

		$switching_locked = get_transient( self::INSTALL_LOCK_KEY );
		if ( $switching_locked ) {
			return new WP_Error(
				'switching_locked',
				__( 'The plugin is in the process of being updated', 'amp-qa-tester' ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	/**
	 * Get the file upgrader.
	 *
	 * @return DestructivePluginUpgrader File upgrader.
	 */
	protected function get_upgrader() {
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

		return new DestructivePluginUpgrader( new WP_Ajax_Upgrader_Skin() );
	}
}
