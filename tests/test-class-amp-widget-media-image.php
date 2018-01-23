<?php
/**
 * Tests for class AMP_Widget_Media_Image.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Media_Image.
 *
 * @package AMP
 */
class Test_AMP_Widget_Media_Image extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$wp_widget = 'WP_Widget_Media_Image';
		if ( ! class_exists( $wp_widget ) ) {
			$this->markTestSkipped( sprintf( 'The widget %s is not present, so neither is its child class.', $wp_widget ) );
		}
		parent::setUp();
		AMP_Theme_Support::init();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Media_Image::__construct().
	 */
	public function test_construct() {
		$widget = new WP_Widget_Media_Image();
		$this->assertEquals( 'media_image', $widget->id_base );
		$this->assertEquals( 'Image', $widget->name );
		$this->assertEquals( 'widget_media_image', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Displays an image.', $widget->widget_options['description'] );
	}

	/**
	 * Test render_media().
	 *
	 * Mock image logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_Media_Image::render_media().
	 */
	public function test_render_media() {
		$widget           = new AMP_Widget_Media_Image();
		$first_test_image = '/tmp/test-image.jpg';
		copy( DIR_TESTDATA . '/images/test-image.jpg', $first_test_image );
		$attachment_id = self::factory()->attachment->create_object( array(
			'file'           => $first_test_image,
			'post_parent'    => 0,
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Test Image',
		) );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $first_test_image ) );
		$instance = array(
			'title'         => 'Test Image Widget',
			'attachment_id' => $attachment_id,
			'height'        => 100,
			'width'         => 100,
			'url'           => 'https://example.com/amp',
		);

		ob_start();
		$widget->render_media( $instance );
		$output = ob_get_clean();

		$this->assertNotContains( '<img', $output );
		$this->assertNotContains( 'style=', $output );
	}

}
