<?php
/**
 * Tests for AMP_Validation_Utils class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Validation_Utils class.
 *
 * @since 0.7
 */
class Test_AMP_Validation_Utils extends \WP_UnitTestCase {

	/**
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Validation_Utils';

	/**
	 * An instance of DOMElement to test.
	 *
	 * @var DOMElement
	 */
	public $node;

	/**
	 * A tag that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_tag = '<script async src="https://example.com/script"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

	/**
	 * The name of a tag that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_tag_name = 'script';

	/**
	 * The name of an attribute that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_attribute_name = 'onload';

	/**
	 * A mock plugin name that outputs invalid markup.
	 *
	 * @var string
	 */
	public $plugin_name = 'foo-bar';

	/**
	 * A valid image that sanitizers should not alter.
	 *
	 * @var string
	 */
	public $valid_amp_img = '<amp-img id="img-123" media="(min-width: 600x)" src="/assets/example.jpg" width="200" height="500" layout="responsive"></amp-img>';

	/**
	 * The name of the tag to test.
	 *
	 * @var string
	 */
	const TAG_NAME = 'img';

	/**
	 * Backed up $wp_registered_widgets.
	 *
	 * @var array
	 */
	protected $original_wp_registered_widgets;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 * @global $wp_registered_widgets
	 */
	public function setUp() {
		parent::setUp();
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		AMP_Validation_Utils::reset_validation_results();
		$this->original_wp_registered_widgets = $GLOBALS['wp_registered_widgets'];
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		$GLOBALS['wp_registered_widgets'] = $this->original_wp_registered_widgets; // WPCS: override ok.
		remove_theme_support( 'amp' );
		unset( $GLOBALS['current_screen'] );
		parent::tearDown();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Validation_Utils::init()
	 */
	public function test_init() {
		add_theme_support( 'amp' );
		AMP_Validation_Utils::init();
		$this->assertEquals( 10, has_action( 'edit_form_top', self::TESTED_CLASS . '::print_edit_form_validation_status' ) );
		$this->assertEquals( 10, has_action( 'init', self::TESTED_CLASS . '::register_post_type' ) );
		$this->assertEquals( 10, has_action( 'all_admin_notices', self::TESTED_CLASS . '::plugin_notice' ) );
		$this->assertEquals( 10, has_filter( 'manage_' . AMP_Validation_Utils::POST_TYPE_SLUG . '_posts_columns', self::TESTED_CLASS . '::add_post_columns' ) );
		$this->assertEquals( 10, has_action( 'manage_posts_custom_column', self::TESTED_CLASS . '::output_custom_column' ) );
		$this->assertEquals( 10, has_filter( 'post_row_actions', self::TESTED_CLASS . '::filter_row_actions' ) );
		$this->assertEquals( 10, has_filter( 'bulk_actions-edit-' . AMP_Validation_Utils::POST_TYPE_SLUG, self::TESTED_CLASS . '::add_bulk_action' ) );
		$this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-' . AMP_Validation_Utils::POST_TYPE_SLUG, self::TESTED_CLASS . '::handle_bulk_action' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', self::TESTED_CLASS . '::remaining_error_notice' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', self::TESTED_CLASS . '::persistent_object_caching_notice' ) );
		$this->assertEquals( 10, has_action( 'admin_menu', self::TESTED_CLASS . '::remove_publish_meta_box' ) );
		$this->assertEquals( 10, has_action( 'add_meta_boxes', self::TESTED_CLASS . '::add_meta_boxes' ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', self::TESTED_CLASS . '::add_rest_api_fields' ) );
	}

	/**
	 * Test add_validation_hooks.
	 *
	 * @covers AMP_Validation_Utils::add_validation_hooks()
	 */
	public function test_add_validation_hooks() {
		AMP_Validation_Utils::add_validation_hooks();
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_content', array( self::TESTED_CLASS, 'decorate_filter_source' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_excerpt', array( self::TESTED_CLASS, 'decorate_filter_source' ) ) );
		$this->assertEquals( 10, has_action( 'amp_content_sanitizers', array( self::TESTED_CLASS, 'add_validation_callback' ) ) );
		$this->assertEquals( -1, has_action( 'do_shortcode_tag', array( self::TESTED_CLASS, 'decorate_shortcode_source' ) ) );
	}

	/**
	 * Test add_validation_hooks with Gutenberg active.
	 *
	 * @covers AMP_Validation_Utils::add_validation_hooks()
	 */
	public function test_add_validation_hooks_gutenberg() {
		if ( ! function_exists( 'do_blocks' ) ) {
			$this->markTestSkipped( 'Gutenberg not active.' );
		}
		if ( ( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) ) ) {
			$this->markTestSkipped( 'The WP version is less than 4.9, so Gutenberg did not init.' );
		}

		$priority = has_filter( 'the_content', 'do_blocks' );
		$this->assertNotFalse( $priority );
		AMP_Validation_Utils::add_validation_hooks();
		$this->assertEquals( $priority - 1, has_filter( 'the_content', array( self::TESTED_CLASS, 'add_block_source_comments' ) ) );
	}

	/**
	 * Get block data.
	 *
	 * @see Test_AMP_Validation_Utils::test_add_block_source_comments()
	 * @return array
	 */
	public function get_block_data() {
		return array(
			'paragraph'    => array(
				"<!-- wp:paragraph -->\n<p>Latest posts:</p>\n<!-- /wp:paragraph -->",
				"<!--amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<p>Latest posts:</p>\n<!--/amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}}}-->",
				array(
					'element' => 'p',
					'blocks'  => array( 'core/paragraph' ),
				),
			),
			'latest_posts' => array(
				'<!-- wp:latest-posts /-->',
				'<!--amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"block_content_index":0,"type":"plugin","name":"gutenberg","function":"render_block_core_latest_posts"}--><ul class="wp-block-latest-posts aligncenter"><li><a href="{{url}}">{{title}}</a></li></ul><!--/amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"type":"plugin","name":"gutenberg","function":"render_block_core_latest_posts"}-->',
				array(
					'element' => 'ul',
					'blocks'  => array( 'core/latest-posts' ),
				),
			),
			'columns'      => array(
				"<!-- wp:columns -->\n<div class=\"wp-block-columns has-2-columns\">\n    <!-- wp:quote {\"layout\":\"column-1\",\"foo\":{\"bar\":1}} -->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!-- /wp:quote -->\n\n    <!-- wp:html {\"layout\":\"column-2\"} -->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!-- /wp:html -->\n</div>\n<!-- /wp:columns -->",
				"<!--amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<div class=\"wp-block-columns has-2-columns\">\n\n\n\n<!--amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}},\"block_content_index\":1,\"block_attrs\":{\"layout\":\"column-1\",\"foo\":{\"bar\":1}}}-->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!--/amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}}}--><!--amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}},\"block_content_index\":2,\"block_attrs\":{\"layout\":\"column-2\"}}-->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!--/amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}}}--></div>\n<!--/amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}}}-->",
				array(
					'element' => 'blockquote',
					'blocks'  => array(
						'core/columns',
						'core/quote',
					),
				),
			),
		);
	}

	/**
	 * Test add_block_source_comments.
	 *
	 * @param string $content  Content.
	 * @param string $expected Expected content.
	 * @param array  $query    Query.
	 * @dataProvider get_block_data
	 * @covers AMP_Validation_Utils::add_block_source_comments()
	 */
	public function test_add_block_source_comments( $content, $expected, $query ) {
		if ( ! function_exists( 'do_blocks' ) ) {
			$this->markTestSkipped( 'Gutenberg not active.' );
		}
		if ( ( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) ) ) {
			$this->markTestSkipped( 'The WP version is less than 4.9, so Gutenberg did not init.' );
		}

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: Override ok.
		$this->assertInstanceOf( 'WP_Post', get_post() );

		$rendered_block = do_blocks( AMP_Validation_Utils::add_block_source_comments( $content ) );

		$expected = str_replace(
			array(
				'{{post_id}}',
				'{{title}}',
				'{{url}}',
			),
			array(
				$post->ID,
				get_the_title( $post ),
				get_permalink( $post ),
			),
			$expected
		);
		$this->assertEquals(
			preg_replace( '/(?<=>)\s+(?=<)/', '', str_replace( '%d', $post->ID, $expected ) ),
			preg_replace( '/(?<=>)\s+(?=<)/', '', $rendered_block )
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $rendered_block );
		$el  = $dom->getElementsByTagName( $query['element'] )->item( 0 );

		$this->assertEquals(
			$query['blocks'],
			wp_list_pluck( AMP_Validation_Utils::locate_sources( $el ), 'block_name' )
		);
	}

	/**
	 * Test add_validation_error.
	 *
	 * @covers AMP_Validation_Utils::add_validation_error()
	 */
	public function test_track_removed() {
		$this->assertEmpty( AMP_Validation_Utils::$validation_errors );
		AMP_Validation_Utils::add_validation_error( array(
			'node' => $this->node,
		) );

		$this->assertEquals(
			array(
				array(
					'node_name'       => 'img',
					'sources'         => array(),
					'code'            => AMP_Validation_Utils::INVALID_ELEMENT_CODE,
					'node_attributes' => array(),
				),
			),
			AMP_Validation_Utils::$validation_errors
		);
		AMP_Validation_Utils::reset_validation_results();
	}

	/**
	 * Test was_node_removed.
	 *
	 * @covers AMP_Validation_Utils::add_validation_error()
	 */
	public function test_was_node_removed() {
		$this->assertEmpty( AMP_Validation_Utils::$validation_errors );
		AMP_Validation_Utils::add_validation_error(
			array(
				'node' => $this->node,
			)
		);
		$this->assertNotEmpty( AMP_Validation_Utils::$validation_errors );
	}

	/**
	 * Test process_markup.
	 *
	 * @covers AMP_Validation_Utils::process_markup()
	 */
	public function test_process_markup() {
		$this->set_capability();
		AMP_Validation_Utils::process_markup( $this->valid_amp_img );
		$this->assertEquals( array(), AMP_Validation_Utils::$validation_errors );

		AMP_Validation_Utils::reset_validation_results();
		$video = '<video src="https://example.com/video">';
		AMP_Validation_Utils::process_markup( $video );
		// This isn't valid AMP, but the sanitizer should convert it to an <amp-video>, without stripping anything.
		$this->assertEquals( array(), AMP_Validation_Utils::$validation_errors );

		AMP_Validation_Utils::reset_validation_results();

		AMP_Validation_Utils::process_markup( $this->disallowed_tag );
		$this->assertCount( 1, AMP_Validation_Utils::$validation_errors );
		$this->assertEquals( 'script', AMP_Validation_Utils::$validation_errors[0]['node_name'] );

		AMP_Validation_Utils::reset_validation_results();
		$disallowed_style = '<div style="display:none"></div>';
		AMP_Validation_Utils::process_markup( $disallowed_style );
		$this->assertEquals( array(), AMP_Validation_Utils::$validation_errors );

		AMP_Validation_Utils::reset_validation_results();
		$invalid_video = '<video width="200" height="100"></video>';
		AMP_Validation_Utils::process_markup( $invalid_video );
		$this->assertCount( 1, AMP_Validation_Utils::$validation_errors );
		$this->assertEquals( 'video', AMP_Validation_Utils::$validation_errors[0]['node_name'] );
		AMP_Validation_Utils::reset_validation_results();

		AMP_Validation_Utils::process_markup( '<button onclick="evil()">Do it</button>' );
		$this->assertCount( 1, AMP_Validation_Utils::$validation_errors );
		$this->assertEquals( 'onclick', AMP_Validation_Utils::$validation_errors[0]['node_name'] );
		AMP_Validation_Utils::reset_validation_results();
	}

	/**
	 * Test has_cap.
	 *
	 * @covers AMP_Validation_Utils::has_cap()
	 */
	public function test_has_cap() {
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'subscriber',
		) ) );
		$this->assertFalse( AMP_Validation_Utils::has_cap() );

		$this->set_capability();
		$this->assertTrue( AMP_Validation_Utils::has_cap() );
	}

	/**
	 * Test get_response.
	 *
	 * @covers AMP_Validation_Utils::summarize_validation_errors()
	 */
	public function test_summarize_validation_errors() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		AMP_Validation_Utils::process_markup( $this->disallowed_tag );
		$response = AMP_Validation_Utils::summarize_validation_errors( AMP_Validation_Utils::$validation_errors );
		AMP_Validation_Utils::reset_validation_results();
		$expected_response = array(
			AMP_Validation_Utils::REMOVED_ELEMENTS   => array(
				'script' => 1,
			),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES => array(),
			'sources_with_invalid_output'            => array(),
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test reset_validation_results.
	 *
	 * @covers AMP_Validation_Utils::reset_validation_results()
	 */
	public function test_reset_validation_results() {
		AMP_Validation_Utils::add_validation_error( array(
			'code' => 'test',
		) );
		AMP_Validation_Utils::reset_validation_results();
		$this->assertEquals( array(), AMP_Validation_Utils::$validation_errors );
	}

	/**
	 * Test print_edit_form_validation_status
	 *
	 * @covers AMP_Validation_Utils::print_edit_form_validation_status()
	 */
	public function test_print_edit_form_validation_status() {
		add_theme_support( 'amp' );

		AMP_Validation_Utils::register_post_type();
		$this->set_capability();
		$post = $this->factory()->post->create_and_get();
		ob_start();
		AMP_Validation_Utils::print_edit_form_validation_status( $post );
		$output = ob_get_clean();

		$this->assertNotContains( 'notice notice-warning', $output );
		$this->assertNotContains( 'Warning:', $output );

		$this->create_custom_post(
			array(
				array(
					'code'            => AMP_Validation_Utils::INVALID_ELEMENT_CODE,
					'node_name'       => $this->disallowed_tag_name,
					'parent_name'     => 'div',
					'node_attributes' => array(),
					'sources'         => array(
						array(
							'type' => 'plugin',
							'name' => $this->plugin_name,
						),
					),
				),
			),
			amp_get_permalink( $post->ID )
		);
		ob_start();
		AMP_Validation_Utils::print_edit_form_validation_status( $post );
		$output = ob_get_clean();

		$this->assertContains( 'notice notice-warning', $output );
		$this->assertContains( 'Warning:', $output );
		$this->assertContains( '<code>script</code>', $output );
	}

	/**
	 * Test get_existing_validation_errors.
	 *
	 * @covers AMP_Validation_Utils::get_existing_validation_errors()
	 */
	public function test_get_existing_validation_errors() {
		add_theme_support( 'amp' );
		AMP_Validation_Utils::register_post_type();
		$post = $this->factory()->post->create_and_get();
		$this->assertEquals( null, AMP_Validation_Utils::get_existing_validation_errors( $post ) );

		// Create an error custom post for the $post_id, so the function will return existing errors.
		$this->create_custom_post( array(), amp_get_permalink( $post->ID ) );
		$this->assertEquals(
			$this->get_mock_errors(),
			AMP_Validation_Utils::get_existing_validation_errors( $post )
		);
	}

	/**
	 * Test source comments.
	 *
	 * @covers AMP_Validation_Utils::locate_sources()
	 * @covers AMP_Validation_Utils::parse_source_comment()
	 * @covers AMP_Validation_Utils::get_source_comment()
	 * @covers AMP_Validation_Utils::remove_source_comments()
	 */
	public function test_source_comments() {
		$source1 = array(
			'type'      => 'plugin',
			'name'      => 'foo',
			'shortcode' => 'test',
			'function'  => __FUNCTION__,
		);
		$source2 = array(
			'type'     => 'theme',
			'name'     => 'bar',
			'function' => __FUNCTION__,
			'hook'     => 'something',
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( implode(
			'',
			array(
				AMP_Validation_Utils::get_source_comment( $source1, true ),
				AMP_Validation_Utils::get_source_comment( $source2, true ),
				'<b id="test">Test</b>',
				AMP_Validation_Utils::get_source_comment( $source2, false ),
				AMP_Validation_Utils::get_source_comment( $source1, false ),
			)
		) );

		/**
		 * Comments.
		 *
		 * @var DOMComment[] $comments
		 */
		$comments = array();
		$xpath    = new DOMXPath( $dom );
		foreach ( $xpath->query( '//comment()' ) as $comment ) {
			$comments[] = $comment;
		}
		$this->assertCount( 4, $comments );

		$sources = AMP_Validation_Utils::locate_sources( $dom->getElementById( 'test' ) );
		$this->assertInternalType( 'array', $sources );
		$this->assertCount( 2, $sources );

		$this->assertEquals( $source1, $sources[0] );
		$parsed_comment = AMP_Validation_Utils::parse_source_comment( $comments[0] );
		$this->assertEquals( $source1, $parsed_comment['source'] );
		$this->assertFalse( $parsed_comment['closing'] );
		$parsed_comment = AMP_Validation_Utils::parse_source_comment( $comments[3] );
		$this->assertEquals( $source1, $parsed_comment['source'] );
		$this->assertTrue( $parsed_comment['closing'] );

		$this->assertEquals( $source2, $sources[1] );
		$parsed_comment = AMP_Validation_Utils::parse_source_comment( $comments[1] );
		$this->assertEquals( $source2, $parsed_comment['source'] );
		$this->assertFalse( $parsed_comment['closing'] );
		$parsed_comment = AMP_Validation_Utils::parse_source_comment( $comments[2] );
		$this->assertEquals( $source2, $parsed_comment['source'] );
		$this->assertTrue( $parsed_comment['closing'] );

		AMP_Validation_Utils::remove_source_comments( $dom );
		$this->assertEquals( 0, $xpath->query( '//comment()' )->length );
	}

	/**
	 * Test wrap_widget_callbacks.
	 *
	 * @covers AMP_Validation_Utils::wrap_widget_callbacks()
	 */
	public function test_wrap_widget_callbacks() {
		global $wp_registered_widgets, $_wp_sidebars_widgets;

		$widget_id = 'search-2';
		$this->assertArrayHasKey( $widget_id, $wp_registered_widgets );
		$this->assertInternalType( 'array', $wp_registered_widgets[ $widget_id ]['callback'] );
		$this->assertInstanceOf( 'WP_Widget_Search', $wp_registered_widgets[ $widget_id ]['callback'][0] );

		AMP_Validation_Utils::wrap_widget_callbacks();
		$this->assertInstanceOf( 'Closure', $wp_registered_widgets[ $widget_id ]['callback'] );

		$sidebar_id = 'amp-sidebar';
		register_sidebar( array(
			'id'           => $sidebar_id,
			'after_widget' => '</li>',
		) );
		$_wp_sidebars_widgets[ $sidebar_id ] = array( $widget_id ); // WPCS: global override ok.

		AMP_Theme_Support::start_output_buffering();
		dynamic_sidebar( $sidebar_id );
		$output = ob_get_clean();

		$this->assertStringStartsWith(
			'<!--amp-source-stack {"type":"core","name":"wp-includes","function":"WP_Widget_Search::display_callback","widget_id":"search-2"}--><li id="search-2"',
			$output
		);
		$this->assertStringEndsWith(
			'</li><!--/amp-source-stack {"type":"core","name":"wp-includes","function":"WP_Widget_Search::display_callback","widget_id":"search-2"}-->',
			$output
		);
	}

	/**
	 * Test wrap_hook_callbacks.
	 *
	 * @covers AMP_Validation_Utils::wrap_hook_callbacks()
	 */
	public function test_callback_wrappers() {
		global $post;
		$that = $this;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->set_capability();
		$action_no_tag_output     = 'foo_action';
		$action_core_output       = 'core_action';
		$action_no_output         = 'bar_action_no_output';
		$action_function_callback = 'baz_action_function';
		$action_no_argument       = 'test_action_no_argument';
		$action_one_argument      = 'baz_action_one_argument';
		$action_two_arguments     = 'example_action_two_arguments';
		$notice                   = 'Example notice';

		AMP_Validation_Utils::add_validation_hooks();

		add_action( $action_function_callback, '_amp_print_php_version_admin_notice' );
		add_action( $action_no_argument, array( $this, 'output_div' ) );
		add_action( $action_one_argument, array( $this, 'output_notice' ) );
		add_action( $action_two_arguments, array( $this, 'output_message' ), 10, 2 );
		add_action( $action_no_output, array( $this, 'get_string' ), 10, 2 );
		add_action( $action_no_tag_output, 'the_ID' );
		add_action( $action_core_output, 'edit_post_link' );
		add_action( $action_no_output, '__return_false' );

		// All of the callback functions remain as-is. They will only change for a given hook at the 'all' action.
		$this->assertEquals( 10, has_action( $action_no_tag_output, 'the_ID' ) );
		$this->assertEquals( 10, has_action( $action_no_output, array( $this, 'get_string' ) ) );
		$this->assertEquals( 10, has_action( $action_no_argument, array( $this, 'output_div' ) ) );
		$this->assertEquals( 10, has_action( $action_one_argument, array( $this, 'output_notice' ) ) );
		$this->assertEquals( 10, has_action( $action_two_arguments, array( $this, 'output_message' ) ) );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_function_callback );
		$output = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error">', $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_argument );
		$output = ob_get_clean();
		$this->assertContains( '<div></div>', $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_one_argument, $notice );
		$output = ob_get_clean();
		$this->assertContains( $notice, $output );
		$this->assertContains( sprintf( '<div class="notice notice-warning"><p>%s</p></div>', $notice ), $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_two_arguments, $notice, get_the_ID() );
		$output = ob_get_clean();
		AMP_Theme_Support::start_output_buffering();
		self::output_message( $notice, get_the_ID() );
		$expected_output = ob_get_clean();
		$this->assertContains( $expected_output, $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		// This action's callback doesn't output any HTML tags, so no HTML should be present.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_tag_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack ', $output );
		$this->assertNotContains( '<!--/amp-source-stack ', $output );

		// This action's callback comes from core.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_core_output );
		$output = ob_get_clean();
		$this->assertContains( '<!--amp-source-stack {"type":"core","name":"wp-includes"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"core","name":"wp-includes"', $output );

		// This action's callback doesn't echo any markup, so it shouldn't be wrapped in comments.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack ', $output );
		$this->assertNotContains( '<!--/amp-source-stack ', $output );

		// Ensure that nested actions output the expected stack, and that has_action() works as expected in spite of the function wrapping.
		$handle_outer_action = function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			do_action( 'inner_action' );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
		};
		$handle_inner_action = function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			echo '<b>Hello</b>';
		};
		add_action( 'outer_action', $handle_outer_action );
		add_action( 'inner_action', $handle_inner_action );
		AMP_Theme_Support::start_output_buffering();
		do_action( 'outer_action' );
		$output = ob_get_clean();
		$this->assertEquals(
			implode( '', array(
				'<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"outer_action"}-->',
				'<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"inner_action"}-->',
				'<b>Hello</b>',
				'<!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"inner_action"}-->',
				'<!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"outer_action"}-->',
			) ),
			$output
		);
	}

	/**
	 * Test decorate_shortcode_source.
	 *
	 * @covers AMP_Validation_Utils::decorate_shortcode_source()
	 * @covers AMP_Validation_Utils::decorate_filter_source()
	 */
	public function test_decorate_shortcode_and_filter_source() {
		AMP_Validation_Utils::add_validation_hooks();
		add_shortcode( 'test', function() {
			return '<b>test</b>';
		} );

		$filtered_content = apply_filters( 'the_content', 'before[test]after' );
		$source_json      = '{"hook":"the_content","filter":true,"sources":[{"type":"core","name":"wp-includes","function":"WP_Embed::run_shortcode"},{"type":"core","name":"wp-includes","function":"WP_Embed::autoembed"},{"type":"core","name":"wp-includes","function":"wptexturize"},{"type":"core","name":"wp-includes","function":"wpautop"},{"type":"core","name":"wp-includes","function":"shortcode_unautop"},{"type":"core","name":"wp-includes","function":"prepend_attachment"},{"type":"core","name":"wp-includes","function":"wp_make_content_images_responsive"},{"type":"core","name":"wp-includes","function":"capital_P_dangit"},{"type":"core","name":"wp-includes","function":"do_shortcode"},{"type":"core","name":"wp-includes","function":"convert_smilies"}]}';
		$expected_content = implode( '', array(
			"<!--amp-source-stack $source_json>",
			'<p>before<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","shortcode":"test"}--><b>test</b><!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","shortcode":"test"}-->after</p>' . "\n",
			"<!--/amp-source-stack $source_json>",
		) );
	}

	/**
	 * Test get_source
	 *
	 * @covers AMP_Validation_Utils::get_source()
	 */
	public function test_get_source() {
		$source = AMP_Validation_Utils::get_source( 'amp_after_setup_theme' );
		$this->assertEquals( 'amp', $source['name'] );
		$this->assertEquals( 'plugin', $source['type'] );

		$source = AMP_Validation_Utils::get_source( 'the_content' );
		$this->assertEquals( 'wp-includes', $source['name'] );
		$this->assertEquals( 'core', $source['type'] );
	}

	/**
	 * Test wrapped_callback
	 *
	 * @covers AMP_Validation_Utils::wrapped_callback()
	 */
	public function test_wrapped_callback() {
		$test_string = "<b class='\nfoo\nbar\n'>Cool!</b>";
		$callback    = array(
			'function'      => function() use ( $test_string ) {
				echo $test_string; // WPCS: XSS OK.
			},
			'accepted_args' => 0,
			'source'        => array(
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			),
		);

		$wrapped_callback = AMP_Validation_Utils::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		AMP_Theme_Support::start_output_buffering();
		call_user_func( $wrapped_callback );
		$output = ob_get_clean();

		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertContains( $test_string, $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );

		$callback = array(
			'function'      => array( $this, 'get_string' ),
			'accepted_args' => 0,
			'source'        => array(
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			),
		);

		$wrapped_callback = AMP_Validation_Utils::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		AMP_Theme_Support::start_output_buffering();
		$result = call_user_func( $wrapped_callback );
		$output = ob_get_clean();
		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertEquals( '', $output );
		$this->assertEquals( call_user_func( array( $this, 'get_string' ) ), $result );
		unset( $post );
	}

	/**
	 * Add a nonce to the $_REQUEST, so that is_authorized() returns true.
	 *
	 * @return void
	 */
	public function set_capability() {
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );
	}

	/**
	 * Outputs a div.
	 *
	 * @return void
	 */
	public function output_div() {
		echo '<div></div>';
	}

	/**
	 * Outputs a notice.
	 *
	 * @param string $message The message to output.
	 *
	 * @return void
	 */
	public function output_notice( $message ) {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_attr( $message ) );
	}

	/**
	 * Outputs a message with an excerpt.
	 *
	 * @param string $message The message to output.
	 * @param string $id The ID of the post.
	 *
	 * @return void
	 */
	public function output_message( $message, $id ) {
		printf( '<<p>%s</p><p>%s</p>', esc_attr( $message ), esc_html( $id ) );
	}

	/**
	 * Gets a string.
	 *
	 * @return string
	 */
	public function get_string() {
		return 'Example string';
	}

	/**
	 * Test should_validate_response.
	 *
	 * @covers AMP_Validation_Utils::should_validate_response()
	 */
	public function test_should_validate_response() {
		global $post;
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		$this->assertFalse( AMP_Validation_Utils::should_validate_response() );
		$_GET[ AMP_Validation_Utils::VALIDATE_QUERY_VAR ] = 1;
		$this->assertFalse( AMP_Validation_Utils::should_validate_response() );
		$this->set_capability();
		$this->assertTrue( AMP_Validation_Utils::should_validate_response() );
	}

	/**
	 * Test add_validation_callback
	 *
	 * @covers AMP_Validation_Utils::add_validation_callback()
	 */
	public function test_add_validation_callback() {
		global $post;
		$post       = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$sanitizers = array(
			'AMP_Img_Sanitizer'      => array(),
			'AMP_Form_Sanitizer'     => array(),
			'AMP_Comments_Sanitizer' => array(),
		);

		$expected_callback   = self::TESTED_CLASS . '::add_validation_error';
		$filtered_sanitizers = AMP_Validation_Utils::add_validation_callback( $sanitizers );
		foreach ( $filtered_sanitizers as $sanitizer => $args ) {
			$this->assertEquals( $expected_callback, $args['validation_error_callback'] );
		}
		remove_theme_support( 'amp' );
	}

	/**
	 * Test for register_post_type()
	 *
	 * @covers AMP_Validation_Utils::register_post_type()
	 */
	public function test_register_post_type() {
		AMP_Validation_Utils::register_post_type();
		$amp_post_type = get_post_type_object( AMP_Validation_Utils::POST_TYPE_SLUG );

		$this->assertTrue( in_array( AMP_Validation_Utils::POST_TYPE_SLUG, get_post_types(), true ) );
		$this->assertEquals( array(), get_all_post_type_supports( AMP_Validation_Utils::POST_TYPE_SLUG ) );
		$this->assertEquals( AMP_Validation_Utils::POST_TYPE_SLUG, $amp_post_type->name );
		$this->assertEquals( 'Validation Status', $amp_post_type->label );
		$this->assertEquals( false, $amp_post_type->public );
		$this->assertTrue( $amp_post_type->show_ui );
		$this->assertEquals( AMP_Options_Manager::OPTION_NAME, $amp_post_type->show_in_menu );
		$this->assertTrue( $amp_post_type->show_in_admin_bar );
	}

	/**
	 * Test for store_validation_errors()
	 *
	 * @covers AMP_Validation_Utils::store_validation_errors()
	 */
	public function test_store_validation_errors() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );

		$this->assertCount( 1, AMP_Validation_Utils::$validation_errors );
		$this->assertEquals( 'script', AMP_Validation_Utils::$validation_errors[0]['node_name'] );
		$this->assertEquals(
			array(
				'type' => 'plugin',
				'name' => 'foo',
			),
			AMP_Validation_Utils::$validation_errors[0]['sources'][0]
		);

		$url     = home_url( '/' );
		$post_id = AMP_Validation_Utils::store_validation_errors( AMP_Validation_Utils::$validation_errors, $url );
		$this->assertNotEmpty( $post_id );
		$custom_post               = get_post( $post_id );
		$validation                = AMP_Validation_Utils::summarize_validation_errors( json_decode( $custom_post->post_content, true ) );
		$expected_removed_elements = array(
			'script' => 1,
		);
		AMP_Validation_Utils::reset_validation_results();

		// This should create a new post for the errors.
		$this->assertEquals( AMP_Validation_Utils::POST_TYPE_SLUG, $custom_post->post_type );
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Utils::REMOVED_ELEMENTS ] );
		$this->assertEquals( array(), $validation[ AMP_Validation_Utils::REMOVED_ATTRIBUTES ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Utils::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::AMP_URL_META, true );
		$this->assertEquals( $url, $meta );

		AMP_Validation_Utils::reset_validation_results();
		$url = home_url( '/?baz' );
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors( AMP_Validation_Utils::$validation_errors, $url );
		AMP_Validation_Utils::reset_validation_results();
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::AMP_URL_META, false );
		// A post exists for these errors, so the URL should be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertContains( $url, $meta );

		$url = home_url( '/?foo-bar' );
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors( AMP_Validation_Utils::$validation_errors, $url );
		AMP_Validation_Utils::reset_validation_results();
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::AMP_URL_META, false );

		// The URL should again be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertContains( $url, $meta );

		AMP_Validation_Utils::reset_validation_results();
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}--><nonexistent></nonexistent><!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors( AMP_Validation_Utils::$validation_errors, $url );
		AMP_Validation_Utils::reset_validation_results();
		$error_post                = get_post( $custom_post_id );
		$validation                = AMP_Validation_Utils::summarize_validation_errors( json_decode( $error_post->post_content, true ) );
		$expected_removed_elements = array(
			'nonexistent' => 1,
		);

		// A post already exists for this URL, so it should be updated.
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Utils::REMOVED_ELEMENTS ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Utils::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$this->assertContains( $url, get_post_meta( $custom_post_id, AMP_Validation_Utils::AMP_URL_META, false ) );

		AMP_Validation_Utils::reset_validation_results();
		AMP_Validation_Utils::process_markup( $this->valid_amp_img );

		// There are no errors, so the existing error post should be deleted.
		$custom_post_id = AMP_Validation_Utils::store_validation_errors( AMP_Validation_Utils::$validation_errors, $url );
		AMP_Validation_Utils::reset_validation_results();

		$this->assertNull( $custom_post_id );
		remove_theme_support( 'amp' );
	}

	/**
	 * Test for store_validation_errors() when existing post is trashed.
	 *
	 * @covers AMP_Validation_Utils::store_validation_errors()
	 */
	public function test_store_validation_errors_untrashing() {
		$validation_errors = $this->get_mock_errors();

		$first_post_id = AMP_Validation_Utils::store_validation_errors( $validation_errors, home_url( '/foo/' ) );
		$this->assertInternalType( 'int', $first_post_id );

		$post_name = get_post( $first_post_id )->post_name;
		wp_trash_post( $first_post_id );
		$this->assertEquals( $post_name . '__trashed', get_post( $first_post_id )->post_name );

		$next_post_id = AMP_Validation_Utils::store_validation_errors( $validation_errors, home_url( '/bar/' ) );
		$this->assertInternalType( 'int', $next_post_id );
		$this->assertEquals( $post_name, get_post( $next_post_id )->post_name );
		$this->assertEquals( $next_post_id, $first_post_id );

		$this->assertEqualSets(
			array(
				home_url( '/foo/' ),
				home_url( '/bar/' ),
			),
			get_post_meta( $next_post_id, AMP_Validation_Utils::AMP_URL_META, false )
		);
	}

	/**
	 * Test for get_validation_status_post().
	 *
	 * @covers AMP_Validation_Utils::get_validation_status_post()
	 */
	public function test_get_validation_status_post() {
		global $post;
		$post           = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$custom_post_id = $this->factory()->post->create( array(
			'post_type' => AMP_Validation_Utils::POST_TYPE_SLUG,
		) );

		$url = get_permalink( $custom_post_id );
		$this->assertEquals( null, AMP_Validation_Utils::get_validation_status_post( $url ) );

		update_post_meta( $custom_post_id, AMP_Validation_Utils::AMP_URL_META, $url );
		$this->assertEquals( $custom_post_id, AMP_Validation_Utils::get_validation_status_post( $url )->ID );
	}

	/**
	 * Test for validate_after_plugin_activation().
	 *
	 * @covers AMP_Validation_Utils::validate_after_plugin_activation()
	 */
	public function test_validate_after_plugin_activation() {
		add_filter( 'amp_pre_get_permalink', '__return_empty_string' );
		$r = AMP_Validation_Utils::validate_after_plugin_activation();
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'no_published_post_url_available', $r->get_error_code() );
		remove_filter( 'amp_pre_get_permalink', '__return_empty_string' );

		$validation_errors = array(
			array(
				'code' => 'example',
			),
		);

		$this->factory()->post->create();
		$filter = function() use ( $validation_errors ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION_ERRORS:' . wp_json_encode( $validation_errors )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Utils::validate_after_plugin_activation();
		remove_filter( 'pre_http_request', $filter );
		$this->assertEquals( $validation_errors, $r );
		$this->assertEquals( $validation_errors, get_transient( AMP_Validation_Utils::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY ) );
	}

	/**
	 * Test for validate_url().
	 *
	 * @covers AMP_Validation_Utils::validate_url()
	 */
	public function test_validate_url() {
		$validation_errors = array(
			array(
				'code' => 'example',
			),
		);

		// Test headers absent.
		$this->factory()->post->create();
		$filter = function() use ( $validation_errors ) {
			return array(
				'body'    => '',
				'headers' => array(),
			);
		};
		add_filter( 'pre_http_request', $filter );
		$r = AMP_Validation_Utils::validate_url( home_url( '/' ) );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'response_comment_absent', $r->get_error_code() );
		remove_filter( 'pre_http_request', $filter );

		// Test success.
		$that          = $this;
		$validated_url = home_url( '/foo/' );
		$filter        = function( $pre, $r, $url ) use ( $validation_errors, $validated_url, $that ) {
			unset( $pre, $r );
			$that->assertStringStartsWith(
				add_query_arg(
					AMP_Validation_Utils::VALIDATE_QUERY_VAR,
					1,
					$validated_url
				),
				$url
			);
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION_ERRORS:' . wp_json_encode( $validation_errors )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Utils::validate_url( $validated_url );
		$this->assertEquals( $validation_errors, $r );
		remove_filter( 'pre_http_request', $filter );
	}

	/**
	 * Test for plugin_notice()
	 *
	 * @covers AMP_Validation_Utils::plugin_notice()
	 */
	public function test_plugin_notice() {
		global $pagenow;
		ob_start();
		AMP_Validation_Utils::plugin_notice();
		$output = ob_get_clean();
		$this->assertEmpty( $output );
		$pagenow          = 'plugins.php'; // WPCS: global override ok.
		$_GET['activate'] = 'true';

		set_transient( AMP_Validation_Utils::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY, array(
			array(
				'code'    => 'example',
				'sources' => array(
					array(
						'type' => 'plugin',
						'name' => 'foo-bar',
					),
				),
			),
		) );
		ob_start();
		AMP_Validation_Utils::plugin_notice();
		$output = ob_get_clean();
		$this->assertContains( 'Warning: The following plugin may be incompatible with AMP', $output );
		$this->assertContains( $this->plugin_name, $output );
		$this->assertContains( 'More details', $output );
		$this->assertContains( admin_url( 'edit.php' ), $output );
	}

	/**
	 * Test for add_post_columns()
	 *
	 * @covers AMP_Validation_Utils::add_post_columns()
	 */
	public function test_add_post_columns() {
		$initial_columns = array(
			'cb' => '<input type="checkbox">',
		);
		$this->assertEquals(
			array_merge(
				$initial_columns,
				array(
					'url_count'                            => 'Count',
					AMP_Validation_Utils::REMOVED_ELEMENTS => 'Removed Elements',
					AMP_Validation_Utils::REMOVED_ATTRIBUTES => 'Removed Attributes',
					AMP_Validation_Utils::SOURCES_INVALID_OUTPUT => 'Incompatible Sources',
				)
			),
			AMP_Validation_Utils::add_post_columns( $initial_columns )
		);
	}

	/**
	 * Test for output_custom_column()
	 *
	 * @dataProvider get_custom_columns
	 * @covers AMP_Validation_Utils::output_custom_column()
	 * @param string $column_name The name of the column.
	 * @param string $expected_value The value that is expected to be present in the column markup.
	 */
	public function test_output_custom_column( $column_name, $expected_value ) {
		ob_start();
		AMP_Validation_Utils::output_custom_column( $column_name, $this->create_custom_post() );
		$this->assertContains( $expected_value, ob_get_clean() );
	}

	/**
	 * Gets the test data for test_output_custom_column().
	 *
	 * @return array $columns
	 */
	public function get_custom_columns() {
		return array(
			'url_count'             => array(
				'url_count',
				'1',
			),
			'invalid_element'       => array(
				AMP_Validation_Utils::REMOVED_ELEMENTS,
				$this->disallowed_tag_name,
			),
			'removed_attributes'    => array(
				AMP_Validation_Utils::REMOVED_ATTRIBUTES,
				$this->disallowed_attribute_name,
			),
			'sources_invalid_input' => array(
				AMP_Validation_Utils::SOURCES_INVALID_OUTPUT,
				$this->plugin_name,
			),
		);
	}

	/**
	 * Test for filter_row_actions()
	 *
	 * @covers AMP_Validation_Utils::filter_row_actions()
	 */
	public function test_filter_row_actions() {
		$this->set_capability();

		$initial_actions = array(
			'trash' => '<a href="https://example.com">Trash</a>',
		);
		$post            = $this->factory()->post->create_and_get();
		$this->assertEquals( $initial_actions, AMP_Validation_Utils::filter_row_actions( $initial_actions, $post ) );

		$custom_post_id = $this->create_custom_post();
		$actions        = AMP_Validation_Utils::filter_row_actions( $initial_actions, get_post( $custom_post_id ) );
		$url            = get_post_meta( $custom_post_id, AMP_Validation_Utils::AMP_URL_META, true );
		$this->assertContains( $url, $actions[ AMP_Validation_Utils::RECHECK_ACTION ] );
		$this->assertEquals( $initial_actions['trash'], $actions['trash'] );
	}

	/**
	 * Test for add_bulk_action()
	 *
	 * @covers AMP_Validation_Utils::add_bulk_action()
	 */
	public function test_add_bulk_action() {
		$initial_action = array(
			'edit' => 'Edit',
		);
		$actions        = AMP_Validation_Utils::add_bulk_action( $initial_action );
		$this->assertFalse( isset( $action['edit'] ) );
		$this->assertEquals( 'Recheck', $actions[ AMP_Validation_Utils::RECHECK_ACTION ] );
	}

	/**
	 * Test for handle_bulk_action()
	 *
	 * @covers AMP_Validation_Utils::handle_bulk_action()
	 */
	public function test_handle_bulk_action() {
		$initial_redirect                          = admin_url( 'plugins.php' );
		$items                                     = array( $this->create_custom_post() );
		$urls_tested                               = '1';
		$_GET[ AMP_Validation_Utils::URLS_TESTED ] = $urls_tested;

		// The action isn't correct, so the callback should return the URL unchanged.
		$this->assertEquals( $initial_redirect, AMP_Validation_Utils::handle_bulk_action( $initial_redirect, 'trash', $items ) );

		$that   = $this;
		$filter = function() use ( $that ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION_ERRORS:' . wp_json_encode( $that->get_mock_errors() )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$this->assertEquals(
			add_query_arg(
				array(
					AMP_Validation_Utils::URLS_TESTED      => $urls_tested,
					AMP_Validation_Utils::REMAINING_ERRORS => count( $items ),
				),
				$initial_redirect
			),
			AMP_Validation_Utils::handle_bulk_action( $initial_redirect, AMP_Validation_Utils::RECHECK_ACTION, $items )
		);
		remove_filter( 'pre_http_request', $filter, 10, 3 );
	}

	/**
	 * Test for remaining_error_notice()
	 *
	 * @covers AMP_Validation_Utils::remaining_error_notice()
	 */
	public function test_remaining_error_notice() {
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertEmpty( ob_get_clean() );

		$_GET['post_type'] = 'post';
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertEmpty( ob_get_clean() );

		set_current_screen( 'edit.php' );
		get_current_screen()->post_type = AMP_Validation_Utils::POST_TYPE_SLUG;

		$_GET[ AMP_Validation_Utils::REMAINING_ERRORS ] = '1';
		$_GET[ AMP_Validation_Utils::URLS_TESTED ]      = '1';
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertContains( 'The rechecked URL still has validation errors', ob_get_clean() );

		$_GET[ AMP_Validation_Utils::URLS_TESTED ] = '2';
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertContains( 'The rechecked URLs still have validation errors', ob_get_clean() );

		$_GET[ AMP_Validation_Utils::REMAINING_ERRORS ] = '0';
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertContains( 'The rechecked URLs have no validation error', ob_get_clean() );

		$_GET[ AMP_Validation_Utils::URLS_TESTED ] = '1';
		ob_start();
		AMP_Validation_Utils::remaining_error_notice();
		$this->assertContains( 'The rechecked URL has no validation error', ob_get_clean() );

		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test for handle_inline_recheck()
	 *
	 * @covers AMP_Validation_Utils::handle_inline_recheck()
	 */
	public function test_handle_inline_recheck() {
		$post_id              = $this->create_custom_post();
		$_REQUEST['_wpnonce'] = wp_create_nonce( AMP_Validation_Utils::NONCE_ACTION . $post_id );
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );

		try {
			AMP_Validation_Utils::handle_inline_recheck( $post_id );
		} catch ( WPDieException $e ) {
			$exception = $e;
		}

		// This calls wp_redirect(), which throws an exception.
		$this->assertTrue( isset( $exception ) );
	}

	/**
	 * Test for remove_publish_meta_box()
	 *
	 * @covers AMP_Validation_Utils::remove_publish_meta_box()
	 */
	public function test_remove_publish_meta_box() {
		global $wp_meta_boxes;
		AMP_Validation_Utils::remove_publish_meta_box();
		$contexts = $wp_meta_boxes[ AMP_Validation_Utils::POST_TYPE_SLUG ]['side'];
		foreach ( $contexts as $context ) {
			$this->assertFalse( $context['submitdiv'] );
		}
	}

	/**
	 * Test for add_meta_boxes()
	 *
	 * @covers AMP_Validation_Utils::add_meta_boxes()
	 */
	public function test_add_meta_boxes() {
		global $wp_meta_boxes;
		AMP_Validation_Utils::add_meta_boxes();
		$side_meta_box = $wp_meta_boxes[ AMP_Validation_Utils::POST_TYPE_SLUG ]['side']['default'][ AMP_Validation_Utils::STATUS_META_BOX ];
		$this->assertEquals( AMP_Validation_Utils::STATUS_META_BOX, $side_meta_box['id'] );
		$this->assertEquals( 'Status', $side_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_status_meta_box',
			),
			$side_meta_box['callback']
		);

		$full_meta_box = $wp_meta_boxes[ AMP_Validation_Utils::POST_TYPE_SLUG ]['normal']['default'][ AMP_Validation_Utils::VALIDATION_ERRORS_META_BOX ];
		$this->assertEquals( AMP_Validation_Utils::VALIDATION_ERRORS_META_BOX, $full_meta_box['id'] );
		$this->assertEquals( 'Validation Errors', $full_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_validation_errors_meta_box',
			),
			$full_meta_box['callback']
		);
	}

	/**
	 * Test for print_status_meta_box()
	 *
	 * @covers AMP_Validation_Utils::print_status_meta_box()
	 */
	public function test_print_status_meta_box() {
		$this->set_capability();
		$post_storing_error = get_post( $this->create_custom_post() );
		$url                = get_post_meta( $post_storing_error->ID, AMP_Validation_Utils::AMP_URL_META, true );
		$post_with_error    = AMP_Validation_Utils::get_validation_status_post( $url );
		ob_start();
		AMP_Validation_Utils::print_status_meta_box( $post_storing_error );
		$output = ob_get_clean();

		$this->assertContains( date_i18n( 'M j, Y @ H:i', strtotime( $post_with_error->post_date ) ), $output );
		$this->assertContains( 'Published on:', $output );
		$this->assertContains( 'Move to Trash', $output );
		$this->assertContains( esc_url( get_delete_post_link( $post_storing_error->ID ) ), $output );
		$this->assertContains( 'misc-pub-section', $output );
		$this->assertContains(
			AMP_Validation_Utils::get_recheck_link(
				$post_with_error,
				add_query_arg(
					'post',
					$post_with_error->ID,
					admin_url( 'post.php' )
				)
			),
			$output
		);
	}

	/**
	 * Test for print_status_meta_box()
	 *
	 * @covers AMP_Validation_Utils::print_status_meta_box()
	 */
	public function test_print_validation_errors_meta_box() {
		$this->set_capability();
		$post_storing_error     = get_post( $this->create_custom_post() );
		$first_url              = get_post_meta( $post_storing_error->ID, AMP_Validation_Utils::AMP_URL_META, true );
		$second_url_same_errors = get_permalink( $this->factory()->post->create() );
		AMP_Validation_Utils::store_validation_errors( $this->get_mock_errors(), $second_url_same_errors );
		ob_start();
		AMP_Validation_Utils::print_validation_errors_meta_box( $post_storing_error );
		$output = ob_get_clean();

		$this->assertContains( '<details', $output );
		$this->assertContains( $this->disallowed_tag_name, $output );
		$this->assertContains( $this->disallowed_attribute_name, $output );
		$this->assertContains( 'URLs', $output );
		$this->assertContains( $first_url, $output );
		$this->assertContains( $second_url_same_errors, $output );
		AMP_Validation_Utils::reset_validation_results();
	}

	/**
	 * Test for get_debug_url()
	 *
	 * @covers AMP_Validation_Utils::get_debug_url()
	 */
	public function test_get_debug_url() {
		$this->assertContains( AMP_Validation_Utils::VALIDATE_QUERY_VAR . '=1', AMP_Validation_Utils::get_debug_url( 'https://example.com/foo/' ) );
		$this->assertContains( AMP_Validation_Utils::DEBUG_QUERY_VAR . '=1', AMP_Validation_Utils::get_debug_url( 'https://example.com/foo/' ) );
		$this->assertStringEndsWith( '#development=1', AMP_Validation_Utils::get_debug_url( 'https://example.com/foo/' ) );
	}

	/**
	 * Test for get_recheck_link()
	 *
	 * @covers AMP_Validation_Utils::get_recheck_link()
	 */
	public function test_get_recheck_link() {
		$this->set_capability();
		$post_id = $this->create_custom_post();
		$url     = get_edit_post_link( $post_id, 'raw' );
		$link    = AMP_Validation_Utils::get_recheck_link( get_post( $post_id ), $url );
		$this->assertContains( AMP_Validation_Utils::RECHECK_ACTION, $link );
		$this->assertContains( wp_create_nonce( AMP_Validation_Utils::NONCE_ACTION . $post_id ), $link );
		$this->assertContains( 'Recheck the URL for AMP validity', $link );
	}

	/**
	 * Test enqueue_block_validation.
	 *
	 * @covers AMP_Validation_Utils::enqueue_block_validation()
	 */
	public function test_enqueue_block_validation() {
		if ( ! function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$this->markTestSkipped( 'Gutenberg not available.' );
		}

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$slug = 'amp-block-validation';
		$this->set_capability();
		AMP_Validation_Utils::enqueue_block_validation();

		$script        = wp_scripts()->registered[ $slug ];
		$inline_script = $script->extra['after'][1];
		$this->assertContains( 'js/amp-block-validation.js', $script->src );
		$this->assertEquals( array( 'underscore' ), $script->deps );
		$this->assertEquals( AMP__VERSION, $script->ver );
		$this->assertTrue( in_array( $slug, wp_scripts()->queue, true ) );
		$this->assertContains( 'ampBlockValidation.boot', $inline_script );
		$this->assertContains( AMP_Validation_Utils::VALIDITY_REST_FIELD_NAME, $inline_script );
		$this->assertContains( '"domain":"amp"', $inline_script );
	}

	/**
	 * Test add_rest_api_fields.
	 *
	 * @covers AMP_Validation_Utils::add_rest_api_fields()
	 */
	public function test_add_rest_api_fields() {
		// Test in a non Native-AMP (canonical) context.
		AMP_Validation_Utils::add_rest_api_fields();
		$post_types_non_canonical = array_intersect(
			get_post_types_by_support( 'amp' ),
			get_post_types( array(
				'show_in_rest' => true,
			) )
		);
		$this->assert_rest_api_field_present( $post_types_non_canonical );

		// Test in a Native AMP (canonical) context.
		add_theme_support( 'amp' );
		AMP_Validation_Utils::add_rest_api_fields();
		$post_types_canonical = get_post_types_by_support( 'editor' );
		$this->assert_rest_api_field_present( $post_types_canonical );
	}

	/**
	 * Asserts that the post types have the additional REST field.
	 *
	 * @covers AMP_Validation_Utils::add_rest_api_fields()
	 * @param array $post_types The post types that should have the REST field.
	 * @return void
	 */
	public function assert_rest_api_field_present( $post_types ) {
		foreach ( $post_types as $post_type ) {
			$field = $GLOBALS['wp_rest_additional_fields'][ $post_type ][ AMP_Validation_Utils::VALIDITY_REST_FIELD_NAME ];
			$this->assertEquals(
				$field['schema'],
				array(
					'description' => 'AMP validity status',
					'type'        => 'object',
				)
			);
			$this->assertEquals( $field['get_callback'], array( self::TESTED_CLASS, 'get_amp_validity_rest_field' ) );
		}
	}

	/**
	 * Test get_amp_validity_rest_field.
	 *
	 * @covers AMP_Validation_Utils::get_amp_validity_rest_field()
	 */
	public function test_rest_field_amp_validation() {
		AMP_Validation_Utils::register_post_type();
		$id = $this->factory()->post->create();
		$this->assertNull( AMP_Validation_Utils::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		) );

		// Create an error custom post for the ID, so this will return the errors in the field.
		$this->create_custom_post( array(), amp_get_permalink( $id ) );

		// Make sure capability check is honored.
		$this->assertNull( AMP_Validation_Utils::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		) );

		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		// GET request.
		$field = AMP_Validation_Utils::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		);
		$this->assertArrayHasKey( 'errors', $field );
		$this->assertArrayHasKey( 'link', $field );
		$this->assertEquals( $field['errors'], $this->get_mock_errors() );

		// @todo Test successful loopback request to test.
		// PUT request.
		$field = AMP_Validation_Utils::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'PUT' )
		);
		$this->assertArrayHasKey( 'errors', $field );
		$this->assertArrayHasKey( 'link', $field );
		$this->assertEquals( $field['errors'], $this->get_mock_errors() );
	}

	/**
	 * Creates and inserts a custom post.
	 *
	 * @param array  $errors Validation errors to populate.
	 * @param string $url    URL that the errors occur on. Defaults to the home page.
	 * @return int|WP_Error $error_post The ID of new custom post, or an error.
	 */
	public function create_custom_post( $errors = null, $url = null ) {
		if ( ! $errors ) {
			$errors = $this->get_mock_errors();
		}
		$content        = wp_json_encode( $errors );
		$encoded_errors = md5( $content );
		$post_args      = array(
			'post_type'    => AMP_Validation_Utils::POST_TYPE_SLUG,
			'post_name'    => $encoded_errors,
			'post_content' => $content,
			'post_status'  => 'publish',
		);
		$error_post     = wp_insert_post( wp_slash( $post_args ) );
		if ( ! $url ) {
			$url = home_url( '/' );
		}
		update_post_meta( $error_post, AMP_Validation_Utils::AMP_URL_META, $url );
		return $error_post;
	}

	/**
	 * Gets mock errors for tests.
	 *
	 * @return array $errors[][] {
	 *     The data of the validation errors.
	 *
	 *     @type string    $code        Error code.
	 *     @type string    $node_name   Name of removed node.
	 *     @type string    $parent_name Name of parent node.
	 *     @type array[][] $sources     Source data, including plugins and themes.
	 * }
	 */
	public function get_mock_errors() {
		return array(
			array(
				'code'            => AMP_Validation_Utils::INVALID_ELEMENT_CODE,
				'node_name'       => $this->disallowed_tag_name,
				'parent_name'     => 'div',
				'node_attributes' => array(),
				'sources'         => array(
					array(
						'type' => 'plugin',
						'name' => $this->plugin_name,
					),
				),
			),
			array(
				'code'               => AMP_Validation_Utils::INVALID_ATTRIBUTE_CODE,
				'node_name'          => $this->disallowed_attribute_name,
				'parent_name'        => 'div',
				'element_attributes' => array(
					$this->disallowed_attribute_name => '',
				),
				'sources'            => array(
					array(
						'type' => 'plugin',
						'name' => $this->plugin_name,
					),
				),
			),
		);
	}

	/**
	 * Test for persistent_object_caching_notice()
	 *
	 * @covers AMP_Validation_Utils::persistent_object_caching_notice()
	 */
	public function test_persistent_object_caching_notice() {
		set_current_screen( 'toplevel_page_amp-options' );
		$text = 'The AMP plugin performs at its best when persistent object cache is enabled.';

		wp_using_ext_object_cache( null );
		ob_start();
		AMP_Validation_Utils::persistent_object_caching_notice();
		$this->assertContains( $text, ob_get_clean() );

		wp_using_ext_object_cache( true );
		ob_start();
		AMP_Validation_Utils::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

		set_current_screen( 'edit.php' );

		wp_using_ext_object_cache( null );
		ob_start();
		AMP_Validation_Utils::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

		wp_using_ext_object_cache( true );
		ob_start();
		AMP_Validation_Utils::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

	}

}
