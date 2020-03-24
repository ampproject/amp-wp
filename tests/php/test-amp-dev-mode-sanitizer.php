<?php
/**
 * Test AMP_Dev_Mode_Sanitizer.
 *
 * @package AMP
 */

/**
 * Test AMP_Dev_Mode_Sanitizer.
 *
 * @covers AMP_Dev_Mode_Sanitizer
 */
class AMP_Dev_Mode_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Test sanitize method.
	 *
	 * @covers \AMP_Dev_Mode_Sanitizer::sanitize()
	 */
	public function test_sanitize() {
		$dom = AMP_DOM_Utils::get_dom_from_content(
			'
			<p id="no-dev-mode-needed">All good.</p>
			<p id="errors-ignored" data-ampdevmode onclick="alert(\'Ha!\')">I haz errors.</p>
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
					'//*[ @id = "errors-ignored" ]',
					'//*[ @id = "greet" ]',
					'//*[ @id = "wpadminbar" ]',
					'//*[ @id = "wpadminbar" ]//*',
					'//style[ @id = "admin-bar-inline-css" ]',
				],
			]
		);
		$sanitizer->sanitize();

		( new AMP_Tag_And_Attribute_Sanitizer( $dom, [ 'allow_dirty_styles' => true ] ) )->sanitize();

		// Assert dev mode is set correctly on elements.
		$this->assertEquals( '_amp_exempt', $dom->documentElement->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertEquals( '_amp_exempt', $dom->getElementById( 'greet' )->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertEquals( '_amp_exempt', $dom->getElementById( 'wpadminbar' )->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertEquals( '_amp_exempt', $dom->getElementById( 'wp-admin-bar-img' )->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertEquals( '_amp_exempt', $dom->getElementById( 'admin-bar-inline-css' )->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertEquals( '', $dom->getElementById( 'errors-ignored' )->getAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );
		$this->assertFalse( $dom->getElementById( 'no-dev-mode-needed' )->hasAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE ) );

		// Assert elements are sanitized if dev mode is set by plugin.
		$this->assertTrue( $dom->getElementById( 'errors-ignored' )->hasAttribute( 'onclick' ) );
		$this->assertFalse( $dom->getElementById( 'greet' )->hasAttribute( 'onclick' ) );
	}
}
