<?php
/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @covers AMP_Core_Block_Handler
 */
class Test_AMP_Core_Block_Handler extends WP_UnitTestCase {

	use MarkupComparison;
	use AssertContainsCompatibility;
	use WithoutBlockPreRendering {
		setUp as public prevent_block_pre_render;
	}

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
		$this->prevent_block_pre_render();
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
		$audio_populated_block   = "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls src=\"https://example.com/content/uploads/2019/02/do-you-know-I-am-batman.mp3\"></audio></figure>\n<!-- /wp:audio -->";
		$this->assertEmpty( apply_filters( 'the_content', $audio_placeholder_block ) );
		$this->assertNotEmpty( apply_filters( 'the_content', $audio_populated_block ) );

		$image_placeholder_block = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n<!-- /wp:image -->";
		$image_populated_block   = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"https://example.com/content/uploads/2019/02/1200px-American_bison_k5680-1-1024x668.jpg\" alt=\"\"/></figure>\n<!-- /wp:image -->";
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

	/** @return array */
	public function get_data_for_test_ampify_cover_block() {
		return [
			'image_background_no_position'      => [
				"<!-- wp:cover {\"url\":\"https://example.com/content/uploads/2011/07/dsc09114.jpg\"} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-760\" alt=\"\" src=\"https://example.com/content/uploads/2011/07/dsc09114.jpg\" data-object-fit=\"cover\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-760\" object-fit=\"cover\" layout=\"fill\" alt=\"\" src=\"https://example.com/content/uploads/2011/07/dsc09114.jpg\" data-object-fit=\"cover\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n</div></div>\n",
			],
			'image_background_other_classes'    => [
				"<!-- wp:cover {\"url\":\"https://example.com/content/uploads/2011/07/dsc09114.jpg\"} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"foo wp-block-cover__image-background bar wp-image-760\" alt=\"\" src=\"https://example.com/content/uploads/2011/07/dsc09114.jpg\" data-object-fit=\"cover\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><img class=\"foo wp-block-cover__image-background bar wp-image-760\" object-fit=\"cover\" layout=\"fill\" alt=\"\" src=\"https://example.com/content/uploads/2011/07/dsc09114.jpg\" data-object-fit=\"cover\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n</div></div>\n",
			],
			'image_background_with_positioning_and_nested_image' => [
				"<!-- wp:cover {\"url\":\"https://wordpressdev.lndo.site/content/uploads/2011/07/img_0747.jpg\",\"id\":769,\"focalPoint\":{\"x\":\"0.71\",\"y\":\"0.59\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-769\" alt=\"\" src=\"https://wordpressdev.lndo.site/content/uploads/2011/07/img_0747.jpg\" style=\"object-position:71% 59%\" data-object-fit=\"cover\" data-object-position=\"71% 59%\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:image {\"id\":1948,\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://wordpressdev.lndo.site/content/uploads/2013/04/cropped-triforce-wallpaper.jpg\" alt=\"\" class=\"wp-image-1948\"/></figure>\n<!-- /wp:image --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-769\" object-fit=\"cover\" layout=\"fill\" object-position=\"71% 59%\" alt=\"\" src=\"https://wordpressdev.lndo.site/content/uploads/2011/07/img_0747.jpg\" style=\"object-position:71% 59%\" data-object-fit=\"cover\" data-object-position=\"71% 59%\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n\n\n\n<figure class=\"wp-block-image size-large\"><img src=\"https://wordpressdev.lndo.site/content/uploads/2013/04/cropped-triforce-wallpaper.jpg\" alt=\"\" class=\"wp-image-1948\"/></figure>\n</div></div>\n",
			],
			'video_background_no_position'      => [
				"<!-- wp:cover {\"url\":\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\",\"backgroundType\":\"video\"} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\" data-object-fit=\"cover\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" object-fit=\"cover\" layout=\"fill\" autoplay muted loop playsinline src=\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\" data-object-fit=\"cover\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n</div></div>\n",
			],
			'video_background_with_positioning' => [
				"<!-- wp:cover {\"url\":\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\",\"backgroundType\":\"video\",\"focalPoint\":{\"x\":\"0.19\",\"y\":\"0.99\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\" style=\"object-position:19% 99%\" data-object-fit=\"cover\" data-object-position=\"19% 99%\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" object-fit=\"cover\" layout=\"fill\" object-position=\"19% 99%\" autoplay muted loop playsinline src=\"https://example.com/content/uploads/2020/11/story_video_dog.mp4\" style=\"object-position:19% 99%\" data-object-fit=\"cover\" data-object-position=\"19% 99%\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Test</p>\n</div></div>\n",
			],
			'wordpress_53_examples'             => [
				"<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48,\"focalPoint\":{\"x\":0.7559055118110236,\"y\":0.023529411764705882}} -->\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg);background-position:75.59055118110236% 2.3529411764705883%\"><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Cover Image</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\"} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Dog!</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48} -->\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg)\"><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:image {\"id\":48,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:video {\"id\":58} -->\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n<!-- /wp:video --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\"} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:image {\"id\":48,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:video {\"id\":58} -->\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n<!-- /wp:video --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48} -->\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg)\"><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Cover</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\"} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Inner video!</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg);background-position:75.59055118110236% 2.3529411764705883%\"><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Cover Image</p>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" object-fit=\"cover\" layout=\"fill\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Dog!</p>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg)\"><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n\n\n\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n\n\n\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" object-fit=\"cover\" layout=\"fill\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n\n\n\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n\n\n\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\" style=\"background-image:url(https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg)\"><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Cover</p>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background\" object-fit=\"cover\" layout=\"fill\" object-fit=\"cover\" layout=\"fill\" autoplay muted loop src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Inner video!</p>\n</div></div>\n</div></div>\n",
			],
			'wordpress_57_examples'             => [
				"<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48,\"focalPoint\":{\"x\":\"0.44\",\"y\":\"0.08\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:44% 8%\" data-object-fit=\"cover\" data-object-position=\"44% 8%\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Cover Image</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\",\"focalPoint\":{\"x\":\"0.51\",\"y\":\"0.89\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:51% 89%\" data-object-fit=\"cover\" data-object-position=\"51% 89%\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Dog!</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48,\"focalPoint\":{\"x\":\"0.54\",\"y\":\"1.00\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:54% 100%\" data-object-fit=\"cover\" data-object-position=\"54% 100%\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:image {\"id\":48,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:video {\"id\":58} -->\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n<!-- /wp:video --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\",\"focalPoint\":{\"x\":\"0.15\",\"y\":\"0.17\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:15% 17%\" data-object-fit=\"cover\" data-object-position=\"15% 17%\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:image {\"id\":48,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:video {\"id\":58} -->\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n<!-- /wp:video --></div></div>\n<!-- /wp:cover -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\",\"id\":48,\"focalPoint\":{\"x\":\"0.52\",\"y\":\"0.94\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:52% 94%\" data-object-fit=\"cover\" data-object-position=\"52% 94%\"/><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Nested Cover</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:cover {\"url\":\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\",\"id\":58,\"backgroundType\":\"video\",\"focalPoint\":{\"x\":\"0.45\",\"y\":\"0.82\"}} -->\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:45% 82%\" data-object-fit=\"cover\" data-object-position=\"45% 82%\"></video><div class=\"wp-block-cover__inner-container\"><!-- wp:paragraph {\"align\":\"center\",\"placeholder\":\"Write title…\",\"fontSize\":\"large\"} -->\n<p class=\"has-text-align-center has-large-font-size\">Inner video!</p>\n<!-- /wp:paragraph --></div></div>\n<!-- /wp:cover --></div></div>\n<!-- /wp:cover -->",
				"\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" object-fit=\"cover\" layout=\"fill\" object-position=\"44% 8%\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:44% 8%\" data-object-fit=\"cover\" data-object-position=\"44% 8%\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Cover Image</p>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" object-fit=\"cover\" layout=\"fill\" object-position=\"51% 89%\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:51% 89%\" data-object-fit=\"cover\" data-object-position=\"51% 89%\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Dog!</p>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" object-fit=\"cover\" layout=\"fill\" object-position=\"54% 100%\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:54% 100%\" data-object-fit=\"cover\" data-object-position=\"54% 100%\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n\n\n\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n\n\n\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" object-fit=\"cover\" layout=\"fill\" object-position=\"15% 17%\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:15% 17%\" data-object-fit=\"cover\" data-object-position=\"15% 17%\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Image and Video</p>\n\n\n\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-768x1024.jpg\" alt=\"\" class=\"wp-image-48\"/></figure>\n\n\n\n<figure class=\"wp-block-video\"><video controls src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\"></video></figure>\n</div></div>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><img class=\"wp-block-cover__image-background wp-image-48\" object-fit=\"cover\" layout=\"fill\" object-position=\"52% 94%\" alt=\"\" src=\"https://example.com/wp-content/uploads/2021/02/image.MP_-scaled.jpg\" style=\"object-position:52% 94%\" data-object-fit=\"cover\" data-object-position=\"52% 94%\"/><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Nested Cover</p>\n\n\n\n<div class=\"wp-block-cover has-background-dim\"><video class=\"wp-block-cover__video-background intrinsic-ignore\" object-fit=\"cover\" layout=\"fill\" object-position=\"45% 82%\" autoplay muted loop playsinline src=\"https://example.com/wp-content/uploads/2021/02/stamp_video_dog.mp4\" style=\"object-position:45% 82%\" data-object-fit=\"cover\" data-object-position=\"45% 82%\"></video><div class=\"wp-block-cover__inner-container\">\n<p class=\"has-text-align-center has-large-font-size\">Inner video!</p>\n</div></div>\n</div></div>\n",
			],
		];
	}

	/**
	 * Test that cover video gets fill layout and object-fit=cover.
	 *
	 * @dataProvider get_data_for_test_ampify_cover_block
	 * @covers \AMP_Core_Block_Handler::ampify_cover_block()
	 * @param string $block_content    Block content.
	 * @param string $expected_content Expected content.
	 */
	public function test_ampify_cover_block( $block_content, $expected_content ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			$this->markTestSkipped();
		}

		// Prevent Gutenberg render_block_core_cover() from varying the output across WP versions; it merely injects a playsinline attribute.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'core/cover' );
		if ( $block_type ) {
			$old_render_callback         = $block_type->render_callback;
			$block_type->render_callback = null;
		}

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$actual_content = do_blocks( $block_content );

		$this->assertEqualMarkup(
			$expected_content,
			$actual_content,
			'Expected snapshot JSON: ' . wp_json_encode( $actual_content, JSON_UNESCAPED_SLASHES )
		);

		if ( $block_type && isset( $old_render_callback ) ) {
			$block_type->render_callback = $old_render_callback;
		}
	}

	/**
	 * Test process_categories_widgets.
	 *
	 * @covers AMP_Core_Block_Handler::process_categories_widgets()
	 * @see WP_Widget_Categories
	 */
	public function test_process_categories_widgets() {
		$instance_count = 2;

		ob_start();
		the_widget(
			'WP_Widget_Categories',
			[ 'dropdown' => '1' ],
			[]
		);
		the_widget(
			'WP_Widget_Categories',
			[ 'dropdown' => '1' ],
			[
				'before_widget' => '<section>',
				'after_widget'  => '</section>',
			]
		);
		$html = ob_get_clean();

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		/**
		 * Elements.
		 *
		 * @var DOMElement $select
		 * @var DOMElement $form
		 */
		$selects = $dom->getElementsByTagName( 'select' );
		$forms   = $dom->getElementsByTagName( 'form' );

		$this->assertEquals( $instance_count, $dom->body->getElementsByTagName( 'script' )->length );
		$this->assertEquals( $instance_count, $selects->length );
		$this->assertEquals( $instance_count, $forms->length );

		$embed = new AMP_Core_Block_Handler();
		$embed->register_embed();
		$embed->sanitize_raw_embeds( $dom );

		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();

		$error_count = 0;
		$sanitizer   = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function () use ( &$error_count ) {
					$error_count++;
					return true;
				},
			]
		);
		$sanitizer->sanitize();
		$this->assertEquals( 0, $error_count );

		$this->assertEquals( 0, $dom->body->getElementsByTagName( 'script' )->length );
		$this->assertEquals( $instance_count, $selects->length );
		foreach ( $selects as $select ) {
			$this->assertTrue( $select->hasAttribute( 'on' ) );
		}
		$ids = [];
		foreach ( $forms as $form ) {
			$this->assertTrue( $form->hasAttribute( 'id' ) );
			$ids[] = $form->getAttribute( 'id' );
		}
		$this->assertCount( $instance_count, array_unique( $ids ) );
	}

	/**
	 * Test process_archives_widgets.
	 *
	 * @covers AMP_Core_Block_Handler::process_archives_widgets()
	 * @see WP_Widget_Archives
	 */
	public function test_process_archives_widgets() {
		$instance_count = 2;
		self::factory()->post->create( [ 'post_date' => '2010-01-01 01:01:01' ] );

		ob_start();
		the_widget(
			'WP_Widget_Archives',
			[ 'dropdown' => '1' ],
			[]
		);
		the_widget(
			'WP_Widget_Archives',
			[ 'dropdown' => '1' ],
			[
				'before_widget' => '<section>',
				'after_widget'  => '</section>',
			]
		);
		$html = ob_get_clean();

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		/**
		 * Elements.
		 *
		 * @var DOMElement $select
		 */
		$selects = $dom->getElementsByTagName( 'select' );

		$this->assertEquals( $instance_count, $selects->length );

		$embed = new AMP_Core_Block_Handler();
		$embed->register_embed();
		$embed->sanitize_raw_embeds( $dom, [ 'amp_to_amp_linking_enabled' => true ] );

		$error_count = 0;
		$sanitizer   = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function () use ( &$error_count ) {
					$error_count++;
					return true;
				},
			]
		);
		$sanitizer->sanitize();
		$this->assertEquals( 0, $error_count );

		$this->assertEquals( 0, $dom->body->getElementsByTagName( 'script' )->length );
		$this->assertEquals( $instance_count, $selects->length );
		foreach ( $selects as $select ) {
			$this->assertTrue( $select->hasAttribute( 'on' ) );
			$this->assertEquals( 'change:AMP.navigateTo(url=event.value)', $select->getAttribute( 'on' ) );

			$options = $dom->xpath->query( '//option[ @value != "" ]', $select );

			$this->assertGreaterThan( 0, $options->length );
			foreach ( $options as $option ) {
				/**
				 * Option element.
				 *
				 * @var DOMElement $option
				 */
				$query = wp_parse_url( $option->getAttribute( 'value' ), PHP_URL_QUERY );
				$this->assertNotEmpty( $query );
				$query_vars = [];
				wp_parse_str( $query, $query_vars );
				$this->assertArrayHasKey( amp_get_slug(), $query_vars );
			}
		}
	}

	/**
	 * Test process_text_widgets.
	 *
	 * @covers AMP_Core_Block_Handler::process_text_widgets()
	 * @see WP_Widget_Archives
	 */
	public function test_process_text_widgets() {
		$instance_count = 2;

		$embed = new AMP_Core_Block_Handler();
		$embed->register_embed();

		$video_attachment_id = $this->get_video_attachment_id();
		$video_metadata      = wp_get_attachment_metadata( $video_attachment_id );

		$text  = sprintf(
			'[video width="%d" height="%d" mp4="%s"][/video]',
			$video_metadata['width'],
			$video_metadata['height'],
			esc_url( wp_get_attachment_url( $video_attachment_id ) )
		);
		$text .= "\n\n";
		$text .= '<iframe src="https://example.com" width="265" height="150"></iframe>';

		$instance = [
			'text'   => $text,
			'filter' => true,
			'visual' => true,
		];

		ob_start();
		the_widget(
			'WP_Widget_Text',
			$instance,
			[]
		);
		the_widget(
			'WP_Widget_Text',
			$instance,
			[
				'before_widget' => '<section>',
				'after_widget'  => '</section>',
			]
		);
		$html = ob_get_clean();

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		/**
		 * Elements.
		 *
		 * @var DOMElement $element
		 */
		$text_widgets = $dom->xpath->query( '//div[ @class = "textwidget" ]' );

		$this->assertEquals( $instance_count, $text_widgets->length );

		$embed->sanitize_raw_embeds( $dom );

		$sanitizer = new AMP_Video_Sanitizer( $dom );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();

		$error_count = 0;
		$sanitizer   = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function () use ( &$error_count ) {
					$error_count++;
					return true;
				},
			]
		);
		$sanitizer->sanitize();
		$this->assertEquals( 0, $error_count );

		foreach ( $text_widgets as $text_widget ) {
			$video_div = $dom->xpath->query( './/div[ @class = "wp-video" ]', $text_widget )->item( 0 );
			$this->assertInstanceOf( 'DOMElement', $video_div );
			$this->assertFalse( $video_div->hasAttribute( 'style' ) );

			$amp_video = $video_div->getElementsByTagName( 'amp-video' )->item( 0 );
			$this->assertInstanceOf( 'DOMElement', $amp_video );
			$this->assertEquals( $video_metadata['width'], $amp_video->getAttribute( 'width' ) );
			$this->assertEquals( $video_metadata['height'], $amp_video->getAttribute( 'height' ) );

			$amp_iframe = $text_widget->getElementsByTagName( 'amp-iframe' )->item( 0 );
			$this->assertInstanceOf( 'DOMElement', $amp_iframe );
			$this->assertEquals( '265', $amp_iframe->getAttribute( 'width' ) );
			$this->assertEquals( '150', $amp_iframe->getAttribute( 'height' ) );
		}
	}
}
