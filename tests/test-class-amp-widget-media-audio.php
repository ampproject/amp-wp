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
		$this->instance = new AMP_Widget_Media_Audio();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Media_Audio::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_widget = $wp_widget_factory->widgets['AMP_Widget_Media_Audio'];

		$this->assertEquals( 'media_audio', $amp_widget->id_base );
		$this->assertEquals( 'Audio', $amp_widget->name );
		$this->assertEquals( 'widget_media_audio', $amp_widget->widget_options['classname'] );
		$this->assertEquals( true, $amp_widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Displays an audio player.', $amp_widget->widget_options['description'] );
	}

	/**
	 * Test render_media().
	 *
	 * Mock audio logic mainly copied from Test_WP_Widget_Media_image::test_render_media().
	 *
	 * @see AMP_Widget_Media_Audio::render_media().
	 */
	public function test_render_media() {
		$audio = '/tmp/small-audio.mp3';
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
		$this->instance->render_media( $instance );
		$output = ob_get_clean();

		$this->assertFalse( strpos( $output, '<audio' ) );
		$this->assertContains( '<amp-audio', $output );
	}

}
