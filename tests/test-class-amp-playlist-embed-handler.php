<?php
/**
 * Tests for AMP_Playlist_Embed_Handler.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for AMP_Playlist_Embed_Handler.
 *
 * @covers AMP_Playlist_Embed_Handler
 */
class Test_AMP_Playlist_Embed_Handler extends WP_UnitTestCase {

	/**
	 * Instance of the tested class.
	 *
	 * @var AMP_Playlist_Embed_Handler.
	 */
	public $instance;

	/**
	 * Set up test.
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Playlist_Embed_Handler();
	}

	/**
	 * Tear down test.
	 */
	public function tearDown() {
		wp_dequeue_style( 'wp-mediaelement' );
	}

	/**
	 * Test register_embed.
	 *
	 * @covers AMP_Playlist_Embed_Handler::register_embed()
	 */
	public function test_register_embed() {
		global $shortcode_tags;
		$shortcode = 'playlist';
		$this->assertFalse( isset( $shortcode_tags[ $shortcode ] ) );
		$this->instance->register_embed();
		$this->assertEquals( 'AMP_Playlist_Embed_Handler', get_class( $shortcode_tags[ $shortcode ][0] ) );
		$this->assertEquals( 'shortcode', $shortcode_tags[ $shortcode ][1] );
		$this->instance->unregister_embed();
	}

	/**
	 * Test unregister_embed.
	 *
	 * @covers AMP_Playlist_Embed_Handler::unregister_embed()
	 */
	public function test_unregister_embed() {
		global $shortcode_tags;
		$shortcode = 'playlist';
		$this->instance->unregister_embed();
		$this->assertFalse( isset( $shortcode_tags[ $shortcode ] ) );
	}

	/**
	 * Test shortcode.
	 *
	 * Logic for creating the videos copied from Tests_Media.
	 *
	 * @covers AMP_Playlist_Embed_Handler::shortcode()
	 */
	public function test_shortcode() {
		$file_1 = 'example-video-1.mp4';
		$file_2 = 'example-video-2.mkv';
		$files  = array(
			$file_1,
			$file_2,
		);

		$ids = array();
		foreach ( $files as $file ) {
			$ids[] = $this->factory()->attachment->create_object(
				$file,
				0,
				array(
					'post_mime_type' => 'video/mp4',
					'post_type'      => 'attachment',
				)
			);
		}
		$attr     = array(
			'ids'  => implode( ',', $ids ),
			'type' => 'video',
		);
		$playlist = $this->instance->shortcode( $attr );
		$this->assertContains( '<amp-video', $playlist );
		$this->assertContains( '<amp-state', $playlist );
		$this->assertContains( $file_1, $playlist );
		$this->assertContains( $file_2, $playlist );
		$this->assertContains( '[src]="playlist0[playlist0.currentVideo].videoUrl"', $playlist );
		$this->assertContains( 'on="tap:AMP.setState({playlist0: {currentVideo: 0}})"', $playlist );
	}

}
