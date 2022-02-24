<?php
/**
 * Class AMP_Auto_Lightbox_Disable_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the auto lightbox disable sanitizer class.
 *
 * @coversDefaultClass AMP_Auto_Lightbox_Disable_Sanitizer
 */
class AMP_Auto_Lightbox_Disable_Sanitizer_Test extends TestCase {

	use MarkupComparison;

	/**
	 * @covers ::sanitize()
	 */
	public function test_sanitize() {
		$source   = '<html><body class="body-class"></body></html>';
		$expected = '<html><body class="body-class" data-amp-auto-lightbox-disable></body></html>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Auto_Lightbox_Disable_Sanitizer( $dom );
		$sanitizer->sanitize();

		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $actual );

	}
}
