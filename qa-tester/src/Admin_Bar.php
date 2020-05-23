<?php

namespace AmpProject\AmpWP_QA_Tester;

/**
 * Class handling the plugin's admin bar menu.
 *
 * @since 1.0.0
 * @package AmpProject\AmpWP_QA_Tester
 */
class Admin_Bar {

	/**
	 * URL base of the currently installed plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $test_url;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {
		$this->test_url = get_site_option( Plugin::URL_STORAGE_KEY );
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
				$dependencies,
				$version,
				true
		);

		wp_add_inline_script( 'amp-qa-tester-admin-bar-script', sprintf( 'var ampQaTesterUrl="%s";', esc_url_raw( $this->test_url ) ), 'before' );

		// Enqueue styling.
		wp_enqueue_style(
				'amp-qa-tester-admin-bar-style',
				Plugin::get_asset_url( 'css/admin-bar-compiled.css' ),
				[],
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
		$on = str_replace( Plugin::REPO_BASE, '', $this->test_url );
		$on = str_replace( 'pulls/', 'PR #', $on );
		$on = str_replace( 'develop', __( 'develop branch', 'amp-qa-tester' ), $on );
		$on = str_replace( 'release', __( 'latest release', 'amp-qa-tester' ), $on );
		if ( '' === $on ) {
			$on = __( 'latest release', 'amp-qa-tester' );
		}

		/* translators: %s: the version of plugin currently running */
		$menu_title = sprintf( __( 'Using AMP: %s', 'amp-qa-tester' ), $on );
		$args = [
			'id'    => 'amp-qa-tester',
			'title' => '<span class="amp-qa-tester-adminbar__icon"></span> <span class="amp-qa-tester-adminbar__label">' . $menu_title . '</span>',
			'href'  => '#',
			'meta'  => [
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
						<div id="amp-qa-tester-pull-request-selector"></div>
					</li>
				</ul>
			</div>
		</div>
		<?php

		// Get the buffer output.
		return ob_get_clean();
	}
}
