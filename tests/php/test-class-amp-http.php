<?php
/**
 * Tests for AMP_HTTP.
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_HTTP.
 *
 * @covers AMP_HTTP
 */
class Test_AMP_HTTP extends TestCase {

	/**
	 * Set up before class.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		AMP_HTTP::$server_timing = true;
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 *
	 * @global WP_Scripts $wp_scripts
	 */
	public function tearDown() {
		parent::tearDown();
		AMP_HTTP::$headers_sent          = [];
		AMP_HTTP::$purged_amp_query_vars = [];
	}

	/**
	 * Test \AMP_HTTP::send_header() when no args are passed.
	 *
	 * @covers \AMP_HTTP::send_header()
	 */
	public function test_send_header_no_args() {
		AMP_HTTP::send_header( 'Foo', 'Bar' );
		$this->assertStringContainsString(
			[
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * Test \AMP_HTTP::send_header() when replace arg is passed.
	 *
	 * @covers \AMP_HTTP::send_header()
	 */
	public function test_send_header_replace_arg() {
		AMP_HTTP::send_header(
			'Foo',
			'Bar',
			[
				'replace' => false,
			]
		);
		$this->assertStringContainsString(
			[
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => false,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * Test \AMP_HTTP::send_header() when status code is passed.
	 *
	 * @covers \AMP_HTTP::send_header()
	 */
	public function test_send_header_status_code() {
		AMP_HTTP::send_header(
			'Foo',
			'Bar',
			[
				'status_code' => 400,
			]
		);
		$this->assertStringContainsString(
			[
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => true,
				'status_code' => 400,
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * Test \AMP_HTTP::send_server_timing() when positive duration passed.
	 *
	 * @covers \AMP_HTTP::send_server_timing()
	 */
	public function test_send_server_timing_positive_duration() {
		$this->setExpectedDeprecated( 'AMP_HTTP::send_server_timing' );
		AMP_HTTP::send_server_timing( 'name', 123, 'Description' );
		$this->assertCount( 1, AMP_HTTP::$headers_sent );
		$this->assertEquals( 'Server-Timing', AMP_HTTP::$headers_sent[0]['name'] );
		$values = preg_split( '/\s*;\s*/', AMP_HTTP::$headers_sent[0]['value'] );
		$this->assertEquals( 'name', $values[0] );
		$this->assertEquals( 'desc="Description"', $values[1] );
		$this->assertStringStartsWith( 'dur=123000.', $values[2] );
		$this->assertFalse( AMP_HTTP::$headers_sent[0]['replace'] );
		$this->assertNull( AMP_HTTP::$headers_sent[0]['status_code'] );
	}

	/**
	 * Test \AMP_HTTP::send_server_timing() when positive duration passed.
	 *
	 * @covers \AMP_HTTP::send_server_timing()
	 */
	public function test_send_server_timing_negative_duration() {
		$this->setExpectedDeprecated( 'AMP_HTTP::send_server_timing' );
		AMP_HTTP::send_server_timing( 'name', -microtime( true ) );
		$this->assertCount( 1, AMP_HTTP::$headers_sent );
		$this->assertEquals( 'Server-Timing', AMP_HTTP::$headers_sent[0]['name'] );
		$values = preg_split( '/\s*;\s*/', AMP_HTTP::$headers_sent[0]['value'] );
		$this->assertEquals( 'name', $values[0] );
		$this->assertStringStartsWith( 'dur=0.', $values[1] );
		$this->assertFalse( AMP_HTTP::$headers_sent[0]['replace'] );
		$this->assertNull( AMP_HTTP::$headers_sent[0]['status_code'] );
	}

	/**
	 * Test purge_amp_query_vars.
	 *
	 * @covers AMP_HTTP::purge_amp_query_vars()
	 */
	public function test_purge_amp_query_vars() {
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		$bad_query_vars = [
			'amp_latest_update_time' => '1517199956',
			'amp_last_check_time'    => '1517599126',
			'__amp_source_origin'    => home_url(),
		];
		$ok_query_vars  = [
			'bar' => 'baz',
		];
		$all_query_vars = array_merge( $bad_query_vars, $ok_query_vars );

		$_SERVER['QUERY_STRING'] = build_query( $all_query_vars );

		remove_action( 'wp', 'amp_maybe_add_actions' );
		$this->go_to( add_query_arg( $all_query_vars, home_url( '/foo/' ) ) );
		$_REQUEST = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		foreach ( $all_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertStringContainsString( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertStringContainsString( "$key=$value", $_SERVER['REQUEST_URI'] );
		}

		AMP_HTTP::$purged_amp_query_vars = [];
		AMP_HTTP::purge_amp_query_vars();
		$this->assertEqualSets( AMP_HTTP::$purged_amp_query_vars, $bad_query_vars );

		foreach ( $bad_query_vars as $key => $value ) {
			$this->assertArrayNotHasKey( $key, $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertArrayNotHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertStringNotContainsString( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertStringNotContainsString( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		foreach ( $ok_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->assertStringContainsString( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertStringContainsString( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification
	}

	/**
	 * Test get_amp_cache_hosts().
	 *
	 * @covers AMP_HTTP::get_amp_cache_hosts()
	 * @covers AMP_HTTP::filter_allowed_redirect_hosts()
	 */
	public function test_get_amp_cache_hosts() {

		// Note that filters are used instead of updating option because of WP_HOME and WP_SITEURL constants.
		add_filter(
			'home_url',
			static function () {
				return 'https://example.com';
			}
		);
		add_filter(
			'site_url',
			static function () {
				return 'https://example.org';
			}
		);

		$hosts = AMP_HTTP::get_amp_cache_hosts();

		$expected = [
			'cdn.ampproject.org',
			'example-org.cdn.ampproject.org',
			'example-org.bing-amp.com',
			'example-com.cdn.ampproject.org',
			'example-com.bing-amp.com',
		];
		$this->assertEqualSets( $expected, $hosts );

		$extra_allowed_redirect_hosts = [
			'example.net',
			'example.info',
		];

		$this->assertEqualSets(
			array_merge( $extra_allowed_redirect_hosts, $expected ),
			AMP_HTTP::filter_allowed_redirect_hosts( $extra_allowed_redirect_hosts )
		);
	}

	/**
	 * Test send_cors_headers().
	 *
	 * @covers AMP_HTTP::send_cors_headers()
	 */
	public function test_send_cors_headers() {

		// Initial case case.
		AMP_HTTP::$headers_sent = [];
		AMP_HTTP::send_cors_headers();
		$this->assertEmpty( AMP_HTTP::$headers_sent );

		// Try an invalid Origin header.
		AMP_HTTP::$headers_sent          = [];
		AMP_HTTP::$purged_amp_query_vars = [];
		$_SERVER['HTTP_ORIGIN']          = 'https://evil.example.com';
		AMP_HTTP::send_cors_headers();
		$this->assertEmpty( AMP_HTTP::$headers_sent );

		// Try an invalid __amp_source_origin.
		AMP_HTTP::$headers_sent          = [];
		AMP_HTTP::$purged_amp_query_vars = [];
		unset( $_SERVER['HTTP_ORIGIN'] );
		$_GET['__amp_source_origin'] = 'https://evil.example.com';
		AMP_HTTP::send_cors_headers();
		$this->assertEmpty( AMP_HTTP::$headers_sent );

		// Try an allowed Origin header.
		AMP_HTTP::$headers_sent          = [];
		AMP_HTTP::$purged_amp_query_vars = [];
		$_SERVER['HTTP_ORIGIN']          = home_url();
		AMP_HTTP::send_cors_headers();
		$this->assertEquals(
			[
				[
					'name'        => 'Access-Control-Allow-Origin',
					'value'       => home_url(),
					'replace'     => false,
					'status_code' => null,
				],
				[
					'name'        => 'Access-Control-Allow-Credentials',
					'value'       => 'true',
					'replace'     => true,
					'status_code' => null,
				],
				[
					'name'        => 'Vary',
					'value'       => 'Origin',
					'replace'     => false,
					'status_code' => null,
				],
			],
			AMP_HTTP::$headers_sent
		);

		// The __amp_source_origin is specified but the Origin header is not.
		AMP_HTTP::$headers_sent      = [];
		$_GET['__amp_source_origin'] = 'https://cdn.ampproject.org';
		$_SERVER['REQUEST_METHOD']   = 'POST';
		unset( $_SERVER['HTTP_ORIGIN'] );
		AMP_HTTP::purge_amp_query_vars();
		AMP_HTTP::send_cors_headers();
		$this->assertEquals(
			[
				[
					'name'        => 'Access-Control-Allow-Origin',
					'value'       => 'https://cdn.ampproject.org',
					'replace'     => false,
					'status_code' => null,
				],
				[
					'name'        => 'Access-Control-Allow-Credentials',
					'value'       => 'true',
					'replace'     => true,
					'status_code' => null,
				],
				[
					'name'        => 'Vary',
					'value'       => 'Origin',
					'replace'     => false,
					'status_code' => null,
				],
				[
					'name'        => 'AMP-Access-Control-Allow-Source-Origin',
					'value'       => 'https://cdn.ampproject.org',
					'replace'     => true,
					'status_code' => null,
				],
				[
					'name'        => 'Access-Control-Expose-Headers',
					'value'       => 'AMP-Access-Control-Allow-Source-Origin',
					'replace'     => false,
					'status_code' => null,
				],
			],
			AMP_HTTP::$headers_sent
		);

		// The Origin header and the __amp_source_origin are both specified.
		AMP_HTTP::$headers_sent      = [];
		$_GET['__amp_source_origin'] = home_url();
		$_SERVER['REQUEST_METHOD']   = 'POST';
		$_SERVER['HTTP_ORIGIN']      = 'https://cdn.ampproject.org';
		AMP_HTTP::purge_amp_query_vars();
		AMP_HTTP::send_cors_headers();
		$this->assertEquals(
			[
				[
					'name'        => 'Access-Control-Allow-Origin',
					'value'       => 'https://cdn.ampproject.org',
					'replace'     => false,
					'status_code' => null,
				],
				[
					'name'        => 'Access-Control-Allow-Credentials',
					'value'       => 'true',
					'replace'     => true,
					'status_code' => null,
				],
				[
					'name'        => 'Vary',
					'value'       => 'Origin',
					'replace'     => false,
					'status_code' => null,
				],
				[
					'name'        => 'AMP-Access-Control-Allow-Source-Origin',
					'value'       => home_url(),
					'replace'     => true,
					'status_code' => null,
				],
				[
					'name'        => 'Access-Control-Expose-Headers',
					'value'       => 'AMP-Access-Control-Allow-Source-Origin',
					'replace'     => false,
					'status_code' => null,
				],
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * Test handle_xhr_request().
	 *
	 * @covers AMP_HTTP::handle_xhr_request()
	 */
	public function test_handle_xhr_request() {
		$_GET[ AMP_HTTP::ACTION_XHR_CONVERTED_QUERY_VAR ] = 1;
		$_SERVER['REQUEST_METHOD']                        = 'POST';
		AMP_HTTP::purge_amp_query_vars();
		AMP_HTTP::handle_xhr_request();
		$this->assertEquals( PHP_INT_MAX, has_filter( 'wp_redirect', [ AMP_HTTP::class, 'intercept_post_request_redirect' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'comment_post_redirect', [ AMP_HTTP::class, 'filter_comment_post_redirect' ] ) );
		$this->assertEquals(
			[ AMP_HTTP::class, 'handle_wp_die' ],
			apply_filters( 'wp_die_handler', '__return_true' )
		);
	}

	/**
	 * Test intercept_post_request_redirect().
	 *
	 * @covers AMP_HTTP::intercept_post_request_redirect()
	 */
	public function test_intercept_post_request_redirect() {

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$url = home_url( '', 'https' ) . ':443/?test=true#test';

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function () {
				return '__return_false';
			}
		);

		$redirecting_json = wp_json_encode(
			[
				'message'     => __( 'Redirecting…', 'amp' ),
				'redirecting' => true,
			]
		);

		// Test redirecting to full URL with HTTPS protocol.
		AMP_HTTP::$headers_sent = [];
		ob_start();
		AMP_HTTP::intercept_post_request_redirect( $url );
		$this->assertEquals( $redirecting_json, ob_get_clean() );
		$this->assertStringContainsString(
			[
				'name'        => 'AMP-Redirect-To',
				'value'       => $url,
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
		$this->assertStringContainsString(
			[
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => false,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);

		// Test redirecting to non-HTTPS URL.
		AMP_HTTP::$headers_sent = [];
		ob_start();
		$url = home_url( '/', 'http' );
		AMP_HTTP::intercept_post_request_redirect( $url );
		$this->assertEquals( $redirecting_json, ob_get_clean() );
		$this->assertStringContainsString(
			[
				'name'        => 'AMP-Redirect-To',
				'value'       => preg_replace( '#^\w+:#', '', $url ),
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
		$this->assertStringContainsString(
			[
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => false,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);

		// Test redirecting to host-less location.
		AMP_HTTP::$headers_sent = [];
		ob_start();
		AMP_HTTP::intercept_post_request_redirect( '/new-location/' );
		$this->assertEquals( $redirecting_json, ob_get_clean() );
		$this->assertStringContainsString(
			[
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);

		// Test redirecting to scheme-less location.
		AMP_HTTP::$headers_sent = [];
		ob_start();
		$url = home_url( '/new-location/' );
		AMP_HTTP::intercept_post_request_redirect( substr( $url, strpos( $url, ':' ) + 1 ) );
		$this->assertEquals( $redirecting_json, ob_get_clean() );
		$this->assertStringContainsString(
			[
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);

		// Test redirecting to empty location.
		AMP_HTTP::$headers_sent = [];
		ob_start();
		AMP_HTTP::intercept_post_request_redirect( '' );
		$this->assertEquals( $redirecting_json, ob_get_clean() );
		$this->assertStringContainsString(
			[
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url(), 'https' ),
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * Test handle_wp_die().
	 *
	 * @covers AMP_HTTP::handle_wp_die()
	 */
	public function test_handle_wp_die() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function() {
				return '__return_null';
			}
		);

		ob_start();
		AMP_HTTP::handle_wp_die( 'string' );
		$this->assertEquals( '{"message":"string"}', ob_get_clean() );

		ob_start();
		$error = new WP_Error( 'code', 'The Message' );
		AMP_HTTP::handle_wp_die( $error );
		$this->assertEquals( '{"message":"The Message"}', ob_get_clean() );
	}

	/**
	 * Test filter_comment_post_redirect().
	 *
	 * @covers AMP_HTTP::filter_comment_post_redirect()
	 */
	public function test_filter_comment_post_redirect() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function() {
				return '__return_null';
			}
		);

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$post    = self::factory()->post->create_and_get();
		$comment = self::factory()->comment->create_and_get(
			[
				'comment_post_ID' => $post->ID,
			]
		);
		$url     = get_comment_link( $comment );

		// Test without comments_live_list.
		$filtered_url = AMP_HTTP::filter_comment_post_redirect( $url, $comment );
		$this->assertNotEquals(
			strtok( $url, '#' ),
			strtok( $filtered_url, '#' )
		);

		// Test with comments_live_list.
		add_theme_support(
			AMP_Theme_Support::SLUG,
			[
				'comments_live_list' => true,
			]
		);
		add_filter(
			'amp_comment_posted_message',
			static function( $message, WP_Comment $filter_comment ) {
				return sprintf( '(comment=%d,approved=%d)', $filter_comment->comment_ID, $filter_comment->comment_approved );
			},
			10,
			2
		);

		// Test approved comment.
		$comment->comment_approved = '1';
		ob_start();
		AMP_HTTP::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=1)', $comment->comment_ID ),
			$response['message']
		);

		// Test moderated comment.
		$comment->comment_approved = '0';
		ob_start();
		AMP_HTTP::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=0)', $comment->comment_ID ),
			$response['message']
		);
	}

	/**
	 * Test get_response_content_type().
	 *
	 * @covers \AMP_HTTP::get_response_content_type()
	 */
	public function test_get_response_content_type() {
		$this->assertSame( ini_get( 'default_mimetype' ), AMP_HTTP::get_response_content_type() );
	}
}
