<?php
/**
 * Tests for the BlockSources class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\BlockSources;
use AmpProject\AmpWP\DevTools\FileReflection;
use AmpProject\AmpWP\DevTools\LikelyCulpritDetector;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\Tests\Helpers\MockPluginEnvironment;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Tests for the BlockSources class.
 *
 * @since 2.1
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\BlockSources
 */
class BlockSourcesTest extends TestCase {

	use PrivateAccess;

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

		$this->instance = $this->get_new_instance();
	}

	/**
	 * @return BlockSources.
	 */
	public function get_new_instance() {
		$plugin_registry = new PluginRegistry();
		return new BlockSources( $plugin_registry, new LikelyCulpritDetector( new FileReflection( $plugin_registry ) ) );
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

	/** @covers ::is_needed() */
	public function test_is_needed() {
		if ( version_compare( get_bloginfo( 'version' ), '5.5', '<' ) ) {
			$this->assertFalse( BlockSources::is_needed() );
		} else {
			$this->assertFalse( BlockSources::is_needed() );
			set_current_screen( 'edit.php' );
			$this->assertTrue( BlockSources::is_needed() );
		}
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
		if ( version_compare( get_bloginfo( 'version' ), '5.5', '<' ) ) {
			$this->markTestSkipped( 'Detecting block sources requires WordPress 5.5.' );
		}

		$this->instance->clear_block_sources_cache();
		$this->instance->register();

		// Test registration of a core block.
		register_block_type( 'core/test-block' );

		$this->assertEquals(
			[
				'core/test-block' => [
					'name'  => '',
					'type'  => '',
					'title' => 'WordPress core',
				],
			],
			$this->instance->get_block_sources()
		);

		require_once MockPluginEnvironment::BAD_PLUGINS_DIR . '/' . MockPluginEnvironment::BAD_BLOCK_PLUGIN_FILE;

		$this->assertEquals(
			[
				'core/test-block' => [
					'name'  => '',
					'type'  => '',
					'title' => 'WordPress core',
				],
				// @todo How to test this with a real plugin in the plugins directory.
				'bad/bad-block'   => [
					'name'  => '',
					'type'  => '',
					'title' => 'WordPress core',
				],
			],
			$this->instance->get_block_sources()
		);
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
	 *
	 * @param bool $using_object_cache Using object cache.
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

		$this->instance = $this->get_new_instance();
		$this->assertEquals( $test_data, $this->instance->get_block_sources() );

		$this->instance->clear_block_sources_cache();

		$this->instance = $this->get_new_instance();
		$this->assertEquals( [], $this->instance->get_block_sources() );

		wp_using_ext_object_cache( $original_using_object_cache );
	}
}
