<?php

require_once( AMP__DIR__ . '/includes/options/views/class-amp-analytics-options-submenu-page.php' );

class AMP_Analytics_Options_Submenu {

	private $parent_menu_slug;
	private $menu_slug;
	private $menu_page;

	public function __construct( $parent_menu_slug ) {
		$this->parent_menu_slug = $parent_menu_slug;
		$this->menu_slug = 'amp-analytics-options';
		$this->menu_page = new AMP_Analytics_Options_Submenu_Page();
	}

	public function init() {
		$this->add_submenu();
	}

	private function add_submenu() {
		add_submenu_page(
			$this->parent_menu_slug,
			'AMP Analytics Options',
			'Analytics',
			'manage_options',
			$this->menu_slug,
			array($this->menu_page, 'render')
		);
	}
}