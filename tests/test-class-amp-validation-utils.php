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
	 * The expected REST API route.
	 *
	 * @var string
	 */
	public $expected_route = '/amp-wp/v1/validate';

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
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Validation_Utils::init()
	 */
	public function test_init() {
		AMP_Validation_Utils::init();
		$this->assertEquals( 10, has_action( 'rest_api_init', self::TESTED_CLASS . '::amp_rest_validation' ) );
		$this->assertEquals( 10, has_action( 'edit_form_top', self::TESTED_CLASS . '::validate_content' ) );
		$this->assertEquals( 10, has_action( 'init', self::TESTED_CLASS . '::register_post_type' ) );
		$this->assertEquals( 10, has_action( 'all_admin_notices', self::TESTED_CLASS . '::plugin_notice' ) );
		$this->assertEquals( 10, has_filter( 'manage_' . AMP_Validation_Utils::POST_TYPE_SLUG . '_posts_columns', self::TESTED_CLASS . '::add_post_columns' ) );
		$this->assertEquals( 10, has_action( 'manage_posts_custom_column', self::TESTED_CLASS . '::output_custom_column' ) );
		$this->assertEquals( 10, has_filter( 'post_row_actions', self::TESTED_CLASS . '::add_recheck' ) );
		$this->assertEquals( 10, has_filter( 'bulk_actions-edit-' . AMP_Validation_Utils::POST_TYPE_SLUG, self::TESTED_CLASS . '::add_bulk_action' ) );
		$this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-' . AMP_Validation_Utils::POST_TYPE_SLUG, self::TESTED_CLASS . '::handle_bulk_action' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', self::TESTED_CLASS . '::remaining_error_notice' ) );
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Validation_Utils::add_validation_hooks()
	 */
	public function test_add_validation_hooks() {
		AMP_Validation_Utils::add_validation_hooks();
		$this->assertEquals( 10, has_action( 'wp', array( self::TESTED_CLASS, 'callback_wrappers' ) ) );
		$this->assertEquals( 10, has_action( 'amp_content_sanitizers', array( self::TESTED_CLASS, 'add_validation_callback' ) ) );
		$this->assertEquals( -1, has_action( 'do_shortcode_tag', array( self::TESTED_CLASS, 'decorate_shortcode_source' ) ) );
	}

	/**
	 * Test track_removed.
	 *
	 * @covers AMP_Validation_Utils::track_removed()
	 */
	public function test_track_removed() {
		$this->assertEmpty( AMP_Validation_Utils::$removed_nodes );
		$removed = array(
			'node'    => $this->node,
			'sources' => array(),
		);
		AMP_Validation_Utils::track_removed( $removed );

		$this->assertSame( array( $removed ), AMP_Validation_Utils::$removed_nodes );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test was_node_removed.
	 *
	 * @covers AMP_Validation_Utils::was_node_removed()
	 */
	public function test_was_node_removed() {
		$this->assertFalse( AMP_Validation_Utils::was_node_removed() );
		AMP_Validation_Utils::track_removed(
			array(
				'node' => $this->node,
			)
		);
		$this->assertTrue( AMP_Validation_Utils::was_node_removed() );
	}

	/**
	 * Test process_markup.
	 *
	 * @covers AMP_Validation_Utils::process_markup()
	 */
	public function test_process_markup() {
		$this->set_capability();
		AMP_Validation_Utils::process_markup( $this->valid_amp_img );
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );

		AMP_Validation_Utils::reset_removed();
		$video = '<video src="https://example.com/video">';
		AMP_Validation_Utils::process_markup( $video );
		// This isn't valid AMP, but the sanitizer should convert it to an <amp-video>, without stripping anything.
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );

		AMP_Validation_Utils::reset_removed();

		AMP_Validation_Utils::process_markup( $this->disallowed_tag );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'script', AMP_Validation_Utils::$removed_nodes[0]['node']->nodeName );

		AMP_Validation_Utils::reset_removed();
		$disallowed_style = '<div style="display:none"></div>';
		AMP_Validation_Utils::process_markup( $disallowed_style );
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );

		AMP_Validation_Utils::reset_removed();
		$invalid_video = '<video width="200" height="100"></video>';
		AMP_Validation_Utils::process_markup( $invalid_video );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'video', AMP_Validation_Utils::$removed_nodes[0]['node']->nodeName );
		AMP_Validation_Utils::reset_removed();

		AMP_Validation_Utils::process_markup( '<button onclick="evil()">Do it</button>' );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'onclick', AMP_Validation_Utils::$removed_nodes[0]['node']->nodeName );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test amp_rest_validation.
	 *
	 * @covers AMP_Validation_Utils::amp_rest_validation()
	 */
	public function test_amp_rest_validation() {
		$routes  = rest_get_server()->get_routes();
		$route   = $routes[ $this->expected_route ][0];
		$methods = array(
			'POST' => true,
		);
		$args    = array(
			'markup' => array(
				'validate_callback' => array( self::TESTED_CLASS, 'validate_arg' ),
			),
		);

		$this->assertEquals( $args, $route['args'] );
		$this->assertEquals( array( self::TESTED_CLASS, 'validate_markup' ), $route['callback'] );
		$this->assertEquals( $methods, $route['methods'] );
		$this->assertEquals( array( self::TESTED_CLASS, 'has_cap' ), $route['permission_callback'] );
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
	 * Test validate_markup.
	 *
	 * @covers AMP_Validation_Utils::validate_markup()
	 */
	public function test_validate_markup() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->set_capability();
		$request = new WP_REST_Request( 'POST', $this->expected_route );
		$request->set_header( 'content-type', 'application/json' );
		$markup = $this->disallowed_tag;
		$request->set_body( wp_json_encode( array(
			AMP_Validation_Utils::MARKUP_KEY => $markup,
		) ) );
		$response          = AMP_Validation_Utils::validate_markup( $request );
		$expected_response = array(
			AMP_Validation_Utils::ERROR_KEY          => true,
			AMP_Validation_Utils::REMOVED_ELEMENTS   => array(
				'script' => 1,
			),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES => array(),
			'processed_markup'                       => '',
			'sources_with_invalid_output'            => array(),
		);
		$this->assertEquals( $expected_response, $response );

		$request->set_body( wp_json_encode( array(
			AMP_Validation_Utils::MARKUP_KEY => $this->valid_amp_img,
		) ) );
		$response          = AMP_Validation_Utils::validate_markup( $request );
		$expected_response = array(
			AMP_Validation_Utils::ERROR_KEY          => false,
			AMP_Validation_Utils::REMOVED_ELEMENTS   => array(),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES => array(),
			'processed_markup'                       => '<p>' . $this->valid_amp_img . '</p>',
			'sources_with_invalid_output'            => array(),
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test get_response.
	 *
	 * @covers AMP_Validation_Utils::get_validation_results()
	 */
	public function test_get_validation_results() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		AMP_Validation_Utils::process_markup( $this->disallowed_tag );
		$response = AMP_Validation_Utils::get_validation_results();
		AMP_Validation_Utils::reset_removed();
		$expected_response = array(
			AMP_Validation_Utils::ERROR_KEY          => true,
			AMP_Validation_Utils::REMOVED_ELEMENTS   => array(
				'script' => 1,
			),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES => array(),
			'sources_with_invalid_output'            => array(),
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test reset_removed
	 *
	 * @covers AMP_Validation_Utils::reset_removed()
	 */
	public function test_reset_removed() {
		AMP_Validation_Utils::$removed_nodes[] = $this->node;
		AMP_Validation_Utils::reset_removed();
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );
	}

	/**
	 * Test validate_arg
	 *
	 * @covers AMP_Validation_Utils::validate_arg()
	 */
	public function test_validate_arg() {
		$invalid_number = 54321;
		$invalid_array  = array( 'foo', 'bar' );
		$valid_string   = '<div class="baz"></div>';
		$this->assertFalse( AMP_Validation_Utils::validate_arg( $invalid_number ) );
		$this->assertFalse( AMP_Validation_Utils::validate_arg( $invalid_array ) );
		$this->assertTrue( AMP_Validation_Utils::validate_arg( $valid_string ) );
	}

	/**
	 * Test validate_content
	 *
	 * @covers AMP_Validation_Utils::validate_content()
	 */
	public function test_validate_content() {
		$this->set_capability();
		$post = $this->factory()->post->create_and_get();
		ob_start();
		AMP_Validation_Utils::validate_content( $post );
		$output = ob_get_clean();

		$this->assertNotContains( 'notice notice-warning', $output );
		$this->assertNotContains( 'Warning:', $output );

		$post->post_content = $this->disallowed_tag;
		ob_start();
		AMP_Validation_Utils::validate_content( $post );
		$output = ob_get_clean();

		$this->assertContains( 'notice notice-warning', $output );
		$this->assertContains( 'Warning:', $output );
		$this->assertContains( '<code>script</code>', $output );
		AMP_Validation_Utils::reset_removed();

		$youtube            = 'https://www.youtube.com/watch?v=GGS-tKTXw4Y';
		$post->post_content = $youtube;
		ob_start();
		AMP_Validation_Utils::validate_content( $post );
		$output = ob_get_clean();

		// The YouTube embed handler should convert the URL into a valid AMP element.
		$this->assertNotContains( 'notice notice-warning', $output );
		$this->assertNotContains( 'Warning:', $output );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test get_source_comment_start.
	 *
	 * @covers AMP_Validation_Utils::get_source_comment_start()
	 */
	public function test_get_source_comment_start() {
		$this->markTestIncomplete();
	}

	/**
	 * Test get_source_comment_end.
	 *
	 * @covers AMP_Validation_Utils::get_source_comment_end()
	 */
	public function test_get_source_comment_end() {
		$this->markTestIncomplete();
	}

	/**
	 * Test parse_source_comment.
	 *
	 * @covers AMP_Validation_Utils::parse_source_comment()
	 */
	public function test_parse_source_comment() {
		$this->markTestIncomplete();
	}

	/**
	 * Test locate_sources.
	 *
	 * @covers AMP_Validation_Utils::locate_sources()
	 */
	public function test_locate_sources() {
		$this->markTestIncomplete();
	}

	/**
	 * Test callback_wrappers
	 *
	 * @covers AMP_Validation_Utils::callback_wrappers()
	 */
	public function test_callback_wrappers() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->set_capability();
		$action_non_plugin        = 'foo_action';
		$action_no_output         = 'bar_action_no_output';
		$action_function_callback = 'baz_action_function';
		$action_no_argument       = 'test_action_no_argument';
		$action_one_argument      = 'baz_action_one_argument';
		$action_two_arguments     = 'example_action_two_arguments';
		$notice                   = 'Example notice';

		add_action( $action_function_callback, '_amp_print_php_version_admin_notice' );
		add_action( $action_no_argument, array( $this, 'output_div' ) );
		add_action( $action_one_argument, array( $this, 'output_notice' ) );
		add_action( $action_two_arguments, array( $this, 'output_message' ), 10, 2 );
		add_action( $action_no_output, array( $this, 'get_string' ), 10, 2 );
		add_action( $action_non_plugin, 'the_ID' );
		add_action( $action_no_output, '__return_false' );

		$this->assertEquals( 10, has_action( $action_no_argument, array( $this, 'output_div' ) ) );
		$this->assertEquals( 10, has_action( $action_one_argument, array( $this, 'output_notice' ) ) );
		$this->assertEquals( 10, has_action( $action_two_arguments, array( $this, 'output_message' ) ) );

		$_GET[ AMP_Validation_Utils::VALIDATION_QUERY_VAR ] = 1;
		AMP_Validation_Utils::callback_wrappers();
		$this->assertEquals( 10, has_action( $action_non_plugin, 'the_ID' ) );
		$this->assertNotEquals( 10, has_action( $action_no_output, array( $this, 'get_string' ) ) );
		$this->assertNotEquals( 10, has_action( $action_no_argument, array( $this, 'output_div' ) ) );
		$this->assertNotEquals( 10, has_action( $action_one_argument, array( $this, 'output_notice' ) ) );
		$this->assertNotEquals( 10, has_action( $action_two_arguments, array( $this, 'output_message' ) ) );

		ob_start();
		do_action( $action_function_callback );
		$output = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error">', $output );
		$this->assertContains( '<!--amp-source-stack:plugin:amp', $output );
		$this->assertContains( '<!--/amp-source-stack:plugin:amp', $output );

		ob_start();
		do_action( $action_no_argument );
		$output = ob_get_clean();
		$this->assertContains( '<div></div>', $output );
		$this->assertContains( '<!--amp-source-stack:plugin:amp', $output );
		$this->assertContains( '<!--/amp-source-stack:plugin:amp', $output );

		ob_start();
		do_action( $action_one_argument, $notice );
		$output = ob_get_clean();
		$this->assertContains( $notice, $output );
		$this->assertContains( sprintf( '<div class="notice notice-warning"><p>%s</p></div>', $notice ), $output );
		$this->assertContains( '<!--amp-source-stack:plugin:amp', $output );
		$this->assertContains( '<!--/amp-source-stack:plugin:amp', $output );

		ob_start();
		do_action( $action_two_arguments, $notice, get_the_ID() );
		$output = ob_get_clean();
		ob_start();
		self::output_message( $notice, get_the_ID() );
		$expected_output = ob_get_clean();
		$this->assertContains( $expected_output, $output );
		$this->assertContains( '<!--amp-source-stack:plugin:amp', $output );
		$this->assertContains( '<!--/amp-source-stack:plugin:amp', $output );

		// This action's callback isn't from a plugin or theme, so it shouldn't be wrapped in comments.
		ob_start();
		do_action( $action_non_plugin );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack:plugin:', $output );
		$this->assertNotContains( '<!--/amp-source-stack:plugin:', $output );

		// This action's callback doesn't echo any markup, so it shouldn't be wrapped in comments.
		ob_start();
		do_action( $action_no_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack:plugin:', $output );
		$this->assertNotContains( '<!--/amp-source-stack:plugin:', $output );
	}

	/**
	 * Test decorate_shortcode_source.
	 *
	 * @covers AMP_Validation_Utils::decorate_shortcode_source()
	 */
	public function test_decorate_shortcode_source() {
		$this->markTestIncomplete();
	}

	/**
	 * Test validate_content
	 *
	 * @covers AMP_Validation_Utils::validate_content()
	 */
	public function test_get_source() {
		$plugin = AMP_Validation_Utils::get_source( 'amp_after_setup_theme' );
		$this->assertContains( 'amp', $plugin['name'] );
		$this->assertEquals( 'plugin', $plugin['type'] );
		$the_content = AMP_Validation_Utils::get_source( 'the_content' );
		$this->assertEquals( null, $the_content );
		$core_function = AMP_Validation_Utils::get_source( 'the_content' );
		$this->assertEquals( null, $core_function );
	}

	/**
	 * Test wrapped_callback
	 *
	 * @covers AMP_Validation_Utils::wrapped_callback()
	 */
	public function test_wrapped_callback() {
		$callback = array(
			'function'      => function() {
				echo '<b>Cool!</b>';
			},
			'accepted_args' => 0,
			'type'          => 'plugin',
			'name'          => 'amp',
			'hook'          => 'bar',
		);

		$wrapped_callback = AMP_Validation_Utils::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		ob_start();
		call_user_func( $wrapped_callback );
		$output = ob_get_clean();

		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertContains( '<b>Cool!</b>', $output );
		$this->assertContains( '<!--amp-source-stack:plugin:amp {"hook":"bar"}', $output );
		$this->assertContains( '<!--/amp-source-stack:plugin:amp', $output );

		$callback = array(
			'function'      => array( $this, 'get_string' ),
			'accepted_args' => 0,
			'type'          => 'plugin',
			'name'          => 'amp',
			'hook'          => 'bar',
		);

		$wrapped_callback = AMP_Validation_Utils::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		ob_start();
		$result = call_user_func( $wrapped_callback );
		$output = ob_get_clean();
		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertEquals( '', $output );
		$this->assertEquals( call_user_func( array( $this, 'get_string' ) ), $result );
		unset( $post );
	}

	/**
	 * Test display_error().
	 *
	 * @covers AMP_Validation_Utils::display_error()
	 */
	public function test_display_error() {
		$response = array(
			AMP_Validation_Utils::ERROR_KEY => false,
		);
		ob_start();
		AMP_Validation_Utils::display_error( $response );
		$output = ob_get_clean();
		$this->assertFalse( strpos( $output, 'notice notice-error' ) );
		$this->assertFalse( strpos( $output, 'Notice: your post fails AMP validation' ) );

		$removed_element   = 'script';
		$removed_attribute = 'onload';
		$response          = array(
			AMP_Validation_Utils::ERROR_KEY          => true,
			AMP_Validation_Utils::REMOVED_ELEMENTS   => array(
				$removed_element => 1,
			),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES => array(
				$removed_attribute => 1,
			),
		);
		ob_start();
		AMP_Validation_Utils::display_error( $response );
		$output = ob_get_clean();
		$this->assertContains( 'notice notice-warning', $output );
		$this->assertContains( 'Warning:', $output );
		$this->assertContains( $removed_element, $output );
		$this->assertContains( $removed_attribute, $output );
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
	 * Test should_validate_front_end
	 *
	 * @covers AMP_Validation_Utils::should_validate_front_end()
	 */
	public function test_should_validate_front_end() {
		global $post;
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Validation_Utils::should_validate_front_end() );
		$_GET[ AMP_Validation_Utils::VALIDATION_QUERY_VAR ] = 1;
		$this->assertFalse( AMP_Validation_Utils::should_validate_front_end() );
		$this->set_capability();
		$this->assertTrue( AMP_Validation_Utils::should_validate_front_end() );
		remove_theme_support( 'amp' );
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

		$expected_callback   = self::TESTED_CLASS . '::track_removed';
		$filtered_sanitizers = AMP_Validation_Utils::add_validation_callback( $sanitizers );
		foreach ( $filtered_sanitizers as $sanitizer => $args ) {
			$this->assertEquals( $expected_callback, $args[ AMP_Validation_Utils::CALLBACK_KEY ] );
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
		global $post, $wp;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack:plugin:foo-->' . $this->disallowed_tag . '<!--/amp-source-stack:plugin:foo-->' );

		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'script', AMP_Validation_Utils::$removed_nodes[0]['node']->nodeName );
		$this->assertEquals(
			array(
				'type' => 'plugin',
				'name' => 'foo',
			),
			AMP_Validation_Utils::$removed_nodes[0]['sources'][0]
		);

		$post_id                   = AMP_Validation_Utils::store_validation_errors();
		$custom_post               = get_post( $post_id );
		$validation                = json_decode( $custom_post->post_content, true );
		$expected_removed_elements = array(
			'script' => 1,
		);
		$url                       = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		AMP_Validation_Utils::reset_removed();

		// This should create a new post for the errors.
		$this->assertEquals( AMP_Validation_Utils::POST_TYPE_SLUG, $custom_post->post_type );
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Utils::REMOVED_ELEMENTS ] );
		$this->assertEquals( true, $validation[ AMP_Validation_Utils::ERROR_KEY ] );
		$this->assertEquals( array(), $validation[ AMP_Validation_Utils::REMOVED_ATTRIBUTES ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Utils::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::AMP_URL_META, true );
		$this->assertEquals( $url, $meta );

		AMP_Validation_Utils::reset_removed();
		$wp->query_string = 'baz'; // WPCS: global override ok.
		$this->set_capability();
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack:plugin:foo-->' . $this->disallowed_tag . '<!--/amp-source-stack:plugin:foo-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors();
		AMP_Validation_Utils::reset_removed();
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::URLS_VALIDATION_ERROR, true );
		$url  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		// A post exists for these errors, so the URL should be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertEquals( $url, $meta );

		$wp->query_string = 'foo-bar'; // WPCS: global override ok.
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack:plugin:foo-->' . $this->disallowed_tag . '<!--/amp-source-stack:plugin:foo-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors();
		AMP_Validation_Utils::reset_removed();
		$meta = get_post_meta( $post_id, AMP_Validation_Utils::URLS_VALIDATION_ERROR, false );
		$url  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

		// The URL should again be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertContains( $url, $meta );

		AMP_Validation_Utils::reset_removed();
		AMP_Validation_Utils::process_markup( '<!--amp-source-stack:plugin:foo--><nonexistent></nonexistent><!--/amp-source-stack:plugin:foo-->' );
		$custom_post_id = AMP_Validation_Utils::store_validation_errors();
		AMP_Validation_Utils::reset_removed();
		$error_post = get_post( $custom_post_id );
		$this->assertTrue( true );
		$validation                = json_decode( $error_post->post_content, true );
		$url                       = get_post_meta( $custom_post_id, AMP_Validation_Utils::AMP_URL_META, true );
		$expected_url              = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		$expected_removed_elements = array(
			'nonexistent' => 1,
		);

		// A post already exists for this URL, so it should be updated.
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Utils::REMOVED_ELEMENTS ] );
		$this->assertTrue( $validation[ AMP_Validation_Utils::ERROR_KEY ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Utils::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$this->assertEquals( $expected_url, $url );

		AMP_Validation_Utils::reset_removed();
		AMP_Validation_Utils::process_markup( $this->valid_amp_img );

		// There are no errors, so the existing error post should be deleted.
		$custom_post_id = AMP_Validation_Utils::store_validation_errors();
		AMP_Validation_Utils::reset_removed();

		$this->assertNull( $custom_post_id );
		remove_theme_support( 'amp' );
	}

	/**
	 * Test for existing_post()
	 *
	 * @covers AMP_Validation_Utils::existing_post()
	 */
	public function test_existing_post() {
		global $post;
		$post           = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$custom_post_id = $this->factory()->post->create( array(
			'post_type' => AMP_Validation_Utils::POST_TYPE_SLUG,
		) );

		$url = get_permalink( $custom_post_id );
		$this->assertEquals( null, AMP_Validation_Utils::existing_post( $url ) );

		update_post_meta( $custom_post_id, AMP_Validation_Utils::AMP_URL_META, $url );
		$this->assertEquals( $custom_post_id, AMP_Validation_Utils::existing_post( $url ) );
	}

	/**
	 * Test for validate_home()
	 *
	 * @covers AMP_Validation_Utils::validate_home()
	 */
	public function test_validate_home() {
		$this->assertTrue( is_array( AMP_Validation_Utils::validate_home() ) );
	}

	/**
	 * Test for validate_url()
	 *
	 * @covers AMP_Validation_Utils::validate_url()
	 */
	public function test_validate_url() {
		$response = AMP_Validation_Utils::validate_url( get_permalink( $this->factory()->post->create() ) );
		$this->assertTrue( is_array( $response ) );
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

		$this->create_custom_post();
		ob_start();
		AMP_Validation_Utils::plugin_notice();
		$output = ob_get_clean();
		$this->assertContains( 'Warning: the following plugins are incompatible with AMP', $output );
		$this->assertContains( $this->plugin_name, $output );
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
					'url'                                  => 'URL',
					AMP_Validation_Utils::REMOVED_ELEMENTS => 'Removed Elements',
					AMP_Validation_Utils::REMOVED_ATTRIBUTES => 'Removed Attributes',
					AMP_Validation_Utils::SOURCES_INVALID_OUTPUT => 'Incompatible Source',
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
			'url'                   => array(
				'url',
				get_home_url(),
			),
			'removed_element'       => array(
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
	 * Test for add_recheck()
	 *
	 * @covers AMP_Validation_Utils::add_recheck()
	 */
	public function test_add_recheck() {
		$initial_actions = array(
			'trash' => '<a href="https://example.com">Trash</a>',
		);
		$post            = $this->factory()->post->create_and_get();
		$this->assertEquals( $initial_actions, AMP_Validation_Utils::add_recheck( $initial_actions, $post ) );

		$custom_post_id = $this->create_custom_post();
		$actions        = AMP_Validation_Utils::add_recheck( $initial_actions, get_post( $custom_post_id ) );
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
		$this->assertEquals(
			add_query_arg(
				array(
					AMP_Validation_Utils::REMAINING_ERRORS => count( $items ),
					AMP_Validation_Utils::URLS_TESTED      => $urls_tested,
				),
				$initial_redirect
			),
			AMP_Validation_Utils::handle_bulk_action( $initial_redirect, AMP_Validation_Utils::RECHECK_ACTION, $items )
		);
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

		$_GET['post_type']                              = AMP_Validation_Utils::POST_TYPE_SLUG;
		$_GET[ AMP_Validation_Utils::REMAINING_ERRORS ] = AMP_Validation_Utils::REMAINING_ERRORS_VALUE;
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
	}

	/**
	 * Test for handle_inline_recheck()
	 *
	 * @see AMP_Validation_Utils::handle_inline_recheck()
	 */
	public function test_handle_inline_recheck() {
		try {
			AMP_Validation_Utils::handle_inline_recheck( $this->create_custom_post() );
		} catch ( WPDieException $e ) {
			$exception = $e;
		}

		// This calls wp_redirect(), which throws an exception.
		$this->assertTrue( isset( $exception ) );
	}

	/**
	 * Creates and inserts a custom post.
	 *
	 * @return int|WP_Error $error_post The ID of new custom post, or an error.
	 */
	public function create_custom_post() {
		$response       = array(
			AMP_Validation_Utils::ERROR_KEY              => true,
			AMP_Validation_Utils::REMOVED_ELEMENTS       => array(
				$this->disallowed_tag_name => 1,
			),
			AMP_Validation_Utils::REMOVED_ATTRIBUTES     => array(
				$this->disallowed_attribute_name => 1,
			),
			AMP_Validation_Utils::SOURCES_INVALID_OUTPUT => array(
				'plugin' => array( $this->plugin_name ),
			),
		);
		$content        = wp_json_encode( $response );
		$encoded_errors = md5( $content );
		$post_args      = array(
			'post_type'    => AMP_Validation_Utils::POST_TYPE_SLUG,
			'post_name'    => $encoded_errors,
			'post_content' => $content,
			'post_status'  => 'publish',
		);
		$error_post     = wp_insert_post( $post_args );
		$url            = get_home_url();
		update_post_meta( $error_post, AMP_Validation_Utils::AMP_URL_META, $url );
		return $error_post;
	}

}
