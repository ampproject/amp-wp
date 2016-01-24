<?php

class AMP_Iframe_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_iframes' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_iframe' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" frameborder="0" class="iframe-class wp-amp-enforced-sizes" allowfullscreen="true" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw"></amp-iframe>',
			),

			'simple_iframe_with_sandbox' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-same-origin"></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_blacklisted_attribute' => array(
				'<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" scrolling="auto"></iframe>',
				'<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_sizes_attribute' => array(
				'<iframe src="https://example.com/iframe" width="500" height="281" sizes="(min-width: 100px) 300px, 90vw"></iframe>',
				'<amp-iframe src="https://example.com/iframe" width="500" height="281" sizes="(min-width: 100px) 300px, 90vw" sandbox="allow-scripts allow-same-origin"></amp-iframe>',
			),

			'multiple_same_iframe' => array(
				'
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>
				',
				'
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
				',
			),

			'multiple_different_iframes' => array(
				'
<iframe src="https://player.vimeo.com/video/12345" width="500" height="281"></iframe>
<iframe src="https://player.vimeo.com/video/67890" width="280" height="501"></iframe>
<iframe src="https://player.vimeo.com/video/11111" width="700" height="601"></iframe>
				',
				'
<amp-iframe src="https://player.vimeo.com/video/12345" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/67890" width="280" height="501" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 280px) 280px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
<amp-iframe src="https://player.vimeo.com/video/11111" width="700" height="601" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 700px) 700px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>
				',
			),
			'iframe_in_p_tag' => array(
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe></p>',
				'<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>',
			),
			'iframes_in_p_tag' => array(
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>',
			),
			'iframes_and_contents_in_p_tag' => array(
				'<p>contents<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'<p>contents</p><amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"></amp-iframe>',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test_get_scripts__didnt_convert() {
		$source = '<p>Hello World</p>';
		$expected = array();

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}

	public function test_get_scripts__did_convert() {
		$source = '<iframe src="https://player.vimeo.com/video/132886713" width="500" height="281"></iframe>';
		$expected = array( 'amp-iframe' => 'https://cdn.ampproject.org/v0/amp-iframe-0.1.js' );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}

	public function test__args__placeholder() {
		$source = '<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe>';
		$expected = '<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" sizes="(min-width: 500px) 500px, 100vw" class="wp-amp-enforced-sizes"><div layout="fill" placeholder="" class="amp-wp-iframe-placeholder"></div></amp-iframe>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom, array(
			'add_placeholder' => true,
		) );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}
}
