<?php

class AMP_Img_Converter_Test extends WP_UnitTestCase {
	function test_no_images() {
		$content = '<p>Lorem Ipsum Demet Delorit.</p>';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $content, $converted );
	}

	function test_image_with_self_closing_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_no_end_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!">';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_end_tag() {
		$content = '<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>';
		$expected = '<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></amp-img>';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_image_with_blacklisted_attribute() {
		$content = '<img src="http://placehold.it/350x150" style="border: 1px solid red;" />';
		$expected = '<amp-img src="http://placehold.it/350x150"></amp-img>';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_same_image() {
		$content = '
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
			';
		$expected = '
<amp-img src="http://placehold.it/350x150"></amp-img>
<amp-img src="http://placehold.it/350x150"></amp-img>
<amp-img src="http://placehold.it/350x150"></amp-img>
<amp-img src="http://placehold.it/350x150"></amp-img>
			';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_different_images() {
		$content = '
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/360x160" />
<img src="http://placehold.it/370x170" />
<img src="http://placehold.it/380x180" />
			';
		$expected = '
<amp-img src="http://placehold.it/350x150"></amp-img>
<amp-img src="http://placehold.it/360x160"></amp-img>
<amp-img src="http://placehold.it/370x170"></amp-img>
<amp-img src="http://placehold.it/380x180"></amp-img>
			';

		$converter = new AMP_Img_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
