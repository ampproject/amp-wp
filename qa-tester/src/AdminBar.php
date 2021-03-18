<?php
/**
 * Class AdminBar.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use WP_Admin_Bar;

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

		// The AMP plugin only adds the `data-ampdevmode` attribute to scripts exclusively depending on `admin-bar`. The
		// dependencies of those exclusive scripts do not get the aforementioned attribute, however, so to resolve that
		// each dependency of this script is marked as being exclusively dependent on the admin bar.
		foreach ( $dependencies as $dependency ) {
			wp_scripts()->registered[ $dependency ]->deps[] = 'admin-bar';
		}

		// Enqueue scripts.
		wp_enqueue_script(
			self::ASSET_HANDLE,
			Plugin::get_asset_url( 'js/admin-bar.js' ),
			array_merge( [ 'admin-bar' ], $dependencies ),
			$version,
			true
		);

		// Enqueue styling.
		wp_enqueue_style(
			self::ASSET_HANDLE,
			Plugin::get_asset_url( 'css/admin-bar-compiled.css' ),
			[ 'admin-bar' ],
			$version
		);

		wp_styles()->add_data( 'amp-qa-tester-admin-bar-style', 'rtl', 'replace' );
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
				return $this->build_id;
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
