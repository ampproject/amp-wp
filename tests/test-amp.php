<?php
/**
 * Tests for amp.php.
 *
 * @package AMP
 */

/**
 * Tests for amp.php.
 */
class Test_AMP extends WP_UnitTestCase {

	/**
	 * Test amp_is_canonical().
	 *
	 * @see amp_is_canonical()
	 */
	public function test_amp_is_canonical() {
		global $_wp_theme_features;
		$this->assertFalse( amp_is_canonical() );

		$_wp_theme_features['amp'] = true;
		$this->assertTrue( amp_is_canonical() );

		$_wp_theme_features['amp'] = array(
			'template_path' => get_template_directory() . 'amp-templates/',
		);
		$this->assertFalse( amp_is_canonical() );
		unset( $_wp_theme_features['amp'] );
	}

}
