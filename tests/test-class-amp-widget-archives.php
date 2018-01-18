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
		$this->instance = new AMP_Widget_Archives();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Archives::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_archives = $wp_widget_factory->widgets['AMP_Widget_Archives'];
		$this->assertEquals( 'AMP_Widget_Archives', get_class( $amp_archives ) );
		$this->assertEquals( 'archives', $amp_archives->id_base );
		$this->assertEquals( 'Archives', $amp_archives->name );
		$this->assertEquals( 'widget_archive', $amp_archives->widget_options['classname'] );
		$this->assertEquals( true, $amp_archives->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'A monthly archive of your site&#8217;s Posts.', $amp_archives->widget_options['description'] );
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
		$this->instance->widget( $arguments, $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, 'onchange=' ) );
	}

}
