<?php

class AMP_Content_filter__Test extends WP_UnitTestCase {
	public function test__sanitize__unchanged() {
		$source_html = '<b>Hello</b>';
		$expected_return = array( '<b>Hello</b>', array(), array() );

		$actual_return = AMP_Content_Filter::filter( $source_html, array( 'AMP_Test_Stub_Filter' => array() ) );

		$this->assertEquals( $expected_return, $actual_return );
	}

	public function test__filter__append_with_scripts_and_styles() {
		$source_html = '<b>Hello</b>';
		$expected_return = array( '<b>Hello</b><em>World</em>', array( 'scripts' ), array( 'styles' ) );

		$actual_return = AMP_Content_Filter::filter( $source_html, array( 'AMP_Test_World_Filter' => array() ) );

		$this->assertEquals( $expected_return, $actual_return );
	}
}
