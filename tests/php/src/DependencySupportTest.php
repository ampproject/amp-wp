<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Infrastructure\Service;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\DependencySupport */
class DependencySupportTest extends TestCase {

	/** @var DependencySupport */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new DependencySupport();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/** @covers ::has_support() */
	public function test_has_support() {
		$gutenberg_supported = defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, DependencySupport::GB_MIN_VERSION, '>=' )
			? GUTENBERG_VERSION
			: null;
		$wp_supported        = version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '>=' );

		if ( $gutenberg_supported && $wp_supported ) {
			$this->assertTrue( $this->instance->has_support() );
		} elseif ( ! $gutenberg_supported && $wp_supported ) {
			$this->assertTrue( $this->instance->has_support() );
		} elseif ( $gutenberg_supported && ! $wp_supported ) {
			$this->assertTrue( $this->instance->has_support() );
		} else {
			$this->assertFalse( $this->instance->has_support() );
		}
	}

	/** @covers ::has_support_from_core() */
	public function test_has_support_from_core() {
		if ( version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '>=' ) ) {
			$this->assertTrue( $this->instance->has_support_from_core() );
		} else {
			$this->assertFalse( $this->instance->has_support_from_core() );
		}
	}

	/** @covers ::has_support_from_gutenberg_plugin */
	public function test_has_support_from_gutenberg_plugin() {
		if ( defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, DependencySupport::GB_MIN_VERSION, '>=' ) ) {
			$this->assertTrue( $this->instance->has_support_from_gutenberg_plugin() );
		} else {
			$this->assertFalse( $this->instance->has_support_from_gutenberg_plugin() );
		}
	}
}
