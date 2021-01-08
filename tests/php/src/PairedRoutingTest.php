<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PairedRouting;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\LegacyTransitionalUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\PathSuffixUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Fixture\DummyPairedUrlStructure;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/** @coversDefaultClass \AmpProject\AmpWP\PairedRouting */
class PairedRoutingTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility;
	use PrivateAccess;

	/** @var PairedRouting */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( PairedRouting::class );
	}

	public function tearDown() {
		unset( $_SERVER['REQUEST_URI'] );
		parent::tearDown();
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( PairedRouting::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		remove_all_actions( 'plugins_loaded' ); // @todo This is needed because the instance already got registered.
		$this->instance->register();
		$this->assertEquals( 10, has_filter( 'amp_rest_options_schema', [ $this->instance, 'filter_rest_options_schema' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_rest_options', [ $this->instance, 'filter_rest_options' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );
		$this->assertEquals( 8, has_action( 'template_redirect', [ $this->instance, 'redirect_extraneous_paired_endpoint' ] ) );
		$this->assertEquals( 7, has_action( 'plugins_loaded', [ $this->instance, 'initialize_paired_request' ] ) );
	}

	/** @return array */
	public function get_data_for_test_get_paired_url_structure() {
		return [
			'query_var'           => [
				Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				QueryVarUrlStructure::class,
			],
			'path_suffix'         => [
				Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX,
				PathSuffixUrlStructure::class,
			],
			'legacy_transitional' => [
				Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL,
				LegacyTransitionalUrlStructure::class,
			],
			'legacy_reader'       => [
				Option::PAIRED_URL_STRUCTURE_LEGACY_READER,
				LegacyReaderUrlStructure::class,
			],
			'bogus'               => [
				'bogus',
				QueryVarUrlStructure::class,
			],
		];
	}

	/**
	 * @covers ::get_paired_url_structure()
	 * @dataProvider get_data_for_test_get_paired_url_structure
	 * @param string $option_value    Option value.
	 * @param string $structure_class Expected structure.
	 */
	public function test_get_paired_url_structure( $option_value, $structure_class ) {
		AMP_Options_Manager::update_option( Option::PAIRED_URL_STRUCTURE, $option_value );
		$structure = $this->instance->get_paired_url_structure();
		$this->assertInstanceOf( $structure_class, $structure );
	}

	/** @covers ::get_paired_url_structure() */
	public function test_get_paired_url_structure_custom_filtered() {
		add_filter(
			'amp_custom_paired_url_structure',
			static function () {
				return DummyPairedUrlStructure::class;
			}
		);
		$structure = $this->instance->get_paired_url_structure();
		$this->assertInstanceOf( DummyPairedUrlStructure::class, $structure );
	}

	/** @covers ::filter_rest_options_schema() */
	public function test_filter_rest_options_schema() {
		$existing = [
			'foo' => [
				'type' => 'string',
			],
		];

		$filtered = $this->instance->filter_rest_options_schema( $existing );
		$this->assertArrayHasKey( 'foo', $filtered );
		$this->assertArrayHasKey( Option::PAIRED_URL_STRUCTURE, $filtered );
		$this->assertArrayHasKey( PairedRouting::PAIRED_URL_EXAMPLES, $filtered );
		$this->assertArrayHasKey( PairedRouting::AMP_SLUG, $filtered );
		$this->assertArrayHasKey( PairedRouting::ENDPOINT_PATH_SLUG_CONFLICTS, $filtered );
		$this->assertArrayHasKey( PairedRouting::REWRITE_USING_PERMALINKS, $filtered );
	}

	/** @covers ::filter_rest_options() */
	public function test_filter_rest_options() {
		$existing = [
			'foo' => 'bar',
		];

		$options = $this->instance->filter_rest_options( $existing );

		$this->assertEquals( amp_get_slug(), $options[ PairedRouting::AMP_SLUG ] );
		$this->assertEquals(
			AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ),
			$options[ Option::PAIRED_URL_STRUCTURE ]
		);
		$this->assertEquals( $this->instance->get_paired_url_examples(), $options[ PairedRouting::PAIRED_URL_EXAMPLES ] );
		$this->assertEquals( $this->instance->get_custom_paired_structure_sources(), $options[ PairedRouting::CUSTOM_PAIRED_ENDPOINT_SOURCES ] );
		$this->assertEquals( $this->instance->get_endpoint_path_slug_conflicts(), $options[ PairedRouting::ENDPOINT_PATH_SLUG_CONFLICTS ] );
		$this->assertEquals( $this->instance->get_wp_rewrite()->using_permalinks(), $options[ PairedRouting::REWRITE_USING_PERMALINKS ] );
	}

	/** @return array */
	public function get_data_for_test_paired_requests() {
		return [
			'query_var_reader_mode_amp'             => [
				AMP_Theme_Support::READER_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				'/?amp=1',
				true,
			],
			'query_var_reader_mode_non_amp'         => [
				AMP_Theme_Support::READER_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				'/',
				false,
			],
			'path_suffix_transitional_mode_amp'     => [
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX,
				'/amp/',
				true,
			],
			'path_suffix_transitional_mode_non_amp' => [
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX,
				'/',
				false,
			],
			'legacy_reader_mode_amp'                => [
				AMP_Theme_Support::READER_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_LEGACY_READER,
				'/amp/',
				true,
			],
			'legacy_transitional_mode_amp'          => [
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL,
				'/?amp=1',
				true,
			],
			'standard_mode'                         => [
				AMP_Theme_Support::STANDARD_MODE_SLUG,
				Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				'/',
				null,
			],
		];
	}

	/**
	 * Test initialize_paired_request, integrated with other methods.
	 *
	 * @covers ::initialize_paired_request()
	 * @covers ::detect_endpoint_in_environment()
	 * @covers ::extract_endpoint_from_environment_before_parse_request()
	 * @covers ::filter_request_after_endpoint_extraction()
	 * @covers ::restore_path_endpoint_in_environment()
	 *
	 * @dataProvider get_data_for_test_paired_requests
	 *
	 * @param string $mode
	 * @param string $structure
	 * @param string $request_uri
	 * @param bool $did_request_endpoint
	 */
	public function test_initialize_paired_request_integration( $mode, $structure, $request_uri, $did_request_endpoint ) {
		global $wp;
		$post_id = self::factory()->post->create();
		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, $mode );
		AMP_Options_Manager::update_option( Option::PAIRED_URL_STRUCTURE, $structure );

		$permalink   = get_permalink( $post_id );
		$request_uri = rtrim( wp_parse_url( $permalink, PHP_URL_PATH ), '/' ) . $request_uri;

		$_SERVER['REQUEST_URI'] = $request_uri;
		$this->instance->initialize_paired_request();
		$this->go_to( $request_uri );

		$this->assertSame( $did_request_endpoint, $this->get_private_property( $this->instance, 'did_request_endpoint' ) );
		$this->assertSame( $request_uri, $_SERVER['REQUEST_URI'] );
		$this->assertEquals(
			trim( strtok( $request_uri, '?' ), '/' ),
			$wp->request
		);
		if ( $did_request_endpoint ) {
			$this->assertTrue( get_query_var( amp_get_slug() ) );
		} else {
			$this->assertEquals( '', get_query_var( amp_get_slug() ) );
		}
	}

	/** @return array */
	public function get_data_for_test_initialize_paired_request() {
		return [
			'query_var'   => [
				Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
				false,
			],
			'path_suffix' => [
				Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX,
				true,
			],
		];
	}

	/**
	 * @covers ::initialize_paired_request()
	 *
	 * @dataProvider get_data_for_test_initialize_paired_request
	 * @param string $structure
	 * @param bool $filtering_unique_post_slug
	 */
	public function test_initialize_paired_request( $structure, $filtering_unique_post_slug ) {
		AMP_Options_Manager::update_option( Option::PAIRED_URL_STRUCTURE, $structure );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->instance->initialize_paired_request();
		$this->assertFalse( $this->get_private_property( $this->instance, 'did_request_endpoint' ) );
		$this->assertEquals( 10, has_filter( 'do_parse_request', [ $this->instance, 'extract_endpoint_from_environment_before_parse_request' ] ) );
		$this->assertEquals( 10, has_filter( 'request', [ $this->instance, 'filter_request_after_endpoint_extraction' ] ) );
		$this->assertEquals( 10, has_action( 'parse_request', [ $this->instance, 'restore_path_endpoint_in_environment' ] ) );
		if ( $filtering_unique_post_slug ) {
			$this->assertEquals( 10, has_filter( 'wp_unique_post_slug', [ $this->instance, 'filter_unique_post_slug' ] ) );
		} else {
			$this->assertFalse( has_filter( 'wp_unique_post_slug', [ $this->instance, 'filter_unique_post_slug' ] ) );
		}

		$this->assertEquals( 9, has_action( 'template_redirect', [ $this->instance, 'redirect_paired_amp_unavailable' ] ) );
		$this->assertEquals( 10, has_action( 'parse_query', [ $this->instance, 'correct_query_when_is_front_page' ] ) );
		$this->assertEquals( 10, has_action( 'wp', [ $this->instance, 'add_paired_request_hooks' ] ) );
		$this->assertEquals( 10, has_action( 'admin_notices', [ $this->instance, 'add_permalink_settings_notice' ] ) );
	}

	/** @covers ::initialize_paired_request() */
	public function test_initialize_paired_request_in_standard_mode() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		AMP_Options_Manager::update_option( Option::PAIRED_URL_STRUCTURE, Option::PAIRED_URL_STRUCTURE_QUERY_VAR );
		$this->instance->initialize_paired_request();
		$this->assertNull( $this->get_private_property( $this->instance, 'did_request_endpoint' ) );
		$this->assertFalse( has_filter( 'do_parse_request', [ $this->instance, 'extract_endpoint_from_environment_before_parse_request' ] ) );
	}

	/** @covers ::add_permalink_settings_notice() */
	public function test_add_permalink_settings_notice() {
		set_current_screen( 'options' );
		$this->assertEmpty( get_echo( [ $this->instance, 'add_permalink_settings_notice' ] ) );

		set_current_screen( 'options-permalink' );
		$this->assertStringContains( 'notice-info', get_echo( [ $this->instance, 'add_permalink_settings_notice' ] ) );
	}

	/** @covers ::get_endpoint_path_slug_conflicts() */
	public function test_get_endpoint_path_slug_conflicts() {
		$this->assertCount( 0, $this->instance->get_endpoint_path_slug_conflicts() );

		// Posts.
		self::factory()->post->create( [ 'post_name' => amp_get_slug() ] );
		$this->assertEquals(
			[ 'posts' ],
			array_keys( $this->instance->get_endpoint_path_slug_conflicts() )
		);

		// Terms.
		self::factory()->term->create(
			[
				'taxonomy' => 'category',
				'name'     => amp_get_slug(),
			]
		);
		$this->assertEquals(
			[ 'posts', 'terms' ],
			array_keys( $this->instance->get_endpoint_path_slug_conflicts() )
		);

		// Users.
		self::factory()->user->create(
			[
				'user_login' => 'amp',
			]
		);
		$this->assertEquals(
			[ 'posts', 'terms', 'users' ],
			array_keys( $this->instance->get_endpoint_path_slug_conflicts() )
		);

		// Post types.
		register_post_type( amp_get_slug() );
		$this->assertEquals(
			[ 'posts', 'terms', 'users', 'post_types' ],
			array_keys( $this->instance->get_endpoint_path_slug_conflicts() )
		);

		// Taxonomies.
		register_taxonomy( amp_get_slug(), 'post' );
		$this->assertEquals(
			[ 'posts', 'terms', 'users', 'post_types', 'taxonomies' ],
			array_keys( $this->instance->get_endpoint_path_slug_conflicts() )
		);
	}

	/** @covers ::detect_endpoint_in_environment() */
	public function test_detect_endpoint_in_environment() {
		unset( $_SERVER['REQUEST_URI'] );
		$this->instance->detect_endpoint_in_environment();
		$this->assertFalse( $this->get_private_property( $this->instance, 'did_request_endpoint' ) );

		$_SERVER['REQUEST_URI'] = $this->instance->remove_endpoint( '/' );
		$this->instance->detect_endpoint_in_environment();
		$this->assertFalse( $this->get_private_property( $this->instance, 'did_request_endpoint' ) );

		$_SERVER['REQUEST_URI'] = $this->instance->add_endpoint( '/' );
		$this->instance->detect_endpoint_in_environment();
		$this->assertTrue( $this->get_private_property( $this->instance, 'did_request_endpoint' ) );
	}

	/** @covers ::extract_endpoint_from_environment_before_parse_request() */
	public function test_extract_endpoint_from_environment_before_parse_request() {
		$this->markTestIncomplete();
	}

	/** @covers ::filter_request_after_endpoint_extraction() */
	public function test_filter_request_after_endpoint_extraction() {
		$this->markTestIncomplete();
	}

	/** @covers ::restore_path_endpoint_in_environment() */
	public function test_restore_path_endpoint_in_environment() {
		$this->markTestIncomplete();
	}

	/** @covers ::filter_unique_post_slug() */
	public function test_filter_unique_post_slug() {
		$this->markTestIncomplete();
	}

	/** @covers ::add_paired_request_hooks() */
	public function test_add_paired_request_hooks() {
		$this->markTestIncomplete();
	}

	/** @covers ::get_wp_rewrite() */
	public function test_get_wp_rewrite() {
		$this->markTestIncomplete();
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->markTestIncomplete();
	}

	/** @covers ::sanitize_options() */
	public function test_sanitize_options() {
		$this->markTestIncomplete();
	}

	/** @covers ::has_endpoint() */
	public function test_has_endpoint() {
		$this->markTestIncomplete();
	}

	/** @covers ::add_endpoint() */
	public function test_add_endpoint() {
		$this->markTestIncomplete();
	}

	/** @covers ::remove_endpoint() */
	public function test_remove_endpoint() {
		$this->markTestIncomplete();
	}

	/** @covers ::has_custom_paired_url_structure() */
	public function test_has_custom_paired_url_structure() {
		$this->markTestIncomplete();
	}

	/** @covers ::get_all_structure_paired_urls() */
	public function test_get_all_structure_paired_urls() {
		$this->markTestIncomplete();
	}

	/** @covers ::get_paired_url_examples() */
	public function test_get_paired_url_examples() {
		$this->markTestIncomplete();
	}

	/** @covers ::get_custom_paired_structure_sources() */
	public function test_get_custom_paired_structure_sources() {
		$this->markTestIncomplete();
	}

	/** @covers ::correct_query_when_is_front_page() */
	public function test_correct_query_when_is_front_page() {
		$this->markTestIncomplete();
	}

	/** @covers ::maybe_add_paired_endpoint() */
	public function test_maybe_add_paired_endpoint() {
		$this->markTestIncomplete();
	}

	/** @covers ::redirect_extraneous_paired_endpoint() */
	public function test_redirect_extraneous_paired_endpoint() {
		$this->markTestIncomplete();
	}

	/** @covers ::redirect_paired_amp_unavailable() */
	public function test_redirect_paired_amp_unavailable() {
		$this->markTestIncomplete();
	}

	/** @covers ::redirect_location() */
	public function test_redirect_location() {
		$this->markTestIncomplete();
	}
}
