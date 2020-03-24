<?php
/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\AmpWP\Tests\AssertContainsCompatibility;

/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @covers AMP_Core_Block_Handler
 */
class Test_AMP_Core_Block_Handler extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/**
	 * Set up.
	 */
	public function setUp() {
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestIncomplete( 'Files needed for testing missing.' );
		}
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Missing required render_block filter.' );
		}
		parent::setUp();
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		if ( did_action( 'add_attachment' ) ) {
			$this->remove_added_uploads();
		}
		parent::tearDown();
	}

	/**
	 * Get video attachment ID.
	 *
	 * @return int|WP_Error ID or error.
	 */
	protected function get_video_attachment_id() {
		$temp_file = trailingslashit( get_temp_dir() ) . 'core-block-handler-test-' . wp_generate_uuid4() . '.mp4';
		copy( DIR_TESTDATA . '/uploads/small-video.mp4', $temp_file );
		$attachment_id = self::factory()->attachment->create_upload_object( $temp_file );

		// Remove the file extension from the post_title media_handle_upload().
		$attachment               = get_post( $attachment_id, ARRAY_A );
		$attachment['post_title'] = str_replace( '.mp4', '', $attachment['post_title'] );
		$attachment['post_name']  = str_replace( '-mp4', '', $attachment['post_name'] );
		wp_update_post( wp_slash( $attachment ) );

		return $attachment_id;
	}

	/**
	 * Test register_embed().
	 *
	 * @covers AMP_Core_Block_Handler::register_embed()
	 * @covers AMP_Core_Block_Handler::unregister_embed()
	 */
	public function test_register_and_unregister_embed() {
		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.

		$categories_block = '<!-- wp:categories {"displayAsDropdown":true,"showHierarchy":true,"showPostCounts":true} /-->';
		$archives_block   = '<!-- wp:archives {"displayAsDropdown":true,"showPostCounts":true} /-->';

		$handler->register_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertStringContains( '<select', $rendered );
		$this->assertStringNotContains( 'onchange', $rendered );
		$this->assertStringContains( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertStringContains( '<select', $rendered );
			$this->assertStringNotContains( 'onchange', $rendered );
			$this->assertStringContains( 'on="change', $rendered );
		}

		$handler->unregister_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertStringContains( '<select', $rendered );
		$this->assertStringContains( 'onchange', $rendered );
		$this->assertStringNotContains( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertStringContains( '<select', $rendered );
			$this->assertStringContains( 'onchange', $rendered );
			$this->assertStringNotContains( 'on="change', $rendered );
		}
	}

	/**
	 * Test that placeholder blocks don't result in validation errors.
	 *
	 * @covers \AMP_Core_Block_Handler::filter_rendered_block()
	 */
	public function test_placeholder_blocks() {
		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$audio_placeholder_block = "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls></audio></figure>\n<!-- /wp:audio -->";
		$audio_populated_block   = "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls src=\"https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3\"></audio></figure>\n<!-- /wp:audio -->";
		$this->assertEmpty( apply_filters( 'the_content', $audio_placeholder_block ) );
		$this->assertNotEmpty( apply_filters( 'the_content', $audio_populated_block ) );

		$image_placeholder_block = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n<!-- /wp:image -->";
		$image_populated_block   = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"https://wordpressdev.lndo.site/content/uploads/2019/02/1200px-American_bison_k5680-1-1024x668.jpg\" alt=\"\"/></figure>\n<!-- /wp:image -->";
		$this->assertEmpty( apply_filters( 'the_content', $image_placeholder_block ) );
		$this->assertNotEmpty( apply_filters( 'the_content', $image_populated_block ) );
	}

	/**
	 * Test that video width/height attributes are added.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_video_block()
	 */
	public function test_ampify_video_block() {
		$attachment_id = $this->get_video_attachment_id();

		$post_id = self::factory()->post->create(
			[
				'post_title'   => 'Video',
				'post_content' => sprintf(
					"<!-- wp:video {\"id\":%d} -->\n<figure class=\"wp-block-video\"><video controls src=\"%s\"></video></figure>\n<!-- /wp:video -->",
					$attachment_id,
					wp_get_attachment_url( $attachment_id )
				),
			]
		);

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = apply_filters( 'the_content', get_post( $post_id )->post_content );

		$this->assertStringContains( '<video width="560" height="320" ', $content );
	}

	/**
	 * Test that cover video gets fill layout and object-fit=cover.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_cover_block()
	 */
	public function test_ampify_cover_block() {
		$attachment_id = $this->get_video_attachment_id();

		$post_id = self::factory()->post->create(
			[
				'post_title'   => 'Cover Video',
				'post_content' => sprintf(
					"<!-- wp:cover {\"url\":%s,\"id\":%s,\"backgroundType\":\"video\",\"align\":\"center\"} -->\n<div class=\"wp-block-cover aligncenter has-background-dim\"><video class=\"wp-block-cover__video-background\" autoplay muted loop src=\"%s\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Compare the video and image blocks.<br>This block is centered.</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->",
					wp_json_encode( wp_get_attachment_url( $attachment_id ) ),
					wp_json_encode( $attachment_id ),
					esc_attr( wp_get_attachment_url( $attachment_id ) )
				),
			]
		);

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = apply_filters( 'the_content', get_post( $post_id )->post_content );

		$this->assertStringContains( '<video layout="fill" object-fit="cover"', $content );
		$this->assertStringNotContains( 'width=', $content );
		$this->assertStringNotContains( 'height=', $content );
	}
}
