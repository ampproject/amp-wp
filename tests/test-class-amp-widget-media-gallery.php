<?php
/**
 * Tests for class AMP_Widget_Media_Gallery.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Media_Gallery.
 *
 * @package AMP
 */
class Test_AMP_Widget_Media_Gallery extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$wp_widget = 'WP_Widget_Media_Gallery';
		if ( ! class_exists( $wp_widget ) ) {
			$this->markTestSkipped( sprintf( 'The widget %s is not present, so neither is its child class.', $wp_widget ) );
		}
		parent::setUp();
		add_theme_support( 'amp' );
		wp_maybe_load_widgets();
		AMP_Theme_Support::init();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Media_Gallery::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_Media_Gallery();
		$this->assertEquals( 'media_gallery', $widget->id_base );
		$this->assertEquals( 'Gallery', $widget->name );
		$this->assertEquals( 'widget_media_gallery', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Displays an image gallery.', $widget->widget_options['description'] );
	}

	/**
	 * Test render_media().
	 *
	 * Mock image logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_Media_Gallery::render_media().
	 */
	public function test_render_media() {
		$widget           = new AMP_Widget_Media_Gallery();
		$first_test_image = '/tmp/test-image.jpg';
		copy( DIR_TESTDATA . '/images/test-image.jpg', $first_test_image );
		$first_attachment_id = self::factory()->attachment->create_object( array(
			'file'           => $first_test_image,
			'post_parent'    => 0,
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Test Image',
		) );
		wp_update_attachment_metadata( $first_attachment_id, wp_generate_attachment_metadata( $first_attachment_id, $first_test_image ) );
		$ids[] = $first_attachment_id;

		$second_test_image = '/tmp/test-image.jpg';
		copy( DIR_TESTDATA . '/images/test-image.jpg', $second_test_image );
		$second_attachment_id = self::factory()->attachment->create_object( array(
			'file'           => $second_test_image,
			'post_parent'    => 0,
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Test Image',
		) );
		wp_update_attachment_metadata( $second_attachment_id, wp_generate_attachment_metadata( $second_attachment_id, $second_test_image ) );
		$ids[]    = $second_attachment_id;
		$instance = array(
			'title' => 'Test Gallery Widget',
			'ids'   => $ids,
		);

		ob_start();
		$widget->render_media( $instance );
		$output = ob_get_clean();

		$this->assertNotContains( '<img', $output );
		$this->assertNotContains( '<style', $output );
	}

}
