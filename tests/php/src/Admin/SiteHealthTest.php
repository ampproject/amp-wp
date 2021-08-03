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

/**
 * Test SiteHealthTest.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\SiteHealth
 */
class SiteHealthTest extends TestCase {

	use PrivateAccess;

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
	}

	/**
	 * Tears down after each test.
	 *
	 * @inheritDoc
	 */
	public function tearDown() {
		unset( $GLOBALS['_wp_using_ext_object_cache'] );
		parent::tearDown();
	}

	/**
	 * Test init.
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 10, has_filter( 'site_status_tests', [ $this->instance, 'add_tests' ] ) );
		$this->assertEquals( 10, has_filter( 'debug_information', [ $this->instance, 'add_debug_information' ] ) );
		$this->assertEquals( 10, has_filter( 'site_status_test_result', [ $this->instance, 'modify_test_result' ] ) );
		$this->assertEquals( 10, has_filter( 'site_status_test_php_modules', [ $this->instance, 'add_extensions' ] ) );

		$this->assertEquals( 10, has_action( 'admin_print_styles-site-health.php', [ $this->instance, 'add_styles' ] ) );
		$this->assertEquals( 10, has_action( 'admin_print_styles-tools_page_health-check', [ $this->instance, 'add_styles' ] ) );
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
	 * Test persistent_object_cache.
	 *
	 * @covers ::persistent_object_cache()
	 * @covers ::check_available_cache()
	 */
	public function test_persistent_object_cache() {
		$data = [
			'test' => 'amp_persistent_object_cache',
		];

		$GLOBALS['_wp_using_ext_object_cache'] = false;
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
			$this->instance->persistent_object_cache()
		);

		$GLOBALS['_wp_using_ext_object_cache'] = true;
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
			$this->instance->persistent_object_cache()
		);
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
}
