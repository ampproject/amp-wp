<?php
/**
 * Tests for class AMP_Widget_Media_Audio.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Media_Audio.
 *
 * @package AMP
 */
class Test_AMP_Widget_Media_Audio extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		$wp_widget = 'WP_Widget_Media_Audio';
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
	 * @see AMP_Widget_Media_Audio::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_Media_Audio();
		$this->assertEquals( 'media_audio', $widget->id_base );
		$this->assertEquals( 'Audio', $widget->name );
		$this->assertEquals( 'widget_media_audio', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Displays an audio player.', $widget->widget_options['description'] );
	}

	/**
	 * Test render_media().
	 *
	 * Mock audio logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_Media_Audio::render_media().
	 */
	public function test_render_media() {
		$widget = new AMP_Widget_Media_Audio();
		$audio  = '/tmp/small-audio.mp3';
		copy( DIR_TESTDATA . '/uploads/small-audio.mp3', $audio );
		$attachment_id = self::factory()->attachment->create_object( array(
			'file'           => $audio,
			'post_parent'    => 0,
			'post_mime_type' => 'audio/mp3',
			'post_title'     => 'Test Audio',
		) );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $audio ) );
		$instance = array(
			'title'         => 'Test Audio Widget',
			'attachment_id' => $attachment_id,
			'url'           => 'https://example.com/amp',
		);

		ob_start();
		$widget->render_media( $instance );
		$output = ob_get_clean();

		$this->assertNotContains( '<audio', $output );
		$this->assertContains( '<amp-audio', $output );
	}

}
