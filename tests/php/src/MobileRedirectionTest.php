<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PairedRouting;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\MobileRedirection;
use AmpProject\AmpWP\Services;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_Customize_Manager;
use AMP_HTTP;

/** @coversDefaultClass \AmpProject\AmpWP\MobileRedirection */
final class MobileRedirectionTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/** @var MobileRedirection */
	private $instance;

	/** @var PairedRouting */
	private $paired_routing;

	public function set_up() {
		parent::set_up();
		$this->paired_routing = $this->injector->make( PairedRouting::class );
		$this->instance       = new MobileRedirection( $this->paired_routing );
	}

	public function tear_down() {
		$_COOKIE = [];
		unset( $GLOBALS['wp_customize'] );
		AMP_HTTP::$purged_amp_query_vars = [];
		$GLOBALS['wp_the_query']         = $GLOBALS['wp_query']; // This is missing in core.
		parent::tear_down();
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
		remove_all_filters( 'amp_to_amp_linking_enabled' );
		AMP_Options_Manager::update_option( Option::MOBILE_REDIRECT, false );
		$instance = new MobileRedirection( $this->paired_routing );
		$instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $instance, 'sanitize_options' ] ) );
		$this->assert_hooks_not_added( $instance );
	}

	/** @covers ::register() */
	public function test_register_enabled_but_standard_mode() {
		remove_all_filters( 'amp_to_amp_linking_enabled' );
		AMP_Options_Manager::update_options(
			[
				Option::MOBILE_REDIRECT => true,
				Option::THEME_SUPPORT   => AMP_Theme_Support::STANDARD_MODE_SLUG,
			]
		);
		$instance = new MobileRedirection( $this->paired_routing );
		$instance->register();
		$this->assertSame( 10, has_filter( 'amp_default_options', [ $instance, 'filter_default_options' ] ) );
		$this->assertSame( 10, has_filter( 'amp_options_updating', [ $instance, 'sanitize_options' ] ) );
		$this->assert_hooks_not_added( $instance );
	}

	/**
	 * Get data for test_add_mobile_alternate_link
	 *
	 * @return array
	 */
	public function get_add_mobile_alternate_link() {
		return [
			'mobile_redirection_enabled'                   => [
				[
					Option::MOBILE_REDIRECT => true,
				],
				10,
			],
			'mobile_redirection_enabled_in_canonical_mode' => [
				[
					Option::MOBILE_REDIRECT => true,
					Option::THEME_SUPPORT   => AMP_Theme_Support::STANDARD_MODE_SLUG,
				],
				false,
			],
			'sandboxing_set_to_loose'                      => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => true,
					Option::SANDBOXING_LEVEL   => 1,
				],
				10,
			],
			'sandboxing_set_to_loose_in_canonical_mode'    => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => true,
					Option::SANDBOXING_LEVEL   => 1,
					Option::THEME_SUPPORT      => AMP_Theme_Support::STANDARD_MODE_SLUG,
				],
				false,
			],
			'sandboxing_set_to_moderate'                   => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => true,
					Option::SANDBOXING_LEVEL   => 2,
				],
				10,
			],
			'sandboxing_set_to_moderate_in_canonical_mode' => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => true,
					Option::SANDBOXING_LEVEL   => 2,
					Option::THEME_SUPPORT      => AMP_Theme_Support::STANDARD_MODE_SLUG,
				],
				false,
			],
			'sandboxing_set_to_strict'                     => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => true,
					Option::SANDBOXING_LEVEL   => 3,
				],
				false,
			],
			'sandboxing_and_mobile_redirection_disabled'   => [
				[
					Option::MOBILE_REDIRECT    => false,
					Option::SANDBOXING_ENABLED => false,
				],
				false,
			],
		];
	}

	/**
	 * @dataProvider get_add_mobile_alternate_link
	 *
	 * Test action which adds mobile alternative link to head if:
	 * - mobile redirection is enabled.
	 * - sandboxing level is set to Loose or Moderate.
	 *
	 * @covers ::register()
	 *
	 * @param array $options AMP options.
	 * @param bool|int  $expected Expected result.
	 */
	public function test_add_mobile_alternate_link( $options, $expected ) {
		AMP_Options_Manager::update_options( $options );

		$this->instance->register();

		$this->assertSame( $expected, has_action( 'wp_head', [ $this->instance, 'add_mobile_alternative_link' ] ) );
	}

	/**
	 * Assert the service hooks were not added.
	 *
	 * @param MobileRedirection $instance
	 */
	private function assert_hooks_not_added( MobileRedirection $instance ) {
		$this->assertFalse( has_action( 'template_redirect', [ $instance, 'redirect' ] ) );
		$this->assertFalse( has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertFalse( has_filter( 'comment_post_redirect', [ $instance, 'filter_comment_post_redirect' ] ) );
		$this->assertFalse( has_filter( 'get_comments_link', [ $instance, 'add_noamp_mobile_query_var' ] ) );
		$this->assertFalse( has_filter( 'respond_link', [ $instance, 'add_noamp_mobile_query_var' ] ) );
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->instance->register();
		$this->assertEquals(
			[
				'foo'                   => 'bar',
				Option::MOBILE_REDIRECT => true,
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

	/**
	 * @covers ::add_mobile_switcher_head_hooks()
	 * @covers ::add_mobile_switcher_footer_hooks()
	 * @covers ::add_a2a_linking_hooks()
	 */
	public function test_add_mobile_switcher_hooks() {
		$this->call_private_method( $this->instance, 'add_mobile_switcher_head_hooks' );
		$this->assertEquals( 10, has_action( 'wp_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_head', [ $this->instance, 'add_mobile_version_switcher_styles' ] ) );

		$this->call_private_method( $this->instance, 'add_mobile_switcher_footer_hooks' );
		$this->assertEquals( 10, has_action( 'wp_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );
		$this->assertEquals( 10, has_action( 'amp_post_template_footer', [ $this->instance, 'add_mobile_version_switcher_link' ] ) );

		$this->call_private_method( $this->instance, 'add_a2a_linking_hooks' );
		$this->assertEquals( 0, has_filter( 'amp_to_amp_linking_enabled', '__return_true' ) );
		$this->assertEquals( 100, has_filter( 'amp_to_amp_linking_element_excluded', [ $this->instance, 'filter_amp_to_amp_linking_element_excluded' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_to_amp_linking_element_query_vars', [ $this->instance, 'filter_amp_to_amp_linking_element_query_vars' ] ) );
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
	public function test_is_using_client_side_redirection_in_customizer_preview() {
		$this->assertTrue( $this->instance->is_using_client_side_redirection() );

		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		$this->assertFalse( $this->instance->is_using_client_side_redirection() );

		$this->assertFalse( $this->instance->is_using_client_side_redirection() );
		$this->init_customizer_preview();
		$this->assertTrue( $this->instance->is_using_client_side_redirection(), 'Expected client-side redirection to be enforced because in Customizer preview.' );
	}

	/** @covers ::is_using_client_side_redirection() */
	public function test_is_using_client_side_redirection_paired_browsing_active() {
		if ( ! Services::get( 'dependency_support' )->has_support() ) {
			$this->markTestSkipped( 'Paired browsing is not available in the current environment.' );
		}

		$this->assertTrue( $this->instance->is_using_client_side_redirection() );
		add_filter( 'amp_mobile_client_side_redirection', '__return_false' );
		$this->assertFalse( $this->instance->is_using_client_side_redirection() );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->register_paired_browsing_service();

		$this->assertTrue( $this->instance->is_using_client_side_redirection(), 'Expected client-side redirection to be enforced because (possibly) in paired browsing.' );
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
		$this->assertStringContainsString( '<script type="text/javascript">', $output );
		$this->assertStringContainsString( 'noampQueryVarName', $output );

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
		$this->assertMatchesRegularExpression( '#<script\b[^>]*? data-cfasync="false"[^>]*>#', $output );
		$this->assertStringContainsString( 'noampQueryVarName', $output );
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
		$this->assertStringContainsString( QueryVar::AMP . '=1', $filtered_comment_link );

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
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( '/' );
		ob_start();
		$this->instance->add_mobile_alternative_link();
		$output = ob_get_clean();
		$this->assertTrue( amp_is_request() );
		$this->assertEmpty( $output );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( '/' );
		ob_start();
		$this->instance->add_mobile_alternative_link();
		$output = ob_get_clean();
		$this->assertFalse( amp_is_request() );
		$this->assertStringStartsWith( '<link rel="alternate" type="text/html" media="only screen and (max-width: 640px)"', $output );
	}

	/** @covers ::add_mobile_version_switcher_styles() */
	public function test_add_mobile_version_switcher_styles() {
		ob_start();
		$this->instance->add_mobile_version_switcher_styles();
		$output = ob_get_clean();
		$this->assertStringStartsWith( '<style>', $output );
		$this->assertStringContainsString( '#amp-mobile-version-switcher', $output );
		$this->assertStringNotContainsString( 'body.lock-scrolling > #amp-mobile-version-switcher', $output );

		add_filter(
			'template',
			static function () {
				return 'twentytwentyone';
			}
		);
		ob_start();
		$this->instance->add_mobile_version_switcher_styles();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'body.lock-scrolling > #amp-mobile-version-switcher', $output );

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
		$data = [];
		foreach ( [ AMP_Theme_Support::READER_MODE_SLUG, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG ] as $template_mode ) {
			foreach ( [ true, false ] as $is_amp ) {
				foreach ( [ true, false ] as $is_customizer ) {
					foreach ( [ true, false ] as $is_paired_browsing ) {
						// Skip ths condition for Reader mode since Paired Browsing not relevant.
						if ( AMP_Theme_Support::READER_MODE_SLUG === $template_mode ) {
							continue;
						}

						$slug = implode(
							'_',
							[
								$template_mode,
								$is_amp ? 'amp' : 'noamp',
								$is_customizer ? 'with_customize' : 'without_customize',
								$is_paired_browsing ? 'with_pairedbrowsing' : 'without_pairedbrowsing',
							]
						);

						$data[ $slug ] = [
							$template_mode,
							$is_amp,
							$is_customizer,
							$is_paired_browsing,
						];
					}
				}
			}
		}
		return $data;
	}

	/**
	 * @dataProvider get_test_data_for_add_mobile_version_switcher
	 * @covers ::add_mobile_version_switcher_link()
	 *
	 * @param string $template_mode      Template mode.
	 * @param bool   $is_amp             Is AMP.
	 * @param bool   $is_customizer      Is Customizer preview.
	 * @param bool   $is_paired_browsing Is paired browsing.
	 */
	public function test_add_mobile_version_switcher( $template_mode, $is_amp, $is_customizer, $is_paired_browsing ) {
		if ( $is_paired_browsing && ! Services::get( 'dependency_support' )->has_support() ) {
			$this->markTestSkipped( 'Paired browsing is not available in the current environment.' );
		}

		$post_id = self::factory()->post->create();
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, $template_mode );

		$url = $is_amp ? amp_get_permalink( $post_id ) : get_permalink( $post_id );

		$link_rel = $is_amp ? 'nofollow' : '';

		if ( $is_paired_browsing ) {
			$this->register_paired_browsing_service();
		}
		if ( $is_customizer ) {
			$this->init_customizer_preview();
			$this->assertTrue( is_customize_preview() );
		}

		add_filter(
			'amp_mobile_version_switcher_link_text',
			static function ( $link_text ) {
				return $link_text . ' ' . ( amp_is_request() ? '(non-AMP version)' : '(AMP version)' );
			}
		);

		$this->go_to( $url );
		$this->assertEquals( $is_amp, amp_is_request() );
		ob_start();
		$this->instance->add_mobile_version_switcher_link();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'rel="' . $link_rel . '"', $output );
		$this->assertStringContainsString( 'amp-mobile-version-switcher', $output );

		if ( $is_customizer ? AMP_Theme_Support::READER_MODE_SLUG === $template_mode : $is_paired_browsing ) {
			$this->assertStringContainsString( '<script data-ampdevmode>', $output );
		} else {
			$this->assertStringNotContainsString( '<script data-ampdevmode>', $output );
		}

		// When mobile version switcher text is empty.
		add_filter( 'amp_mobile_version_switcher_link_text', '__return_empty_string' );
		ob_start();
		$this->instance->add_mobile_version_switcher_link();
		$output = ob_get_clean();

		$this->assertEmpty( $output );

		// When mobile version switcher is disabled.
		add_filter( 'amp_mobile_version_switcher_used', '__return_false' );
		ob_start();
		$this->instance->add_mobile_version_switcher_link();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Register paired browsing service.
	 *
	 * @see PairedBrowsing::is_needed()
	 */
	private function register_paired_browsing_service() {
		$service_classes = $this->call_private_method( $this->plugin, 'get_service_classes' );
		$service_id      = 'admin.paired_browsing';
		$service_class   = $service_classes[ $service_id ];

		// Make sure the service is not registered yet.
		$this->assertFalse( $this->container->has( $service_id ) );
		$this->assertFalse( call_user_func( [ $service_class, 'is_needed' ] ) );

		// Enable conditions to allow the PairedBrowsing service to be registered.
		do_action( 'wp_loaded' );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		add_filter( 'amp_dev_mode_enabled', '__return_true' );
		$this->assertTrue( call_user_func( [ $service_class, 'is_needed' ] ) );

		$this->call_private_method( $this->plugin, 'maybe_register_service', [ $service_id, $service_classes[ $service_id ] ] );
		$this->assertTrue( $this->container->has( $service_id ) );
	}

	/**
	 * Initialize Customizer preview.
	 */
	private function init_customizer_preview() {
		global $wp_customize;
		require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->start_previewing_theme();
	}
}
