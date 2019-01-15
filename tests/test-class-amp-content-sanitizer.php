<?php

class Test_AMP_Content_Sanitizer extends WP_UnitTestCase {
	public function test__sanitize__unchanged() {
		$source_html     = '<b>Hello</b>';
		$expected_return = array( '<b>Hello</b>', array(), array() );

		$actual_return = AMP_Content_Sanitizer::sanitize( $source_html, array( 'AMP_Test_Stub_Sanitizer' => array() ) );

		$this->assertEquals( $expected_return, $actual_return );
	}

	public function test__sanitize__append_with_scripts_and_styles() {
		$source_html     = '<b>Hello</b>';
		$expected_return = array( '<b>Hello</b><em>World</em>', array( 'scripts' ), array( 'styles' ) );

		$actual_return = AMP_Content_Sanitizer::sanitize( $source_html, array( 'AMP_Test_World_Sanitizer' => array() ) );

		$this->assertEquals( $expected_return, $actual_return );
	}
}
