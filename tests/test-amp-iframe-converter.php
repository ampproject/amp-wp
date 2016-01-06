<?php

class AMP_Iframe_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_iframes' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_iframe' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency allowfullscreen></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency allowfullscreen sandbox="allow-scripts allow-same-origin"></amp-iframe>',
			),

			'simple_iframe_with_sandbox' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-same-origin"></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-same-origin"></amp-iframe>',
			),

			'iframe_with_blacklisted_attribute' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" scrolling="auto"></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin"></amp-iframe>',
			),

			'multiple_same_iframe' => array(
				'
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
				',
				'
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin"></amp-iframe>
				',
			),

			'multiple_different_iframes' => array(
				'
<iframe src="https://player.vimeo.com/video/12345" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/67890" width="280" height="501"></iframe>
<iframe src="https://player.vimeo.com/video/11111" width="700" height="601"></iframe>
				',
				'
<amp-iframe src="https://player.vimeo.com/video/12345" width="500" height="281" sandbox="allow-scripts allow-same-origin"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/67890" width="280" height="501" sandbox="allow-scripts allow-same-origin"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/11111" width="700" height="601" sandbox="allow-scripts allow-same-origin"></amp-iframe>
				',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$converter = new AMP_Iframe_Converter( $source );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
