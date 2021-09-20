<?php
/**
 * Tests for FileReflection class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\FileReflection;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;

/**
 * Tests for FileReflection class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\FileReflection
 */
class FileReflectionTest extends DependencyInjectedTestCase {

	/**
	 * Test instance.
	 *
	 * @var FileReflection
	 */
	private $file_reflection;

	public function set_up() {
		parent::set_up();
		$this->file_reflection = $this->injector->make( FileReflection::class );
	}

	/**
	 * Tests FileReflection::register().
	 *
	 * @covers ::register
	 */
	public function test_register() {
		$this->file_reflection->register();
		$this->assertEquals( ~PHP_INT_MAX, has_action( 'setup_theme', [ $this->file_reflection, 'reset_theme_variables' ] ) );
	}

	/**
	 * Tests FileReflection::get_file_source().
	 *
	 * Note that this is mainly tested in CallbackReflectionTest.
	 *
	 * @see \AmpProject\AmpWP\Tests\DevTools\CallbackReflectionTest::test_get_source()
	 * @covers ::get_file_source
	 */
	public function test_get_file_source_plugin() {
		$source = $this->file_reflection->get_file_source( __FILE__ );
		$this->assertEquals( 'plugin', $source['type'] );
		$this->assertEquals( 'amp', $source['name'] );
	}

	/**
	 * Tests FileReflection::get_file_source() for unknown file.
	 *
	 * @covers ::get_file_source
	 */
	public function test_get_file_source_unknown() {
		$this->assertEquals( [], $this->file_reflection->get_file_source( '/tmp' ) );
	}
}
