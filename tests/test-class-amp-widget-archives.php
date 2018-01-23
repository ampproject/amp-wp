<?php
/**
 * Tests for class AMP_Widget_Archives.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Archives.
 *
 * @package AMP
 */
class Test_AMP_Widget_Archives extends WP_UnitTestCase {

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
	 * @see AMP_Widget_Archives::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_Archives();
		$this->assertEquals( 'AMP_Widget_Archives', get_class( $widget ) );
		$this->assertEquals( 'archives', $widget->id_base );
		$this->assertEquals( 'Archives', $widget->name );
		$this->assertEquals( 'widget_archive', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A monthly archive of your site&#8217;s Posts.', $widget->widget_options['description'] );
	}

	/**
	 * Test widget().
	 *
	 * @see AMP_Widget_Archives::widget().
	 */
	public function test_widget() {
		$widget    = new AMP_Widget_Archives();
		$arguments = array(
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);
		$instance  = array(
			'title'    => 'Test Archives Widget',
			'dropdown' => 1,
		);
		ob_start();
		$widget->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertNotContains( 'onchange=', $output );
	}

}
