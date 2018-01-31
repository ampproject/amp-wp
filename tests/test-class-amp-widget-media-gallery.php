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
		if ( ! class_exists( 'AMP_Widget_Media_Gallery' ) ) {
			$this->markTestSkipped( 'This WordPress version does not have a Gallery widget.' );
		}
		parent::setUp();
		AMP_Theme_Support::register_widgets();
		$this->instance = new AMP_Widget_Media_Gallery();
	}

	/**
	 * Test render_media().
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

		$this->assertContains( 'amp-carousel', $output );
		$this->assertContains( $first_test_image, $output );
		$this->assertContains( $second_test_image, $output );
	}

}
