<?php
/**
 * Test Site_Health.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Admin\SiteHealth;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\PrivateAccess;

/**
 * Test Site_Health.
 */
class Test_Site_Health extends WP_UnitTestCase {

	use AssertContainsCompatibility;
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
		$this->instance = new SiteHealth();
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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'site_status_tests', [ $this->instance, 'add_tests' ] ) );
		$this->assertEquals( 10, has_action( 'debug_information', [ $this->instance, 'add_debug_information' ] ) );
		$this->assertEquals( 10, has_action( 'site_status_test_php_modules', [ $this->instance, 'add_extensions' ] ) );
	}

	/**
	 * Test add_tests.
	 *
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::add_tests()
	 */
	public function test_add_tests() {
		$tests = $this->instance->add_tests( [] );
		$this->assertArrayHasKey( 'direct', $tests );
		$this->assertArrayHasKey( 'amp_persistent_object_cache', $tests['direct'] );
		$this->assertArrayHasKey( 'amp_curl_multi_functions', $tests['direct'] );
		$this->assertArrayHasKey( 'amp_icu_version', $tests['direct'] );
		$this->assertArrayHasKey( 'amp_xdebug_extension', $tests['direct'] );
	}

	/**
	 * Test persistent_object_cache.
	 *
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::persistent_object_cache()
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
	 * Test curl_multi_functions.
	 *
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::curl_multi_functions()
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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::icu_version()
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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::css_transient_caching()
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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::xdebug_extension()
	 */
	public function test_xdebug_extension() {
		$actual = $this->instance->xdebug_extension();
		$this->assertEquals( 'amp_xdebug_extension', $actual['test'] );

		$this->assertStringContains(
			esc_html( 'The Xdebug extension can cause some of the AMP plugin&#8217;s processes to time out depending on your system resources and configuration. It should not be enabled on a live site (production environment).' ),
			$actual['description']
		);
	}

	/**
	 * Test add_debug_information.
	 *
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::add_debug_information()
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
		];
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $debug_info['amp_wp']['fields'], "Expected key: $key" );
			$this->assertFalse( $debug_info['amp_wp']['fields'][ $key ]['private'], "Expected private for key: $key" );
		}
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
				'post',
			],
			'only_singular'               => [
				[],
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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::get_supported_templates()
	 *
	 * @param array  $supported_content_types The supported content types, like 'post'.
	 * @param array  $supported_templates     The supported templates, like 'is_author'.
	 * @param string $theme_support           The theme support, like 'standard'.
	 * @param string $expected                The expected string of supported templates.
	 */
	public function test_get_supported_templates( $supported_content_types, $supported_templates, $theme_support, $expected ) {
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		AMP_Options_Manager::update_option( 'supported_templates', $supported_templates );
		AMP_Options_Manager::update_option( 'theme_support', $theme_support );
		AMP_Theme_Support::read_theme_support();

		$basic_post_types = [ 'post', 'page' ];
		foreach ( array_diff( $basic_post_types, $supported_content_types ) as $post_type ) {
			remove_post_type_support( $post_type, AMP_Theme_Support::SLUG );
		}

		foreach ( $supported_content_types as $post_type ) {
			add_post_type_support( $post_type, AMP_Theme_Support::SLUG );
		}

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
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::get_serve_all_templates()
	 *
	 * @param string $theme_support          The template mode, like 'standard'.
	 * @param bool   $do_serve_all_templates Whether the option to serve all templates is true.
	 * @param string $expected               The expected return value.
	 */
	public function test_get_serve_all_templates( $theme_support, $do_serve_all_templates, $expected ) {
		AMP_Options_Manager::update_option( 'theme_support', $theme_support );
		AMP_Options_Manager::update_option( 'all_templates_supported', $do_serve_all_templates );
		AMP_Theme_Support::read_theme_support();

		$this->assertEquals( $expected, $this->call_private_method( $this->instance, 'get_serve_all_templates' ) );
	}

	/**
	 * Test add_extensions.
	 *
	 * @covers \AmpProject\AmpWP\Admin\SiteHealth::add_extensions()
	 */
	public function test_add_extensions() {
		$this->assertEquals(
			[
				'intl'     => [
					'extension' => 'intl',
					'function'  => 'idn_to_utf8',
					'required'  => false,
				],
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
	}
}
