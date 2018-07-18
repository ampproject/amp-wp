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
	 * Test enqueue_pointer.
	 *
	 * @covers AMP_Admin_Pointer::enqueue_pointer()
	 */
	public function test_enqueue_pointer() {
		$user_id             = $this->factory()->user->create();
		$dismissed_key       = 'dismissed_wp_pointers';
		$pointer_script_slug = 'wp-pointer';
		wp_set_current_user( $user_id );

		// When this is in the meta value of dismissed pointers, the tested method should exit without enqueuing the assets.
		update_user_meta( $user_id, $dismissed_key, AMP_Admin_Pointer::TEMPLATE_POINTER_ID );
		$this->instance->enqueue_pointer();
		$this->assertFalse( wp_style_is( $pointer_script_slug ) );
		$this->assertFalse( wp_script_is( AMP_Admin_Pointer::SCRIPT_SLUG ) );

		// When this isn't in the meta value of dismissed pointers, the method should enqueue the assets.
		update_user_meta( $user_id, $dismissed_key, 'foo-pointer' );
		$this->instance->enqueue_pointer();
		$script = wp_scripts()->registered[ AMP_Admin_Pointer::SCRIPT_SLUG ];

		$this->assertTrue( wp_style_is( $pointer_script_slug ) );
		$this->assertTrue( wp_script_is( AMP_Admin_Pointer::SCRIPT_SLUG ) );
		$this->assertEquals( array( 'jquery', 'wp-pointer' ), $script->deps );
		$this->assertEquals( AMP_Admin_Pointer::SCRIPT_SLUG, $script->handle );
		$this->assertEquals( amp_get_asset_url( 'js/amp-admin-pointer.js' ), $script->src );
		$this->assertEquals( AMP__VERSION, $script->ver );
		$this->assertContains( 'ampAdminPointer.load(', $script->extra['after'][1] );
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
