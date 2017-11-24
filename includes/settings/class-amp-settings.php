<?php
/**
 * AMP Settings.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Settings class.
 *
 * @since 0.6
 */
class AMP_Settings {

	/**
	 * Menu slug.
	 *
	 * @const string
	 * @since 0.6
	 */
	const MENU_SLUG = 'amp_settings';

	/**
	 * Settings key.
	 *
	 * @const string
	 * @since 0.6
	 */
	const SETTINGS_KEY = 'amp';

	/**
	 * Initialize.
	 *
	 * @since 0.6
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @since 0.6
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'AMP', 'amp' ),
			__( 'AMP', 'amp' ),
			'manage_options',
			self::MENU_SLUG,
			'__return_false',
			$this->get_svg_icon()
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'AMP Settings', 'amp' ),
			__( 'Settings', 'amp' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_screen' )
		);
	}

	/**
	 * Display Settings.
	 *
	 * @since 0.6
	 * @return void Void on user capabilities check failure.
	 */
	public function render_screen() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		 * Fires before the AMP settings screen is rendered.
		 *
		 * @since 0.6
		 */
		do_action( 'amp_settings_screen' );
		include_once AMP__DIR__ . '/templates/admin/settings/screen.php';
	}

	/**
	 * Getter for the AMP svg menu icon.
	 *
	 * @since 0.6
	 * @return object The AMP svg menu icon.
	 */
	public function get_svg_icon() {
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB3aWR0aD0iNjJweCIgaGVpZ2h0PSI2MnB4IiB2aWV3Qm94PSIwIDAgNjIgNjIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+QU1QLUJyYW5kLUJsYWNrLUljb248L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iYW1wLWxvZ28taW50ZXJuYWwtc2l0ZSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyBpZD0iQU1QLUJyYW5kLUJsYWNrLUljb24iIGZpbGw9IiMwMDAwMDAiPiAgICAgICAgICAgIDxwYXRoIGQ9Ik00MS42Mjg4NjY3LDI4LjE2MTQzMzMgTDI4LjYyNDM2NjcsNDkuODAzNTY2NyBMMjYuMjY4MzY2Nyw0OS44MDM1NjY3IEwyOC41OTc1LDM1LjcwMTY2NjcgTDIxLjM4MzgsMzUuNzEwOTY2NyBDMjEuMzgzOCwzNS43MTA5NjY3IDIxLjMxNTYsMzUuNzEzMDMzMyAyMS4yODM1NjY3LDM1LjcxMzAzMzMgQzIwLjYzMzYsMzUuNzEzMDMzMyAyMC4xMDc2MzMzLDM1LjE4NzA2NjcgMjAuMTA3NjMzMywzNC41MzcxIEMyMC4xMDc2MzMzLDM0LjI1ODEgMjAuMzY3LDMzLjc4NTg2NjcgMjAuMzY3LDMzLjc4NTg2NjcgTDMzLjMyOTEzMzMsMTIuMTY5NTY2NyBMMzUuNzI0NCwxMi4xNzk5IEwzMy4zMzYzNjY3LDI2LjMwMzUgTDQwLjU4NzI2NjcsMjYuMjk0MiBDNDAuNTg3MjY2NywyNi4yOTQyIDQwLjY2NDc2NjcsMjYuMjkzMTY2NyA0MC43MDE5NjY3LDI2LjI5MzE2NjcgQzQxLjM1MTkzMzMsMjYuMjkzMTY2NyA0MS44Nzc5LDI2LjgxOTEzMzMgNDEuODc3OSwyNy40NjkxIEM0MS44Nzc5LDI3LjczMjYgNDEuNzc0NTY2NywyNy45NjQwNjY3IDQxLjYyNzgzMzMsMjguMTYwNCBMNDEuNjI4ODY2NywyOC4xNjE0MzMzIFogTTMxLDAgQzEzLjg3ODcsMCAwLDEzLjg3OTczMzMgMCwzMSBDMCw0OC4xMjEzIDEzLjg3ODcsNjIgMzEsNjIgQzQ4LjEyMDI2NjcsNjIgNjIsNDguMTIxMyA2MiwzMSBDNjIsMTMuODc5NzMzMyA0OC4xMjAyNjY3LDAgMzEsMCBMMzEsMCBaIiBpZD0iRmlsbC0xIj48L3BhdGg+ICAgICAgICA8L2c+ICAgIDwvZz48L3N2Zz4=';
	}

	/**
	 * Get the instance of AMP_Settings.
	 *
	 * @since 0.6
	 * @return object $instance AMP_Settings instance.
	 */
	public static function get_instance() {
		static $instance;

		if ( ! ( $instance instanceof AMP_Settings ) ) {
			$instance = new AMP_Settings();
		}

		return $instance;
	}

}
