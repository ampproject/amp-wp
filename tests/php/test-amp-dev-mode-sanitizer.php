<?php
/**
 * Test AMP_Dev_Mode_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * Test AMP_Dev_Mode_Sanitizer.
 *
 * @covers AMP_Dev_Mode_Sanitizer
 */
class AMP_Dev_Mode_Sanitizer_Test extends TestCase {

	/**
	 * Test sanitize method.
	 *
	 * @covers \AMP_Dev_Mode_Sanitizer::sanitize()
	 */
	public function test_sanitize() {
		$dom = AMP_DOM_Utils::get_dom_from_content(
			'
			<p id="no-dev-mode-needed">All good.</p>
			<button id="greet" onclick="alert(\'Hi!\')">Greet</button>
			<div id="wpadminbar">
				<li id="wp-admin-bar-img"><img src="about:blank" alt="Blank!"></li>
			</div>
			<style id="admin-bar-inline-css">body { color:red !important; }</style>
			'
		);

		$sanitizer = new AMP_Dev_Mode_Sanitizer(
			$dom,
			[
				'element_xpaths' => [
					'//*[ @id = "greet" ]',
					'//*[ @id = "wpadminbar" ]',
					'//*[ @id = "wpadminbar" ]//*',
					'//style[ @id = "admin-bar-inline-css" ]',
				],
			]
		);
		$sanitizer->sanitize();

		// Assert dev mode is set correctly on elements.
		$this->assertFalse( $dom->hasInitialAmpDevMode() );
		$this->assertTrue( $dom->documentElement->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertTrue( $dom->getElementById( 'greet' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertTrue( $dom->getElementById( 'wpadminbar' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertTrue( $dom->getElementById( 'wp-admin-bar-img' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertTrue( $dom->getElementById( 'admin-bar-inline-css' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertFalse( $dom->getElementById( 'no-dev-mode-needed' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
	}
}
