<?php

class AMP_Iframe_Converter_Test extends WP_UnitTestCase {
	function test_no_iframes() {
		$content = '<p>Lorem Ipsum Demet Delorit.</p>';
		$converter = new AMP_Iframe_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $content, $converted );
	}

	function test_simple_iframe() {
		$content = '<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class" sandbox="allow-same-origin" allowtransparency allowfullscreen></iframe>';
		$expected = '<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class" sandbox="allow-same-origin" allowtransparency allowfullscreen></amp-iframe>';

		$converter = new AMP_Iframe_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_iframe_with_blacklisted_attribute() {
		$content = '<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" scrolling="auto"></iframe>';
		$expected = '<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></amp-iframe>';

		$converter = new AMP_Iframe_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_same_iframe() {
		$content = '
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
		';
		$expected = '
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></amp-iframe>
		';

		$converter = new AMP_Iframe_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_different_iframes() {
		$content = '
<iframe src="https://player.vimeo.com/video/12345" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/67890" width="280" height="501"></iframe>
<iframe src="https://player.vimeo.com/video/11111" width="700" height="601"></iframe>
		';
		$expected = '
<amp-iframe src="https://player.vimeo.com/video/12345" width="500" height="281"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/67890" width="280" height="501"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/11111" width="700" height="601"></amp-iframe>
		';

		$converter = new AMP_Iframe_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
