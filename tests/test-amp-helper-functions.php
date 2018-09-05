<?php
/**
 * Test AMP helper functions.
 *
 * @package AMP
 */

/**
 * Class Test_AMP_Helper_Functions
 */
class Test_AMP_Helper_Functions extends WP_UnitTestCase {

	/**
	 * The mock Site Icon value to use in a filter.
	 *
	 * @var string
	 */
	const MOCK_SITE_ICON = 'https://example.com/new-site-icon.jpg';

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_theme_support( 'amp' );
		global $wp_scripts, $pagenow;
		$wp_scripts = null;
		$pagenow    = 'index.php'; // Since clean_up_global_scope() doesn't.
		parent::tearDown();
	}

	/**
	 * Filter for amp_pre_get_permalink and amp_get_permalink.
	 *
	 * @param string $url     URL.
	 * @param int    $post_id Post ID.
	 * @return string URL.
	 */
	public function return_example_url( $url, $post_id ) {
		$current_filter = current_filter();
		return 'http://overridden.example.com/?' . build_query( compact( 'url', 'post_id', 'current_filter' ) );
	}

	/**
	 * Test amp_get_slug().
	 *
	 * @covers \amp_get_slug()
	 */
	public function test_amp_get_slug() {
		$this->assertSame( 'amp', amp_get_slug() );
	}

	/**
	 * Test amp_get_current_url().
	 *
	 * @covers \amp_get_current_url()
	 */
	public function test_amp_get_current_url() {
		$request_uris = array(
			'/foo',
			'/bar?baz',
			null,
		);

		foreach ( $request_uris as $request_uri ) {
			if ( $request_uri ) {
				$_SERVER['REQUEST_URI'] = wp_slash( $request_uri );
			} else {
				unset( $_SERVER['REQUEST_URI'] );
			}
			$this->assertEquals(
				home_url( $request_uri ? $request_uri : '/' ),
				amp_get_current_url(),
				sprintf( 'Unexpected for URI: %s', wp_json_encode( $request_uri, 64 /* JSON_UNESCAPED_SLASHES */ ) )
			);
		}
	}

	/**
	 * Test amp_get_permalink() without pretty permalinks.
	 *
	 * @covers \amp_get_permalink()
	 */
	public function test_amp_get_permalink_without_pretty_permalinks() {
		remove_theme_support( 'amp' );
		delete_option( 'permalink_structure' );
		flush_rewrite_rules();

		$drafted_post   = $this->factory()->post->create( array(
			'post_name'   => 'draft',
			'post_status' => 'draft',
			'post_type'   => 'post',
		) );
		$published_post = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'post',
		) );
		$published_page = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );

		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_page ) );

		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_pre_get_permalink', $url );
		$this->assertContains( 'url=0', $url );

		remove_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_get_permalink', $url );
		remove_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ) );

		// Now check with theme support added (in paired mode).
		add_theme_support( 'amp', array( 'template_dir' => './' ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $published_page ) );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$this->assertNotContains( 'current_filter=amp_get_permalink', amp_get_permalink( $published_post ) ); // Filter does not apply.
		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$this->assertNotContains( 'current_filter=amp_pre_get_permalink', amp_get_permalink( $published_post ) ); // Filter does not apply.
	}

	/**
	 * Test amp_get_permalink() with pretty permalinks.
	 *
	 * @covers \amp_get_permalink()
	 */
	public function test_amp_get_permalink_with_pretty_permalinks() {
		global $wp_rewrite;
		update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
		$wp_rewrite->use_trailing_slashes = true;
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();

		$add_anchor_fragment = function( $url ) {
			return $url . '#anchor';
		};

		$drafted_post   = $this->factory()->post->create( array(
			'post_name'   => 'draft',
			'post_status' => 'draft',
		) );
		$published_post = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
		) );
		$published_page = $this->factory()->post->create( array(
			'post_name'   => 'publish',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '/amp/', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '?amp', amp_get_permalink( $published_page ) );

		add_filter( 'post_link', $add_anchor_fragment );
		$this->assertStringEndsWith( '/amp/#anchor', amp_get_permalink( $published_post ) );
		remove_filter( 'post_link', $add_anchor_fragment );

		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_pre_get_permalink', $url );
		$this->assertContains( 'url=0', $url );

		remove_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10 );
		$url = amp_get_permalink( $published_post );
		$this->assertContains( 'current_filter=amp_get_permalink', $url );
		remove_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10 );

		// Now check with theme support added (in paired mode).
		add_theme_support( 'amp', array( 'template_dir' => './' ) );
		$this->assertStringEndsWith( '&amp', amp_get_permalink( $drafted_post ) );
		$this->assertStringEndsWith( '?amp', amp_get_permalink( $published_post ) );
		$this->assertStringEndsWith( '?amp', amp_get_permalink( $published_page ) );
		add_filter( 'amp_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$this->assertNotContains( 'current_filter=amp_get_permalink', amp_get_permalink( $published_post ) ); // Filter does not apply.
		add_filter( 'amp_pre_get_permalink', array( $this, 'return_example_url' ), 10, 2 );
		$this->assertNotContains( 'current_filter=amp_pre_get_permalink', amp_get_permalink( $published_post ) ); // Filter does not apply.

		// Make sure that if permalink has anchor that it is persists.
		add_filter( 'post_link', $add_anchor_fragment );
		$this->assertStringEndsWith( '/?amp#anchor', amp_get_permalink( $published_post ) );
	}

	/**
	 * Test amp_get_permalink() with theme support paired mode.
	 *
	 * @covers \amp_get_permalink()
	 */
	public function test_amp_get_permalink_with_theme_support() {
		global $wp_rewrite;
		add_theme_support( 'amp' );

		update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
		$wp_rewrite->use_trailing_slashes = true;
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();

		$post_id = $this->factory()->post->create();
		$this->assertEquals( get_permalink( $post_id ), amp_get_permalink( $post_id ) );

		add_theme_support( 'amp', array(
			'template_dir' => 'amp',
		) );
	}

	/**
	 * Test amp_remove_endpoint.
	 *
	 * @covers \amp_remove_endpoint()
	 */
	public function test_amp_remove_endpoint() {
		$this->assertEquals( 'https://example.com/foo/', amp_remove_endpoint( 'https://example.com/foo/?amp' ) );
		$this->assertEquals( 'https://example.com/foo/?#bar', amp_remove_endpoint( 'https://example.com/foo/?amp#bar' ) );
		$this->assertEquals( 'https://example.com/foo/', amp_remove_endpoint( 'https://example.com/foo/amp/' ) );
		$this->assertEquals( 'https://example.com/foo/?blaz', amp_remove_endpoint( 'https://example.com/foo/amp/?blaz' ) );
	}


	/**
	 * Test that hook is added.
	 *
	 * @covers \amp_add_frontend_actions()
	 */
	public function test_amp_add_frontend_actions() {
		$this->assertFalse( has_action( 'wp_head', 'amp_add_amphtml_link' ) );
		amp_add_frontend_actions();
		$this->assertEquals( 10, has_action( 'wp_head', 'amp_add_amphtml_link' ) );
	}

	/**
	 * URLs to test amphtml link.
	 *
	 * @return array
	 */
	public function get_amphtml_urls() {
		$post_id = $this->factory()->post->create();
		return array(
			'home' => array(
				home_url( '/' ),
				add_query_arg( amp_get_slug(), '', home_url( '/' ) ),
			),
			'404'  => array(
				home_url( '/no-existe/' ),
				add_query_arg( amp_get_slug(), '', home_url( '/no-existe/' ) ),
			),
			'post' => array(
				get_permalink( $post_id ),
				amp_get_permalink( $post_id ),
			),
		);
	}

	/**
	 * Adding link when theme support is not present.
	 *
	 * @dataProvider get_amphtml_urls
	 * @covers \amp_add_amphtml_link()
	 * @param string $canonical_url Canonical URL.
	 * @param string $amphtml_url   The amphtml URL.
	 */
	public function test_amp_add_amphtml_link( $canonical_url, $amphtml_url ) {
		$test = $this; // For PHP 5.3.
		AMP_Options_Manager::update_option( 'force_sanitization', false );

		$get_amp_html_link = function() {
			ob_start();
			amp_add_amphtml_link();
			return ob_get_clean();
		};

		$assert_amphtml_link_present = function() use ( $test, $amphtml_url, $get_amp_html_link ) {
			$test->assertEquals(
				sprintf( '<link rel="amphtml" href="%s">', esc_url( $amphtml_url ) ),
				$get_amp_html_link()
			);
		};

		$this->go_to( $canonical_url );
		$assert_amphtml_link_present();

		// Make sure adding the filter hides the amphtml link.
		add_filter( 'amp_frontend_show_canonical', '__return_false' );
		$this->assertEmpty( $get_amp_html_link() );
		remove_filter( 'amp_frontend_show_canonical', '__return_false' );
		$assert_amphtml_link_present();

		// Make sure that the link is not provided when there are validation errors associated with the URL.
		add_theme_support( 'amp', array(
			'template_dir' => './',
		) );
		AMP_Theme_Support::init();
		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			$canonical_url
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_url_post_id );

		// Allow the URL when the errors are forcibly sanitized.
		$this->assertContains( '<!--', $get_amp_html_link() );
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		$assert_amphtml_link_present();
	}

	/**
	 * Test is_amp_endpoint() function.
	 *
	 * @covers \is_amp_endpoint()
	 */
	public function test_is_amp_endpoint() {
		$this->go_to( get_permalink( $this->factory()->post->create() ) );
		$this->assertFalse( is_amp_endpoint() );

		// Legacy query var.
		set_query_var( amp_get_slug(), '' );
		$this->assertTrue( is_amp_endpoint() );
		unset( $GLOBALS['wp_query']->query_vars[ amp_get_slug() ] );
		$this->assertFalse( is_amp_endpoint() );

		// Paired theme support.
		add_theme_support( 'amp', array( 'template_dir' => './' ) );
		$_GET['amp'] = '';
		$this->assertTrue( is_amp_endpoint() );
		unset( $_GET['amp'] );
		$this->assertFalse( is_amp_endpoint() );
		remove_theme_support( 'amp' );

		// Native theme support.
		add_theme_support( 'amp' );
		$this->assertTrue( is_amp_endpoint() );

		// Special core pages.
		$pages = array( 'wp-login.php', 'wp-signup.php', 'wp-activate.php' );
		foreach ( $pages as $page ) {
			$GLOBALS['pagenow'] = $page;
			$this->assertFalse( is_amp_endpoint() );
		}
		unset( $GLOBALS['pagenow'] );

		/**
		 * Simulate a user unchecking almost all of the boxes in 'AMP Settings' > 'Supported Templates'.
		 * The user has chosen not to show them as AMP, so most URLs should not be AMP endpoints.
		 */
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		AMP_Options_Manager::update_option( 'supported_templates', array( 'is_author' ) );

		// A post shouldn't be an AMP endpoint, as it was unchecked in the UI via the options above.
		$this->go_to( $this->factory()->post->create() );
		$this->assertFalse( is_amp_endpoint() );

		// The homepage shouldn't be an AMP endpoint, as it was also unchecked in the UI.
		$this->go_to( home_url( '/' ) );
		$this->assertFalse( is_amp_endpoint() );

		// When the user passes a flag to the WP-CLI command, it forces AMP validation no matter whether the user disabled AMP on any template.
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = AMP_Validation_Manager::get_amp_validate_nonce();
		$this->assertTrue( is_amp_endpoint() );
	}

	/**
	 * Filter calls.
	 *
	 * @var array
	 */
	protected $last_filter_call;

	/**
	 * Capture filter call.
	 *
	 * @param mixed $value Value.
	 * @return mixed Value.
	 */
	public function capture_filter_call( $value ) {
		$this->last_filter_call = array(
			'current_filter' => current_filter(),
			'args'           => func_get_args(),
		);
		return $value;
	}

	/**
	 * Test script registering.
	 *
	 * @covers \amp_register_default_scripts()
	 * @covers \amp_filter_script_loader_tag()
	 * @covers \amp_render_scripts()
	 * @global WP_Scripts $wp_scripts
	 */
	public function test_script_registering() {
		global $wp_scripts;
		$wp_scripts = null;
		$this->assertEquals( 10, has_action( 'wp_default_scripts', 'amp_register_default_scripts' ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'script_loader_tag', 'amp_filter_script_loader_tag' ) );

		$this->assertTrue( wp_script_is( 'amp-runtime', 'registered' ) );
		$this->assertTrue( wp_script_is( 'amp-mustache', 'registered' ) );
		$this->assertTrue( wp_script_is( 'amp-list', 'registered' ) );
		$this->assertTrue( wp_script_is( 'amp-bind', 'registered' ) );

		wp_enqueue_script( 'amp-mathml' );
		wp_enqueue_script( 'amp-mustache' );
		$this->assertTrue( wp_script_is( 'amp-mathml', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'amp-mustache', 'enqueued' ) );

		// Try overriding URL.
		wp_scripts()->registered['amp-mustache']->src = 'https://cdn.ampproject.org/v0/amp-mustache-0.1.js';

		ob_start();
		wp_print_scripts();
		$output = ob_get_clean();

		$this->assertStringStartsWith( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0.js\' async></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-mathml-latest.js\' async custom-element="amp-mathml"></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-mustache-0.1.js\' async custom-template="amp-mustache"></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Try rendering via amp_render_scripts() instead of amp_render_scripts(), which is how component scripts get added normally.
		$output = amp_render_scripts( array(
			'amp-mathml'    => true, // But already printed above.
			'amp-carousel'  => 'https://cdn.ampproject.org/v0/amp-mustache-2.0.js',
			'amp-accordion' => true,
		) );
		$this->assertNotContains( 'amp-mathml', $output, 'The amp-mathml component was already printed above.' );
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-mustache-2.0.js\' async custom-element="amp-carousel"></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-accordion-latest.js\' async custom-element="amp-accordion"></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Try some experimental component to ensure expected script attributes are added.
		wp_register_script( 'amp-foo', 'https://cdn.ampproject.org/v0/amp-foo-0.1.js', array( 'amp-runtime' ), null );
		ob_start();
		wp_print_scripts( 'amp-foo' );
		$output = ob_get_clean();
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-foo-0.1.js\' async custom-element="amp-foo"></script>', $output ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Test amp_get_content_embed_handlers().
	 *
	 * @covers \amp_get_content_embed_handlers()
	 */
	public function test_amp_get_content_embed_handlers() {
		$post = $this->factory()->post->create_and_get();
		add_filter( 'amp_content_embed_handlers', array( $this, 'capture_filter_call' ), 10, 2 );

		$this->last_filter_call = null;
		add_theme_support( 'amp' );
		$handlers = amp_get_content_embed_handlers();
		$this->assertArrayHasKey( 'AMP_SoundCloud_Embed_Handler', $handlers );
		$this->assertEquals( 'amp_content_embed_handlers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertNull( $this->last_filter_call['args'][1] );

		$this->last_filter_call = null;
		remove_theme_support( 'amp' );
		$handlers = amp_get_content_embed_handlers( $post );
		$this->assertArrayHasKey( 'AMP_SoundCloud_Embed_Handler', $handlers );
		$this->assertEquals( 'amp_content_embed_handlers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertEquals( $post, $this->last_filter_call['args'][1] );
	}

	/**
	 * Test deprecated $post param for amp_get_content_embed_handlers().
	 *
	 * @covers \amp_get_content_embed_handlers()
	 */
	public function test_amp_get_content_embed_handlers_deprecated_param() {
		$post = $this->factory()->post->create_and_get();
		$this->setExpectedDeprecated( 'amp_get_content_embed_handlers' );
		add_theme_support( 'amp' );
		amp_get_content_embed_handlers( $post );
	}

	/**
	 * Test amp_get_content_sanitizers().
	 *
	 * @covers \amp_get_content_sanitizers()
	 */
	public function test_amp_get_content_sanitizers() {
		$post = $this->factory()->post->create_and_get();
		add_filter( 'amp_content_sanitizers', array( $this, 'capture_filter_call' ), 10, 2 );

		$this->last_filter_call = null;
		add_theme_support( 'amp' );
		$handlers = amp_get_content_sanitizers();
		$this->assertArrayHasKey( 'AMP_Style_Sanitizer', $handlers );
		$this->assertEquals( 'amp_content_sanitizers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$handler_classes = array_keys( $handlers );
		$this->assertNull( $this->last_filter_call['args'][1] );
		$this->assertEquals( 'AMP_Tag_And_Attribute_Sanitizer', end( $handler_classes ) );

		$this->last_filter_call = null;
		remove_theme_support( 'amp' );
		$handlers = amp_get_content_sanitizers( $post );
		$this->assertArrayHasKey( 'AMP_Style_Sanitizer', $handlers );
		$this->assertEquals( 'amp_content_sanitizers', $this->last_filter_call['current_filter'] );
		$this->assertEquals( $handlers, $this->last_filter_call['args'][0] );
		$this->assertEquals( $post, $this->last_filter_call['args'][1] );

		// Make sure the style and whitelist sanitizers are always at the end, even after filtering.
		add_filter( 'amp_content_sanitizers', function( $classes ) {
			$classes['Even_After_Whitelist_Sanitizer'] = array();
			return $classes;
		} );
		$orderd_sanitizers = array_keys( amp_get_content_sanitizers() );
		$this->assertEquals( 'Even_After_Whitelist_Sanitizer', $orderd_sanitizers[ count( $orderd_sanitizers ) - 3 ] );
		$this->assertEquals( 'AMP_Style_Sanitizer', $orderd_sanitizers[ count( $orderd_sanitizers ) - 2 ] );
		$this->assertEquals( 'AMP_Tag_And_Attribute_Sanitizer', $orderd_sanitizers[ count( $orderd_sanitizers ) - 1 ] );
	}

	/**
	 * Test deprecated $post param for amp_get_content_sanitizers().
	 *
	 * @covers \amp_get_content_sanitizers()
	 */
	public function test_amp_get_content_sanitizers_deprecated_param() {
		$post = $this->factory()->post->create_and_get();
		$this->setExpectedDeprecated( 'amp_get_content_sanitizers' );
		add_theme_support( 'amp' );
		amp_get_content_sanitizers( $post );
	}

	/**
	 * Test post_supports_amp().
	 *
	 * @covers \post_supports_amp()
	 */
	public function test_post_supports_amp() {
		add_post_type_support( 'page', amp_get_slug() );

		// Test disabled by default for page for posts and show on front.
		update_option( 'show_on_front', 'page' );
		$post = $this->factory()->post->create_and_get( array( 'post_type' => 'page' ) );
		$this->assertTrue( post_supports_amp( $post ) );
		update_option( 'show_on_front', 'page' );
		$this->assertTrue( post_supports_amp( $post ) );
		update_option( 'page_for_posts', $post->ID );
		$this->assertFalse( post_supports_amp( $post ) );
		update_option( 'page_for_posts', '' );
		update_option( 'page_on_front', $post->ID );
		$this->assertFalse( post_supports_amp( $post ) );
		update_option( 'show_on_front', 'posts' );
		$this->assertTrue( post_supports_amp( $post ) );

		// Test disabled by default for page templates.
		update_post_meta( $post->ID, '_wp_page_template', 'foo.php' );
		$this->assertFalse( post_supports_amp( $post ) );

		// Reset.
		remove_post_type_support( 'page', amp_get_slug() );
	}

	/**
	 * Test amp_get_post_image_metadata()
	 *
	 * @covers \amp_get_post_image_metadata()
	 */
	public function test_amp_get_post_image_metadata() {
		$post_id = $this->factory()->post->create();
		$this->assertFalse( amp_get_post_image_metadata( $post_id ) );

		$first_test_image = '/tmp/test-image.jpg';
		copy( DIR_TESTDATA . '/images/test-image.jpg', $first_test_image );
		$attachment_id = self::factory()->attachment->create_object( array(
			'file'           => $first_test_image,
			'post_parent'    => 0,
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Test Image',
		) );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $first_test_image ) );

		set_post_thumbnail( $post_id, $attachment_id );
		$metadata = amp_get_post_image_metadata( $post_id );
		$this->assertEquals( 'ImageObject', $metadata['@type'] );
		$this->assertEquals( 50, $metadata['width'] );
		$this->assertEquals( 50, $metadata['height'] );
		$this->assertStringEndsWith( 'test-image.jpg', $metadata['url'] );

		delete_post_thumbnail( $post_id );
		$this->assertFalse( amp_get_post_image_metadata( $post_id ) );
		wp_update_post( array(
			'ID'          => $attachment_id,
			'post_parent' => $post_id,
		) );
		$metadata = amp_get_post_image_metadata( $post_id );
		$this->assertStringEndsWith( 'test-image.jpg', $metadata['url'] );

		// Test an 'attachment' post type.
		$attachment_src          = 'example/attachment.jpeg';
		$attachment_height       = 45;
		$attachment_width        = 600;
		$attachment_id           = $this->factory()->attachment->create_object(
			$attachment_src,
			0,
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);
		$expected_attachment_img = wp_get_attachment_image_src( $attachment_id, 'full', false );

		update_post_meta(
			$attachment_id,
			'_wp_attachment_metadata',
			array(
				'height' => $attachment_height,
				'width'  => $attachment_width,
			)
		);
		$this->go_to( get_permalink( $attachment_id ) );

		$this->assertEquals(
			array(
				'@type'  => 'ImageObject',
				'height' => $attachment_height,
				'url'    => $expected_attachment_img[0],
				'width'  => $attachment_width,
			),
			amp_get_post_image_metadata( $attachment_id )
		);

		// Test a video as an 'attachment' post type, which shouldn't have a schema.org image.
		$attachment_src = 'example/test-video.mpeg';
		$attachment_id  = $this->factory()->attachment->create_object(
			$attachment_src,
			0,
			array(
				'post_mime_type' => 'video/mpeg',
			)
		);
		$this->go_to( get_permalink( $attachment_id ) );
		$this->assertFalse( amp_get_post_image_metadata( $attachment_id ) );
	}

	/**
	 * Test amp_get_schemaorg_metadata().
	 *
	 * @covers \amp_get_schemaorg_metadata()
	 */
	public function test_amp_get_schemaorg_metadata() {
		update_option( 'blogname', 'Foo' );
		$publisher_type     = 'Organization';
		$logo_type          = 'ImageObject';
		$expected_publisher = array(
			'@type' => $publisher_type,
			'name'  => 'Foo',
		);

		$user_id = $this->factory()->user->create( array(
			'first_name' => 'John',
			'last_name'  => 'Smith',
		) );
		$page_id = $this->factory()->post->create( array(
			'post_type'   => 'page',
			'post_title'  => 'Example Page',
			'post_author' => $user_id,
		) );
		$post_id = $this->factory()->post->create( array(
			'post_type'   => 'post',
			'post_title'  => 'Example Post',
			'post_author' => $user_id,
		) );

		// Test non-singular, with no publisher logo.
		$this->go_to( home_url() );
		$metadata = amp_get_schemaorg_metadata();
		$this->assertEquals( 'http://schema.org', $metadata['@context'] );
		$this->assertArrayNotHasKey( '@type', $metadata );
		$this->assertArrayHasKey( 'publisher', $metadata );
		$this->assertEquals( $expected_publisher, $metadata['publisher'] );

		// Test the custom_logo as the publisher logo.
		$custom_logo_src    = 'example/custom-logo.jpeg';
		$custom_logo_height = 45;
		$custom_logo_width  = 600;
		$custom_logo_id     = $this->factory()->attachment->create_object(
			$custom_logo_src,
			0,
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);
		$expected_logo_img  = wp_get_attachment_image_src( $custom_logo_id, 'full', false );

		update_post_meta(
			$custom_logo_id,
			'_wp_attachment_metadata',
			array(
				'width'  => $custom_logo_width,
				'height' => $custom_logo_height,
			)
		);
		set_theme_mod( 'custom_logo', $custom_logo_id );
		$metadata = amp_get_schemaorg_metadata();
		$this->assertEquals(
			array(
				'@type'  => $logo_type,
				'height' => $custom_logo_height,
				'url'    => $expected_logo_img[0],
				'width'  => $custom_logo_width,
			),
			$metadata['publisher']['logo']
		);
		set_theme_mod( 'custom_logo', null );

		// Test the site icon as the publisher logo.
		$site_icon_src          = 'foo/site-icon.jpeg';
		$site_icon_id           = $this->factory()->attachment->create_object(
			$site_icon_src,
			0,
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);
		$expected_site_icon_img = wp_get_attachment_image_src( $site_icon_id, 'full', false );

		update_option( 'site_icon', $site_icon_id );
		$expected_schema_site_icon = array(
			'@type'  => $logo_type,
			'height' => 32,
			'url'    => $expected_site_icon_img[0],
			'width'  => 32,
		);
		$metadata                  = amp_get_schemaorg_metadata();
		$this->assertEquals( $expected_schema_site_icon, $metadata['publisher']['logo'] );
		update_option( 'site_icon', null );

		/**
		 * Test the publisher logo when the Custom Logo naturally has too much height, a common scenario.
		 *
		 * It's expected that the URL is the same,
		 * but the height should be 60, the maximum height for the schema.org publisher logo.
		 * And the width should be proportional to the new height.
		 */
		$custom_logo_excessive_height = 250;
		update_post_meta(
			$custom_logo_id,
			'_wp_attachment_metadata',
			array(
				'width'  => $custom_logo_width,
				'height' => $custom_logo_excessive_height,
			)
		);
		set_theme_mod( 'custom_logo', $custom_logo_id );
		update_option( 'site_icon', $site_icon_id );
		$metadata   = amp_get_schemaorg_metadata();
		$max_height = 60;
		$this->assertEquals(
			array(
				'@type'  => $logo_type,
				'height' => $max_height,
				'url'    => $expected_logo_img[0],
				'width'  => $custom_logo_width * $max_height / $custom_logo_excessive_height, // Proportional to downsized height.
			),
			$metadata['publisher']['logo']
		);
		set_theme_mod( 'custom_logo', null );
		update_option( 'site_icon', null );

		/**
		 * Test that the 'amp_site_icon_url' filter also applies to the Custom Logo.
		 *
		 * Before, this only applied to the Site Icon, as that was the only possible schema.org publisher logo.
		 * But now that the Custom Logo is preferred, this filter should also apply to that.
		 */
		update_post_meta(
			$custom_logo_id,
			'_wp_attachment_metadata',
			array(
				'width'  => $custom_logo_width,
				'height' => $custom_logo_height,
			)
		);
		set_theme_mod( 'custom_logo', $custom_logo_id );
		add_filter( 'amp_site_icon_url', array( __CLASS__, 'mock_site_icon' ) );
		$metadata = amp_get_schemaorg_metadata();
		$this->assertEquals( self::MOCK_SITE_ICON, $metadata['publisher']['logo']['url'] );
		remove_filter( 'amp_site_icon_url', array( __CLASS__, 'mock_site_icon' ) );
		set_theme_mod( 'custom_logo', null );

		// Test page.
		$this->go_to( get_permalink( $page_id ) );
		$metadata = amp_get_schemaorg_metadata();
		$this->assertEquals( 'http://schema.org', $metadata['@context'] );
		$this->assertEquals( $expected_publisher, $metadata['publisher'] );
		$this->assertEquals( 'WebPage', $metadata['@type'] );
		$this->assertArrayHasKey( 'author', $metadata );
		$this->assertEquals( get_permalink( $page_id ), $metadata['mainEntityOfPage'] );
		$this->assertEquals( get_the_title( $page_id ), $metadata['headline'] );
		$this->assertArrayHasKey( 'datePublished', $metadata );
		$this->assertArrayHasKey( 'dateModified', $metadata );

		// Test post.
		$this->go_to( get_permalink( $post_id ) );
		$metadata = amp_get_schemaorg_metadata();
		$this->assertEquals( 'http://schema.org', $metadata['@context'] );
		$this->assertEquals( $expected_publisher, $metadata['publisher'] );
		$this->assertEquals( 'BlogPosting', $metadata['@type'] );
		$this->assertEquals( get_permalink( $post_id ), $metadata['mainEntityOfPage'] );
		$this->assertEquals( get_the_title( $post_id ), $metadata['headline'] );
		$this->assertArrayHasKey( 'datePublished', $metadata );
		$this->assertArrayHasKey( 'dateModified', $metadata );
		$this->assertEquals(
			array(
				'@type' => 'Person',
				'name'  => 'John Smith',
			),
			$metadata['author']
		);

		// Test override.
		$this->go_to( get_permalink( $post_id ) );
		$self = $this;
		add_filter( 'amp_post_template_metadata', function( $meta, $post ) use ( $self, $post_id ) {
			$self->assertEquals( $post_id, $post->ID );
			$meta['did_amp_post_template_metadata'] = true;
			$self->assertArrayNotHasKey( 'amp_schemaorg_metadata', $meta );
			return $meta;
		}, 10, 2 );
		add_filter( 'amp_schemaorg_metadata', function( $meta ) use ( $self ) {
			$meta['did_amp_schemaorg_metadata'] = true;
			$self->assertArrayHasKey( 'did_amp_post_template_metadata', $meta );
			$meta['author']['name'] = 'George';
			return $meta;
		} );

		$metadata = amp_get_schemaorg_metadata();
		$this->assertArrayHasKey( 'did_amp_post_template_metadata', $metadata );
		$this->assertArrayHasKey( 'did_amp_schemaorg_metadata', $metadata );
		$this->assertEquals( 'George', $metadata['author']['name'] );
	}

	/**
	 * Get a mock publisher logo URL, to test that the filter works as expected.
	 *
	 * @param string $site_icon The publisher logo in the schema.org data.
	 * @return string $site_icon The filtered publisher logo in the schema.org data.
	 */
	public static function mock_site_icon( $site_icon ) {
		unset( $site_icon );
		return self::MOCK_SITE_ICON;
	}
}
