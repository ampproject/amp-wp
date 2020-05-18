<?php
/**
 * Tests for AMP_Setup_Wizard_Submenu class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Setup_Wizard_Submenu  class.
 *
 * @group setup
 *
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 *
 * @covers AMP_Setup_Wizard_Submenu
 */
class Test_AMP_Setup_Wizard_Submenu  extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->wizard = new AMP_Setup_Wizard_Submenu( 'amp-options' );
	}

	/**
	 * Tests AMP_Setup_Wizard_Submenu::init
	 *
	 * @covers AMP_Setup_Wizard_Submenu::init
	 */
	public function test_init() {
		global $submenu;

		wp_set_current_user( 1 );

		$this->wizard->init();

		$this->assertEquals( end( $submenu['amp-options'] )[2], 'amp-setup' );
	}
}
