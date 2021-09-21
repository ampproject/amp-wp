<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Sandboxing;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AMP_Script_Sanitizer;
use AMP_Form_Sanitizer;
use AMP_Comments_Sanitizer;

/** @coversDefaultClass \AmpProject\AmpWP\Sandboxing */
class SandboxingTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/** @var Sandboxing */
	private $instance;

	public function setUp() {
		parent::setUp();
		$this->instance = $this->injector->make( Sandboxing::class );
	}

	public function test__construct() {
		$this->assertInstanceOf( Sandboxing::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		$this->assertFalse( Sandboxing::is_needed() );
		add_filter( 'amp_experimental_sandboxing_enabled', '__return_true' );
		$this->assertTrue( Sandboxing::is_needed() );
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
		$this->assertArrayHasKey( Sandboxing::OPTION_SANDBOXING_LEVEL, $filtered );
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->assertEquals(
			[ Sandboxing::OPTION_SANDBOXING_LEVEL => Sandboxing::DEFAULT_SANDBOXING_LEVEL ],
			$this->instance->filter_default_options( [] )
		);
	}

	/** @covers ::sanitize_options() */
	public function test_sanitize_options() {
		$this->assertEquals(
			[ Sandboxing::OPTION_SANDBOXING_LEVEL => 2 ],
			$this->instance->sanitize_options(
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 2 ],
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 'bad' ]
			)
		);

		$this->assertEquals(
			[ Sandboxing::OPTION_SANDBOXING_LEVEL => 3 ],
			$this->instance->sanitize_options(
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 2 ],
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 3 ]
			)
		);

		$this->assertEquals(
			[ Sandboxing::OPTION_SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 1 ],
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 0 ]
			)
		);

		$this->assertEquals(
			[ Sandboxing::OPTION_SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 1 ],
				[ Sandboxing::OPTION_SANDBOXING_LEVEL => 4 ]
			)
		);
	}

	/** @covers ::add_hooks() */
	public function test_add_hooks_not_standard_mode() {
		AMP_Options_Manager::update_options(
			[
				Option::THEME_SUPPORT               => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Sandboxing::OPTION_SANDBOXING_LEVEL => 2,
			]
		);
		$this->instance->add_hooks();
		$this->assertFalse( has_filter( 'amp_meta_generator', [ $this->instance, 'filter_amp_meta_generator' ] ) );
	}

	/** @return array */
	public function get_data_to_test_add_hooks() {
		return [
			'level_1' => [
				'level'                   => 1,
				'expected_sanitizer_args' => [
					AMP_Script_Sanitizer::class   => [
						'sanitize_js_scripts'   => true,
						'comment_reply_allowed' => 'conditionally',
					],
					AMP_Form_Sanitizer::class     => [ 'native_post_forms_allowed' => 'conditionally' ],
					AMP_Comments_Sanitizer::class => [ 'ampify_comment_threading' => 'conditionally' ],
				],
			],
			'level_2' => [
				'level'                   => 2,
				'expected_sanitizer_args' => [
					AMP_Script_Sanitizer::class   => [
						'sanitize_js_scripts'   => true,
						'comment_reply_allowed' => 'conditionally',
					],
					AMP_Form_Sanitizer::class     => [ 'native_post_forms_allowed' => 'conditionally' ],
					AMP_Comments_Sanitizer::class => [ 'ampify_comment_threading' => 'conditionally' ],
				],
			],
			'level_3' => [
				'level'                   => 3,
				'expected_sanitizer_args' => [
					AMP_Script_Sanitizer::class => [ 'sanitize_js_scripts' => true ],
				],
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
				Option::THEME_SUPPORT               => AMP_Theme_Support::STANDARD_MODE_SLUG,
				Sandboxing::OPTION_SANDBOXING_LEVEL => $level,
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
		AMP_Options_Manager::update_option( Sandboxing::OPTION_SANDBOXING_LEVEL, 2 );
		$this->assertEquals(
			'PX Plugin 4.0; sandboxing-level=2',
			$this->instance->filter_amp_meta_generator( 'PX Plugin 4.0' )
		);

		AMP_Options_Manager::update_option( Sandboxing::OPTION_SANDBOXING_LEVEL, 1 );
		$this->assertEquals(
			'PX Plugin 4.0; sandboxing-level=1',
			$this->instance->filter_amp_meta_generator( 'PX Plugin 4.0' )
		);
	}
}
