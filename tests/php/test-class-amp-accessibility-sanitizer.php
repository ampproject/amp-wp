<?php
/**
 * Class AMP_Accessibility_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the accessibility sanitizer class.
 *
 * @coversDefaultClass AMP_Accessibility_Sanitizer
 */
class AMP_Accessibility_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/** @var string */
	private $original_wp_current_template_content;

	/** @var array */
	private $original_wp_theme_features;

	/**
	 * Setup.
	 *
	 * @inheritDoc
	 */
	public function set_up() {

		parent::set_up();

		global $_wp_current_template_content, $_wp_theme_features;
		$this->original_wp_current_template_content = $_wp_current_template_content;
		$this->original_wp_theme_features           = $_wp_theme_features;
	}

	/**
	 * Tear down.
	 *
	 * @inheritDoc
	 */
	public function tear_down() {

		parent::tear_down();

		global $_wp_current_template_content, $_wp_theme_features;
		$_wp_current_template_content = $this->original_wp_current_template_content;
		$_wp_theme_features           = $this->original_wp_theme_features;
	}

	public function get_sanitize_test_data() {
		return [
			'valid markup remains unchanged'            => [
				'<div href="#" on="tap:some_id.toggleClass(some_class)" role="button" tabindex="0"></div>',
				'<div href="#" on="tap:some_id.toggleClass(some_class)" role="button" tabindex="0"></div>',
			],

			'missing role is added'                     => [
				'<div href="#" on="tap:some_id.toggleClass(some_class)" tabindex="1"></div>',
				'<div href="#" on="tap:some_id.toggleClass(some_class)" tabindex="1" role="button"></div>',
			],

			'missing tab index is added'                => [
				'<div href="#" on="tap:some_id.toggleClass(some_class)" role="button"></div>',
				'<div href="#" on="tap:some_id.toggleClass(some_class)" role="button" tabindex="0"></div>',
			],

			'missing both attributes'                   => [
				'<div href="#" on="tap:some_id.toggleClass(some_class)"></div>',
				'<div href="#" on="tap:some_id.toggleClass(some_class)" role="button" tabindex="0"></div>',
			],

			'no attributes needed on <a> elements'      => [
				'<a href="#" on="tap:some_id.toggleClass(some_class)"></a>',
				'<a href="#" on="tap:some_id.toggleClass(some_class)"></a>',
			],

			'no attributes needed on <button> elements' => [
				'<button href="#" on="tap:some_id.toggleClass(some_class)"></button>',
				'<button href="#" on="tap:some_id.toggleClass(some_class)"></button>',
			],
		];
	}

	/**
	 * Test sanitizing to enforce accessibility requirements.
	 *
	 * @dataProvider get_sanitize_test_data
	 * @covers AMP_Accessibility_Sanitizer::sanitize()
	 *
	 * @param string $source   Source HTML.
	 * @param string $expected Expected target HTML.
	 */
	public function test_sanitize( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Accessibility_Sanitizer( $dom );
		$sanitizer->sanitize();

		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $actual );
	}

	/**
	 * Data provider for $this->test_add_skip_link()
	 *
	 * @return string[][]
	 */
	public function get_skip_link_test_data() {

		$style_element = '<style id="amp-skip-link-styles">
			.skip-link.screen-reader-text {
				border: 0;
				clip: rect(1px,1px,1px,1px);
				clip-path: inset(50%);
				height: 1px;
				margin: -1px;
				overflow: hidden;
				padding: 0;
				position: absolute !important;
				width: 1px;
				word-wrap: normal !important;
			}

			.skip-link.screen-reader-text:focus {
				background-color: #eee;
				clip: auto !important;
				clip-path: none;
				color: #444;
				display: block;
				font-size: 1em;
				height: auto;
				left: 5px;
				line-height: normal;
				padding: 15px 23px 14px;
				text-decoration: none;
				top: 5px;
				width: auto;
				z-index: 100000;
			}
		</style>';

		return [
			'with_id'              => [
				'source'   => '<html><body><main id="main-container">Hello World!</main></body></html>',
				'expected' => $style_element . '<a class="skip-link screen-reader-text" href="#main-container">Skip to content</a><main id="main-container">Hello World!</main>',
			],
			'without_id'           => [
				'source'   => '<html><body><main>Hello World!</main></body></html>',
				'expected' => $style_element . '<a class="skip-link screen-reader-text" href="#wp--skip-link--target-0">Skip to content</a><main id="wp--skip-link--target-0">Hello World!</main>',
			],
			'without_main_element' => [
				'source'   => '<html><body><div id="main-container">Hello World!</div></body></html>',
				'expected' => '<div id="main-container">Hello World!</div>',
			],
		];

	}

	/**
	 * @dataProvider get_skip_link_test_data
	 *
	 * @covers ::add_skip_link()
	 *
	 * @param string $source   Source HTML.
	 * @param string $expected Expected target HTML.
	 */
	public function test_add_skip_link( $source, $expected ) {

		global $_wp_current_template_content, $_wp_theme_features;

		$_wp_current_template_content          = true;
		$_wp_theme_features['block-templates'] = true;

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Accessibility_Sanitizer( $dom );
		$sanitizer->add_skip_link();

		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $actual );
	}

	/**
	 * @covers ::add_skip_link()
	 */
	public function test_add_skip_link_for_none_fse_theme() {

		global $_wp_current_template_content, $_wp_theme_features;

		$source   = '<html><body><main id="main-container">Hello World!</main></body></html>';
		$expected = '<main id="main-container">Hello World!</main>';

		// Test 1: If it's not block theme.
		unset( $_wp_theme_features['block-templates'] );

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Accessibility_Sanitizer( $dom );
		$sanitizer->add_skip_link();
		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $actual );

		// Test 2: If it's not block template.
		$_wp_theme_features['block-templates'] = true;
		$_wp_current_template_content          = false;

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Accessibility_Sanitizer( $dom );
		$sanitizer->add_skip_link();
		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $actual );

	}
}
