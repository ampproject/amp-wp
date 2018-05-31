<?php
/**
 * Class AMP_Analytics_Options_Submenu
 *
 * @package AMP
 */

/**
 * Class AMP_Analytics_Options_Submenu
 */
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
		add_action(
			'admin_print_styles-amp_page_' . $this->menu_slug,
			array( $this, 'amp_options_styles' )
		);
	}

	private function add_submenu() {
		add_submenu_page(
			$this->parent_menu_slug,
			__( 'AMP Analytics Options', 'amp' ),
			__( 'Analytics', 'amp' ),
			'manage_options',
			$this->menu_slug,
			array( $this->menu_page, 'render' )
		);
	}

	public function amp_options_styles() {
		?>
		<style>
			.analytics-data-container .button.delete,
			.analytics-data-container .button.delete:hover,
			.analytics-data-container .button.delete:active,
			.analytics-data-container .button.delete:focus {
				background: red;
				border-color: red;
				text-shadow: 0 0 0;
				margin: 0 5px;
			}
			.amp-analytics-options.notice {
				width: 300px;
			}
		</style>;

		<?php
	}
}
