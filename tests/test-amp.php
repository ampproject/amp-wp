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
	 * Tear down and clean up.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_theme_support( 'amp' );
	}

	/**
	 * Test amp_is_canonical().
	 *
	 * @covers amp_is_canonical()
	 */
	public function test_amp_is_canonical() {
		remove_theme_support( 'amp' );
		$this->assertFalse( amp_is_canonical() );

		add_theme_support( 'amp' );
		$this->assertTrue( amp_is_canonical() );

		remove_theme_support( 'amp' );
		add_theme_support( 'amp', array(
			'template_path' => get_template_directory() . 'amp-templates/',
		) );
		$this->assertFalse( amp_is_canonical() );

		remove_theme_support( 'amp' );
		add_theme_support( 'amp', array(
			'custom_prop' => 'something',
		) );
		$this->assertTrue( amp_is_canonical() );
	}
}
