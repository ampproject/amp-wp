<?php
/**
 * Class AdminBar.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use WP_Admin_Bar;
use WP_Dependencies;

/**
 * Class handling the plugin's admin bar menu.
 *
 * @since 1.0.0
 */
class AdminBar {

	const ASSET_HANDLE = 'amp-qa-tester-admin-bar';

	/**
	 * ID of the currently installed build.
	 *
	 * @var string
	 */
	protected $build_id;

	/**
	 * Origin of the build currently installed.
	 *
	 * @var string
	 */
	protected $build_origin;

	/**
	 * Last known installed build version.
	 *
	 * @var string
	 */
	protected $build_version;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$build_info = get_site_option( Plugin::ID_STORAGE_KEY );

		if ( false === $build_info ) {
			return;
		}

		$this->build_id      = $build_info['build_id'];
		$this->build_origin  = $build_info['build_origin'];
		$this->build_version = $build_info['build_version'];
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		add_action( 'admin_bar_menu', [ $this, 'add_menu_button' ], 99 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_plugin_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugin_assets' ] );
	}

	/**
	 * Get all script dependencies for the provided dependencies.
	 *
	 * @param WP_Dependencies $dependencies_object Dependencies object (WP_Scripts or WP_Styles).
	 * @param string[]        $dependency_handles  Handles of the (enqueued) dependencies.
	 * @return string[] All dependencies of dependencies.
	 */
	private function get_all_dependencies( WP_Dependencies $dependencies_object, $dependency_handles ) {
		$original_handles_to_do     = $dependencies_object->to_do;
		$dependencies_object->to_do = [];
		$dependencies_object->all_deps( $dependency_handles, true );
		$all_dependencies           = $dependencies_object->to_do;
		$dependencies_object->to_do = $original_handles_to_do;
		return $all_dependencies;
	}

	/**
	 * Enqueue the plugin assets.
	 */
	public function enqueue_plugin_assets() {
		// Only active if the admin bar is showing.
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$asset_file   = Plugin::get_path( 'assets/js/admin-bar.asset.php' );
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		$dependencies[] = 'hoverIntent';

		// Enqueue scripts.
		wp_enqueue_script(
			self::ASSET_HANDLE,
			Plugin::get_asset_url( 'js/admin-bar.js' ),
			array_merge( [ 'admin-bar' ], $dependencies ),
			$version,
			true
		);

		// Enqueue styling for light DOM.
		wp_enqueue_style(
			self::ASSET_HANDLE,
			Plugin::get_asset_url( 'css/admin-bar-light-dom-compiled.css' ),
			[ 'admin-bar' ],
			$version
		);
		wp_styles()->add_data( 'amp-qa-tester-admin-bar-style', 'rtl', 'replace' );

		// Pass URL for CSS file to be loaded in shadow DOM.
		$css_url = Plugin::get_asset_url( 'css/admin-bar-compiled' . ( is_rtl() ? '-rtl' : '' ) . '.css' );
		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf( 'var ampQaTester = %s;', wp_json_encode( compact( 'css_url' ) ) ),
			'before'
		);

		// Explicitly mark all scripts and styles (and their dependencies) as being part of AMP dev mode.
		// This is necessary because not all dependencies (and their recursive dependencies) will have a dependency
		// on the admin-bar, and thus the AMP plugin won't opt them in to dev mode automatically.
		$is_amp_request = function_exists( 'amp_is_request' ) ? amp_is_request() : is_amp_endpoint();
		if ( $is_amp_request ) {
			$script_dev_mode_handles = $this->get_all_dependencies( wp_scripts(), $dependencies );
			add_filter(
				'script_loader_tag',
				function ( $tag, $handle ) use ( $script_dev_mode_handles ) {
					if ( in_array( $handle, $script_dev_mode_handles, true ) ) {
						$tag = preg_replace( '/(?<=<script)/', ' data-ampdevmode ', $tag );
					}
					return $tag;
				},
				10,
				2
			);
		}
	}

	/**
	 * Render the admin bar button.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP AdminBar object.
	 */
	public function add_menu_button( WP_Admin_Bar $wp_admin_bar ) {
		// TODO: update build name when AMP plugin is updated via usual WP ajax call.
		/* translators: %s: the version of plugin currently running */
		$menu_title = sprintf( __( 'Using AMP: %s', 'amp-qa-tester' ), $this->get_user_friendly_build_name() );
		$args       = [
			'id'     => 'amp-qa-tester',
			'title'  => '<span class="amp-qa-tester-adminbar__label">' . $menu_title . '</span>',
			'href'   => '#',
			'meta'   => [
				'class' => 'menupop amp-qa-tester-adminbar',
			],
			'parent' => 'top-secondary',
		];

		$args['meta']['html'] = $this->menu_markup();
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Return the admin bar content markup.
	 *
	 * @return string HTML markup.
	 */
	private function menu_markup() {
		// Start buffer output.
		ob_start();
		?>
		<div id="amp-qa-tester-adminbar" class="ab-sub-wrapper">
			<div id="amp-qa-tester-adminbar-inner">
				<ul class="ab-submenu">
					<li>
						<div id="amp-qa-tester-build-selector"></div>
					</li>
				</ul>
			</div>
		</div>
		<?php

		// Get the buffer output.
		return ob_get_clean();
	}

	/**
	 * Get user-friendly name for currently installed build.
	 *
	 * @return string
	 */
	private function get_user_friendly_build_name() {
		$current_amp_version = Plugin::get_amp_version();

		if ( $this->build_version !== $current_amp_version ) {
			return $current_amp_version;
		}

		switch ( $this->build_origin ) {
			case 'release':
				/* translators: %s is the name of the release */
				return sprintf( __( '%s release', 'amp-qa-tester' ), $this->build_id );
			case 'pr':
				return 'PR # ' . $this->build_id;
			case 'branch':
				/* translators: %s is the name of the branch */
				return sprintf( __( '%s branch', 'amp-qa-tester' ), $this->build_id );
			default:
				return __( 'Unknown version', 'amp-qa-tester' );
		}
	}
}
