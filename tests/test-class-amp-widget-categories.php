<?php
/**
 * Tests for class AMP_Widget_Categories.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Categories.
 *
 * @package AMP
 */
class Test_AMP_Widget_Categories extends WP_UnitTestCase {

	/**
	 * Instance of the widget.
	 *
	 * @var AMP_Widget_Categories
	 */
	public $widget;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		add_theme_support( AMP_Theme_Support::SLUG );
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
		$this->widget = new AMP_Widget_Categories();
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		parent::tearDown();
		remove_theme_support( AMP_Theme_Support::SLUG );
	}

	/**
	 * Test construct().
	 *
	 * @covers AMP_Widget_Categories::__construct()
	 */
	public function test_construct() {
		$this->assertEquals( 'AMP_Widget_Categories', get_class( $this->widget ) );
		$this->assertEquals( 'categories', $this->widget->id_base );
		$this->assertEquals( 'Categories', $this->widget->name );
		$this->assertEquals( 'widget_categories', $this->widget->widget_options['classname'] );
		$this->assertEquals( true, $this->widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A list or dropdown of categories.', $this->widget->widget_options['description'] );
	}

	/**
	 * Test widget().
	 *
	 * @covers AMP_Widget_Categories::widget()
	 */
	public function test_widget() {
		wp();
		$this->assertTrue( is_amp_endpoint() );
		$arguments = array(
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);
		$instance  = array(
			'title'    => 'Test Categories Widget',
			'dropdown' => 1,
		);
		ob_start();
		$this->widget->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertContains( 'on="change:', $output );
		$this->assertNotContains( '<script type=', $output );
	}

}
