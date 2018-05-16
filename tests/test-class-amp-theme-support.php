<?php
/**
 * Tests for Theme Support.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for Theme Support.
 *
 * @covers AMP_Theme_Support
 */
class Test_AMP_Theme_Support extends WP_UnitTestCase {

	/**
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Theme_Support';

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 *
	 * @global WP_Scripts $wp_scripts
	 */
	public function tearDown() {
		global $wp_scripts;
		$wp_scripts = null;
		parent::tearDown();
		remove_theme_support( 'amp' );
		$_REQUEST                = array(); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$_SERVER['QUERY_STRING'] = '';
		unset( $_SERVER['REQUEST_URI'] );
		unset( $_SERVER['REQUEST_METHOD'] );
		unset( $GLOBALS['content_width'] );
		if ( isset( $GLOBALS['wp_customize'] ) ) {
			$GLOBALS['wp_customize']->stop_previewing_theme();
		}
		AMP_Response_Headers::$headers_sent = array();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Theme_Support::init()
	 * @covers AMP_Theme_Support::finish_init()
	 */
	public function test_init() {
		$_REQUEST['__amp_source_origin'] = 'foo';
		$_GET['__amp_source_origin']     = 'foo';
		AMP_Theme_Support::init();
		$this->assertNotEquals( 10, has_action( 'widgets_init', array( self::TESTED_CLASS, 'register_widgets' ) ) );

		// Ensure that purge_amp_query_vars() didn't execute.
		$this->assertTrue( isset( $_REQUEST['__amp_source_origin'] ) ); // WPCS: CSRF ok.

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		$this->assertEquals( 10, has_action( 'widgets_init', array( self::TESTED_CLASS, 'register_widgets' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'wp', array( self::TESTED_CLASS, 'finish_init' ) ) );
		$this->assertFalse( isset( $_REQUEST['__amp_source_origin'] ) ); // WPCS: CSRF ok.

		add_theme_support( 'amp', 'invalid_argumnet_type' );
		$e = null;
		try {
			AMP_Theme_Support::init();
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertInstanceOf( 'PHPUnit_Framework_Error_Notice', $e );
		$this->assertEquals( 'Expected AMP theme support arg to be array.', $e->getMessage() );

		add_theme_support( 'amp', array(
			'invalid_param_key' => array(),
		) );
		try {
			AMP_Theme_Support::init();
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertEquals( 'Expected AMP theme support to only have template_dir and/or available_callback.', $e->getMessage() );
	}

	/**
	 * Test that amphtml link is added at the right time.
	 *
	 * @covers AMP_Theme_Support::finish_init()
	 */
	public function test_amphtml_link() {
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		add_theme_support( 'amp', array(
			'template_dir'       => '...',
			'available_callback' => function() {
				return is_singular();
			},
		) );

		// Test paired mode singular.
		remove_action( 'wp_head', 'amp_frontend_add_canonical' );
		$this->go_to( get_permalink( $post_id ) );
		AMP_Theme_Support::finish_init();
		$this->assertEquals( 10, has_action( 'wp_head', 'amp_frontend_add_canonical' ) );

		// Test paired mode homepage.
		remove_action( 'wp_head', 'amp_frontend_add_canonical' );
		$this->go_to( home_url() );
		AMP_Theme_Support::finish_init();
		$this->assertFalse( has_action( 'wp_head', 'amp_frontend_add_canonical' ) );

		// Test canonical.
		remove_theme_support( 'amp' );
		add_theme_support( 'amp' );
		$this->go_to( get_permalink( $post_id ) );
		AMP_Theme_Support::finish_init();
		$this->assertFalse( has_action( 'wp_head', 'amp_frontend_add_canonical' ) );
	}

	/**
	 * Test redirect_canonical_amp.
	 *
	 * @covers AMP_Theme_Support::redirect_canonical_amp()
	 */
	public function test_redirect_canonical_amp() {
		set_query_var( amp_get_slug(), 1 );
		try {
			AMP_Theme_Support::redirect_canonical_amp();
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		// wp_safe_redirect() modifies the headers, and causes an error.
		$this->assertTrue( isset( $e ) );
	}

	/**
	 * Test is_paired_available.
	 *
	 * @covers AMP_Theme_Support::is_paired_available()
	 */
	public function test_is_paired_available() {

		// Establish initial state.
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		remove_theme_support( 'amp' );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );

		// Paired support is not available if theme support is not present or canonical.
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );

		// Paired mode is available once template_dir is supplied.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		// Paired mode not available when post does not support AMP.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		$this->assertTrue( is_singular() );
		query_posts( array( 's' => 'test' ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );
		remove_filter( 'amp_skip_post', '__return_true' );

		// Check that available_callback works.
		add_theme_support( 'amp', array(
			'template_dir'       => 'amp-templates',
			'available_callback' => 'is_singular',
		) );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		query_posts( array( 's' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
	}

	/**
	 * Test is_customize_preview_iframe.
	 *
	 * @covers AMP_Theme_Support::is_customize_preview_iframe()
	 */
	public function test_is_customize_preview_iframe() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$GLOBALS['wp_customize'] = new WP_Customize_Manager();
		$this->assertFalse( AMP_Theme_Support::is_customize_preview_iframe() );
		$GLOBALS['wp_customize'] = new WP_Customize_Manager( array(
			'messenger_channel' => 'baz',
		) );
		$this->assertFalse( AMP_Theme_Support::is_customize_preview_iframe() );
		$GLOBALS['wp_customize']->start_previewing_theme();
		$this->assertTrue( AMP_Theme_Support::is_customize_preview_iframe() );
	}

	/**
	 * Test register_paired_hooks.
	 *
	 * @covers AMP_Theme_Support::register_paired_hooks()
	 */
	public function test_register_paired_hooks() {
		$template_types = array(
			'paged',
			'index',
			'404',
			'archive',
			'author',
			'category',
		);
		AMP_Theme_Support::register_paired_hooks();
		foreach ( $template_types as $template_type ) {
			$this->assertEquals( 10, has_filter( "{$template_type}_template_hierarchy", array( self::TESTED_CLASS, 'filter_paired_template_hierarchy' ) ) );
		}
		$this->assertEquals( 100, has_filter( 'template_include', array( self::TESTED_CLASS, 'filter_paired_template_include' ) ) );
	}

	/**
	 * Test validate_non_amp_theme.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @global WP_Scripts $wp_scripts
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_validate_non_amp_theme() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en-US" class="no-js">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width">
				<?php wp_head(); ?>
			</head>
			<body>
				<?php wp_print_scripts( 'amp-mathml' ); ?>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		// Invalid viewport meta tag is not present.
		$this->assertNotContains( '<meta name="viewport" content="width=device-width">', $sanitized_html );

		// Correct viewport meta tag was added.
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );

		// MathML script was added.
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-mathml-latest.js" async custom-element="amp-mathml"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Test add_hooks.
	 *
	 * @covers AMP_Theme_Support::add_hooks()
	 */
	public function test_add_hooks() {
		AMP_Theme_Support::add_hooks();
		$this->assertFalse( has_action( 'wp_head', 'wp_post_preview_js' ) );
		$this->assertFalse( has_action( 'wp_head', 'print_emoji_detection_script' ) );
		$this->assertFalse( has_action( 'wp_print_styles', 'print_emoji_styles' ) );
		$this->assertFalse( has_action( 'wp_head', 'wp_oembed_add_host_js' ) );

		$this->assertEquals( 0, has_action( 'wp_print_styles', array( self::TESTED_CLASS, 'print_amp_styles' ) ) );
		$this->assertEquals( 20, has_action( 'wp_head', 'amp_add_generator_metadata' ) );
		$this->assertEquals( 20, has_action( 'wp_head', 'amp_add_generator_metadata' ) );
		$this->assertEquals( 10, has_action( 'wp_enqueue_scripts', array( self::TESTED_CLASS, 'enqueue_assets' ) ) );

		$this->assertEquals( 1000, has_action( 'wp_enqueue_scripts', array( self::TESTED_CLASS, 'dequeue_customize_preview_scripts' ) ) );
		$this->assertEquals( 10, has_filter( 'customize_partial_render', array( self::TESTED_CLASS, 'filter_customize_partial_render' ) ) );
		$this->assertEquals( 10, has_action( 'wp_footer', 'amp_print_analytics' ) );
		$this->assertEquals( 100, has_filter( 'show_admin_bar', '__return_false' ) );
		$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.PHP.NewConstants.php_int_minFound
		$this->assertEquals( $priority, has_action( 'template_redirect', array( self::TESTED_CLASS, 'start_output_buffering' ) ) );

		$this->assertEquals( PHP_INT_MAX, has_filter( 'wp_list_comments_args', array( self::TESTED_CLASS, 'set_comments_walker' ) ) );
		$this->assertEquals( 10, has_filter( 'comment_form_defaults', array( self::TESTED_CLASS, 'filter_comment_form_defaults' ) ) );
		$this->assertEquals( 10, has_filter( 'comment_reply_link', array( self::TESTED_CLASS, 'filter_comment_reply_link' ) ) );
		$this->assertEquals( 10, has_filter( 'cancel_comment_reply_link', array( self::TESTED_CLASS, 'filter_cancel_comment_reply_link' ) ) );
		$this->assertEquals( 100, has_action( 'comment_form', array( self::TESTED_CLASS, 'amend_comment_form' ) ) );
		$this->assertFalse( has_action( 'comment_form', 'wp_comment_form_unfiltered_html_nonce' ) );
	}

	/**
	 * Test purge_amp_query_vars.
	 *
	 * @covers AMP_Theme_Support::purge_amp_query_vars()
	 */
	public function test_purge_amp_query_vars() {
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		$bad_query_vars = array(
			'amp_latest_update_time' => '1517199956',
			'amp_last_check_time'    => '1517599126',
			'__amp_source_origin'    => home_url(),
		);
		$ok_query_vars  = array(
			'bar' => 'baz',
		);
		$all_query_vars = array_merge( $bad_query_vars, $ok_query_vars );

		$_SERVER['QUERY_STRING'] = build_query( $all_query_vars );

		remove_action( 'wp', 'amp_maybe_add_actions' );
		$this->go_to( add_query_arg( $all_query_vars, home_url( '/foo/' ) ) );
		$_REQUEST = $_GET;
		foreach ( $all_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET );
			$this->assertArrayHasKey( $key, $_REQUEST );
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}

		AMP_Theme_Support::$purged_amp_query_vars = array();
		AMP_Theme_Support::purge_amp_query_vars();
		$this->assertEqualSets( AMP_Theme_Support::$purged_amp_query_vars, $bad_query_vars );

		foreach ( $bad_query_vars as $key => $value ) {
			$this->assertArrayNotHasKey( $key, $_GET );
			$this->assertArrayNotHasKey( $key, $_REQUEST );
			$this->assertNotContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertNotContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		foreach ( $ok_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET );
			$this->assertArrayHasKey( $key, $_REQUEST );
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification
	}

	/**
	 * Test handle_xhr_request().
	 *
	 * @covers AMP_Theme_Support::handle_xhr_request()
	 */
	public function test_handle_xhr_request() {
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertEmpty( AMP_Response_Headers::$headers_sent );

		$_GET['_wp_amp_action_xhr_converted'] = '1';

		// Try bad source origin.
		$_GET['__amp_source_origin'] = 'http://evil.example.com/';
		$_SERVER['REQUEST_METHOD']   = 'POST';
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertEmpty( AMP_Response_Headers::$headers_sent );

		// Try home source origin.
		$_GET['__amp_source_origin'] = home_url();
		$_SERVER['REQUEST_METHOD']   = 'POST';
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertCount( 1, AMP_Response_Headers::$headers_sent );
		$this->assertEquals(
			array(
				'name'        => 'AMP-Access-Control-Allow-Source-Origin',
				'value'       => home_url(),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent[0]
		);
		$this->assertEquals( PHP_INT_MAX, has_filter( 'wp_redirect', array( 'AMP_Theme_Support', 'intercept_post_request_redirect' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'comment_post_redirect', array( 'AMP_Theme_Support', 'filter_comment_post_redirect' ) ) );
		$this->assertEquals(
			array( 'AMP_Theme_Support', 'handle_wp_die' ),
			apply_filters( 'wp_die_handler', '__return_true' )
		);
	}

	/**
	 * Test filter_comment_post_redirect().
	 *
	 * @covers AMP_Theme_Support::filter_comment_post_redirect()
	 */
	public function test_filter_comment_post_redirect() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function() {
			return '__return_null';
		} );

		add_theme_support( 'amp' );
		$post    = $this->factory()->post->create_and_get();
		$comment = $this->factory()->comment->create_and_get( array(
			'comment_post_ID' => $post->ID,
		) );
		$url     = get_comment_link( $comment );

		// Test without comments_live_list.
		$filtered_url = AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$this->assertNotEquals(
			strtok( $url, '#' ),
			strtok( $filtered_url, '#' )
		);

		// Test with comments_live_list.
		add_theme_support( 'amp', array(
			'comments_live_list' => true,
		) );
		add_filter( 'amp_comment_posted_message', function( $message, WP_Comment $filter_comment ) {
			return sprintf( '(comment=%d,approved=%d)', $filter_comment->comment_ID, $filter_comment->comment_approved );
		}, 10, 2 );

		// Test approved comment.
		$comment->comment_approved = '1';
		ob_start();
		AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=1)', $comment->comment_ID ),
			$response['message']
		);

		// Test moderated comment.
		$comment->comment_approved = '0';
		ob_start();
		AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=0)', $comment->comment_ID ),
			$response['message']
		);
	}

	/**
	 * Test handle_wp_die().
	 *
	 * @covers AMP_Theme_Support::handle_wp_die()
	 */
	public function test_handle_wp_die() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function() {
			return '__return_null';
		} );

		ob_start();
		AMP_Theme_Support::handle_wp_die( 'string' );
		$this->assertEquals( '{"error":"string"}', ob_get_clean() );

		ob_start();
		$error = new WP_Error( 'code', 'The Message' );
		AMP_Theme_Support::handle_wp_die( $error );
		$this->assertEquals( '{"error":"The Message"}', ob_get_clean() );
	}

	/**
	 * Test intercept_post_request_redirect().
	 *
	 * @covers AMP_Theme_Support::intercept_post_request_redirect()
	 */
	public function test_intercept_post_request_redirect() {

		add_theme_support( 'amp' );
		$url = home_url( '', 'https' ) . ':443/?test=true#test';

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function () {
			return '__return_false';
		} );

		// Test redirecting to full URL with HTTPS protocol.
		AMP_Response_Headers::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( $url );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => $url,
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);
		$this->assertContains(
			array(
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);

		// Test redirecting to non-HTTPS URL.
		AMP_Response_Headers::$headers_sent = array();
		ob_start();
		$url = home_url( '/', 'http' );
		AMP_Theme_Support::intercept_post_request_redirect( $url );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => preg_replace( '#^\w+:#', '', $url ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);
		$this->assertContains(
			array(
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);

		// Test redirecting to host-less location.
		AMP_Response_Headers::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '/new-location/' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);

		// Test redirecting to scheme-less location.
		AMP_Response_Headers::$headers_sent = array();
		ob_start();
		$url = home_url( '/new-location/' );
		AMP_Theme_Support::intercept_post_request_redirect( substr( $url, strpos( $url, ':' ) + 1 ) );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);

		// Test redirecting to empty location.
		AMP_Response_Headers::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url(), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);
	}

	/**
	 * Test register_widgets().
	 *
	 * @covers AMP_Theme_Support::register_widgets()
	 * @global WP_Widget_Factory $wp_widget_factory
	 */
	public function test_register_widgets() {
		global $wp_widget_factory;
		remove_all_actions( 'widgets_init' );
		$wp_widget_factory->widgets = array();
		wp_widgets_init();
		AMP_Theme_Support::register_widgets();

		$this->assertArrayNotHasKey( 'WP_Widget_Categories', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'AMP_Widget_Categories', $wp_widget_factory->widgets );
	}

	/**
	 * Test register_content_embed_handlers.
	 *
	 * @covers AMP_Theme_Support::register_content_embed_handlers()
	 * @global int $content_width
	 */
	public function test_register_content_embed_handlers() {
		global $content_width;
		$content_width  = 1234;
		$embed_handlers = AMP_Theme_Support::register_content_embed_handlers();
		foreach ( $embed_handlers as $embed_handler ) {
			$this->assertTrue( is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) );
			$reflection = new ReflectionObject( $embed_handler );
			$args       = $reflection->getProperty( 'args' );
			$args->setAccessible( true );
			$property = $args->getValue( $embed_handler );
			$this->assertEquals( $content_width, $property['content_max_width'] );
		}
	}

	/**
	 * Test set_comments_walker.
	 *
	 * @covers AMP_Theme_Support::set_comments_walker()
	 */
	public function test_set_comments_walker() {
		$args = AMP_Theme_Support::set_comments_walker( array(
			'walker' => null,
		) );
		$this->assertInstanceOf( 'AMP_Comment_Walker', $args['walker'] );
	}

	/**
	 * Test amend_comment_form().
	 *
	 * @covers AMP_Theme_Support::amend_comment_form()
	 */
	public function test_amend_comment_form() {
		$post_id = $this->factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_singular() );

		// Test native AMP.
		add_theme_support( 'amp' );
		$this->assertTrue( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertNotContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );

		// Test paired AMP.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertFalse( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );
	}

	/**
	 * Test filter_paired_template_hierarchy.
	 *
	 * @covers AMP_Theme_Support::filter_paired_template_hierarchy()
	 */
	public function test_filter_paired_template_hierarchy() {
		$template_dir = 'amp-templates';
		add_theme_support( 'amp', array(
			'template_dir' => $template_dir,
		) );
		$templates          = array(
			'single-post-example.php',
			'single-post.php',
			'single.php',
		);
		$filtered_templates = AMP_Theme_Support::filter_paired_template_hierarchy( $templates );
		foreach ( $filtered_templates as $key => $filtered_template ) {
			$this->assertEquals( $template_dir . '/' . $templates[ $key ], $filtered_template );
		}
	}

	/**
	 * Test filter_paired_template_include.
	 *
	 * @covers AMP_Theme_Support::filter_paired_template_include()
	 */
	public function test_filter_paired_template_include() {
		$template_dir = 'amp-templates';
		$template     = 'single.php';
		add_theme_support( 'amp', array(
			'template_dir' => $template_dir,
		) );
		$this->assertEquals( $template, AMP_Theme_Support::filter_paired_template_include( $template ) );
		remove_theme_support( 'amp' );
		try {
			AMP_Theme_Support::filter_paired_template_include( $template );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertTrue( isset( $e ) );
	}

	/**
	 * Test get_current_canonical_url.
	 *
	 * @covers AMP_Theme_Support::get_current_canonical_url()
	 */
	public function test_get_current_canonical_url() {
		global $post, $wp;
		$home_url = home_url( '/' );
		$this->assertEquals( $home_url, AMP_Theme_Support::get_current_canonical_url() );

		$added_query_vars = array(
			'foo' => 'bar',
		);
		$wp->query_vars   = $added_query_vars;
		$this->assertEquals( add_query_arg( $added_query_vars, $home_url ), AMP_Theme_Support::get_current_canonical_url() );

		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->go_to( get_permalink( $post ) );
		$this->assertEquals( wp_get_canonical_url(), AMP_Theme_Support::get_current_canonical_url() );

	}

	/**
	 * Test get_comment_form_state_id.
	 *
	 * @covers AMP_Theme_Support::get_comment_form_state_id()
	 */
	public function test_get_comment_form_state_id() {
		$post_id = 54;
		$this->assertEquals( 'commentform_post_' . $post_id, AMP_Theme_Support::get_comment_form_state_id( $post_id ) );
		$post_id = 91542;
		$this->assertEquals( 'commentform_post_' . $post_id, AMP_Theme_Support::get_comment_form_state_id( $post_id ) );
	}

	/**
	 * Test filter_comment_form_defaults.
	 *
	 * @covers AMP_Theme_Support::filter_comment_form_defaults()
	 */
	public function test_filter_comment_form_defaults() {
		global $post;
		$post     = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$defaults = AMP_Theme_Support::filter_comment_form_defaults( array(
			'title_reply_to'      => 'Reply To',
			'title_reply'         => 'Reply',
			'cancel_reply_before' => '',
			'title_reply_before'  => '',
		) );
		$this->assertContains( AMP_Theme_Support::get_comment_form_state_id( get_the_ID() ), $defaults['title_reply_before'] );
		$this->assertContains( 'replyToName ?', $defaults['title_reply_before'] );
		$this->assertContains( '</span>', $defaults['cancel_reply_before'] );
	}

	/**
	 * Test filter_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_comment_reply_link()
	 */
	public function test_filter_comment_reply_link() {
		global $post;
		$post          = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$comment       = $this->factory()->comment->create_and_get();
		$link          = sprintf( '<a href="%s">', get_comment_link( $comment ) );
		$respond_id    = '5234';
		$reply_text    = 'Reply';
		$reply_to_text = 'Reply to';
		$before        = '<div class="reply">';
		$after         = '</div>';
		$args          = compact( 'respond_id', 'reply_text', 'reply_to_text', 'before', 'after' );
		$comment       = $this->factory()->comment->create_and_get();

		update_option( 'comment_registration', true );
		$filtered_link = AMP_Theme_Support::filter_comment_reply_link( $link, $args, $comment );
		$this->assertEquals( $before . $link . $after, $filtered_link );
		update_option( 'comment_registration', false );

		$filtered_link = AMP_Theme_Support::filter_comment_reply_link( $link, $args, $comment );
		$this->assertStringStartsWith( $before, $filtered_link );
		$this->assertStringEndsWith( $after, $filtered_link );
		$this->assertContains( AMP_Theme_Support::get_comment_form_state_id( get_the_ID() ), $filtered_link );
		$this->assertContains( $comment->comment_author, $filtered_link );
		$this->assertContains( $comment->comment_ID, $filtered_link );
		$this->assertContains( 'tap:AMP.setState', $filtered_link );
		$this->assertContains( $reply_text, $filtered_link );
		$this->assertContains( $reply_to_text, $filtered_link );
		$this->assertContains( $respond_id, $filtered_link );
	}

	/**
	 * Test filter_cancel_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_cancel_comment_reply_link()
	 */
	public function test_filter_cancel_comment_reply_link() {
		global $post;
		$post                   = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$url                    = get_permalink( $post );
		$_SERVER['REQUEST_URI'] = $url;
		$this->factory()->comment->create_and_get();
		$formatted_link = get_cancel_comment_reply_link();
		$link           = remove_query_arg( 'replytocom' );
		$text           = 'Cancel your reply';
		$filtered_link  = AMP_Theme_Support::filter_cancel_comment_reply_link( $formatted_link, $link, $text );
		$this->assertContains( $url, $filtered_link );
		$this->assertContains( $text, $filtered_link );
		$this->assertContains( '<a id="cancel-comment-reply-link"', $filtered_link );
		$this->assertContains( '.values.comment_parent ==', $filtered_link );
		$this->assertContains( 'tap:AMP.setState(', $filtered_link );

		$filtered_link_no_text_passed = AMP_Theme_Support::filter_cancel_comment_reply_link( $formatted_link, $link, '' );
		$this->assertContains( 'Click here to cancel reply.', $filtered_link_no_text_passed );
	}

	/**
	 * Test print_amp_styles.
	 *
	 * @covers AMP_Theme_Support::print_amp_styles()
	 */
	public function test_print_amp_styles() {
		ob_start();
		AMP_Theme_Support::print_amp_styles();
		$output = ob_get_clean();
		$this->assertContains( amp_get_boilerplate_code(), $output );
		$this->assertContains( '<style amp-custom></style>', $output );
	}

	/**
	 * Test ensure_required_markup().
	 *
	 * @dataProvider get_script_data
	 * @covers AMP_Theme_Support::ensure_required_markup()
	 * @param string  $script The value of the script.
	 * @param boolean $expected The expected result.
	 */
	public function test_ensure_required_markup( $script, $expected ) {
		$page = '<html><head><script type="application/ld+json">%s</script></head><body>Test</body></html>';
		$dom  = new DOMDocument();
		$dom->loadHTML( sprintf( $page, $script ) );
		AMP_Theme_Support::ensure_required_markup( $dom );
		$this->assertEquals( $expected, substr_count( $dom->saveHTML(), 'schema.org' ) );
	}

	/**
	 * Test dequeue_customize_preview_scripts.
	 *
	 * @covers AMP_Theme_Support::dequeue_customize_preview_scripts()
	 */
	public function test_dequeue_customize_preview_scripts() {
		// Ensure AMP_Theme_Support::is_customize_preview_iframe() is true.
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$GLOBALS['wp_customize'] = new WP_Customize_Manager( array(
			'messenger_channel' => 'baz',
		) );
		$GLOBALS['wp_customize']->start_previewing_theme();
		$customize_preview = 'customize-preview';
		$preview_style     = 'example-preview-style';
		wp_enqueue_style( $preview_style, home_url( '/' ), array( $customize_preview ) );
		AMP_Theme_Support::dequeue_customize_preview_scripts();
		$this->assertTrue( wp_style_is( $preview_style ) );
		$this->assertTrue( wp_style_is( $customize_preview ) );

		wp_enqueue_style( $preview_style, home_url( '/' ), array( $customize_preview ) );
		wp_enqueue_style( $customize_preview );
		// Ensure AMP_Theme_Support::is_customize_preview_iframe() is false.
		$GLOBALS['wp_customize'] = new WP_Customize_Manager();
		AMP_Theme_Support::dequeue_customize_preview_scripts();
		$this->assertFalse( wp_style_is( $preview_style ) );
		$this->assertFalse( wp_style_is( $customize_preview ) );
	}

	/**
	 * Test start_output_buffering.
	 *
	 * @covers AMP_Theme_Support::start_output_buffering()
	 * @covers AMP_Theme_Support::is_output_buffering()
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 */
	public function test_start_output_buffering() {
		if ( ! function_exists( 'newrelic_disable_autorum ' ) ) {
			/**
			 * Define newrelic_disable_autorum to allow passing line.
			 */
			function newrelic_disable_autorum() {
				return true;
			}
		}

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$initial_ob_level = ob_get_level();
		AMP_Theme_Support::start_output_buffering();
		$this->assertEquals( $initial_ob_level + 1, ob_get_level() );
		ob_end_flush();
		$this->assertEquals( $initial_ob_level, ob_get_level() );
	}

	/**
	 * Test finish_output_buffering.
	 *
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 * @covers AMP_Theme_Support::is_output_buffering()
	 */
	public function test_finish_output_buffering() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$status = ob_get_status();
		$this->assertSame( 1, ob_get_level() );
		$this->assertEquals( 'default output handler', $status['name'] );
		$this->assertFalse( AMP_Theme_Support::is_output_buffering() );

		// start first layer buffer.
		ob_start();
		AMP_Theme_Support::start_output_buffering();
		$this->assertTrue( AMP_Theme_Support::is_output_buffering() );
		$this->assertSame( 3, ob_get_level() );

		echo '<img src="test.png"><script data-test>document.write(\'Illegal\');</script>';

		// Additional nested output bufferings which aren't getting closed.
		ob_start();
		echo 'foo';
		ob_start( function( $response ) {
			return strtoupper( $response );
		} );
		echo 'bar';

		$this->assertTrue( AMP_Theme_Support::is_output_buffering() );
		while ( ob_get_level() > 2 ) {
			ob_end_flush();
		}
		$this->assertFalse( AMP_Theme_Support::is_output_buffering() );
		$output = ob_get_clean();
		$this->assertEquals( 1, ob_get_level() );

		$this->assertContains( '<html amp', $output );
		$this->assertContains( 'foo', $output );
		$this->assertContains( 'BAR', $output );
		$this->assertContains( '<amp-img src="test.png"', $output );
		$this->assertNotContains( '<script data-test', $output );

	}

	/**
	 * Test filter_customize_partial_render.
	 *
	 * @covers AMP_Theme_Support::filter_customize_partial_render()
	 */
	public function test_filter_customize_partial_render() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$partial = '<img src="test.png"><script data-head>document.write(\'Illegal\');</script>';
		$output  = AMP_Theme_Support::filter_customize_partial_render( $partial );
		$this->assertContains( '<amp-img src="test.png"', $output );
		$this->assertNotContains( '<script', $output );
		$this->assertNotContains( '<html', $output );
	}

	/**
	 * Test prepare_response.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @global WP_Scripts $wp_scripts
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response() {
		global $wp_widget_factory, $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();
		$wp_widget_factory = new WP_Widget_Factory();
		wp_widgets_init();

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_script( 'amp-list' );
		} );
		add_action( 'wp_print_scripts', function() {
			echo '<!-- wp_print_scripts -->';
		} );
		add_filter( 'script_loader_tag', function( $tag, $handle ) {
			if ( ! wp_scripts()->get_data( $handle, 'conditional' ) ) {
				$tag = preg_replace( '/(?<=<script)/', " handle='$handle' ", $tag );
			}
			return $tag;
		}, 10, 2 );
		add_action( 'wp_footer', function() {
			wp_print_scripts( 'amp-mathml' );
			?>
			<amp-mathml layout="container" data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>
			<?php
		}, 1 );

		ob_start();
		?>
		<!DOCTYPE html>
		<html amp <?php language_attributes(); ?>>
			<head>
				<?php wp_head(); ?>
				<script data-head>document.write('Illegal');</script>
			</head>
			<body>
				<img width="100" height="100" src="https://example.com/test.png">
				<audio width="400" height="300" src="https://example.com/audios/myaudio.mp3"></audio>
				<amp-ad type="a9"
					width="300"
					height="250"
					data-aax_size="300x250"
					data-aax_pubname="test123"
					data-aax_src="302"></amp-ad>
				<?php wp_footer(); ?>

				<button onclick="alert('Illegal');">no-onclick</button>

				<style>body { background: black; }</style>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$removed_nodes  = array();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array(
			'validation_error_callback' => function( $removed ) use ( &$removed_nodes ) {
				$removed_nodes[ $removed['node']->nodeName ] = $removed['node'];
			},
		) );

		$this->assertNotContains( 'handle=', $sanitized_html );
		$this->assertEquals( 2, substr_count( $sanitized_html, '<!-- wp_print_scripts -->' ) );
		$this->assertContains( '<meta charset="' . get_bloginfo( 'charset' ) . '">', $sanitized_html );
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );
		$this->assertContains( '<style amp-boilerplate>', $sanitized_html );
		$this->assertRegExp( '#<style amp-custom>.*?body{background:black}.*?</style>#s', $sanitized_html );
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0.js" async></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-list-latest.js" async custom-element="amp-list"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-mathml-latest.js" async custom-element="amp-mathml"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<meta name="generator" content="AMP Plugin', $sanitized_html );

		$this->assertNotContains( '<img', $sanitized_html );
		$this->assertContains( '<amp-img', $sanitized_html );

		$this->assertNotContains( '<audio', $sanitized_html );
		$this->assertContains( '<amp-audio', $sanitized_html );

		// Note these are single-quoted because they are injected after the DOM has been re-serialized, so the type and src attributes come from WP_Scripts::do_item().
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-audio-latest.js\' async custom-element="amp-audio"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-ad-latest.js\' async custom-element="amp-ad"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		$this->assertContains( '<button>no-onclick</button>', $sanitized_html );
		$this->assertCount( 3, $removed_nodes );
		$this->assertInstanceOf( 'DOMElement', $removed_nodes['script'] );
		$this->assertInstanceOf( 'DOMAttr', $removed_nodes['onclick'] );
		$this->assertInstanceOf( 'DOMAttr', $removed_nodes['handle'] );
	}

	/**
	 * Test prepare_response for bad/non-HTML.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_bad_html() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();

		// JSON.
		$input = '{"success":true}';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// Nothing, for redirect.
		$input = '';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// HTML, but very stripped down.
		$input  = '<html>Hello</html>';
		$output = AMP_Theme_Support::prepare_response( $input );
		$this->assertContains( '<html amp', $output );
	}

	/**
	 * Test prepare_response to inject html[amp] attribute and ensure HTML5 doctype.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_to_add_html5_doctype_and_amp_attribute() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		ob_start();
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html><head><?php wp_head(); ?></head><body><?php wp_footer(); ?></body></html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		$this->assertStringStartsWith( '<!DOCTYPE html>', $sanitized_html );
		$this->assertContains( '<html amp', $sanitized_html );
	}

	/**
	 * Data provider for test_ensure_required_markup.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'schema_org_not_present'        => array(
				'',
				1,
			),
			'schema_org_present'            => array(
				wp_json_encode( array( '@context' => 'http://schema.org' ) ),
				1,
			),
			'schema_org_output_not_escaped' => array(
				'{"@context":"http://schema.org"',
				1,
			),
			'schema_org_another_key'        => array(
				wp_json_encode( array( '@anothercontext' => 'https://schema.org' ) ),
				1,
			),
		);
	}

	/**
	 * Test enqueue_assets().
	 *
	 * @covers AMP_Theme_Support::enqueue_assets()
	 */
	public function test_enqueue_assets() {
		$script_slug = 'amp-runtime';
		$style_slug  = 'amp-default';
		wp_dequeue_script( $script_slug );
		wp_dequeue_style( $style_slug );
		AMP_Theme_Support::enqueue_assets();
		$this->assertTrue( in_array( $script_slug, wp_scripts()->queue, true ) );
		$this->assertTrue( in_array( $style_slug, wp_styles()->queue, true ) );
	}

	/**
	 * Test AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html().
	 *
	 * @see AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html()
	 */
	public function test_whitelist_layout_in_wp_kses_allowed_html() {
		$attribute             = 'data-amp-layout';
		$image_no_dimensions   = array(
			'img' => array(
				$attribute => true,
			),
		);
		$image_with_dimensions = array_merge(
			$image_no_dimensions,
			array(
				'height' => '100',
				'width'  => '100',
			)
		);

		$this->assertEquals( array(), AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( array() ) );
		$this->assertEquals( $image_no_dimensions, AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_no_dimensions ) );

		$context = AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_with_dimensions );
		$this->assertTrue( $context['img'][ $attribute ] );

		$context = AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_with_dimensions );
		$this->assertTrue( $context['img'][ $attribute ] );

		add_filter( 'wp_kses_allowed_html', 'AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html', 10, 2 );
		$image = '<img data-amp-layout="fill">';
		$this->assertEquals( $image, wp_kses_post( $image ) );
	}
}
