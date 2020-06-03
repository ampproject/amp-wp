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
 * @since 1.6.0
 *
 * @covers AMP_Setup_Wizard_Submenu
 */
class Test_AMP_Setup_Wizard_Submenu  extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var AMP_Setup_Wizard_Submenu
	 */
	private $wizard;

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
