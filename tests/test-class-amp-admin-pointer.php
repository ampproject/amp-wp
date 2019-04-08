<?php
/**
 * Tests for AMP_Admin_Pointer class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Admin_Pointer class.
 *
 * @covers AMP_Admin_Pointer
 * @since 1.0
 */
class Test_AMP_Admin_Pointer extends \WP_UnitTestCase {

	/**
	 * The meta key of the dismissed pointers.
	 *
	 * @var string
	 */
	const DISMISSED_KEY = 'dismissed_wp_pointers';

	/**
	 * An instance of the class to test.
	 *
	 * @var AMP_Admin_Pointer
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Admin_Pointer();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Admin_Pointer::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( $this->instance, 'enqueue_pointer' ) ) );
	}

	/**
	 * Test get_pointer_data.
	 *
	 * @covers AMP_Admin_Pointer::get_pointer_data()
	 */
	public function test_get_pointer_data() {
		$pointer_data = $this->instance->get_pointer_data();
		$pointer      = $pointer_data['pointer'];
		$this->assertContains( '<h3>AMP</h3><p><strong>New AMP Template Modes</strong></p>', $pointer['options']['content'] );
		$this->assertEquals(
			array(
				'align' => 'middle',
				'edge'  => 'left',
			),
			$pointer['options']['position']
		);
		$this->assertEquals( AMP_Admin_Pointer::TEMPLATE_POINTER_ID, $pointer['pointer_id'] );
		$this->assertEquals( '#toplevel_page_amp-options', $pointer['target'] );
	}
}
