<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Block_Uniqid_Sanitizer;
use AmpProject\AmpWP\BlockUniqidTransformer;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/** @coversDefaultClass \AmpProject\AmpWP\BlockUniqidTransformer */
final class BlockUniqidTransformerTest extends DependencyInjectedTestCase {

	use MarkupComparison;

	/** @var BlockUniqidTransformer */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new BlockUniqidTransformer();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/**
	 * @covers ::is_needed()
	 * @covers ::is_affected_gutenberg_version()
	 * @covers ::is_affected_wordpress_version()
	 */
	public function test_is_needed() {
		$instance = $this->injector->make( BlockUniqidTransformer::class );

		if (
			(
				defined( 'GUTENBERG_VERSION' )
				&&
				version_compare( GUTENBERG_VERSION, '10.7', '>=' )
				&&
				version_compare( GUTENBERG_VERSION, '12.7', '<' )
			)
			||
			(
				version_compare( get_bloginfo( 'version' ), '5.8', '>=' )
				&&
				version_compare( get_bloginfo( 'version' ), '6.0', '<' )
			)
		) {
			$this->assertTrue( $instance->is_needed() );
		} else {
			$this->assertFalse( $instance->is_needed() );
		}
	}

	/**
	 * @covers ::is_affected_gutenberg_version()
	 */
	public function test_is_affected_gutenberg_version() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::is_affected_wordpress_version()
	 */
	public function test_is_affected_wordpress_version() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		remove_all_filters( 'amp_content_sanitizers' );

		$this->assertArrayNotHasKey(
			AMP_Block_Uniqid_Sanitizer::class,
			amp_get_content_sanitizers()
		);

		// @todo This needs to force the WP version.
		$this->instance->register();

		if ( $this->instance->is_needed() ) {
			$this->assertArrayHasKey(
				AMP_Block_Uniqid_Sanitizer::class,
				amp_get_content_sanitizers()
			);
		} else {
			$this->assertArrayNotHasKey(
				AMP_Block_Uniqid_Sanitizer::class,
				amp_get_content_sanitizers()
			);
		}
	}
}
