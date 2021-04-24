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
 * @coversDefaultClass AMP_Core_Block_Handler
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

	/**
	 * Test that style attribute is injected into parent of PDF embed.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_file_block()
	 */
	public function test_ampify_file_block() {

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = do_blocks(
			'
			<!-- wp:file {"id":42,"href":"https://example.com/content/uploads/2021/04/example.pdf","displayPreview":true} -->
				<div class="wp-block-file">
					<object class="wp-block-file__embed" data="https://example.com/content/uploads/2021/04/example.pdf" type="application/pdf" style="width:100%;height:600px" aria-label="Embed of example."></object>
					<a href="https://example.com/content/uploads/2021/04/example.pdf">example</a>
					<a href="https://example.com/content/uploads/2021/04/example.pdf" class="wp-block-file__button" download>Download</a>
				</div>
			<!-- /wp:file -->
		'
		);

		if ( function_exists( 'gutenberg_register_block_core_file' ) ) {
			$this->assertTrue( wp_script_is( 'wp-block-library-file' ) );

			ob_start();
			do_action( 'wp_print_footer_scripts' );
			ob_end_clean();

			$this->assertFalse( wp_script_is( 'wp-block-library-file' ) );
		}

		$this->assertStringContains( '<div style="display: block;" class="wp-block-file">', $content );
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
