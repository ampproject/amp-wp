<?php

class AMP_Img_Converter_Test extends WP_UnitTestCase {
	private $converter;
	
	function setUp() {
		$this->converter = new AMP_Img_Converter;
	}

	function test_no_images() {
		$content = '<p>Lorem Ipsum Demet Delorit.</p>';
		$converted = $this->converter->convert( $content );
		$this->assertEquals( $content, $converted );
	}

	function test_image_with_self_closing_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';
		$converted = $this->converter->convert( $content );
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_no_end_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!">';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';
		$converted = $this->converter->convert( $content );
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_end_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';
		$converted = $this->converter->convert( $content );
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_blacklisted_attribute() {
		$content = '<img src="http://placehold.it/350x150" style="border: 1px solid red;" />';
		$expected = '<amp-img src="http://placehold.it/350x150"></amp-img>';
		$converted = $this->converter->convert( $content );
		$this->assertEquals( $expected, $converted );
	}
}

