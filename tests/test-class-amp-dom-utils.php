<?php

class AMP_DOM_Utils_Test extends WP_UnitTestCase {
	public function test_utf8_content() {
		$source = '<p>Iñtërnâtiônàlizætiøn</p>';
		$expected = '<p>Iñtërnâtiônàlizætiøn</p>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}
}
