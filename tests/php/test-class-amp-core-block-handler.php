<?php
/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @coversDefaultClass AMP_Core_Block_Handler
 */
class Test_AMP_Core_Block_Handler extends TestCase {

	use MarkupComparison;
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
	 * @covers AMP_Core_Block_Handler::filter_rendered_block()
	 * @covers AMP_Core_Block_Handler::ampify_archives_block()
	 * @covers AMP_Core_Block_Handler::ampify_categories_block()
	 */
	public function test_register_and_unregister_embed() {
		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.

		$categories_block = '<!-- wp:categories {"displayAsDropdown":true,"showHierarchy":true,"showPostCounts":true} /-->';
		$archives_block   = '<!-- wp:archives {"displayAsDropdown":true,"showPostCounts":true} /-->';

		$handler->register_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertStringContainsString( '<select', $rendered );
		$this->assertStringNotContainsString( 'onchange', $rendered );
		$this->assertStringContainsString( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertStringContainsString( '<select', $rendered );
			$this->assertStringNotContainsString( 'onchange', $rendered );
			$this->assertStringContainsString( 'on="change', $rendered );
		}

		$handler->unregister_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertStringContainsString( '<select', $rendered );
		$this->assertStringContainsString( 'onchange', $rendered );
		$this->assertStringNotContainsString( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertStringContainsString( '<select', $rendered );
			$this->assertStringContainsString( 'onchange', $rendered );
			$this->assertStringNotContainsString( 'on="change', $rendered );
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

		$this->assertStringContainsString( 'width="560" height="320" style="aspect-ratio:560/320"', $content );
	}

	/**
	 * Check that no transformation is made when external video (not yet anyway).
	 *
	 * @link https://github.com/ampproject/amp-wp/issues/5233
	 * @covers \AMP_Core_Block_Handler::ampify_video_block()
	 */
	public function test_ampify_video_block_without_attachment() {
		$post_id = self::factory()->post->create(
			[
				'post_title'   => 'Video',
				'post_content' => '<!-- wp:video --><figure class="wp-block-video"><video controls src="https://example.com/foo.mp4"></video></figure><!-- /wp:video -->',
			]
		);

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = apply_filters( 'the_content', get_post( $post_id )->post_content );

		$this->assertStringContainsString( '<video controls src="https://example.com/foo.mp4">', $content );
	}

	/**
	 * Test embedding a PDF.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_file_block()
	 * @covers \AMP_Core_Block_Handler::dequeue_block_library_file_script()
	 */
	public function test_ampify_file_block_pdf_preview() {

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = do_blocks(
			'
			<!-- wp:file {"id":42,"href":"https://example.com/content/uploads/2021/04/example.pdf?foo=bar","displayPreview":true} -->
				<div class="wp-block-file">
					<object class="wp-block-file__embed" data="https://example.com/content/uploads/2021/04/example.pdf" type="application/pdf" style="width:100%;height:600px" aria-label="Embed of example."></object>
					<a href="https://example.com/content/uploads/2021/04/example.pdf">example</a>
					<a href="https://example.com/content/uploads/2021/04/example.pdf" class="wp-block-file__button" download>Download</a>
				</div>
			<!-- /wp:file -->
			'
		);

		if ( wp_script_is( 'wp-block-library-file', 'registered' ) ) {
			$this->assertTrue( wp_script_is( 'wp-block-library-file', 'enqueued' ) );
		}

		ob_start();
		wp_print_footer_scripts();
		ob_end_clean();

		if ( wp_script_is( 'wp-block-library-file', 'registered' ) ) {
			$this->assertFalse( wp_script_is( 'wp-block-library-file', 'enqueued' ) );
		}

		$this->assertStringContainsString( '<style id="amp-wp-file-block">', $content );
	}

	/**
	 * Test PDF in File block without preview.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_file_block()
	 */
	public function test_ampify_file_block_pdf_non_preview() {

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = do_blocks(
			'
			<!-- wp:file {"id":2924,"href":"https://example.com/content/uploads/2021/04/example.pdf","displayPreview":false} -->
			<div class="wp-block-file"><a href="https://example.com/content/uploads/2021/04/example.pdf">example</a><a href="https://example.com/content/uploads/2021/04/example.pdf" class="wp-block-file__button" download>Download</a></div>
			<!-- /wp:file -->
			'
		);

		$this->assertFalse( wp_script_is( 'wp-block-library-file', 'enqueued' ) );

		$this->assertStringNotContainsString( '<style id="amp-wp-file-block">', $content );
	}

	/**
	 * Test PDF in File block without preview.
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_file_block()
	 */
	public function test_ampify_file_block_non_pdf() {

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$content = do_blocks(
			'
			<!-- wp:file {"id":821,"href":"https://example.com/content/uploads/2021/04/example.mp3"} -->
			<div class="wp-block-file"><a href="https://example.com/content/uploads/2021/04/example.mp3">example</a><a href="https://example.com/content/uploads/2021/04/example.mp3" class="wp-block-file__button" download>Download</a></div>
			<!-- /wp:file -->
			'
		);

		$this->assertFalse( wp_script_is( 'wp-block-library-file', 'enqueued' ) );

		$this->assertStringNotContainsString( '<style id="amp-wp-file-block">', $content );
	}

	/**
	 * Get conversion data for navigation block
	 *
	 * @return array
	 */
	public function get_navigation_block_conversion_data() {
		return [
			'always-overlay-menu' => [
				'
				<!-- wp:navigation {"overlayMenu":"always","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"center"}} -->
				<!-- wp:page-list {"isNavigationChild":true,"showSubmenuIcon":true,"openSubmenusOnClick":false} /-->
				<!-- /wp:navigation -->
				',
				'always',
				[
					'containers' => [
						[
							'is_open'    => true,
							'parent_tag' => 'amp-lightbox',
						],
					],
					'contents'   => [
						[
							'has_id'       => true,
							'parent_class' => 'wp-block-navigation__responsive-dialog',
						],
					],
				],
			],
			'mobile-overlay-menu' => [
				'
				<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"center"}} -->
				<!-- wp:page-list {"isNavigationChild":true,"showSubmenuIcon":true,"openSubmenusOnClick":false} /-->
				<!-- /wp:navigation -->
				',
				'mobile',
				[
					'containers' => [
						[
							'is_open'    => false,
							'parent_tag' => 'nav',
						],
						[
							'is_open'    => true,
							'parent_tag' => 'amp-lightbox',
						],
					],
					'contents'   => [
						[
							'has_id'       => false,
							'parent_class' => 'wp-block-navigation__responsive-container',
						],
						[
							'has_id'       => true,
							'parent_class' => 'wp-block-navigation__responsive-dialog',
						],
					],
				],
			],
			'never-overlay-menu'  => [
				'
				<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"center"}} -->
				<!-- wp:page-list {"isNavigationChild":true,"showSubmenuIcon":true,"openSubmenusOnClick":false} /-->
				<!-- /wp:navigation -->
				',
				'never',
				[
					'containers' => [
						[
							'is_open'    => false,
							'parent_tag' => 'nav',
						],
					],
					'contents'   => [
						[
							'has_id'       => true,
							'parent_class' => 'wp-block-navigation__responsive-container',
						],
					],
				],
			],
		];
	}

	/**
	 * Test navigation block
	 *
	 * Expect that:
	 * - "wp-block-navigation-view" script is not enqueued,
	 * - "button.wp-block-navigation__responsive-container-open" has on="tap:{id}.open" attribute,
	 * - "button.wp-block-navigation__responsive-container-close" has on="tap:{id}.close" attribute,
	 * - there are two "div.wp-block-navigation__responsive-container" elements, one of them is directly
	 *   wrapped by <amp-lightbox id="{id}" layout="nodisplay"> and has "is-menu-open has-modal-open" classes,
	 * - there are no unwanted attributes: "aria-expanded", "aria-modal", "data-micromodal-trigger", "data-micromodal-close".
	 *
	 * @covers \AMP_Core_Block_Handler::ampify_navigation_block()
	 * @dataProvider get_navigation_block_conversion_data
	 *
	 * @param string $source       Source.
	 * @param string $overlay_menu "Overlay menu" attribute value.
	 * @param array  $expectations Test expectations for containers and contents div's.
	 */
	public function test_ampify_navigation_block( $source, $overlay_menu, $expectations ) {
		if ( ! defined( 'GUTENBERG_VERSION' ) || version_compare( GUTENBERG_VERSION, '10.7', '<' ) ) {
			$this->markTestSkipped( 'Requires Gutenberg 10.7 or higher.' );
		}

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$post_id = wp_insert_post(
			[
				'post_title'   => 'Test',
				'post_content' => 'Test',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			],
			true
		);

		$content = do_blocks( $source );
		$dom     = AMP_DOM_Utils::get_dom_from_content( $content );

		$this->assertFalse( wp_script_is( 'wp-block-navigation-view', 'enqueued' ) );

		$amp_lightboxes = $dom->getElementsByTagName( 'amp-lightbox' );
		$this->assertEquals( 'never' !== $overlay_menu ? 1 : 0, $amp_lightboxes->length );

		$class_query = '//%1$s[ contains( concat( " ", normalize-space( @class ), " " ), " %2$s " ) ]';

		// Expect that "mobile" and "always" overlayed menus are wrapped by <amp-lightbox> element,
		// and that open/close buttons are set correctly.
		if ( 'never' !== $overlay_menu ) {
			$amp_lightbox = $amp_lightboxes->item( 0 );

			$this->assertTrue( $amp_lightbox->hasAttribute( 'id' ) );
			$this->assertTrue( $amp_lightbox->hasAttribute( 'layout' ) );
			$this->assertEquals( 'nodisplay', $amp_lightbox->getAttribute( 'layout' ) );

			$amp_lightbox_id   = $amp_lightbox->getAttribute( 'id' );
			$open_button_node  = $dom->xpath->query( sprintf( $class_query, 'button', 'wp-block-navigation__responsive-container-open' ) )->item( 0 );
			$close_button_node = $dom->xpath->query( sprintf( $class_query, 'button', 'wp-block-navigation__responsive-container-close' ) )->item( 0 );

			$this->assertEquals( sprintf( 'tap:%s.open', $amp_lightbox_id ), $open_button_node->getAttribute( 'on' ) );
			$this->assertEquals( sprintf( 'tap:%s.close', $amp_lightbox_id ), $close_button_node->getAttribute( 'on' ) );
		}

		// Expect that "div.wp-block-navigation__responsive-container" elements are wrapped correctly,
		// has expected class names, and are or are not duplicated.
		$containers = $dom->xpath->query( sprintf( $class_query, 'div', 'wp-block-navigation__responsive-container' ) );
		$this->assertEquals( count( $expectations['containers'] ), $containers->length );

		foreach ( $expectations['containers'] as $index => $expectation ) {
			$element        = $containers->item( $index );
			$has_open_class = false !== strpos( $element->getAttribute( 'class' ), 'is-menu-open has-modal-open' );
			$this->assertEquals( $expectation['is_open'], $has_open_class );
			$this->assertEquals( $expectation['parent_tag'], $element->parentNode->tagName );
		}

		// Expect that there are two "div.wp-block-navigation__responsive-container-content" elements, and:
		// - first one is directly wrapped by "div.wp-block-navigation__responsive-container" element and does not has an ID attribute,
		// - second one is not directly wrapped by "div.wp-block-navigation__responsive-container" element and does has an ID attribute.
		$contents = $dom->xpath->query( sprintf( $class_query, 'div', 'wp-block-navigation__responsive-container-content' ) );
		$this->assertEquals( count( $expectations['contents'] ), $contents->length );

		foreach ( $expectations['contents'] as $index => $expectation ) {
			$element = $contents->item( $index );
			$this->assertEquals( $expectation['has_id'], $element->hasAttribute( 'id' ) );
			$this->assertTrue( false !== strpos( $element->parentNode->getAttribute( 'class' ), $expectation['parent_class'] ) );
		}

		// Expect that there are no unwanted attributes: "aria-expanded", "aria-modal", "data-micromodal-trigger", "data-micromodal-close".
		$unwanted_attributes = [
			'aria-expanded',
			'aria-modal',
			'data-micromodal-trigger',
			'data-micromodal-close',
		];

		foreach ( $unwanted_attributes as $unwanted_attribute ) {
			$this->assertTrue( false === strpos( $content, $unwanted_attribute ) );
		}
	}

	/**
	 * Test process_categories_widgets.
	 *
	 * @covers AMP_Core_Block_Handler::process_categories_widgets()
	 * @covers AMP_Core_Block_Handler::sanitize_raw_embeds()
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
	 * @covers AMP_Core_Block_Handler::sanitize_raw_embeds()
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
	 * @covers AMP_Core_Block_Handler::sanitize_raw_embeds()
	 * @covers AMP_Core_Block_Handler::preserve_widget_text_element_dimensions()
	 * @see WP_Widget_Text
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
