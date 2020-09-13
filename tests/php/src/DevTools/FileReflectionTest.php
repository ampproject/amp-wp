<?php
/**
 * Tests for FileReflection class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\FileReflection;
use AmpProject\AmpWP\PluginRegistry;
use WP_UnitTestCase;

/**
 * Tests for FileReflection class.
 *
 * @since 2.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\FileReflection
 */
class FileReflectionTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var FileReflection
	 */
	private $file_reflection;

	public function setUp() {
		parent::setUp();

		$plugin_registry       = new PluginRegistry();
		$this->file_reflection = new FileReflection( $plugin_registry );
	}

	/**
	 * Tests FileReflection::register
	 *
	 * @covers ::register
	 */
	public function test_register() {
		$this->file_reflection->register();
		$this->assertEquals( ~PHP_INT_MAX, has_action( 'setup_theme', [ $this->file_reflection, 'reset_theme_variables' ] ) );
	}

	/**
	 * Tests FileReflection::test_get_file_source
	 *
	 * Note that this is mainly tested in CallbackReflectionTest.
	 *
	 * @see \AmpProject\AmpWP\Tests\DevTools\CallbackReflectionTest::test_get_source()
	 * @covers ::get_file_source
	 */
	public function test_get_file_source() {
		$source = $this->file_reflection->get_file_source( __FILE__ );
		$this->assertEquals( 'plugin', $source['type'] );
		$this->assertEquals( 'amp', $source['name'] );
	}
}
