<?php

require_once( AMP__DIR__ . '/includes/options/class-amp-analytics-options-submenu.php' );
require_once( AMP__DIR__ . '/includes/options/views/class-amp-options-menu-page.php' );

class AMP_Options_Menu {

	private $menu_page;
	private $menu_slug;

	public function __construct() {
		$this->menu_page = new AMP_Options_Menu_Page();
		$this->menu_slug = 'amp-plugin-options';
	}

	public function init() {
		$submenus = array(
			new AMP_Analytics_Options_Submenu( $this->menu_slug ),
		);
		$this->add_amp_options_menu($submenus);
	}
	/**
	 * @param $submenus
	 * Creates the submenu item and calls on the Submenu Page object to render
	 * the actual contents of the page.
	 */
	public function add_amp_options_menu( $submenus ) {
		add_menu_page(
			'AMP Plugin Options',
			'AMP',
			'manage_options',
			$this->menu_slug,
			array( $this->menu_page, 'render' )
		);

		foreach ($submenus as $submenu) {
			$submenu->init($this->menu_slug);
		}
	}
}