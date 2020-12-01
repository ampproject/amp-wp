<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Tests\Editor\EditorSupport */
final class EditorSupportTest extends WP_UnitTestCase {

	/** @var EditorSupport */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new EditorSupport();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( EditorSupport::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 99, has_action( 'admin_enqueue_scripts', [ $this->instance, 'maybe_show_notice' ] ) );
	}

	/** @covers ::has_support_from_gutenberg_plugin */
	public function test_has_support_from_gutenberg_plugin() {
		if ( defined( 'GUTENBERG_VERSION' ) ) {
			$this->assertTrue( $this->instance->has_support_from_gutenberg_plugin() );
		} else {
			if ( version_compare( get_bloginfo( 'version' ), EditorSupport::WP_MIN_VERSION, '>=' ) ) {
				$this->assertTrue( $this->instance->has_support_from_core() );
			} else {
				$this->assertFalse( $this->instance->has_support_from_core() );
			}
		}
	}
}
