<?php
/**
 * Tests for class AMP_Widget_Text.
 *
 * @since 0.7.0
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Text.
 *
 * @since 0.7.0
 * @package AMP
 */
class Test_AMP_Widget_Text extends WP_UnitTestCase {

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
		$class = 'AMP_Widget_Text';
		if ( ! class_exists( $class ) ) {
			$this->markTestSkipped( 'This version of WordPress does not have the Video widget, so this test does not apply.' );
		}
		parent::setUp();
		wp_maybe_load_widgets();
		$this->widget = new $class();
		add_theme_support( AMP_Theme_Support::SLUG );
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
	 * Test inject_video_max_width_style().
	 *
	 * @covers AMP_Widget_Text::inject_video_max_width_style()
	 */
	public function test_inject_video_max_width_style() {
		wp();
		$this->assertTrue( is_amp_endpoint() );
		$video            = '<video src="http://example.com" height="100" width="200"></video>';
		$video_only_width = '<video src="http://example.com/this-video" width="500">';
		$this->assertEquals( $video, $this->widget->inject_video_max_width_style( array( $video ) ) );
		$this->assertEquals( $video_only_width, $this->widget->inject_video_max_width_style( array( $video_only_width ) ) );
		$this->assertEquals( '', $this->widget->inject_video_max_width_style( array( '' ) ) );
	}

}
