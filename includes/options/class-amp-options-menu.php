<?php
/**
 * AMP Options.
 *
 * @package AMP
 */

// Includes.
require_once AMP__DIR__ . '/includes/options/class-amp-analytics-options-submenu.php';
require_once AMP__DIR__ . '/includes/options/views/class-amp-options-manager.php';

/**
 * AMP_Options_Menu class.
 */
class AMP_Options_Menu {

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' );
		add_action( 'admin_menu', array( $this, 'add_menu_items' ) );
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {
		$submenu = new AMP_Analytics_Options_Submenu( AMP_Settings::MENU_SLUG );
		$submenu->init( AMP_Settings::MENU_SLUG );
	}

}
