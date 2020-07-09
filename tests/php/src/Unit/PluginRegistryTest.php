<?php

namespace AmpProject\AmpWP\Tests\Unit;

use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\Tests\Helpers\MockPluginEnvironment;
use PHPUnit\Framework\TestCase;

final class PluginRegistryTest extends TestCase {

	private function populate_plugins() {
		wp_cache_set( 'plugins', [ '' => MockPluginEnvironment::PLUGINS_DATA ], 'plugins' );
		update_option( 'active_plugins', array_diff( array_keys( MockPluginEnvironment::PLUGINS_DATA ), [ MockPluginEnvironment::BAZ_PLUGIN_FILE ] ) );
	}

	public function test_it_can_be_initialized() {
		$plugin_registry = new PluginRegistry();

		$this->assertInstanceOf( PluginRegistry::class, $plugin_registry );
		$this->assertInstanceOf( Service::class, $plugin_registry );
	}

	/** @covers PluginRegistry::get_plugin_slug_from_file() */
	public function test_get_plugin_slug_from_file() {
		$plugin_registry = new PluginRegistry();

		$this->assertEquals( 'foo', $plugin_registry->get_plugin_slug_from_file( 'foo/foo.php' ) );
		$this->assertEquals( 'foo', $plugin_registry->get_plugin_slug_from_file( 'foo/extra.php' ) );
		$this->assertEquals( 'foo.php', $plugin_registry->get_plugin_slug_from_file( 'foo.php' ) );
	}

	/** @covers PluginRegistry::get_plugins() */
	public function test_get_plugins() {
		$this->populate_plugins();

		$plugin_registry = new PluginRegistry();

		$slugify = [ $plugin_registry, 'get_plugin_slug_from_file' ];

		$this->assertEqualSets(
			array_map( $slugify, array_keys( MockPluginEnvironment::PLUGINS_DATA ) ),
			array_keys( $plugin_registry->get_plugins( false, false ) )
		);

		$this->assertEqualSets(
			array_map( $slugify, array_diff( array_keys( MockPluginEnvironment::PLUGINS_DATA ), [ MockPluginEnvironment::AMP_PLUGIN_FILE, MockPluginEnvironment::GUTENBERG_PLUGIN_FILE ] ) ),
			array_keys( $plugin_registry->get_plugins( false, true ) )
		);

		$this->assertEqualSets(
			array_map( $slugify, [ MockPluginEnvironment::FOO_PLUGIN_FILE, MockPluginEnvironment::BAR_PLUGIN_FILE ] ),
			array_keys( $plugin_registry->get_plugins( true ) )
		);
	}

	/** @covers PluginRegistry::get_plugin_from_slug() */
	public function test_get_plugin_from_slug() {
		$this->populate_plugins();
		$plugin_registry = new PluginRegistry();

		$this->assertEquals(
			[
				'file' => MockPluginEnvironment::FOO_PLUGIN_FILE,
				'data' => MockPluginEnvironment::PLUGINS_DATA[ MockPluginEnvironment::FOO_PLUGIN_FILE ],
			],
			$plugin_registry->get_plugin_from_slug( $plugin_registry->get_plugin_slug_from_file( MockPluginEnvironment::FOO_PLUGIN_FILE ) )
		);

		$this->assertEquals(
			[
				'file' => MockPluginEnvironment::BAR_PLUGIN_FILE,
				'data' => MockPluginEnvironment::PLUGINS_DATA[ MockPluginEnvironment::BAR_PLUGIN_FILE ],
			],
			$plugin_registry->get_plugin_from_slug( $plugin_registry->get_plugin_slug_from_file( MockPluginEnvironment::BAR_PLUGIN_FILE ) )
		);

		$this->assertNull( $plugin_registry->get_plugin_from_slug( 'nobody' ) );
	}

	/**
	 * Asserts that the contents of two un-keyed, single arrays are equal, without accounting for the order of elements.
	 *
	 * @see \WP_UnitTestCase_Base::assertEqualSets()
	 *
	 * @param array $expected Expected array.
	 * @param array $actual   Array to check.
	 */
	public function assertEqualSets( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}
}
