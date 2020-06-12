<?php
/**
 * Class Admin_Bar.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

/**
 * Class handling the plugin's admin bar menu.
 *
 * @since 1.0.0
 */
class Admin_Bar {

	/**
	 * Build ID of the currently installed plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $build_id;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->build_id = get_site_option( Plugin::ID_STORAGE_KEY );
	}

	/**
	 * Registers functionality through WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_action( 'admin_bar_menu', [ $this, 'add_menu_button' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugin_assets' ] );
	}

	/**
	 * Enqueue the plugin assets.
	 *
	 * @since 1.0.0
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

		// Enqueue scripts.
		wp_enqueue_script(
			'amp-qa-tester-admin-bar-script',
			Plugin::get_asset_url( 'js/admin-bar.js' ),
			array_merge( [ 'admin-bar' ], $dependencies ),
			$version,
			true
		);

		// Enqueue styling.
		wp_enqueue_style(
			'amp-qa-tester-admin-bar-style',
			Plugin::get_asset_url( 'css/admin-bar-compiled.css' ),
			[ 'admin-bar' ],
			$version
		);

		wp_styles()->add_data( 'amp-qa-tester-admin-bar-style', 'rtl', 'replace' );
	}

	/**
	 * Render the admin bar button.
	 *
	 * @since 1.0.0
	 *
	 * @param object $wp_admin_bar The WP AdminBar object.
	 */
	public function add_menu_button( $wp_admin_bar ) {
		if ( '' === $this->build_id || false === $this->build_id ) {
			$on = __( 'latest release', 'amp-qa-tester' );
		} elseif ( filter_var( $this->build_id, FILTER_VALIDATE_INT ) ) {
			$on = 'PR #' . $this->build_id;
		} else {
			/* translators: %s is the name of the branch */
			$on = sprintf( __( '%s branch', 'amp-qa-tester' ), $this->build_id );
		}

		/* translators: %s: the version of plugin currently running */
		$menu_title = sprintf( __( 'Using AMP: %s', 'amp-qa-tester' ), $on );
		$args       = [
			'id'     => 'amp-qa-tester',
			'title'  => '<span class="amp-qa-tester-adminbar__icon"></span> <span class="amp-qa-tester-adminbar__label">' . $menu_title . '</span>',
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
	 * @since 1.0.0
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
}
