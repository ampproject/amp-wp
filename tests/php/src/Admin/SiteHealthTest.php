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
use AmpProject\AmpWP\Tests\Helpers\HomeUrlLoopbackRequestMocking;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\TestCase;
use WP_REST_Server;
use WP_Error;

/**
 * Test SiteHealthTest.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\SiteHealth
 */
class SiteHealthTest extends TestCase {

	use HomeUrlLoopbackRequestMocking;
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
	public function set_up() {
		parent::set_up();

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

		$this->add_home_url_loopback_request_mocking();
	}

	/**
	 * Tears down after each test.
	 *
	 * @inheritDoc
	 */
	public function tear_down() {
		wp_using_ext_object_cache( $this->was_wp_using_ext_object_cache );
		$GLOBALS['wp_rest_server'] = $this->original_wp_rest_server;
		unset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );

		parent::tear_down();
	}

	/**
	 * Test init.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
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
	 * @covers ::get_persistent_object_cache_learn_more_action()
	 */
	public function test_get_persistent_object_cache_availability() {
		$data = [
			'test' => 'amp_persistent_object_cache',
		];

		wp_using_ext_object_cache( false );

		$page_cache_status = [
			'advanced_cache_present'        => false,
			'page_caching_response_headers' => [ [], [], [] ],
			'response_timing'               => [ 200, 300, 400 ],
		];

		set_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_status );
		$output = $this->instance->persistent_object_cache();
		$this->assertAssocArraySubset(
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
		$this->assertStringNotContainsString( 'Since page caching was detected', $output['description'] );
		$this->assertStringContainsString( '/persistent-object-caching/', $output['actions'] );

		$page_cache_status = [
			'advanced_cache_present'        => true,
			'page_caching_response_headers' => [ [ 'x-cache' ], [ 'x-cache' ], [ 'x-cache' ] ],
			'response_timing'               => [ 200, 300, 400 ],
		];

		set_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_status );

		$output = $this->instance->persistent_object_cache();
		$this->assertAssocArraySubset(
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
			$output
		);
		$this->assertStringContainsString( 'Please check with your host for what persistent caching services are available.', $output['description'] );
		$this->assertStringContainsString( 'Since page caching was detected', $output['description'] );
		$this->assertStringContainsString( '/persistent-object-caching/', $output['actions'] );

		wp_using_ext_object_cache( true );
		$output = $this->instance->persistent_object_cache();
		$this->assertAssocArraySubset(
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
		$this->assertStringNotContainsString( 'Since page caching was detected', $output['description'] );
		$this->assertStringContainsString( '/persistent-object-caching/', $output['actions'] );
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

		$this->assertAssocArraySubset(
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

		$this->assertAssocArraySubset(
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
		$this->assertAssocArraySubset(
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
		$this->assertAssocArraySubset(
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

		$this->assertAssocArraySubset(
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

		$this->assertAssocArraySubset(
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
		$this->assertAssocArraySubset(
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
	 * Test get_good_response_time_threshold.
	 *
	 * @covers ::get_good_response_time_threshold()
	 */
	public function test_get_good_response_time_threshold() {
		$this->assertSame( 600, $this->instance->get_good_response_time_threshold() );

		add_filter(
			'amp_page_cache_good_response_time_threshold',
			static function () {
				return 200;
			}
		);

		$this->assertSame( 200, $this->instance->get_good_response_time_threshold() );

		add_filter(
			'amp_page_cache_good_response_time_threshold',
			static function () {
				return '100';
			},
			100
		);

		$this->assertSame( 100, $this->instance->get_good_response_time_threshold() );
	}

	/**
	 * Data provider for $this->test_page_cache()
	 *
	 * @return array[]
	 */
	public function get_page_cache_data() {
		$recommended_label = 'Page caching is not detected but the server response time is OK';
		$good_label        = 'Page caching is detected and the server response time is good';
		$critical_label    = 'Page caching is not detected and the server response time is slow';
		$error_label       = 'Unable to detect the presence of page caching';

		return [
			'basic-auth-fail'                          => [
				'responses'       => [
					'unauthorized',
				],
				'expected_status' => 'critical',
				'expected_label'  => $error_label,
				'good_basic_auth' => false,
			],
			'no-cache-control'                         => [
				'responses'          => array_fill( 0, 3, [] ),
				'expected_status'    => 'critical',
				'expected_label'     => $critical_label,
				'good_basic_auth'    => null,
				'delay_the_response' => true,
			],
			'no-cache'                                 => [
				'responses'       => array_fill( 0, 3, [ 'cache-control' => 'no-cache' ] ),
				'expected_status' => 'recommended',
				'expected_label'  => $recommended_label,
			],
			'no-cache-arrays'                          => [
				'responses'       => array_fill( 0, 3, [ 'cache-control' => [ 'no-cache', 'no-store' ] ] ),
				'expected_status' => 'recommended',
				'expected_label'  => $recommended_label,
			],
			'no-cache-with-delayed-response'           => [
				'responses'          => array_fill( 0, 3, [ 'cache-control' => 'no-cache' ] ),
				'expected_status'    => 'critical',
				'expected_label'     => $critical_label,
				'good_basic_auth'    => null,
				'delay_the_response' => true,
			],
			'age'                                      => [
				'responses'       => array_fill(
					0,
					3,
					[ 'age' => '1345' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cache-control-max-age'                    => [
				'responses'       => array_fill(
					0,
					3,
					[ 'cache-control' => 'public; max-age=600' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'etag'                                     => [
				'responses'       => array_fill(
					0,
					3,
					[ 'etag' => '"1234567890"' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cache-control-max-age-after-2-requests'   => [
				'responses'       => [
					[],
					[],
					[ 'cache-control' => 'public; max-age=600' ],
				],
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cache-control-with-future-expires'        => [
				'responses'       => array_fill(
					0,
					3,
					[ 'expires' => gmdate( 'r', time() + MINUTE_IN_SECONDS * 10 ) ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cache-control-with-past-expires'          => [
				'responses'          => array_fill(
					0,
					3,
					[ 'expires' => gmdate( 'r', time() - MINUTE_IN_SECONDS * 10 ) ]
				),
				'expected_status'    => 'critical',
				'expected_label'     => $critical_label,
				'good_basic_auth'    => null,
				'delay_the_response' => true,
			],
			'cache-control-with-basic-auth'            => [
				'responses'       => array_fill(
					0,
					3,
					[ 'cache-control' => 'public; max-age=600' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
				'good_basic_auth' => true,
			],
			'cf-cache-status'                          => [
				'responses'       => array_fill(
					0,
					3,
					[ 'cf-cache-status' => 'HIT: 1' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cf-cache-status-without-header-and-delay' => [
				'responses'          => array_fill(
					0,
					3,
					[ 'cf-cache-status' => 'MISS' ]
				),
				'expected_status'    => 'recommended',
				'expected_label'     => $recommended_label,
				'good_basic_auth'    => null,
				'delay_the_response' => false,
			],
			'cf-cache-status-with-delay'               => [
				'responses'          => array_fill(
					0,
					3,
					[ 'cf-cache-status' => 'MISS' ]
				),
				'expected_status'    => 'critical',
				'expected_label'     => $critical_label,
				'good_basic_auth'    => null,
				'delay_the_response' => true,
			],
			'x-cache-enabled'                          => [
				'responses'       => array_fill(
					0,
					3,
					[ 'x-cache-enabled' => 'true' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'x-cache-enabled-with-delay'               => [
				'responses'          => array_fill(
					0,
					3,
					[ 'x-cache-enabled' => 'false' ]
				),
				'expected_status'    => 'critical',
				'expected_label'     => $critical_label,
				'good_basic_auth'    => null,
				'delay_the_response' => true,
			],
			'x-cache-disabled'                         => [
				'responses'       => array_fill(
					0,
					3,
					[ 'x-cache-disabled' => 'off' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cf-apo-via'                               => [
				'responses'       => array_fill(
					0,
					3,
					[ 'cf-apo-via' => 'tcache' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
			'cf-edge-cache'                            => [
				'responses'       => array_fill(
					0,
					3,
					[ 'cf-edge-cache' => 'cache' ]
				),
				'expected_status' => 'good',
				'expected_label'  => $good_label,
			],
		];
	}

	/**
	 * @dataProvider get_page_cache_data
	 * @covers ::page_cache()
	 * @covers ::get_page_cache_headers()
	 * @covers ::check_for_page_caching()
	 */
	public function test_page_cache( $responses, $expected_status, $expected_label, $good_basic_auth = null, $delay_the_response = false ) {

		$badge_color = [
			'critical'    => 'red',
			'recommended' => 'orange',
			'good'        => 'green',
		];

		$expected_props = [
			'badge'  => [
				'label' => 'AMP',
				'color' => $badge_color[ $expected_status ],
			],
			'test'   => 'amp_page_cache',
			'status' => $expected_status,
			'label'  => $expected_label,
		];

		if ( null !== $good_basic_auth ) {
			$_SERVER['PHP_AUTH_USER'] = 'admin';
			$_SERVER['PHP_AUTH_PW']   = 'password';
		}

		$is_unauthorized = false;

		$threshold = 10;
		if ( $delay_the_response ) {
			add_filter(
				'amp_page_cache_good_response_time_threshold',
				static function () use ( $threshold ) {
					return $threshold;
				}
			);
		}

		add_filter(
			'pre_http_request',
			function ( $r, $parsed_args ) use ( &$responses, &$is_unauthorized, $good_basic_auth, $delay_the_response, $threshold ) {

				$expected_response = array_shift( $responses );

				if ( $delay_the_response ) {
					usleep( $threshold * 1000 + 1 );
				}

				if ( 'unauthorized' === $expected_response ) {
					$is_unauthorized = true;

					return [
						'response' => [
							'code'    => 401,
							'message' => 'Unauthorized',
						],
					];
				}

				if ( null !== $good_basic_auth ) {
					$this->assertArrayHasKey(
						'Authorization',
						$parsed_args['headers']
					);
				}

				$this->assertIsArray( $expected_response );

				return [
					'headers'  => $expected_response,
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
				];
			},
			20,
			2
		);

		$actual = $this->instance->page_cache();
		$this->assertArrayHasKey( 'description', $actual );
		$this->assertArrayHasKey( 'actions', $actual );
		if ( $is_unauthorized ) {
			$this->assertStringContainsString( 'Unauthorized', $actual['description'] );
		} else {
			$this->assertStringNotContainsString( 'Unauthorized', $actual['description'] );
		}

		$this->assertEquals(
			$expected_props,
			wp_array_slice_assoc( $actual, array_keys( $expected_props ) )
		);
	}

	/**
	 * @covers ::get_page_cache_detail()
	 * @covers ::check_for_page_caching()
	 */
	public function test_get_page_cache_detail_with_legacy_cache_result() {

		add_filter(
			'pre_http_request',
			function () {
				return [
					'headers'  => [
						'etag' => '"cool"',
					],
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
				];
			},
			20,
			2
		);

		set_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY, 'no', DAY_IN_SECONDS );

		$this->assertAssocArrayContains(
			[
				'status'                 => 'good',
				'advanced_cache_present' => false,
				'headers'                => [
					'etag',
				],
			],
			$this->instance->get_page_cache_detail( true )
		);
	}

	/**
	 * @covers ::get_page_cache_detail()
	 * @covers ::check_for_page_caching()
	 */
	public function test_get_page_cache_detail() {
		$callback = static function () {
			return [
				'headers'  => [
					'age' => '1234',
				],
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		add_filter( 'pre_http_request', $callback, 20 );

		// Test 1: Assert for fresh result. (Even cached result is exist.)
		$page_cache_status = [
			'advanced_cache_present'        => false,
			'page_caching_response_headers' => [ [], [], [] ],
			'response_timing'               => [ 200, 300, 400 ],
		];
		set_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_status, DAY_IN_SECONDS );

		$output = $this->instance->get_page_cache_detail( true );
		$this->assertEquals( 'recommended', $output['status'] );

		$output = $this->instance->get_page_cache_detail();
		$this->assertEquals( 'good', $output['status'] );

		remove_filter( 'pre_http_request', $callback, 20 );

		// Test 2: Test for cached result.
		$page_cache_status = [
			'advanced_cache_present'        => true,
			'page_caching_response_headers' => [ [ 'x-cache' ], [ 'x-cache' ], [ 'x-cache' ] ],
			'response_timing'               => [ 200, 300, 400 ],
		];
		set_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_status, DAY_IN_SECONDS );

		$output = $this->instance->get_page_cache_detail( true );
		$this->assertEquals( 'good', $output['status'] );

		delete_transient( SiteHealth::HAS_PAGE_CACHING_TRANSIENT_KEY );
	}

	/**
	 * @covers ::get_page_cache_detail()
	 * @covers ::check_for_page_caching()
	 */
	public function test_get_page_cache_detail_with_error() {
		$error_object = new WP_Error( 'error_code', 'Error message.' );

		$return_error = static function () use ( $error_object ) {
			return $error_object;
		};

		$return_cached_response = static function () {
			return [
				'headers'  => [
					'cache-control' => 'public; max-age=600',
				],
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		add_filter( 'pre_http_request', $return_error, 20 );

		// Test 1: Assert for fresh result (which is then cached).
		$this->assertEquals(
			$error_object,
			$this->instance->get_page_cache_detail()
		);

		remove_filter( 'pre_http_request', $return_error, 20 );
		add_filter( 'pre_http_request', $return_cached_response, 20 );

		// Test 2: Test for cached result.
		$this->assertEquals(
			$error_object,
			$this->instance->get_page_cache_detail( true )
		);

		// Test 3: Test for non-cached result again now that no error is returned.
		$output = $this->instance->get_page_cache_detail( false );
		$this->assertEquals( 'good', $output['status'] );
		$this->assertContains( 'cache-control', $output['headers'] );

		remove_filter( 'pre_http_request', $return_cached_response, 20 );
		add_filter( 'pre_http_request', $return_error, 20 );

		// Test 4: Test for cached result again now that no error is returned.
		$output = $this->instance->get_page_cache_detail( true );
		$this->assertEquals( 'good', $output['status'] );
		$this->assertContains( 'cache-control', $output['headers'] );
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

	/**
	 * Assert that the expected is a subset of the actual superset.
	 *
	 * @param array $expected Subset.
	 * @param array $actual   Superset.
	 */
	public function assertAssocArraySubset( $expected, $actual ) {
		$this->assertEquals(
			$expected,
			wp_array_slice_assoc( $actual, array_keys( $expected ) )
		);
	}
}
