<?php
/**
 * Tests for class AMP_Widget_Media_Video.
 *
 * @since 0.7.0
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Media_Video.
 *
 * @since 0.7.0
 * @package AMP
 */
class Test_AMP_Widget_Media_Video extends WP_UnitTestCase {

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
		$class = 'AMP_Widget_Media_Video';
		if ( ! class_exists( $class ) ) {
			$this->markTestSkipped( 'This version of WordPress does not have the Video widget, so this test does not apply.' );
		}
		parent::setUp();
		wp_maybe_load_widgets();
		$this->widget = new $class();
	}

	/**
	 * Test inject_video_max_width_style().
	 *
	 * @covers AMP_Widget_Media_Video::inject_video_max_width_style()
	 */
	public function test_inject_video_max_width_style() {
		$video           = '<video src="http://example.com" height="100" width="200"></video>';
		$video_no_height = '<video src="http://example.com" width="200"></video>';
		$this->assertEquals( $video, $this->widget->inject_video_max_width_style( $video ) );
		$this->assertEquals( $video_no_height, $this->widget->inject_video_max_width_style( $video_no_height ) );
		$this->assertEquals( '', $this->widget->inject_video_max_width_style( '' ) );
	}

}
