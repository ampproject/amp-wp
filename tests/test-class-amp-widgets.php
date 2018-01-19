<?php
/**
 * Tests for class AMP_Widgets.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widgets.
 */
class Test_AMP_Widgets extends WP_UnitTestCase {
	/**
	 * Instance of AMP_Widgets.
	 *
	 * @var object
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		wp_maybe_load_widgets();
		$this->instance = new AMP_Widgets();
	}

	/**
	 * Test init().
	 *
	 * @see AMP_Widgets::init().
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_filter( 'widgets_init', array( $this->instance, 'register_widgets' ) ) );
	}

	/**
	 * Test register_widgets().
	 *
	 * @covers AMP_Widgets::get_widgets().
	 * @see AMP_Widgets::register_widgets().
	 */
	public function test_register_widgets() {
		global $wp_widget_factory;
		$this->instance->register_widgets();
		$widgets = $this->instance->get_widgets();

		foreach ( $widgets as $native_wp_widget => $amp_widget ) {
			$this->assertFalse( isset( $wp_widget_factory->widgets[ $native_wp_widget ] ) );
			$this->assertEquals( $amp_widget, get_class( $wp_widget_factory->widgets[ $amp_widget ] ) );
		}
	}

}
