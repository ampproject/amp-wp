<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\AmpSlugCustomizationWatcher;

/** @coversDefaultClass \AmpProject\AmpWP\AmpSlugCustomizationWatcher */
final class AmpSlugCustomizationWatcherTest extends TestCase {

	/** @var AmpSlugCustomizationWatcher */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = new AmpSlugCustomizationWatcher();
	}

	public function test__construct() {
		$this->assertInstanceOf( AmpSlugCustomizationWatcher::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( 8, has_action( 'plugins_loaded', [ $this->instance, 'determine_early_customization' ] ) );
	}

	/** @covers ::determine_early_customization() */
	public function test_determine_early_customization() {
		$this->assertFalse( $this->instance->did_customize_early() );
		$this->assertFalse( $this->instance->did_customize_late() );

		$early_slug = 'early';
		$this->add_query_var_filter( $early_slug );
		$this->assertEquals( $early_slug, amp_get_slug() );
		$this->instance->determine_early_customization();
		$this->assertFalse( has_action( 'after_setup_theme', [ $this->instance, 'determine_late_customization' ] ) );

		$this->assertTrue( $this->instance->did_customize_early() );
		$this->assertFalse( $this->instance->did_customize_late() );
	}

	/**
	 * @covers ::determine_early_customization()
	 * @covers ::determine_late_customization()
	 */
	public function test_determine_late_customization() {
		$this->assertFalse( $this->instance->did_customize_early() );
		$this->assertFalse( $this->instance->did_customize_late() );

		$this->instance->determine_early_customization();
		$this->assertEquals( 4, has_action( 'after_setup_theme', [ $this->instance, 'determine_late_customization' ] ) );

		$late_slug = 'late';
		$this->add_query_var_filter( $late_slug );
		$this->assertEquals( $late_slug, amp_get_slug() );

		$this->instance->determine_late_customization();

		$this->assertFalse( $this->instance->did_customize_early() );
		$this->assertTrue( $this->instance->did_customize_late() );
	}

	/**
	 * Add query var filter.
	 *
	 * @param string $value Query var.
	 */
	private function add_query_var_filter( $value ) {
		remove_all_filters( 'amp_query_var' );
		add_filter(
			'amp_query_var',
			static function () use ( $value ) {
				return $value;
			}
		);
	}
}
