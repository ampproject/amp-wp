<?php
/**
 * Class AMP_Accessibility_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Tests the accessibility sanitizer class.
 */
class AMP_Accessibility_Sanitizer_Test extends TestCase {

	use MarkupComparison;

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
}
