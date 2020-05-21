<?php
/**
 * AMP setup wizard.
 *
 * @package AMP
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */

/**
 * AMP setup wizard class.
 *
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */
final class AMP_Setup_Wizard_Submenu {
	/**
	 * Setup screen ID.
	 *
	 * @var string
	 */
	const SCREEN_ID = 'amp-setup';

	/**
	 * Parent menu slug for the submenu.
	 *
	 * @var string
	 */
	private $parent_menu_slug;

	/**
	 * Menu page instance for rendering the content.
	 *
	 * @var AMP_Setup_Wizard_Submenu_Page
	 */
	private $menu_page;

	/**
	 * Constructor.
	 *
	 * @param string $parent_menu_slug Slug of the parent menu item.
	 */
	public function __construct( $parent_menu_slug ) {
		$this->parent_menu_slug = $parent_menu_slug;
		$this->menu_page        = new AMP_Setup_Wizard_Submenu_Page();
		$this->menu_page->init();
	}

	/**
	 * Adds the submenu page for the setup wizard.
	 */
	public function init() {
		add_submenu_page(
			$this->parent_menu_slug,
			__( 'Setup Wizard', 'amp' ),
			__( 'Setup Wizard', 'amp' ),
			'manage_options',
			self::SCREEN_ID,
			[ $this->menu_page, 'render' ]
		);
	}
}
