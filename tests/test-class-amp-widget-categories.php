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
		AMP_Theme_Support::init();
		$amp_widgets = new AMP_Widgets();
		$amp_widgets->register_widgets();
		$this->instance = new AMP_Widget_Categories();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Categories::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_categories = $wp_widget_factory->widgets['AMP_Widget_Categories'];
		$this->assertEquals( 'AMP_Widget_Categories', get_class( $amp_categories ) );
		$this->assertEquals( 'categories', $amp_categories->id_base );
		$this->assertEquals( 'Categories', $amp_categories->name );
		$this->assertEquals( 'widget_categories', $amp_categories->widget_options['classname'] );
		$this->assertEquals( true, $amp_categories->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A list or dropdown of categories.', $amp_categories->widget_options['description'] );
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
		$this->instance->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, '<script type=' ) );
	}

}
