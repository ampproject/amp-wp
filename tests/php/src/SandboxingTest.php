<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Comments_Sanitizer;
use AMP_Form_Sanitizer;
use AMP_Options_Manager;
use AMP_Script_Sanitizer;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Sandboxing;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;

/** @coversDefaultClass \AmpProject\AmpWP\Sandboxing */
class SandboxingTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/** @var Sandboxing */
	private $instance;

	public function set_up() {
		parent::set_up();
		$this->instance = $this->injector->make( Sandboxing::class );
	}

	public function test__construct() {
		$this->assertInstanceOf( Sandboxing::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
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
		$this->assertArrayHasKey( Option::SANDBOXING_LEVEL, $filtered );
	}

	/** @covers ::filter_default_options() */
	public function test_filter_default_options() {
		$this->assertEquals(
			[
				Option::SANDBOXING_LEVEL   => 1,
				Option::SANDBOXING_ENABLED => false,
			],
			$this->instance->filter_default_options( [] )
		);
	}

	/** @covers ::sanitize_options() */
	public function test_sanitize_options() {
		$this->assertEquals(
			[ Option::SANDBOXING_LEVEL => 2 ],
			$this->instance->sanitize_options(
				[ Option::SANDBOXING_LEVEL => 2 ],
				[ Option::SANDBOXING_LEVEL => 'bad' ]
			)
		);

		$this->assertEquals(
			[ Option::SANDBOXING_LEVEL => 3 ],
			$this->instance->sanitize_options(
				[ Option::SANDBOXING_LEVEL => 2 ],
				[ Option::SANDBOXING_LEVEL => 3 ]
			)
		);

		$this->assertEquals(
			[ Option::SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ Option::SANDBOXING_LEVEL => 1 ],
				[ Option::SANDBOXING_LEVEL => 0 ]
			)
		);

		$this->assertEquals(
			[ Option::SANDBOXING_LEVEL => 1 ],
			$this->instance->sanitize_options(
				[ Option::SANDBOXING_LEVEL => 1 ],
				[ Option::SANDBOXING_LEVEL => 4 ]
			)
		);
	}

	/** @covers ::add_hooks() */
	public function test_add_hooks_not_standard_mode() {
		$this->register_settings_and_set_user();
		AMP_Options_Manager::update_options(
			[
				Option::SANDBOXING_ENABLED => true,
				Option::THEME_SUPPORT      => AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
				Option::SANDBOXING_LEVEL   => 2,
			]
		);
		$this->instance->add_hooks();
		$this->assertFalse( has_filter( 'amp_meta_generator', [ $this->instance, 'filter_amp_meta_generator' ] ) );
	}

	/** @covers ::add_hooks() */
	public function test_add_hooks_without_enabled_level() {
		$this->register_settings_and_set_user();
		AMP_Options_Manager::update_options(
			[
				Option::SANDBOXING_ENABLED => false,
				Option::THEME_SUPPORT      => AMP_Theme_Support::STANDARD_MODE_SLUG,
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
	 * @covers ::add_hooks()
	 * @dataProvider get_data_to_test_add_hooks
	 */
	public function test_add_hooks( $level, $expected_sanitizer_args ) {
		$this->register_settings_and_set_user();
		AMP_Options_Manager::update_options(
			[
				Option::SANDBOXING_ENABLED => true,
				Option::THEME_SUPPORT      => AMP_Theme_Support::STANDARD_MODE_SLUG,
				Option::SANDBOXING_LEVEL   => $level,
			]
		);
		$this->instance->add_hooks();

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

	/** @return array */
	public function get_data_to_test_finalize_document() {
		return [
			'level_3_to_3'         => [
				'min_level'                    => 3,
				'body'                         => '<div></div>',
				'expected_level'               => 3,
				'expected_required_amp_markup' => true,
			],
			'level_1_to_2'         => [
				'min_level'                    => 1,
				'body'                         => sprintf( '<div %s></div>', ValidationExemption::PX_VERIFIED_TAG_ATTRIBUTE ),
				'expected_level'               => 2,
				'expected_required_amp_markup' => false,
			],
			'level_1_to_2_with_v0' => [
				'min_level'                    => 1,
				'body'                         => sprintf( '<script src="https://cdn.ampproject.org/v0/amp-analytics-0.1.mjs" async="" custom-element="amp-analytics" type="module" crossorigin="anonymous"></script><script async nomodule src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js" crossorigin="anonymous" custom-element="amp-analytics"></script><div %s></div>', ValidationExemption::PX_VERIFIED_TAG_ATTRIBUTE ),
				'expected_level'               => 2,
				'expected_required_amp_markup' => true,
			],
			'level_1_to_1'         => [
				'min_level'                    => 1,
				'body'                         => sprintf( '<div %s></div>', ValidationExemption::AMP_UNVALIDATED_TAG_ATTRIBUTE ),
				'expected_level'               => 1,
				'expected_required_amp_markup' => false,
			],
			'level_1_to_1_with_2'  => [
				'min_level'                    => 1,
				'body'                         => sprintf( '<div %s></div><div %s></div>', ValidationExemption::AMP_UNVALIDATED_TAG_ATTRIBUTE, ValidationExemption::PX_VERIFIED_TAG_ATTRIBUTE ),
				'expected_level'               => 1,
				'expected_required_amp_markup' => false,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_finalize_document
	 * @covers ::get_effective_level()
	 * @covers ::finalize_document()
	 * @covers ::remove_required_amp_markup_if_not_used()
	 */
	public function test_finalize_document_and_get_effective_level( $min_level, $body, $expected_level, $expected_required_amp_markup ) {
		$this->register_settings_and_set_user();
		AMP_Options_Manager::update_options(
			[
				Option::SANDBOXING_ENABLED => true,
				Option::SANDBOXING_LEVEL   => $min_level,
			]
		);

		$dom = Document::fromHtml(
			sprintf(
				'
				<html>
					<head>
						<link rel="preconnect" href="https://cdn.ampproject.org">
						<style amp-runtime="" i-amphtml-version="012110290545003">html{/*...*/}</style>
						<script async="" src="https://cdn.ampproject.org/v0.mjs" type="module" crossorigin="anonymous"></script>
						<script async nomodule src="https://cdn.ampproject.org/v0.js" crossorigin="anonymous"></script>
						<meta name="generator" content="AMP Plugin v2.1; foo=bar">
					</head>
					<body>
						%s

						<div id="wpadminbar" class="nojq">
							<div class="quicklinks" id="wp-toolbar" role="navigation" aria-label="Toolbar">
								<ul id="wp-admin-bar-root-default" class="ab-top-menu">
									<li id="wp-admin-bar-amp" class="menupop">
										<a class="ab-item" aria-haspopup="true" href="#" title="Validate URL"><span id="amp-admin-bar-item-status-icon" class="ab-icon amp-icon amp-valid"></span> AMP</a>
										<div class="ab-sub-wrapper">
											<ul id="wp-admin-bar-amp-default" class="ab-submenu">
												<li id="wp-admin-bar-amp-settings"><a class="ab-item" href="#">Settings</a></li>
												<li id="wp-admin-bar-amp-support"><a class="ab-item" href="#">Get support</a></li>
											</ul>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</body>
				</html>
				',
				$body
			)
		);

		$actual_effective_level = $this->instance->get_effective_level( $dom );
		$this->assertEquals( $expected_level, $actual_effective_level );

		$this->instance->finalize_document( $dom, $actual_effective_level );

		$meta = $dom->xpath->query( '//meta[ @name = "generator" ]' )->item( 0 );
		$this->assertInstanceOf( Element::class, $meta );
		$this->assertStringEndsWith( "foo=bar; sandboxing-level={$min_level}:{$expected_level}", $meta->getAttribute( Attribute::CONTENT ) );

		$expressions = [
			'//link[ @rel = "preconnect" and @href = "https://cdn.ampproject.org" ]',
			'//style[ @amp-runtime ]',
			'//script[ @src = "https://cdn.ampproject.org/v0.mjs" ]',
			'//script[ @src = "https://cdn.ampproject.org/v0.js" ]',
		];
		foreach ( $expressions as $expression ) {
			$this->assertEquals( $expected_required_amp_markup ? 1 : 0, $dom->xpath->query( $expression )->length, $expression );
		}

		$root_path = '//div[ @id = "wpadminbar" ]//li[ @id = "wp-admin-bar-amp" ]';
		$this->assertInstanceOf(
			Element::class,
			$dom->xpath->query( $root_path . '/a/span[ contains( @title, "Sandboxing level" ) ]' )->item( 0 )
		);
		$this->assertInstanceOf(
			Element::class,
			$dom->xpath->query( $root_path . '//li[ @id = "wp-admin-bar-amp-sandboxing-level" ]//a[ @href and contains( ., "Sandboxing level" ) ]' )->item( 0 )
		);
	}

	/**
	 * Get data to test remove_required_amp_markup_if_not_used()
	 *
	 * @return array
	 */
	public function get_data_to_test_remove_required_amp_markup_if_not_used() {
		return [
			'do_not_remove_required_amp_markup' => [
				'body'                         => '<amp-img src="https://example.com/image.jpg" width="100" height="100"></amp-img>',
				'expected_required_amp_markup' => true,
			],
			'remove_required_amp_markup'        => [
				'body'                         => '<img src="https://example.com/image.jpg" width="100" height="100">',
				'expected_required_amp_markup' => false,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_remove_required_amp_markup_if_not_used()
	 * @covers ::remove_required_amp_markup_if_not_used()
	 *
	 * @param string $body Body.
	 * @param bool   $expected_required_amp_markup Whether expected required AMP markup.
	 */
	public function test_remove_required_amp_markup_if_not_used( $body, $expected_required_amp_markup ) {
		$dom = Document::fromHtml(
			sprintf(
				'
				<html>
					<head>
						<link rel="preconnect" href="https://cdn.ampproject.org">
						<style amp-runtime="" i-amphtml-version="012110290545003">html{/*...*/}</style>
						<script async="" src="https://cdn.ampproject.org/v0.mjs" type="module" crossorigin="anonymous"></script>
						<script async nomodule src="https://cdn.ampproject.org/v0.js" crossorigin="anonymous"></script>
						<meta name="generator" content="AMP Plugin v2.1; foo=bar">
					</head>
					<body>
						%s

						<div id="wpadminbar" class="nojq">
							<div class="quicklinks" id="wp-toolbar" role="navigation" aria-label="Toolbar">
								<ul id="wp-admin-bar-root-default" class="ab-top-menu">
									<li id="wp-admin-bar-amp" class="menupop">
										<a class="ab-item" aria-haspopup="true" href="#" title="Validate URL"><span id="amp-admin-bar-item-status-icon" class="ab-icon amp-icon amp-valid"></span> AMP</a>
										<div class="ab-sub-wrapper">
											<ul id="wp-admin-bar-amp-default" class="ab-submenu">
												<li id="wp-admin-bar-amp-settings"><a class="ab-item" href="#">Settings</a></li>
												<li id="wp-admin-bar-amp-support"><a class="ab-item" href="#">Get support</a></li>
											</ul>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</body>
				</html>
				',
				$body
			)
		);

		// Keep effective level to 1 for testing because 3 will bail early.
		$this->call_private_method( $this->instance, 'remove_required_amp_markup_if_not_used', [ $dom, 1 ] );

		$expressions = [
			'//link[ @rel = "preconnect" and @href = "https://cdn.ampproject.org" ]',
			'//style[ @amp-runtime ]',
			'//script[ @src = "https://cdn.ampproject.org/v0.mjs" ]',
			'//script[ @src = "https://cdn.ampproject.org/v0.js" ]',
		];

		if ( $expected_required_amp_markup ) {
			$this->assertEquals( 1, $dom->ampElements->length );
		} else {
			$this->assertEquals( 0, $dom->ampElements->length );
		}

		foreach ( $expressions as $expression ) {
			$this->assertEquals( $expected_required_amp_markup ? 1 : 0, $dom->xpath->query( $expression )->length, $expression );
		}
	}

	/**
	 * Register settings and set the current user.
	 */
	private function register_settings_and_set_user() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->instance->register();
		AMP_Options_Manager::register_settings();
	}
}
