<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\AmpSlugCustomizationWatcher;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\SandboxingLevels;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\PairedUrl;
use AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\LegacyTransitionalUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\PathSuffixUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Fixture\DummyPairedUrlStructure;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_Query;
use Exception;

/** @coversDefaultClass \AmpProject\AmpWP\SandboxingLevels */
class SandboxingLevelsTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/** @var SandboxingLevels */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( SandboxingLevels::class );
	}

	public function test__construct() {
		$this->assertInstanceOf( SandboxingLevels::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		$this->assertFalse( SandboxingLevels::is_needed() );
		add_filter( 'amp_experimental_sandboxing_enabled', '__return_true' );
		$this->assertTrue( SandboxingLevels::is_needed() );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 10, has_filter( 'amp_rest_options_schema', [ $this->instance, 'filter_rest_options_schema' ] ) );

		$this->assertEquals( 10, has_filter( 'amp_default_options', [ $this->instance, 'filter_default_options' ] ) );
		$this->assertEquals( 10, has_filter( 'amp_options_updating', [ $this->instance, 'sanitize_options' ] ) );

		$this->assertEquals( 10, has_action( 'init', [ $this->instance, 'add_hooks' ] ) );
	}

	/** @covers ::filter_rest_options_schema() */
	public function test_filter_rest_options_schema() {
		$existing = [
			'foo' => [
				'type' => 'string',
			],
		];

		$filtered = $this->instance->filter_rest_options_schema( $existing );
		$this->assertArrayHasKey( 'foo', $filtered );
		$this->assertArrayHasKey( SandboxingLevels::OPTION_SANDBOXING_LEVEL, $filtered );
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->assertEquals(
			[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => SandboxingLevels::DEFAULT_SANDBOXING_LEVEL ],
			$this->instance->filter_default_options( [] )
		);
	}

	/** @covers ::sanitize_options() */
	public function test_sanitize_options() {
		$this->assertEquals(
			[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 2 ],
			$this->instance->sanitize_options(
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 2 ],
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 'bad' ]
			)
		);

		$this->assertEquals(
			[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 3 ],
			$this->instance->sanitize_options(
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 2 ],
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 3 ]
			)
		);

		$this->assertEquals(
			[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 1 ],
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 0 ]
			)
		);

		$this->assertEquals(
			[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 1 ],
				[ SandboxingLevels::OPTION_SANDBOXING_LEVEL => 4 ]
			)
		);
	}

	/** @return array */
	public function get_data_to_test_add_hooks() {
		return [];
	}

	/**
	 * @covers ::has_endpoint()
	 * @dataProvider get_data_to_test_add_hooks
	 *
	 */
	public function test_add_hooks() {
		$this->markTestIncomplete();
	}

	/** @covers ::filter_amp_meta_generator() */
	public function test_filter_amp_meta_generator() {
		AMP_Options_Manager::update_option( SandboxingLevels::OPTION_SANDBOXING_LEVEL, 2 );
		$this->assertEquals(
			'PX Plugin 4.0; sandboxing-level=2',
			$this->instance->filter_amp_meta_generator( 'PX Plugin 4.0' )
		);

		AMP_Options_Manager::update_option( SandboxingLevels::OPTION_SANDBOXING_LEVEL, 1 );
		$this->assertEquals(
			'PX Plugin 4.0; sandboxing-level=1',
			$this->instance->filter_amp_meta_generator( 'PX Plugin 4.0' )
		);
	}
}
