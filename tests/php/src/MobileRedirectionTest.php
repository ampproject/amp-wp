<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PairedRouting;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\MobileRedirection;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AMP_Options_Manager;
use AMP_Theme_Support;
use WP_Customize_Manager;
use AMP_HTTP;

/** @coversDefaultClass \AmpProject\AmpWP\MobileRedirection */
final class MobileRedirectionTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility;

	/** @var MobileRedirection */
	private $instance;

	/** @var PairedRouting */
	private $paired_routing;

	public function setUp() {
		parent::setUp();
		$this->paired_routing = $this->injector->make( PairedRouting::class );
		$this->instance       = new MobileRedirection( $this->paired_routing );
	}

	public function tearDown() {
		parent::tearDown();
		$_COOKIE = [];
		unset( $GLOBALS['wp_customize'] );
		AMP_HTTP::$purged_amp_query_vars = [];
		$GLOBALS['wp_the_query']         = $GLOBALS['wp_query']; // This is missing in core.
	}

	public function test__construct() {
		$this->assertInstanceOf( MobileRedirection::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register_legacy_reader_mode() {
		AMP_Options_Manager::update_options(
			[
				Option::MOBILE_REDIRECT => true,
				Option::THEME_SUPPORT   => AMP_Theme_Support::READER_MODE_SLUG,
				Option::READER_THEME    => ReaderThemes::DEFAULT_READER_THEME,
			]
		);
		$this->instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );
		$this->assertSame( PHP_INT_MAX, has_action( 'template_redirect', [ $this->instance, 'redirect' ] ) );
		$this->assertSame( 0, has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertSame( 10, has_filter( 'comment_post_redirect', [ $this->instance, 'filter_comment_post_redirect' ] ) );

		$this->assertTrue( amp_is_legacy() );
		$this->assertSame( 10, has_filter( 'get_comments_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
		$this->assertSame( 10, has_filter( 'respond_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
	}

	/** @covers ::register() */
	public function test_register_transitional_mode() {
		AMP_Options_Manager::update_options(
			[
				Option::MOBILE_REDIRECT => true,
				Option::THEME_SUPPORT   => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			]
		);
		$this->instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );
		$this->assertSame( PHP_INT_MAX, has_action( 'template_redirect', [ $this->instance, 'redirect' ] ) );
		$this->assertSame( 0, has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertSame( 10, has_filter( 'comment_post_redirect', [ $this->instance, 'filter_comment_post_redirect' ] ) );

		$this->assertFalse( amp_is_legacy() );
		$this->assertFalse( has_filter( 'get_comments_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
		$this->assertFalse( has_filter( 'respond_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
	}

	/** @covers ::register() */
	public function test_register_not_enabled() {
		AMP_Options_Manager::update_option( Option::MOBILE_REDIRECT, false );
		$this->instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );
		$this->assert_hooks_not_added();
	}

	/** @covers ::register() */
	public function test_register_enabled_but_standard_mode() {
		AMP_Options_Manager::update_options(
			[
				Option::MOBILE_REDIRECT => true,
				Option::THEME_SUPPORT   => AMP_Theme_Support::STANDARD_MODE_SLUG,
			]
		);
		$this->instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );
		$this->assert_hooks_not_added();
	}

	/**
	 * Assert the service hooks were not added.
	 */
	private function assert_hooks_not_added() {
		$this->assertFalse( has_action( 'template_redirect', [ $this->instance, 'redirect' ] ) );
		$this->assertFalse( has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertFalse( has_filter( 'comment_post_redirect', [ $this->instance, 'filter_comment_post_redirect' ] ) );
		$this->assertFalse( has_filter( 'get_comments_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
		$this->assertFalse( has_filter( 'respond_link', [ $this->instance, 'add_noamp_mobile_query_var' ] ) );
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->instance->register();
		$this->assertEquals(
			[
				'foo'                   => 'bar',
				Option::MOBILE_REDIRECT => false,
			],
			$this->instance->filter_default_options( [ 'foo' => 'bar' ] )
		);
	}

	/** @covers ::sanitize_options() */
	public function test_sanitize_options() {
		$this->assertEquals(
			[
				'foo' => 'bar',
			],
			$this->instance->sanitize_options(
				[ 'foo' => 'bar' ],
				[]
			)
		);

		$this->assertEquals(
			[ Option::MOBILE_REDIRECT => true ],
			$this->instance->sanitize_options(
				[],
				[ Option::MOBILE_REDIRECT => 'on' ]
			)
		);

		$this->assertEquals(
			[ Option::MOBILE_REDIRECT => true ],
			$this->instance->sanitize_options(
				[],
				[ Option::MOBILE_REDIRECT => 'true' ]
			)
		);

		$this->assertEquals(
			[ Option::MOBILE_REDIRECT => false ],
			$this->instance->sanitize_options(
				[ Option::MOBILE_REDIRECT => true ],
				[ Option::MOBILE_REDIRECT => false ]
			)
		);

		$this->assertEquals(
			[ Option::MOBILE_REDIRECT => false ],
			$this->instance->sanitize_options(
				[ Option::MOBILE_REDIRECT => true ],
				[ Option::MOBILE_REDIRECT => 'false' ]
			)
		);
	}

	/** @covers ::get_current_amp_url() */
	public function test_get_current_amp_url() {
		$this->go_to( add_query_arg( QueryVar::NOAMP, QueryVar::NOAMP_MOBILE, '/foo/' ) );
		$this->assertEquals(
			$this->paired_routing->add_endpoint( home_url( '/foo/' ) ),
			$this->instance->get_current_amp_url()
		);
	}

	/** @covers ::redirect() */
	public function test_redirect_on_canonical_and_available() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( '/' );
		$this->assertTrue( amp_is_canonical() );
		$this->assertTrue( amp_is_available() );
		$this->instance->redirect();
		$this->assertFalse( has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_on_canonical_and_not_available() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ 'is_author' ] );
		$this->go_to( '/' );
		$this->assertTrue( amp_is_canonical() );
		$this->assertFalse( amp_is_available() );
		$this->instance->redirect();
		$this->assertFalse( has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_on_transitional_and_not_available() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, [ 'is_author' ] );
		$this->go_to( '/' );
		$this->assertFalse( amp_is_canonical() );
		$this->assertFalse( amp_is_available() );
		$this->instance->redirect();
		$this->assertFalse( has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_on_transitional_and_available_and_client_side_on_amp_endpoint() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );

		$this->go_to( '/' );
		set_query_var( QueryVar::AMP, '1' );
		$this->assertFalse( amp_is_canonical() );
		$this->assertTrue( amp_is_available() );
		$this->assertTrue( amp_is_request() );
		$this->instance->redirect();
		$this->assertEquals( 10, has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );

		$this->assertEquals( 0, has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertEquals( 100, has_filter( 'amp_to_amp_linking_element_excluded', [ $this->instance, 'filter_amp_to_amp_linking_element_excluded' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_to_amp_linking_element_query_vars', [ $this->instance, 'filter_amp_to_amp_linking_element_query_vars' ] ) );

		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_when_server_side_and_not_applicable() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		add_filter( 'amp_pre_is_mobile', '__return_false' );

		$this->go_to( '/' );

		$this->assertFalse( amp_is_request() );
		$this->assertFalse( $this->instance->is_mobile_request() );

		$this->instance->redirect();
		$this->assertFalse( has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_not_amp_endpoint_with_client_side_redirection() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );

		$this->go_to( '/' );
		$this->assertFalse( amp_is_request() );
		$this->assertTrue( amp_is_available() );
		$this->instance->redirect();
		$this->assertEquals( 10, has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
		$this->assertEquals( 10, has_action( 'wp_head', [ $this->instance, 'add_mobile_alternative_link' ] ) );
		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_not_amp_endpoint_with_server_side_redirection_on_mobile() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		add_filter( 'amp_pre_is_mobile', '__return_true' );

		$this->go_to( '/' );
		$this->assertFalse( amp_is_request() );
		$this->assertTrue( amp_is_available() );
		$redirected_url = null;
		add_filter(
			'wp_redirect',
			static function ( $redirect_url ) use ( &$redirected_url ) {
				$redirected_url = $redirect_url;
				return false;
			}
		);
		$this->instance->redirect();
		$this->assertNotNull( $redirected_url );
		$this->assertEquals(
			$this->paired_routing->add_endpoint( home_url( '/' ) ),
			$redirected_url
		);
	}

	/** @covers ::redirect() */
	public function test_redirect_not_amp_endpoint_with_server_side_redirection_on_mobile_when_cookie_set() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		add_filter( 'amp_pre_is_mobile', '__return_true' );

		$this->go_to( '/' );
		$this->assertFalse( amp_is_request() );
		$this->assertTrue( amp_is_available() );
		$_COOKIE[ MobileRedirection::DISABLED_STORAGE_KEY ] = '1';
		$this->instance->redirect();

		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_not_amp_endpoint_with_server_side_redirection_on_mobile_when_noamp_query_var_present() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		add_filter( 'amp_pre_is_mobile', '__return_true' );

		$this->go_to( add_query_arg( QueryVar::NOAMP, QueryVar::NOAMP_MOBILE, '/' ) );
		$_GET[ QueryVar::NOAMP ] = QueryVar::NOAMP_MOBILE;
		$this->assertFalse( amp_is_request() );
		$this->assertTrue( amp_is_available() );

		$this->assertArrayNotHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );
		$this->instance->redirect();
		$this->assertArrayHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );

		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
	}

	/** @covers ::redirect() */
	public function test_redirect_on_transitional_and_available_and_server_side_on_amp_endpoint_with_cookie_set() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		add_filter( 'amp_pre_is_mobile', '__return_true' );

		$this->go_to( '/' );
		set_query_var( QueryVar::AMP, '1' );
		$this->assertFalse( amp_is_canonical() );
		$this->assertTrue( amp_is_available() );
		$this->assertTrue( amp_is_request() );
		$_COOKIE[ MobileRedirection::DISABLED_STORAGE_KEY ] = '1';
		$this->instance->redirect();
		$this->assertArrayNotHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );
		$this->assertEquals( 10, has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );

		$this->assertEquals( 0, has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertEquals( 100, has_filter( 'amp_to_amp_linking_element_excluded', [ $this->instance, 'filter_amp_to_amp_linking_element_excluded' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_to_amp_linking_element_query_vars', [ $this->instance, 'filter_amp_to_amp_linking_element_query_vars' ] ) );

		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
	}

	/** @covers ::filter_amp_to_amp_linking_element_excluded() */
	public function test_filter_amp_to_amp_linking_element_excluded() {
		$home_url_without_noamp = home_url( '/' );
		$home_url_with_noamp    = add_query_arg( QueryVar::NOAMP, QueryVar::NOAMP_MOBILE, home_url( '/' ) );

		$this->assertEquals( true, $this->instance->filter_amp_to_amp_linking_element_excluded( true, $home_url_without_noamp ) );
		$this->assertEquals( true, $this->instance->filter_amp_to_amp_linking_element_excluded( true, $home_url_with_noamp ) );
		$this->assertEquals( false, $this->instance->filter_amp_to_amp_linking_element_excluded( false, $home_url_without_noamp ) );
		$this->assertEquals( true, $this->instance->filter_amp_to_amp_linking_element_excluded( false, $home_url_with_noamp ) );
	}

	/** @covers ::filter_amp_to_amp_linking_element_query_vars() */
	public function test_filter_amp_to_amp_linking_element_query_vars() {
		$this->assertEquals(
			[ 'foo' => 'bar' ],
			$this->instance->filter_amp_to_amp_linking_element_query_vars( [ 'foo' => 'bar' ], false )
		);
		$this->assertEquals(
			[
				'foo'           => 'bar',
				QueryVar::NOAMP => QueryVar::NOAMP_MOBILE,
			],
			$this->instance->filter_amp_to_amp_linking_element_query_vars( [ 'foo' => 'bar' ], true )
		);
	}

	/** @covers ::is_mobile_request() */
	public function test_is_mobile_request() {
		unset( $_SERVER['HTTP_USER_AGENT'] );
		$this->assertFalse( $this->instance->is_mobile_request() );

		add_filter( 'amp_pre_is_mobile', '__return_true', 10 );
		$this->assertTrue( $this->instance->is_mobile_request() );

		add_filter( 'amp_pre_is_mobile', '__return_false', 20 );
		$this->assertFalse( $this->instance->is_mobile_request() );

		remove_all_filters( 'amp_pre_is_mobile' );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Mobile Safari/537.36';
		$this->assertTrue( $this->instance->is_mobile_request() );

		$_SERVER['HTTP_USER_AGENT'] = 'Wristwatch';
		$this->assertFalse( $this->instance->is_mobile_request() );

		add_filter(
			'amp_mobile_user_agents',
			function ( $user_agents ) {
				return array_merge( $user_agents, [ 'watch' ] );
			}
		);

		$this->assertTrue( $this->instance->is_mobile_request() );

		$_SERVER['HTTP_USER_AGENT'] = 'Eyeglasses';
		$this->assertFalse( $this->instance->is_mobile_request() );

		add_filter(
			'amp_mobile_user_agents',
			function ( $user_agents ) {
				return array_merge( $user_agents, [ '/eyeglass/i' ] );
			}
		);

		$this->assertTrue( $this->instance->is_mobile_request() );
	}

	/** @covers ::is_using_client_side_redirection() */
	public function test_is_using_client_side_redirection() {
		$this->assertTrue( $this->instance->is_using_client_side_redirection() );

		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		$this->assertFalse( $this->instance->is_using_client_side_redirection() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		add_filter( 'amp_dev_mode_enabled', '__return_true' );
		$this->assertTrue( $this->instance->is_using_client_side_redirection() );
		remove_filter( 'amp_dev_mode_enabled', '__return_true' );

		$this->assertFalse( $this->instance->is_using_client_side_redirection() );
		global $wp_customize;
		require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->start_previewing_theme();
		$this->assertTrue( $this->instance->is_using_client_side_redirection() );
	}

	/** @covers ::get_mobile_user_agents() */
	public function test_get_mobile_user_agents() {
		$this->assertContains( 'Mobile', $this->instance->get_mobile_user_agents() );
		$this->assertNotContains( 'Watch', $this->instance->get_mobile_user_agents() );
		add_filter(
			'amp_mobile_user_agents',
			function ( $user_agents ) {
				return array_merge( $user_agents, [ 'Watch' ] );
			}
		);
		$this->assertContains( 'Watch', $this->instance->get_mobile_user_agents() );
	}

	/** @covers ::is_redirection_disabled_via_query_param() */
	public function test_is_redirection_disabled_via_query_param() {
		$this->assertFalse( $this->instance->is_redirection_disabled_via_query_param() );

		$_GET[ QueryVar::NOAMP ] = QueryVar::NOAMP_MOBILE;
		$this->assertTrue( $this->instance->is_redirection_disabled_via_query_param() );
	}

	/** @covers ::is_redirection_disabled_via_cookie() */
	public function test_is_redirection_disabled_via_cookie() {
		$this->assertFalse( $this->instance->is_redirection_disabled_via_cookie() );
		$_COOKIE[ MobileRedirection::DISABLED_STORAGE_KEY ] = '1';
		$this->assertTrue( $this->instance->is_redirection_disabled_via_cookie() );
	}

	/** @covers ::set_mobile_redirection_disabled_cookie() */
	public function test_set_mobile_redirection_disabled_cookie() {
		$this->assertArrayNotHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );
		$this->instance->set_mobile_redirection_disabled_cookie( true );
		$this->assertArrayHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );
		$this->instance->set_mobile_redirection_disabled_cookie( false );
		$this->assertArrayNotHasKey( MobileRedirection::DISABLED_STORAGE_KEY, $_COOKIE );
	}

	/**
	 * @covers ::add_mobile_redirect_script()
	 * @covers ::get_inline_script_tag()
	 * @covers ::sanitize_script_attributes()
	 */
	public function test_add_mobile_redirect_script() {
		ob_start();
		$this->instance->add_mobile_redirect_script();
		$output = ob_get_clean();
		$this->assertStringContains( '<script type="text/javascript">', $output );
		$this->assertStringContains( 'noampQueryVarName', $output );

		add_filter(
			'wp_inline_script_attributes',
			function ( $attributes, $source ) {
				if ( false !== strpos( $source, 'amp_mobile_redirect_disabled' ) ) {
					$attributes['data-cfasync'] = 'false';
				}
				return $attributes;
			},
			10,
			2
		);
		ob_start();
		$this->instance->add_mobile_redirect_script();
		$output = ob_get_clean();
		$this->assertRegExp( '#<script\b[^>]*? data-cfasync="false"[^>]*>#', $output );
		$this->assertStringContains( 'noampQueryVarName', $output );
	}

	/** @covers ::filter_comment_post_redirect() */
	public function test_filter_comment_post_redirect() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );

		$post_id      = self::factory()->post->create();
		$comment_link = get_permalink( $post_id ) . '#comment-123';

		$this->assertEquals( $comment_link, $this->instance->filter_comment_post_redirect( $comment_link ) );

		AMP_HTTP::$purged_amp_query_vars[ AMP_HTTP::ACTION_XHR_CONVERTED_QUERY_VAR ] = 1;

		$filtered_comment_link = $this->instance->filter_comment_post_redirect( $comment_link );
		$this->assertNotEquals( $comment_link, $filtered_comment_link );
		$this->assertStringContains( QueryVar::AMP . '=1', $filtered_comment_link );

		$external_url = 'https://external.example.com/';
		$this->assertEquals( $external_url, $this->instance->filter_comment_post_redirect( $external_url ) );
	}

	/** @covers ::add_noamp_mobile_query_var() */
	public function test_add_noamp_mobile_query_var() {
		$post_id       = self::factory()->post->create();
		$comments_link = get_comments_link( $post_id );

		$this->assertStringEndsWith(
			QueryVar::NOAMP . '=' . QueryVar::NOAMP_MOBILE . '#respond',
			$this->instance->add_noamp_mobile_query_var( $comments_link )
		);
	}

	/** @covers ::add_mobile_alternative_link() */
	public function test_add_mobile_alternative_link() {
		ob_start();
		$this->instance->add_mobile_alternative_link();
		$output = ob_get_clean();

		$this->assertStringStartsWith( '<link rel="alternate" type="text/html" media="only screen and (max-width: 640px)"', $output );
	}

	/** @covers ::add_mobile_version_switcher_styles() */
	public function test_add_mobile_version_switcher_styles() {
		ob_start();
		$this->instance->add_mobile_version_switcher_styles();
		$output = ob_get_clean();
		$this->assertStringStartsWith( '<style>', $output );
		$this->assertStringContains( '#amp-mobile-version-switcher', $output );
		$this->assertStringNotContains( 'body.lock-scrolling > #amp-mobile-version-switcher', $output );

		add_filter(
			'template',
			static function () {
				return 'twentytwentyone';
			}
		);
		ob_start();
		$this->instance->add_mobile_version_switcher_styles();
		$output = ob_get_clean();
		$this->assertStringContains( 'body.lock-scrolling > #amp-mobile-version-switcher', $output );

		add_filter( 'amp_mobile_version_switcher_styles_used', '__return_false' );
		ob_start();
		$this->instance->add_mobile_version_switcher_styles();
		$output = ob_get_clean();
		$this->assertEmpty( $output );
	}

	/**
	 * Get data to test add_mobile_version_switcher_link.
	 *
	 * @return array
	 */
	public function get_test_data_for_add_mobile_version_switcher() {
		return [
			'amp'    => [
				true,
				'noamphtml nofollow',
			],
			'nonamp' => [
				false,
				'amphtml',
			],
		];
	}

	/**
	 * @dataProvider get_test_data_for_add_mobile_version_switcher
	 * @covers ::add_mobile_version_switcher_link()
	 *
	 * @param bool   $is_amp   Is AMP.
	 * @param string $link_rel Expected link relations.
	 */
	public function test_add_mobile_version_switcher( $is_amp, $link_rel ) {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( '/' );
		if ( $is_amp ) {
			set_query_var( QueryVar::AMP, '1' );
		}
		$this->assertEquals( $is_amp, amp_is_request() );
		ob_start();
		$this->instance->add_mobile_version_switcher_link();
		$output = ob_get_clean();
		$this->assertStringContains( 'rel="' . $link_rel . '"', $output );
		$this->assertStringContains( 'amp-mobile-version-switcher', $output );
		$this->assertStringNotContains( '<script data-ampdevmode>', $output );

		add_filter(
			'amp_mobile_version_switcher_link_text',
			static function ( $link_text ) {
				return $link_text . ' ' . ( amp_is_request() ? '(non-AMP version)' : '(AMP version)' );
			}
		);

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		add_filter( 'amp_dev_mode_enabled', '__return_true' );
		ob_start();
		$this->instance->add_mobile_version_switcher_link();
		$output = ob_get_clean();
		$this->assertStringContains( amp_is_request() ? '(non-AMP version)' : '(AMP version)', $output );
		$this->assertStringContains( '<script data-ampdevmode>', $output );
		$this->assertStringContains( 'notApplicableMessage', $output );
	}
}
