<?php

class Test_AMP_Content_Sanitizer extends WP_UnitTestCase {
	public function test__sanitize__unchanged() {
		$source_html     = '<b>Hello</b>';
		$expected_return = [ '<b>Hello</b>', [], [] ];

		$actual_return = AMP_Content_Sanitizer::sanitize( $source_html, [ 'AMP_Test_Stub_Sanitizer' => [] ] );

		$this->assertEquals( $expected_return, $actual_return );
	}

	public function test__sanitize__append_with_scripts_and_styles() {
		$source_html     = '<b>Hello</b>';
		$expected_return = [ '<b>Hello</b><em>World</em>', [ 'scripts' ], [ 'styles' ] ];

		$actual_return = AMP_Content_Sanitizer::sanitize( $source_html, [ 'AMP_Test_World_Sanitizer' => [] ] );

		$this->assertEquals( $expected_return, $actual_return );
	}
}
