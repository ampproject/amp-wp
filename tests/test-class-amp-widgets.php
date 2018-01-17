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
		$this->assertEquals( 10, has_filter( 'show_recent_comments_widget_style', '__return_false' ) );
		$this->assertEquals( 10, has_action( 'wp_footer', array( $this->instance, 'dequeue_scripts' ) ) );
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
		$this->assertFalse( isset( $wp_widget_factory->widgets['WP_Widget_Archives'] ) );
		$this->assertEquals( 'AMP_Widget_Archives', get_class( $wp_widget_factory->widgets['AMP_Widget_Archives'] ) );
		$this->assertFalse( isset( $wp_widget_factory->widgets['WP_Widget_Categories'] ) );
		$this->assertEquals( 'AMP_Widget_Categories', get_class( $wp_widget_factory->widgets['AMP_Widget_Categories'] ) );
	}

	/**
	 * Test dequeue_scripts().
	 *
	 * @see AMP_Widgets::dequeue_scripts().
	 */
	public function test_dequeue_scripts() {
		$this->assertFalse( wp_script_is( 'wp-mediaelement' ) );
		$this->assertFalse( wp_script_is( 'mediaelement-vimeo' ) );
		$this->assertFalse( wp_style_is( 'wp-mediaelement' ) );
	}

}
