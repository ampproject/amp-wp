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
	 * Test construct().
	 *
	 * @see AMP_Widget_RSS::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_RSS();
		$this->assertEquals( 'rss', $widget->id_base );
		$this->assertEquals( 'RSS', $widget->name );
		$this->assertEquals( 'widget_rss', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Entries from any RSS or Atom feed.', $widget->widget_options['description'] );
	}

	/**
	 * Test widget().
	 *
	 * Mock video logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_RSS::widget().
	 */
	public function test_widget() {
		$widget = new AMP_Widget_RSS();

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
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, '<img' ) );
		$this->assertContains( '<amp-img', $output );
	}

}
