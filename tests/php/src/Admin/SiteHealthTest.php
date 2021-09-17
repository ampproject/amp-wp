<?php
/**
 * Test SiteHealthTest.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Admin\SiteHealth;
use AmpProject\AmpWP\AmpSlugCustomizationWatcher;
use AmpProject\AmpWP\AmpWpPluginFactory;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\TestCase;
use WP_REST_Server;

/**
 * Test SiteHealthTest.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\SiteHealth
 */
class SiteHealthTest extends TestCase {

	use PrivateAccess;

	/**
	 * Whether external object cache is being used.
	 *
	 * @var bool
	 */
	private $was_wp_using_ext_object_cache;

	/** @var WP_REST_Server */
	private $original_wp_rest_server;

	/**
	 * The tested instance.
	 *
	 * @var SiteHealth
	 */
	public $instance;

	/**
	 * Sets up each test.
	 *
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$injector = AmpWpPluginFactory::create()
			->get_container()
			->get( 'injector' );

		$this->instance = $injector->make( SiteHealth::class );

		remove_theme_support( 'amp' );
		foreach ( get_post_types_by_support( 'amp' ) as $post_type ) {
			remove_post_type_support( $post_type, 'amp' );
		}
		delete_option( AMP_Options_Manager::OPTION_NAME );

		$this->was_wp_using_ext_object_cache = wp_using_ext_object_cache();

		$this->original_wp_rest_server = isset( $GLOBALS['wp_rest_server'] ) ? $GLOBALS['wp_rest_server'] : null;
		$GLOBALS['wp_rest_server']     = null;
	}

	/**
	 * Tears down after each test.
	 *
	 * @inheritDoc
	 */
	public function tearDown() {
		parent::tearDown();
		wp_using_ext_object_cache( $this->was_wp_using_ext_object_cache );
		$GLOBALS['wp_rest_server'] = $this->original_wp_rest_server;
		unset( $_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] );
		unset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
	}

	/**
	 * Test init.
	 *
	 * @covers ::register()
	 */
	public function test_register() {

		// Mock ajax request.
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->instance->register();
		$this->assertEquals( 10, has_filter( 'site_status_tests', [ $this->instance, 'add_tests' ] ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->instance, 'register_async_test_endpoints' ] ) );
		$this->assertEquals( 10, has_filter( 'debug_information', [ $this->instance, 'add_debug_information' ] ) );
		$this->assertEquals( 10, has_filter( 'site_status_test_result', [ $this->instance, 'modify_test_result' ] ) );
		$this->assertEquals( 10, has_filter( 'site_status_test_php_modules', [ $this->instance, 'add_extensions' ] ) );

		$this->assertEquals( 10, has_action( 'admin_print_styles-site-health.php', [ $this->instance, 'add_styles' ] ) );
		$this->assertEquals( 10, has_action( 'admin_print_styles-tools_page_health-check', [ $this->instance, 'add_styles' ] ) );
	}

	/**
	 * @covers ::register_async_test_endpoints()
	 */
	public function test_register_async_test_endpoints() {
		$GLOBALS['wp_rest_server'] = null;
		remove_all_actions( 'rest_api_init' );

		$this->instance->register();
		$server = rest_get_server();

		$routes = $server->get_routes( SiteHealth::REST_API_NAMESPACE );

		$endpoint = '/' . SiteHealth::REST_API_NAMESPACE . SiteHealth::REST_API_PAGE_CACHE_ENDPOINT;
		$this->assertArrayHasKey( $endpoint, $routes );
		$this->assertCount( 1, $routes[ $endpoint ] );
		$route = $routes[ $endpoint ][0];

		$this->assertEquals(
			[ 'GET' => true ],
			$route['methods']
		);

		$this->assertEquals(
			[ $this->instance, 'page_cache' ],
			$route['callback']
		);

		$this->assertIsCallable( $route['permission_callback'] );

		$this->assertFalse( call_user_func( $route['permission_callback'] ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
		$this->assertFalse( call_user_func( $route['permission_callback'] ) );

		// Prior to WordPress 5.2, the view_site_health_checks cap didn't exist because Site Health didn't exist.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		if ( version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
			$this->assertTrue( call_user_func( $route['permission_callback'] ) );
		} else {
			$this->assertFalse( call_user_func( $route['permission_callback'] ) );
		}
	}

	/**
	 * Test add_tests.
	 *
	 * @covers ::add_tests()
	 */
	public function test_add_tests() {
		$tests = $this->instance->add_tests( [] );
		$this->assertArrayHasKey( 'direct', $tests );
		$this->assertArrayHasKey( 'amp_persistent_object_cache', $tests['direct'] );

		if ( version_compare( get_bloginfo( 'version' ), '5.6', '>=' ) ) {
			$this->assertArrayHasKey( 'amp_page_cache', $tests['async'] );
		} elseif ( array_key_exists( 'async', $tests ) ) {
			$this->assertArrayNotHasKey( 'amp_page_cache', $tests['async'] );
		}

		$this->assertArrayHasKey( 'amp_curl_multi_functions', $tests['direct'] );
		$this->assertArrayNotHasKey( 'amp_icu_version', $tests['direct'] );
		$this->assertArrayHasKey( 'amp_xdebug_extension', $tests['direct'] );
		$this->assertEquals( QueryVar::AMP, amp_get_slug() );
		$this->assertArrayNotHasKey( 'amp_slug_definition_timing', $tests['direct'] );

		// Test that the the ICU version test is added only when site URL is an IDN.
		add_filter( 'site_url', [ self::class, 'get_idn' ], 10, 4 );
		add_filter( 'amp_query_var', [ self::class, 'get_lite_query_var' ] );

		$tests = $this->instance->add_tests( [] );
		$this->assertArrayHasKey( 'amp_icu_version', $tests['direct'] );
		$this->assertArrayHasKey( 'amp_slug_definition_timing', $tests['direct'] );

		remove_filter( 'site_url', [ self::class, 'get_idn' ] );
		remove_filter( 'amp_query_var', [ self::class, 'get_lite_query_var' ] );
	}

	/**
	 * Test get_persistent_object_cache_availability.
	 *
	 * @covers ::persistent_object_cache()
	 * @covers ::get_persistent_object_cache_availability()
	 */
	public function test_get_persistent_object_cache_availability() {
		$data = [
			'test' => 'amp_persistent_object_cache',
		];

		wp_using_ext_object_cache( false );
		$output = $this->instance->persistent_object_cache();

		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'status' => 'recommended',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
				]
			),
			$output
		);

		$this->assertStringContainsString( 'Please check with your host for what persistent caching services are available.', $output['description'] );

		wp_using_ext_object_cache( true );
		$output = $this->instance->persistent_object_cache();
		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'label'  => 'Persistent object caching is enabled',
					'status' => 'good',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
				]
			),
			$output
		);
		$this->assertStringNotContainsString( 'Please check with your host for what persistent caching services are available.', $output['description'] );
	}

	/**
	 * @covers ::get_persistent_object_cache_availability()
	 */
	public function test_persistent_object_cache_with_suggestions() {

		$output = $this->instance->get_persistent_object_cache_availability();

		$this->assertArrayHasKey( 'redis', $output );
		$this->assertIsBool( $output['redis']['available'] );
		$this->assertEquals( 'Redis', $output['redis']['name'] );

		$this->assertArrayHasKey( 'memcached', $output );
		$this->assertIsBool( $output['memcached']['available'] );
		$this->assertEquals( 'Memcached', $output['memcached']['name'] );

		$this->assertArrayHasKey( 'apcu', $output );
		$this->assertIsBool( $output['apcu']['available'] );
		$this->assertEquals( 'APCu', $output['apcu']['name'] );
	}

	/**
	 * Test slug_definition_timing.
	 *
	 * @covers ::slug_definition_timing()
	 */
	public function test_slug_definition_timing() {
		$data = [
			'test' => 'amp_slug_definition_timing',
		];

		/** @var AmpSlugCustomizationWatcher $amp_slug_customization_watcher */
		$amp_slug_customization_watcher = $this->get_private_property( $this->instance, 'amp_slug_customization_watcher' );
		$this->set_private_property( $amp_slug_customization_watcher, 'is_customized_late', false );
		$this->assertFalse( $amp_slug_customization_watcher->did_customize_late() );

		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'label'  => 'The AMP slug (query var) was defined early',
					'status' => 'good',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
				]
			),
			$this->instance->slug_definition_timing()
		);

		add_filter( 'amp_query_var', [ self::class, 'get_lite_query_var' ] );

		/** @var AmpSlugCustomizationWatcher $amp_slug_customization_watcher */
		$amp_slug_customization_watcher = $this->get_private_property( $this->instance, 'amp_slug_customization_watcher' );
		$this->set_private_property( $amp_slug_customization_watcher, 'is_customized_late', true );
		$this->assertTrue( $amp_slug_customization_watcher->did_customize_late() );

		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'label'  => 'The AMP slug (query var) was defined late',
					'status' => 'recommended',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
				]
			),
			$this->instance->slug_definition_timing()
		);
	}

	/**
	 * Test curl_multi_functions.
	 *
	 * @covers ::curl_multi_functions()
	 */
	public function test_curl_multi_functions() {
		$this->assertArraySubset(
			[
				'test' => 'amp_curl_multi_functions',
			],
			$this->instance->curl_multi_functions()
		);
	}

	/**
	 * Test icu_version.
	 *
	 * @covers ::icu_version()
	 */
	public function test_icu_version() {
		$this->assertArraySubset(
			[
				'test' => 'amp_icu_version',
			],
			$this->instance->icu_version()
		);
	}

	/**
	 * Test css_transient_caching.
	 *
	 * @covers ::css_transient_caching()
	 */
	public function test_css_transient_caching() {
		wp_using_ext_object_cache( false );
		$data = [
			'test' => 'amp_css_transient_caching',
		];

		AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );

		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'status' => 'good',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
				]
			),
			$this->instance->css_transient_caching()
		);

		AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, true );

		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'status' => 'recommended',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
				]
			),
			$this->instance->css_transient_caching()
		);

		wp_using_ext_object_cache( true );
		$this->assertArraySubset(
			array_merge(
				$data,
				[
					'status' => 'good',
					'badge'  => [
						'label' => 'AMP',
						'color' => 'blue',
					],
				]
			),
			$this->instance->css_transient_caching()
		);
	}

	/**
	 * Test xdebug_extension.
	 *
	 * @covers ::xdebug_extension()
	 */
	public function test_xdebug_extension() {
		$actual = $this->instance->xdebug_extension();
		$this->assertEquals( 'amp_xdebug_extension', $actual['test'] );

		$this->assertStringContainsString(
			esc_html( 'The Xdebug extension can cause some of the AMP plugin&#8217;s processes to time out depending on your system resources and configuration. It should not be enabled on a live site (production environment).' ),
			$actual['description']
		);
	}

	/**
	 * Test add_debug_information.
	 *
	 * @covers ::add_debug_information()
	 */
	public function test_add_debug_information() {
		$debug_info = $this->instance->add_debug_information( [] );
		$this->assertArrayHasKey( 'amp_wp', $debug_info );
		$this->assertArrayHasKey( 'fields', $debug_info['amp_wp'] );
		$keys = [
			'amp_mode_enabled',
			'amp_templates_enabled',
			'amp_serve_all_templates',
			'amp_css_transient_caching_disabled',
			'amp_css_transient_caching_threshold',
			'amp_css_transient_caching_sampling_range',
			'amp_css_transient_caching_transient_count',
			'amp_slug_query_var',
			'amp_slug_defined_late',
		];
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $debug_info['amp_wp']['fields'], "Expected key: $key" );
			$this->assertFalse( $debug_info['amp_wp']['fields'][ $key ]['private'], "Expected private for key: $key" );
		}
	}

	/**
	 * Get test data for test_modify_test_result.
	 *
	 * @return array[] Test data.
	 */
	public function get_test_result() {
		return [
			'empty_result'                    => [
				[],
			],
			'good_https_status_result'        => [
				[
					'test'        => 'https_status',
					'status'      => 'good',
					'description' => '',
				],
			],
			'recommended_https_status_result' => [
				[
					'test'        => 'https_status',
					'status'      => 'recommended',
					'description' => '',
				],
				[
					'test'        => 'https_status',
					'status'      => 'critical',
					'description' => '<p>Additionally, AMP requires HTTPS for most components to work properly, including iframes and videos.</p>',
				],
			],
		];
	}

	/**
	 * Test modify_test_result.
	 *
	 * @dataProvider get_test_result
	 *
	 * @covers ::modify_test_result()
	 *
	 * @param array $test_data Data from Site Health test.
	 * @param array $expected  Expected modified test result.
	 */
	public function test_modify_test_result( $test_data, $expected = null ) {
		$test_result = $this->instance->modify_test_result( $test_data );

		if ( ! $expected ) {
			$expected = $test_data;
		}

		$this->assertEquals( $expected, $test_result );
	}

	/**
	 * Gets the test data for test_get_supported_templates().
	 *
	 * @return array The test data.
	 */
	public function get_supported_templates_data() {
		return [
			'no_template_supported'       => [
				[],
				[],
				'standard',
				'No template supported',
			],
			'only_singular'               => [
				[ 'post' ],
				[ 'is_singular' ],
				'transitional',
				'post, is_singular',
			],
			'only_post'                   => [
				[ 'post' ],
				[],
				'transitional',
				'post',
			],
			'only_post_and_author'        => [
				[ 'post' ],
				[ 'is_author' ],
				'transitional',
				'post, is_author',
			],
			'two_templates'               => [
				[ 'post' ],
				[ 'is_singular', 'is_author' ],
				'transitional',
				'post, is_singular, is_author',
			],
			'three_templates'             => [
				[ 'post', 'page' ],
				[ 'is_singular', 'is_author', 'is_tag' ],
				'transitional',
				'post, page, is_singular, is_author, is_tag',
			],
			'three_templates_reader_mode' => [
				[ 'post', 'page' ],
				[ 'is_singular', 'is_author', 'is_tag' ],
				'reader',
				'post, page',
			],
		];
	}

	/**
	 * Test add_debug_information.
	 *
	 * @dataProvider get_supported_templates_data
	 * @covers ::get_supported_templates()
	 *
	 * @param array  $supported_content_types The supported content types, like 'post'.
	 * @param array  $supported_templates     The supported templates, like 'is_author'.
	 * @param string $theme_support           The theme support, like 'standard'.
	 * @param string $expected                The expected string of supported templates.
	 */
	public function test_get_supported_templates( $supported_content_types, $supported_templates, $theme_support, $expected ) {
		remove_theme_support( 'amp' );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		AMP_Options_Manager::update_option( Option::SUPPORTED_TEMPLATES, $supported_templates );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, $theme_support );
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_content_types );

		$this->assertEquals( $expected, $this->call_private_method( $this->instance, 'get_supported_templates' ) );
	}

	/**
	 * Gets the test data for test_get_serve_all_templates().
	 *
	 * @return array The test data.
	 */
	public function get_serve_all_templates_data() {
		return [
			'reader_mode_and_option_true'        => [
				'reader',
				true,
				'This option does not apply to Reader mode.',
			],
			'reader_mode_and_option_false'       => [
				'reader',
				false,
				'This option does not apply to Reader mode.',
			],
			'standard_mode_and_option_true'      => [
				'standard',
				true,
				'true',
			],
			'standard_mode_and_option_false'     => [
				'standard',
				false,
				'false',
			],
			'transitional_mode_and_option_true'  => [
				'transitional',
				false,
				'false',
			],
			'transitional_mode_and_option_false' => [
				'transitional',
				false,
				'false',
			],
		];
	}

	/**
	 * Test get_serve_all_templates.
	 *
	 * @dataProvider get_serve_all_templates_data
	 * @covers ::get_serve_all_templates()
	 *
	 * @param string $theme_support          The template mode, like 'standard'.
	 * @param bool   $do_serve_all_templates Whether the option to serve all templates is true.
	 * @param string $expected               The expected return value.
	 */
	public function test_get_serve_all_templates( $theme_support, $do_serve_all_templates, $expected ) {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, $theme_support );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, $do_serve_all_templates );

		$this->assertEquals( $expected, $this->call_private_method( $this->instance, 'get_serve_all_templates' ) );
	}

	/**
	 * Test add_extensions.
	 *
	 * @covers ::add_extensions()
	 */
	public function test_add_extensions() {
		$this->assertEquals(
			[
				'json'     => [
					'extension' => 'json',
					'function'  => 'json_encode',
					'required'  => false,
				],
				'mbstring' => [
					'extension' => 'mbstring',
					'required'  => false,
				],
				'zip'      => [
					'extension' => 'zip',
					'required'  => false,
				],
			],
			$this->instance->add_extensions( [] )
		);

		// Test that the `intl` extension is added only when site URL is an IDN.
		add_filter( 'site_url', [ self::class, 'get_idn' ], 10, 4 );

		$extensions = $this->instance->add_extensions( [] );
		$this->assertArrayHasKey( 'intl', $extensions );
		$this->assertEquals(
			[
				'extension' => 'intl',
				'function'  => 'idn_to_utf8',
				'required'  => false,
			],
			$extensions['intl']
		);

		remove_filter( 'site_url', [ self::class, 'get_idn' ] );
	}

	/**
	 * Data provider for $this->test_page_cache()
	 *
	 * @return array[]
	 */
	public function get_page_cache_data() {

		return [
			'basic-auth-fail'                => [
				'response_headers' => [],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
					'test'   => 'amp_page_cache',
					'status' => 'recommended',
					'label'  => 'Page caching is not detected',
				],
				'conditions'       => [
					'basic_auth_fail' => true,
				],
			],
			'no-cache'                       => [
				'response_headers' => [],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
					'test'   => 'amp_page_cache',
					'status' => 'recommended',
					'label'  => 'Page caching is not detected',
				],
				'conditions'       => [
					'replay_random_numbers' => false,
				],
			],
			'server-cache'                   => [
				'response_headers' => [
					'cache-control' => 'no-cache',
				],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
					'test'   => 'amp_page_cache',
					'status' => 'recommended',
					'label'  => 'Page caching is detected but client caching headers are missing',
				],
				'conditions'       => [
					'replay_random_numbers' => true,
				],
			],
			'server-cache-with-age'          => [
				'response_headers' => [
					'age' => '1345',
				],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'orange',
					],
					'test'   => 'amp_page_cache',
					'status' => 'recommended',
					'label'  => 'Page caching is detected but client caching headers are missing',
				],
				'conditions'       => [
					'replay_random_numbers' => false,
				],
			],
			'full-cache-with-max-age'        => [
				'response_headers' => [
					'cache-control' => 'public; max-age=600',
				],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
					'test'   => 'amp_page_cache',
					'status' => 'good',
					'label'  => 'Page caching is detected',
				],
				'conditions'       => [
					'replay_random_numbers' => true,
				],
			],
			'full-cache-with-future-expires' => [
				'response_headers' => [
					'expires' => gmdate( 'r', time() + MINUTE_IN_SECONDS * 10 ),
				],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
					'test'   => 'amp_page_cache',
					'status' => 'good',
					'label'  => 'Page caching is detected',
				],
				'conditions'       => [
					'replay_random_numbers' => true,
				],
			],
			'full-cache-with-basic-auth'     => [
				'response_headers' => [
					'cache-control' => 'public; max-age=600',
				],
				'expected'         => [
					'badge'  => [
						'label' => 'AMP',
						'color' => 'green',
					],
					'test'   => 'amp_page_cache',
					'status' => 'good',
					'label'  => 'Page caching is detected',
				],
				'conditions'       => [
					'replay_random_numbers' => true,
					'basic_auth_pass'       => true,
				],
			],
		];
	}

	/**
	 * @dataProvider get_page_cache_data
	 * @covers ::page_cache()
	 * @covers ::get_page_cache_status()
	 * @covers ::send_page_cache_challenge_response_header()
	 * @covers ::get_page_caching_challenge_nonce()
	 */
	public function test_page_cache( $response_headers, $expected, $conditions ) {

		$first_random_number = null;

		if ( ! empty( $conditions['basic_auth_pass'] ) ) {
			$_SERVER['PHP_AUTH_USER'] = 'admin';
			$_SERVER['PHP_AUTH_PW']   = 'password';
		}

		add_filter(
			'pre_http_request',
			function ( $r, $parsed_args ) use ( $response_headers, $conditions, &$first_random_number ) {
				if ( ! empty( $conditions['basic_auth_fail'] ) ) {
					return [
						'response' => [
							'code'    => 401,
							'message' => 'Unauthorized',
						],
					];
				}
				if ( ! empty( $conditions['basic_auth_pass'] ) ) {
					$this->assertArrayHasKey(
						'Authorization',
						$parsed_args['headers']
					);
				}

				$header_name = strtolower( SiteHealth::PAGE_CACHING_CHALLENGE_HEADER );
				$this->assertArrayHasKey(
					$header_name,
					$parsed_args['headers']
				);
				$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = $parsed_args['headers'][ $header_name ];

				if ( ! empty( $conditions['replay_random_numbers'] ) && $first_random_number ) {
					$random_number = $first_random_number;
				} else {
					$headers = $this->instance->send_page_cache_challenge_response_header( [] );

					$this->assertArrayHasKey(
						SiteHealth::PAGE_CACHING_CHALLENGE_HEADER,
						$headers
					);

					$random_number = $headers[ SiteHealth::PAGE_CACHING_CHALLENGE_HEADER ];

					// See \AmpProject\AmpWP\Admin\SiteHealth::send_page_cache_challenge_response_header
					list( $nonce, $requested_random_number ) = explode( ';', $parsed_args['headers'][ $header_name ] );
					$this->assertSame(
						$this->instance->get_page_caching_challenge_nonce(),
						$nonce
					);
					$this->assertSame( $requested_random_number, $random_number );
					$first_random_number = $requested_random_number;
				}
				$response_headers[ $header_name ] = $random_number;

				return [
					'headers'  => $response_headers,
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
				];
			},
			10,
			2
		);

		$actual = $this->instance->page_cache();
		$this->assertArrayHasKey( 'description', $actual );
		$this->assertArrayHasKey( 'actions', $actual );

		if ( ! empty( $conditions['basic_auth_fail'] ) ) {
			$this->assertStringContainsString( 'Unauthorized', $actual['description'] );
		} else {
			$this->assertStringNotContainsString( 'Unauthorized', $actual['description'] );
		}
		unset( $actual['description'] );
		unset( $actual['actions'] );

		$this->assertEquals(
			$expected,
			$actual
		);
	}

	/** @covers ::get_page_caching_challenge_nonce() */
	public function test_get_page_caching_challenge_nonce() {
		$nonce1 = $this->instance->get_page_caching_challenge_nonce();
		$nonce2 = $this->instance->get_page_caching_challenge_nonce();
		$this->assertIsString( $nonce1 );
		$this->assertSame( $nonce1, $nonce2 );
	}

	/** @covers ::send_page_cache_challenge_response_header() */
	public function test_send_page_cache_challenge_response_header() {
		$initial_headers = [ 'Foo' => 'Bar' ];

		// No request header set.
		$this->assertSame(
			$initial_headers,
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);

		// Wrong format.
		$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = 'bad';
		$this->assertSame(
			$initial_headers,
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);
		$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = 'bad;123245;123123';
		$this->assertSame(
			$initial_headers,
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);
		$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = 'bad;asdasd';
		$this->assertSame(
			$initial_headers,
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);

		// Bad nonce.
		$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = 'bad;12345';
		$this->assertSame(
			$initial_headers,
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);

		// Good nonce.
		$nonce         = $this->instance->get_page_caching_challenge_nonce();
		$random_number = '12345';
		$_SERVER[ $this->get_page_caching_challenge_request_header_server_key() ] = "{$nonce};$random_number";
		$this->assertSame(
			array_merge(
				$initial_headers,
				[ SiteHealth::PAGE_CACHING_CHALLENGE_HEADER => $random_number ]
			),
			$this->instance->send_page_cache_challenge_response_header( $initial_headers )
		);
	}

	/**
	 * Get an IDN for testing purposes.
	 *
	 * @return string
	 */
	public static function get_idn() {
		return 'https://foo.xn--57h.bar.com';
	}

	/**
	 * Get an AMP query var for testing purposes.
	 *
	 * @return string
	 */
	public static function get_lite_query_var() {
		return 'lite';
	}

	/** @return string */
	private function get_page_caching_challenge_request_header_server_key() {
		return 'HTTP_' . str_replace( '-', '_', strtoupper( SiteHealth::PAGE_CACHING_CHALLENGE_HEADER ) );
	}
}
