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
		$amp_widgets = new AMP_Widgets();
		$amp_widgets->register_widgets();
		$this->instance = new AMP_Widget_Media_Gallery();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Media_Gallery::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_widget = $wp_widget_factory->widgets['AMP_Widget_Media_Gallery'];

		$this->assertEquals( 'media_gallery', $amp_widget->id_base );
		$this->assertEquals( 'Gallery', $amp_widget->name );
		$this->assertEquals( 'widget_media_gallery', $amp_widget->widget_options['classname'] );
		$this->assertEquals( true, $amp_widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Displays an image gallery.', $amp_widget->widget_options['description'] );
	}

	/**
	 * Test widget().
	 *
	 * Mock image logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_Media_Gallery::widget().
	 */
	public function test_render_media() {
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
		$this->instance->render_media( $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, '<img' ) );
		$this->assertFalse( strpos( $output, '<style' ) );
	}

}
