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
	 * Instance of the widget.
	 *
	 * @var object.
	 */
	public $widget;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		add_theme_support( 'amp' );
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
		$this->widget = new AMP_Widget_Archives();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Archives::__construct().
	 */
	public function test_construct() {
		$this->assertEquals( 10, has_action( 'amp_component_scripts', array( $this->widget, 'form_script' ) ) );
		$this->assertEquals( 'AMP_Widget_Archives', get_class( $this->widget ) );
		$this->assertEquals( 'archives', $this->widget->id_base );
		$this->assertEquals( 'Archives', $this->widget->name );
		$this->assertEquals( 'widget_archive', $this->widget->widget_options['classname'] );
		$this->assertEquals( true, $this->widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A monthly archive of your site&#8217;s Posts.', $this->widget->widget_options['description'] );
	}

	/**
	 * Test form_script().
	 *
	 * @see AMP_Widget_Archives::form_script().
	 */
	public function test_form_script() {
		$expected_key    = 'amp-form';
		$expected_script = 'https://cdn.ampproject.org/v0/amp-form-latest.js';
		$scripts         = array(
			$expected_key => $expected_script,
		);
		$this->assertEquals( $scripts, $this->widget->form_script( $scripts ) );
		$this->assertEquals( $scripts, $this->widget->form_script( array() ) );
	}

	/**
	 * Test widget().
	 *
	 * @see AMP_Widget_Archives::widget().
	 */
	public function test_widget() {
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
		$this->widget->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertNotContains( 'onchange=', $output );
	}

}
