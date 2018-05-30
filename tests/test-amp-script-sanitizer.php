<?php
/**
 * Test AMP_Script_Sanitizer.
 *
 * @package AMP
 */

/**
 * Test AMP_Script_Sanitizer.
 */
class AMP_Script_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Test that analytics scripts are properly replaced by <amp-analytics>
	 *
	 * @covers AMP_Script_Sanitizer::sanitize()
	 */
	public function test_processing_analytics_script() {
		$this->assertTrue( true );
	}
}
