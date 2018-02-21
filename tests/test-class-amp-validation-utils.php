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
	 * A valid image that sanitizers should not alter.
	 *
	 * @var string
	 */
	public $valid_amp_img = '<amp-img id="img-123" media="(min-width: 600x)" src="/assets/example.jpg" width=200 height=500 layout="responsive"></amp-img>';

	/**
	 * The key in the response for whether it has an AMP error.
	 *
	 * @var string
	 */
	public $error_key = 'has_error';

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
	 * @see AMP_Validation_Utils::init()
	 */
	public function test_init() {
		AMP_Validation_Utils::init();
		$this->assertEquals( 10, has_action( 'rest_api_init', 'AMP_Validation_Utils::amp_rest_validation' ) );
		$this->assertEquals( 10, has_action( 'edit_form_top', 'AMP_Validation_Utils::validate_content' ) );
		$this->assertEquals( 10, has_action( 'wp', 'AMP_Validation_Utils::callback_wrappers' ) );
		$this->assertEquals( 10, has_action( 'amp_content_sanitizers', 'AMP_Validation_Utils::add_validation_callback' ) );
	}

	/**
	 * Test track_removed.
	 *
	 * @see AMP_Validation_Utils::track_removed()
	 */
	public function test_track_removed() {
		$this->assertEmpty( AMP_Validation_Utils::$removed_nodes );
		$plugin           = 'amp';
		$expected_plugins = array(
			$plugin,
		);
		AMP_Validation_Utils::track_removed( $this->node, null );
		$this->assertEquals( array(), AMP_Validation_Utils::$plugins_removed_nodes );
		AMP_Validation_Utils::track_removed( $this->node, $plugin );
		$this->assertEquals( array( $this->node, $this->node ), AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( $expected_plugins, AMP_Validation_Utils::$plugins_removed_nodes );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test get_source.
	 *
	 * @dataProvider get_source_data
	 * @see AMP_Validation_Utils::get_source()
	 * @param string $url      The URL for which to get the source.
	 * @param array  $expected The expected return value of the tested function.
	 */
	public function test_get_source( $url, $expected ) {
		$this->assertEquals( $expected, AMP_Validation_Utils::get_source( $url ) );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Gets the test data for test_get_source().
	 *
	 * @return array $source_data The data for test_get_source().
	 */
	public function get_source_data() {
		return array(
			'theme'    => array(
				get_home_url() . '/wp-content/themes/wp-baz/style.css',
				array(
					'type'   => 'themes',
					'source' => 'wp-baz',
				),
			),
			'plugin'   => array(
				get_home_url() . '/wp-content/plugins/abc-plugin/assets/style.css',
				array(
					'type'   => 'plugins',
					'source' => 'abc-plugin',
				),
			),
			'core'     => array(
				get_home_url() . '/wp-includes/css/buttons.css',
				array(
					'type'   => null,
					'source' => null,
				),
			),
			'external' => array(
				'https://example.com/style.css',
				array(
					'type'   => null,
					'source' => null,
				),
			),
		);
	}

	/**
	 * Test was_node_removed.
	 *
	 * @see AMP_Validation_Utils::was_node_removed()
	 */
	public function test_was_node_removed() {
		$this->assertFalse( AMP_Validation_Utils::was_node_removed() );
		AMP_Validation_Utils::track_removed( $this->node );
		$this->assertTrue( AMP_Validation_Utils::was_node_removed() );
	}

	/**
	 * Test process_markup.
	 *
	 * @see AMP_Validation_Utils::process_markup()
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
		$this->assertEquals( 'script', AMP_Validation_Utils::$removed_nodes[0]->nodeName );

		AMP_Validation_Utils::reset_removed();
		$disallowed_style = '<div style="display:none"></div>';
		AMP_Validation_Utils::process_markup( $disallowed_style );
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );

		AMP_Validation_Utils::reset_removed();
		$invalid_video = '<video width="200" height="100"></video>';
		AMP_Validation_Utils::process_markup( $invalid_video );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'video', AMP_Validation_Utils::$removed_nodes[0]->nodeName );
		AMP_Validation_Utils::reset_removed();

		AMP_Validation_Utils::process_markup( '<button onclick="evil()">Do it</button>' );
		$this->assertCount( 1, AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( 'onclick', AMP_Validation_Utils::$removed_nodes[0]->nodeName );
		AMP_Validation_Utils::reset_removed();
	}

	/**
	 * Test amp_rest_validation.
	 *
	 * @see AMP_Validation_Utils::amp_rest_validation()
	 */
	public function test_amp_rest_validation() {
		$routes  = rest_get_server()->get_routes();
		$route   = $routes[ $this->expected_route ][0];
		$methods = array(
			'POST' => true,
		);
		$args    = array(
			'markup' => array(
				'validate_callback' => array( 'AMP_Validation_Utils', 'validate_arg' ),
			),
		);

		$this->assertEquals( $args, $route['args'] );
		$this->assertEquals( array( 'AMP_Validation_Utils', 'validate_markup' ), $route['callback'] );
		$this->assertEquals( $methods, $route['methods'] );
		$this->assertEquals( array( 'AMP_Validation_Utils', 'has_cap' ), $route['permission_callback'] );
	}

	/**
	 * Test has_cap.
	 *
	 * @see AMP_Validation_Utils::has_cap()
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
	 * @see AMP_Validation_Utils::validate_markup()
	 */
	public function test_validate_markup() {
		$this->set_capability();
		$request = new WP_REST_Request( 'POST', $this->expected_route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( array(
			AMP_Validation_Utils::MARKUP_KEY => $this->disallowed_tag,
		) ) );
		$response          = AMP_Validation_Utils::validate_markup( $request );
		$expected_response = array(
			$this->error_key     => true,
			'removed_elements'   => array(
				'script' => 1,
			),
			'removed_attributes' => array(),
			'processed_markup'   => $this->disallowed_tag,
		);
		$this->assertEquals( $expected_response, $response );

		$request->set_body( wp_json_encode( array(
			AMP_Validation_Utils::MARKUP_KEY => $this->valid_amp_img,
		) ) );
		$response          = AMP_Validation_Utils::validate_markup( $request );
		$expected_response = array(
			$this->error_key     => false,
			'removed_elements'   => array(),
			'removed_attributes' => array(),
			'processed_markup'   => $this->valid_amp_img,
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test get_response.
	 *
	 * @see AMP_Validation_Utils::get_response()
	 */
	public function test_get_response() {
		$this->set_capability();
		$response          = AMP_Validation_Utils::get_response( $this->disallowed_tag );
		$expected_response = array(
			$this->error_key     => true,
			'removed_elements'   => array(
				'script' => 1,
			),
			'removed_attributes' => array(),
			'processed_markup'   => $this->disallowed_tag,
		);
		$this->assertEquals( $expected_response, $response );
	}

	/**
	 * Test reset_removed
	 *
	 * @see AMP_Validation_Utils::reset_removed()
	 */
	public function test_reset_removed() {
		AMP_Validation_Utils::$removed_nodes[]         = $this->node;
		AMP_Validation_Utils::$plugins_removed_nodes[] = array( 'amp' );
		AMP_Validation_Utils::reset_removed();
		$this->assertEquals( array(), AMP_Validation_Utils::$removed_nodes );
		$this->assertEquals( array(), AMP_Validation_Utils::$plugins_removed_nodes );
	}

	/**
	 * Test validate_arg
	 *
	 * @see AMP_Validation_Utils::validate_arg()
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
	 * @see AMP_Validation_Utils::validate_content()
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
	 * Test callback_wrappers
	 *
	 * @see AMP_Validation_Utils::callback_wrappers()
	 */
	public function test_callback_wrappers() {
		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->set_capability();
		$action_non_plugin    = 'foo_action';
		$action_no_output     = 'bar_action_no_output';
		$action_no_argument   = 'test_action_no_argument';
		$action_one_argument  = 'baz_action_one_argument';
		$action_two_arguments = 'example_action_two_arguments';
		$notice               = 'Example notice';

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
		do_action( $action_no_argument );
		$output = ob_get_clean();
		$this->assertContains( '<div></div>', $output );
		$this->assertContains( '<!--before:amp', $output );
		$this->assertContains( '<!--after:amp', $output );

		ob_start();
		do_action( $action_one_argument, $notice );
		$output = ob_get_clean();
		$this->assertContains( $notice, $output );
		$this->assertContains( sprintf( '<div class="notice notice-warning"><p>%s</p></div>', $notice ), $output );
		$this->assertContains( '<!--before:amp', $output );
		$this->assertContains( '<!--after:amp', $output );

		ob_start();
		do_action( $action_two_arguments, $notice, get_the_ID() );
		$output = ob_get_clean();
		ob_start();
		self::output_message( $notice, get_the_ID() );
		$expected_output = ob_get_clean();
		$this->assertContains( $expected_output, $output );
		$this->assertContains( '<!--before:amp', $output );
		$this->assertContains( '<!--after:amp', $output );

		// This action's callback isn't from a plugin, so it shouldn't be wrapped in comments.
		ob_start();
		do_action( $action_non_plugin );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--before:', $output );
		$this->assertNotContains( '<!--after:', $output );

		// This action's callback doesn't echo any markup, so it shouldn't be wrapped in comments.
		ob_start();
		do_action( $action_no_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--before:', $output );
		$this->assertNotContains( '<!--after:', $output );
	}

	/**
	 * Test validate_content
	 *
	 * @see AMP_Validation_Utils::validate_content()
	 */
	public function test_get_plugin() {
		$plugin = AMP_Validation_Utils::get_plugin( 'amp_after_setup_theme' );
		$this->assertContains( 'amp', $plugin );
		$the_content = AMP_Validation_Utils::get_plugin( 'the_content' );
		$this->assertEquals( null, $the_content );
		$core_function = AMP_Validation_Utils::get_plugin( 'the_content' );
		$this->assertEquals( null, $core_function );
	}

	/**
	 * Test wrapped_callback
	 *
	 * @see AMP_Validation_Utils::wrapped_callback()
	 */
	public function test_wrapped_callback() {
		global $post;
		$post             = $this->factory()->post->create_and_get(); // WPCS: global override OK.
		$callback         = array(
			'function'      => 'the_ID',
			'accepted_args' => 0,
			'plugin'        => 'amp',
		);
		$wrapped_callback = AMP_Validation_Utils::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		ob_start();
		call_user_func( $wrapped_callback );
		$output = ob_get_clean();

		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertContains( strval( get_the_ID() ), $output );
		$this->assertContains( '<!--before:amp', $output );
		$this->assertContains( '<!--after:amp', $output );

		$callback         = array(
			'function'      => array( $this, 'get_string' ),
			'accepted_args' => 0,
			'plugin'        => 'amp',
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
	 * @see AMP_Validation_Utils::display_error()
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
			AMP_Validation_Utils::ERROR_KEY => true,
			'removed_elements'              => array(
				$removed_element => 1,
			),
			'removed_attributes'            => array(
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
	 * Test add_header
	 *
	 * The headers are output by the time this function executes,
	 * And the tested method calls header().
	 * This produces an error.
	 * So simply check that the error message includes 'header,'
	 * meaning that the method called header() as expected.
	 *
	 * @see AMP_Validation_Utils::add_header()
	 */
	public function test_do_validate_front_end() {
		global $post;
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Validation_Utils::do_validate_front_end() );
		$_GET[ AMP_Validation_Utils::VALIDATION_QUERY_VAR ] = 1;
		$this->assertFalse( AMP_Validation_Utils::do_validate_front_end() );
		$this->set_capability();
		$this->assertTrue( AMP_Validation_Utils::do_validate_front_end() );
		remove_theme_support( 'amp' );
	}

	/**
	 * Test add_header
	 *
	 * The headers are output by the time this function executes,
	 * And the tested method calls header().
	 * This produces an error.
	 * So simply check that the error message includes 'header,'
	 * meaning that the method called header() as expected.
	 *
	 * @see AMP_Validation_Utils::add_header()
	 */
	public function test_add_header() {
		global $post;
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		$this->set_capability();
		$_GET[ AMP_Validation_Utils::VALIDATION_QUERY_VAR ] = 1;
		AMP_Validation_Utils::process_markup( $this->disallowed_tag );
		try {
			AMP_Validation_Utils::add_header();
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertContains( 'header', $e->getMessage() );
		remove_theme_support( 'amp' );
	}

	/**
	 * Test add_validation_callback
	 *
	 * @see AMP_Validation_Utils::add_validation_callback()
	 */
	public function test_add_validation_callback() {
		global $post;
		$post              = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$sanitizers        = array(
			'AMP_Img_Sanitizer'      => array(),
			'AMP_Form_Sanitizer'     => array(),
			'AMP_Comments_Sanitizer' => array(),
		);
		$expected_callback = 'AMP_Validation_Utils::track_removed';
		$this->assertEquals( $sanitizers, AMP_Validation_Utils::add_validation_callback( $sanitizers ) );
		add_theme_support( 'amp' );
		$this->set_capability();
		$_GET[ AMP_Validation_Utils::VALIDATION_QUERY_VAR ] = 1;
		$filtered_sanitizers                                = AMP_Validation_Utils::add_validation_callback( $sanitizers );
		foreach ( $filtered_sanitizers as $sanitizer => $args ) {
			$this->assertEquals( $expected_callback, $args[ AMP_Validation_Utils::CALLBACK_KEY ] );
		}
		remove_theme_support( 'amp' );
	}

}
