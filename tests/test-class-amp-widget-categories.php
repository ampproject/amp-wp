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
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Categories::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_Categories();
		$this->assertEquals( 'AMP_Widget_Categories', get_class( $widget ) );
		$this->assertEquals( 'categories', $widget->id_base );
		$this->assertEquals( 'Categories', $widget->name );
		$this->assertEquals( 'widget_categories', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A list or dropdown of categories.', $widget->widget_options['description'] );
	}

	/**
	 * Test modify_select().
	 *
	 * @see AMP_Widget_Categories::modify_select().
	 */
	public function test_modify_select() {
		$widget         = new AMP_Widget_Categories();
		$categories     = wp_dropdown_categories( array(
			'echo' => 0,
		) );
		$number         = 3;
		$widget->number = $number;
		$this->assertContains( strval( $number ), $widget->modify_select( $categories ) );
	}

	/**
	 * Test widget().
	 *
	 * @see AMP_Widget_Categories::widget().
	 */
	public function test_widget() {
		$widget    = new AMP_Widget_Categories();
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
		$widget->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertNotContains( '<script type=', $output );
	}

}
