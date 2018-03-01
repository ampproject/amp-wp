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
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_theme_support( 'amp' );
		$_REQUEST                = array(); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$_SERVER['QUERY_STRING'] = '';
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
	 * Test prepare_response.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		$wp_widget_factory = new WP_Widget_Factory();
		wp_widgets_init();

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
			'remove_invalid_callback' => function( $removed ) use ( &$removed_nodes ) {
				$removed_nodes[ $removed['node']->nodeName ] = $removed['node'];
			},
		) );

		$this->assertContains( '<meta charset="' . get_bloginfo( 'charset' ) . '">', $sanitized_html );
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );
		$this->assertContains( '<style amp-boilerplate>', $sanitized_html );
		$this->assertContains( '<style amp-custom>body { background: black; }', $sanitized_html );
		$this->assertContains( '<script async src="https://cdn.ampproject.org/v0.js"', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<meta name="generator" content="AMP Plugin', $sanitized_html );

		$this->assertNotContains( '<img', $sanitized_html );
		$this->assertContains( '<amp-img', $sanitized_html );

		$this->assertNotContains( '<audio', $sanitized_html );
		$this->assertContains( '<amp-audio', $sanitized_html );
		$this->assertContains( '<script async custom-element="amp-audio"', $sanitized_html );

		$this->assertContains( '<script async custom-element="amp-ad"', $sanitized_html );

		$this->assertContains( '<button>no-onclick</button>', $sanitized_html );
		$this->assertCount( 2, $removed_nodes );
		$this->assertInstanceOf( 'DOMElement', $removed_nodes['script'] );
		$this->assertInstanceOf( 'DOMAttr', $removed_nodes['onclick'] );
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
	 * Test purge_amp_query_vars.
	 *
	 * @covers AMP_Theme_Support::purge_amp_query_vars()
	 */
	public function test_purge_amp_query_vars() {
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
		$bad_query_vars = array(
			'amp_latest_update_time' => '1517199956',
			'__amp_source_origin'    => home_url(),
		);
		$ok_query_vars  = array(
			'bar' => 'baz',
		);
		$all_query_vars = array_merge( $bad_query_vars, $ok_query_vars );

		$_SERVER['QUERY_STRING'] = build_query( $all_query_vars );

		remove_action( 'wp', 'amp_maybe_add_actions' );
		$this->go_to( add_query_arg( $all_query_vars, home_url( '/foo/' ) ) );
		$_REQUEST = $_GET; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		foreach ( $all_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}

		AMP_Theme_Support::$purged_amp_query_vars = array();
		AMP_Theme_Support::purge_amp_query_vars();
		$this->assertEqualSets( AMP_Theme_Support::$purged_amp_query_vars, $bad_query_vars );

		foreach ( $bad_query_vars as $key => $value ) {
			$this->assertArrayNotHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayNotHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertNotContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertNotContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		foreach ( $ok_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Test get_amp_scripts().
	 *
	 * @covers AMP_Theme_Support::get_amp_scripts()
	 */
	public function test_get_amp_scripts() {
		add_filter( 'amp_component_scripts', function( $scripts ) {
			$scripts['amp-video'] = 'https://cdn.ampproject.org/v0/amp-video-0.1.js';
			return $scripts;
		} );

		$scripts = AMP_Theme_Support::get_amp_scripts( array(
			'amp-mustache' => 'https://cdn.ampproject.org/v0/amp-mustache-0.1.js',
			'amp-bind'     => 'https://cdn.ampproject.org/v0/amp-bind-0.1.js',
		) );

		$this->assertEquals(
			'<script async src="https://cdn.ampproject.org/v0.js"></script><script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.1.js"></script><script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script><script async custom-element="amp-video" src="https://cdn.ampproject.org/v0/amp-video-0.1.js"></script>', // phpcs:ignore
			$scripts
		);
	}

	/**
	 * Test intercept_post_request_redirect().
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @covers AMP_Theme_Support::intercept_post_request_redirect()
	 */
	public function test_intercept_post_request_redirect() {
		if ( ! function_exists( 'xdebug_get_headers' ) ) {
			$this->markTestSkipped( 'xdebug is required for this test' );
		}

		add_theme_support( 'amp' );
		$url = get_home_url();

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function () {
			return '__return_false';
		} );

		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( $url );
		$this->assertEquals( '{"success":true}', ob_get_clean() );

		$this->assertContains( 'AMP-Redirect-To: ' . $url, xdebug_get_headers() );
		$this->assertContains( 'Access-Control-Expose-Headers: AMP-Redirect-To', xdebug_get_headers() );

		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '/new-location/' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains( 'AMP-Redirect-To: https://example.org/new-location/', xdebug_get_headers() );

		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '//example.com/new-location/' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$headers = xdebug_get_headers();
		$this->assertContains( 'AMP-Redirect-To: https://example.com/new-location/', $headers );

		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains( 'AMP-Redirect-To: https://example.org', xdebug_get_headers() );
	}

	/**
	 * Test handle_xhr_request().
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @covers AMP_Theme_Support::handle_xhr_request()
	 */
	public function test_handle_xhr_request() {
		global $pagenow;
		if ( ! function_exists( 'xdebug_get_headers' ) ) {
			$this->markTestSkipped( 'xdebug is required for this test' );
		}

		$_GET['__amp_source_origin'] = 'https://example.org';
		$pagenow                     = 'wp-comments-post.php';
		AMP_Theme_Support::purge_amp_query_vars();

		AMP_Theme_Support::handle_xhr_request();
		$this->assertContains( 'AMP-Access-Control-Allow-Source-Origin: https://example.org', xdebug_get_headers() );
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
