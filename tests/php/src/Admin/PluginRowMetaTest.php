<?php
/**
 * Tests for PluginRowMeta class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\PluginRowMeta;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for PluginRowMeta class.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\PluginRowMeta
 */
class PluginRowMetaTest extends TestCase {

	/**
	 * Test instance.
	 *
	 * @var PluginRowMeta
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		$this->instance = new PluginRowMeta();
	}

	public function test__construct() {
		$this->assertInstanceOf( PluginRowMeta::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Test ::get_registration_action().
	 *
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {
		self::assertEquals( 'admin_init', PluginRowMeta::get_registration_action() );
	}

	/**
	 * Test ::register().
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();

		self::assertEquals( 10, has_filter( 'plugin_row_meta', [ $this->instance, 'get_plugin_row_meta' ] ) );
	}

	/**
	 * Test ::get_plugin_row_meta().
	 *
	 * @covers ::get_plugin_row_meta()
	 */
	public function test_get_plugin_row_meta() {
		$initial_meta = [
			'Link 1',
			'Link 2',
		];

		$this->assertEquals( $initial_meta, $this->instance->get_plugin_row_meta( $initial_meta, 'foo.php' ) );

		$expected_meta = array_merge(
			$initial_meta,
			[
				'<a href="https://wordpress.org/support/plugin/amp/reviews/#new-post" target="_blank" rel="noreferrer noopener">Leave review</a>',
			]
		);

		$this->assertEquals( $expected_meta, $this->instance->get_plugin_row_meta( $initial_meta, 'amp/amp.php' ) );
	}
}
