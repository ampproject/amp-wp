<?php
/**
 * Tests for AMP_Admin_Pointers class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Admin_Pointers class.
 *
 * @covers AMP_Admin_Pointers
 * @since 1.0
 */
class Test_AMP_Admin_Pointers extends WP_UnitTestCase {

	/**
	 * The meta key of the dismissed pointers.
	 *
	 * @var string
	 */
	const DISMISSED_KEY = 'dismissed_wp_pointers';

	/**
	 * An instance of the class to test.
	 *
	 * @var AMP_Admin_Pointers
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Admin_Pointers();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Admin_Pointers::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_scripts' ] ) );
	}
}
