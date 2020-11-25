<?php
/**
 * Tests for the BlockSources class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\BlockSources;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\Tests\Helpers\MockPluginEnvironment;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_UnitTestCase;

/**
 * Tests for the BlockSources class.
 *
 * @since 2.1
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\BlockSources
 */
class BlockSourcesTest extends WP_UnitTestCase {

	use PrivateAccess;

	private function populate_plugins() {
		wp_cache_set( 'plugins', [ '' => MockPluginEnvironment::PLUGINS_DATA ], 'plugins' );
		update_option( 'active_plugins', array_diff( array_keys( MockPluginEnvironment::PLUGINS_DATA ), [ MockPluginEnvironment::BAZ_PLUGIN_FILE ] ) );
	}

	/**
	 * Test instance.
	 *
	 * @var BlockSources
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new BlockSources( new PluginRegistry() );
		$this->populate_plugins();
	}

	/**
	 * Tear down method. Runs after each test.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		parent::tearDown();

		$this->instance->clear_block_sources_cache();
	}

	public function test__construct() {
		$this->assertInstanceOf( BlockSources::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
	}

	/** @covers ::register */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 10, has_filter( 'register_block_type_args', [ $this->instance, 'capture_block_type_source' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'admin_enqueue_scripts', [ $this->instance, 'cache_block_sources' ] ) );
		$this->assertEquals( 10, has_action( 'activated_plugin', [ $this->instance, 'clear_block_sources_cache' ] ) );
		$this->assertEquals( 10, has_action( 'after_switch_theme', [ $this->instance, 'clear_block_sources_cache' ] ) );
		$this->assertEquals( 10, has_action( 'upgrader_process_complete', [ $this->instance, 'clear_block_sources_cache' ] ) );
	}

	/**
	 * @covers ::capture_block_type_source()
	 * @covers ::get_block_sources()
	 */
	public function test_capture_block_type_source() {
		// Test registration of a core block.
		$this->instance->capture_block_type_source(
			[
				'name' => 'core/test-block',
			]
		);

		$this->assertEquals(
			[
				'core/test-block' => [
					'source' => 'core',
					'name'   => null,
				],
			],
			$this->instance->get_block_sources()
		);

		// Test that re-registering the same block doesn't change anything.
		$this->instance->capture_block_type_source(
			[
				'name' => 'core/test-block',
			]
		);

		$this->assertEquals(
			[
				'core/test-block' => [
					'source' => 'core',
					'name'   => null,
				],
			],
			$this->instance->get_block_sources()
		);

		// Test block with plugin source.
		$this->instance->capture_block_type_source(
			[
				'name' => 'amp/block',
			]
		);

		$this->assertEquals(
			[
				'core/test-block' => [
					'source' => 'core',
					'name'   => null,
				],
				'amp/block'       => [
					'source' => 'plugin',
					'name'   => 'AMP',
				],
			],
			$this->instance->get_block_sources()
		);
	}

	/** @covers ::get_source_from_file_list() */
	public function test_get_theme_source_from_file_list() {
		$file_list = [
			WP_CONTENT_DIR . '/themes/some-other-theme/functions.php',
			get_stylesheet_directory() . '/functions.php',
			WP_CONTENT_DIR . '/plugins/my-plugin/my-plugin',
			ABSPATH . 'wp-config.php',
		];

		$expected = [
			'source' => 'theme',
			'name'   => 'WordPress Default',
		];

		$actual = $this->call_private_method( $this->instance, 'get_source_from_file_list', [ $file_list ] );

		$this->assertEquals( $expected, $actual );
	}

	/** @covers ::get_source_from_file_list() */
	public function test_get_plugin_with_subdirectory_source_from_file_list() {
		$file_list = [
			WP_CONTENT_DIR . '/plugins/my-plugin/my-plugin',
			WP_CONTENT_DIR . '/plugins/foo/foo.php',
			WP_CONTENT_DIR . '/themes/some-other-theme/functions.php',
			ABSPATH . 'wp-config.php',
		];

		$expected = [
			'source' => 'plugin',
			'name'   => 'Foo',
		];

		$actual = $this->call_private_method( $this->instance, 'get_source_from_file_list', [ $file_list ] );

		$this->assertEquals( $expected, $actual );
	}

	/** @covers ::get_source_from_file_list() */
	public function test_unknown_source_from_file_list() {
		$file_list = [
			'/var/www/html/some/other/path.php',
			WP_CONTENT_DIR . '/themes/some-other-theme/functions.php',
			ABSPATH . 'wp-config.php',
		];

		$expected = [
			'source' => 'unknown',
			'name'   => null,
		];

		$actual = $this->call_private_method( $this->instance, 'get_source_from_file_list', [ $file_list ] );

		$this->assertEquals( $expected, $actual );
	}

	/** @covers ::get_source_from_file_list() */
	public function test_get_single_file_plugin_source_from_file_list() {
		$file_list = [
			WP_CONTENT_DIR . '/plugins/my-plugin/my-plugin',
			WP_CONTENT_DIR . '/plugins/bar.php',
			WP_CONTENT_DIR . '/themes/some-other-theme/functions.php',
			ABSPATH . 'wp-config.php',
		];

		$expected = [
			'source' => 'plugin',
			'name'   => 'Bar',
		];

		$actual = $this->call_private_method( $this->instance, 'get_source_from_file_list', [ $file_list ] );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Provides whether to test with object cache off or on.
	 */
	public function get_using_object_cache() {
		return [
			[ true ],
			[ false ],
		];
	}

	/**
	 * @covers ::get_block_sources()
	 * @covers ::cache_block_sources()
	 * @covers ::clear_block_sources_cache()
	 * @covers ::set_block_sources_from_cache()
	 * @dataProvider get_using_object_cache
	 */
	public function test_caching( $using_object_cache ) {
		$original_using_object_cache = wp_using_ext_object_cache( $using_object_cache );

		$this->assertEquals( [], $this->instance->get_block_sources() );

		$test_data = [
			'core/test-block' => [
				'source' => 'core',
				'name'   => null,
			],
			'unknown/block'   => [
				'source' => 'unknown',
				'name'   => null,
			],
		];

		$this->set_private_property( $this->instance, 'block_sources', $test_data );
		$this->instance->cache_block_sources();

		$this->instance = null;
		$this->instance = new BlockSources( new PluginRegistry() );
		$this->assertEquals( $test_data, $this->instance->get_block_sources() );

		$this->instance->clear_block_sources_cache();

		$this->instance = null;
		$this->instance = new BlockSources( new PluginRegistry() );
		$this->assertEquals( [], $this->instance->get_block_sources() );

		wp_using_ext_object_cache( $original_using_object_cache );
	}
}
