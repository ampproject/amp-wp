<?php
/**
 * Class AMP_Auto_Lightbox_Disable_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the auto lightbox disable sanitizer class.
 *
 * @coversDefaultClass AMP_Auto_Lightbox_Disable_Sanitizer
 */
class AMP_Auto_Lightbox_Disable_Sanitizer_Test extends TestCase {

	/**
	 * @covers ::sanitize()
	 */
	public function test_sanitize() {

		$source = '<html><body class="body-class"><span>Hello World!</span></body></html>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Auto_Lightbox_Disable_Sanitizer( $dom );
		$sanitizer->sanitize();

		$this->assertTrue( $dom->html->hasAttribute( 'data-amp-auto-lightbox-disable' ) );
	}
}
