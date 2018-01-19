<?php
/**
 * Tests for class AMP_Widget_RSS.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_RSS.
 *
 * @package AMP
 */
class Test_AMP_Widget_RSS extends WP_UnitTestCase {

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
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
		$amp_widgets = new AMP_Widgets();
		$amp_widgets->register_widgets();
		$this->instance = new AMP_Widget_RSS();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_RSS::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_widget = $wp_widget_factory->widgets['AMP_Widget_RSS'];

		$this->assertEquals( 'rss', $amp_widget->id_base );
		$this->assertEquals( 'RSS', $amp_widget->name );
		$this->assertEquals( 'widget_rss', $amp_widget->widget_options['classname'] );
		$this->assertEquals( true, $amp_widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Entries from any RSS or Atom feed.', $amp_widget->widget_options['description'] );
	}

	/**
	 * Test widget().
	 *
	 * Mock video logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_RSS::widget().
	 */
	public function test_widget() {
		$args     = array(
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);
		$instance = array(
			'title'        => 'Test RSS Widget: With Content, Author, Date',
			'url'          => 'https://amphtml.wordpress.com/feed/',
			'show_author'  => 1,
			'show_date'    => 1,
			'show_summary' => 1,
		);

		ob_start();
		$this->instance->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, '<img' ) );
		$this->assertContains( '<amp-img', $output );
	}

}
