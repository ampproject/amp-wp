<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\SandboxingLevels;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AMP_Script_Sanitizer;
use AMP_Form_Sanitizer;
use AMP_Comments_Sanitizer;
use AMP_Img_Sanitizer;
use AMP_Core_Theme_Sanitizer;
use AMP_Gallery_Block_Sanitizer;

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

	/** @covers ::add_hooks() */
	public function test_add_hooks_not_standard_mode( $level ) {
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT                     => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				SandboxingLevels::OPTION_SANDBOXING_LEVEL => $level,
			]
		);
		$this->instance->add_hooks();
		$this->assertFalse( has_filter( 'amp_meta_generator', [ $this->instance, 'filter_amp_meta_generator' ] ) );
	}

	/** @return array */
	public function get_data_to_test_add_hooks() {
		$sanitizer_args_all_levels    = [
			AMP_Script_Sanitizer::class => [ 'sanitize_js_scripts' => true ],
		];
		$sanitizer_args_under_level_3 = [
			AMP_Form_Sanitizer::class          => [ 'native_post_forms_used' => true ],
			AMP_Comments_Sanitizer::class      => [ 'allow_commenting_scripts' => true ],
			AMP_Img_Sanitizer::class           => [ 'native_img_used' => true ],
			AMP_Core_Theme_Sanitizer::class    => [ 'native_img_used' => true ],
			AMP_Gallery_Block_Sanitizer::class => [ 'native_img_used' => true ],
		];

		return [
			'level_1' => [
				'level'                   => 1,
				'expected_sanitizer_args' => array_merge( $sanitizer_args_all_levels, $sanitizer_args_under_level_3 ),
			],
			'level_2' => [
				'level'                   => 2,
				'expected_sanitizer_args' => array_merge( $sanitizer_args_all_levels, $sanitizer_args_under_level_3 ),
			],
			'level_3' => [
				'level'                   => 3,
				'expected_sanitizer_args' => $sanitizer_args_all_levels,
			],
		];
	}

	/**
	 * @covers ::has_endpoint()
	 * @dataProvider get_data_to_test_add_hooks
	 *
	 */
	public function test_add_hooks( $level, $expected_sanitizer_args ) {
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT                     => AMP_Theme_Support::STANDARD_MODE_SLUG,
				SandboxingLevels::OPTION_SANDBOXING_LEVEL => $level,
			]
		);
		$this->instance->add_hooks();
		$this->assertEquals( 10, has_filter( 'amp_meta_generator', [ $this->instance, 'filter_amp_meta_generator' ] ) );

		$sanitizers = amp_get_content_sanitizers();
		foreach ( $expected_sanitizer_args as $sanitizer_class => $expected_args ) {
			$this->assertArrayHasKey( $sanitizer_class, $sanitizers );
			$this->assertEquals(
				$expected_args,
				wp_array_slice_assoc( $sanitizers[ $sanitizer_class ], array_keys( $expected_args ) )
			);
		}

		if ( 1 === $level ) {
			$this->assertEquals( 10, has_filter( 'amp_validation_error_default_sanitized', '__return_false' ) );
		} else {
			$this->assertFalse( has_filter( 'amp_validation_error_default_sanitized', '__return_false' ) );
		}

		$error = apply_filters( 'amp_validation_error', [] );
		$this->assertEquals( $level, $error['sandboxing_level'] );
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
