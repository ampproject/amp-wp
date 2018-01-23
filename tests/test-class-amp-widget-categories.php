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
		add_theme_support( 'amp ' );
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
		$this->widget = new AMP_Widget_Categories();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Categories::__construct().
	 */
	public function test_construct() {
		$this->assertEquals( 10, has_action( 'amp_component_scripts', array( $this->widget, 'form_script' ) ) );
		$this->assertEquals( 'AMP_Widget_Categories', get_class( $this->widget ) );
		$this->assertEquals( 'categories', $this->widget->id_base );
		$this->assertEquals( 'Categories', $this->widget->name );
		$this->assertEquals( 'widget_categories', $this->widget->widget_options['classname'] );
		$this->assertEquals( true, $this->widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A list or dropdown of categories.', $this->widget->widget_options['description'] );
	}

	/**
	 * Test form_script().
	 *
	 * @see AMP_Widget_Categories::form_script().
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
	 * Test modify_select().
	 *
	 * @see AMP_Widget_Categories::modify_select().
	 */
	public function test_modify_select() {
		$categories           = wp_dropdown_categories( array(
			'echo' => 0,
		) );
		$number               = 3;
		$this->widget->number = $number;
		$this->assertContains( strval( $number ), $this->widget->modify_select( $categories ) );
	}

	/**
	 * Test widget().
	 *
	 * @see AMP_Widget_Categories::widget().
	 */
	public function test_widget() {
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

		$this->assertNotContains( '<script type=', $output );
	}

}
