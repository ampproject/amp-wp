<?php

class AMP_Iframe_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_iframes' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_iframe' => array(
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-scripts allow-same-origin" layout="intrinsic"></amp-iframe>',
			),

			'force_https' => array(
				'<iframe src="http://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-scripts allow-same-origin" layout="intrinsic"></amp-iframe>',
			),

			'iframe_without_dimensions' => array(
				'<iframe src="https://example.com/video/132886713"></iframe>',
				'<amp-iframe src="https://example.com/video/132886713" sandbox="allow-scripts allow-same-origin" height="400" layout="fixed-height"></amp-iframe>',
			),

			'iframe_with_height_only' => array(
				'<iframe src="https://example.com/video/132886713" height="400"></iframe>',
				'<amp-iframe src="https://example.com/video/132886713" height="400" sandbox="allow-scripts allow-same-origin" layout="fixed-height"></amp-iframe>',
			),

			'iframe_with_width_only' => array(
				'<iframe src="https://example.com/video/132886713" width="600"></iframe>',
				'<amp-iframe src="https://example.com/video/132886713" sandbox="allow-scripts allow-same-origin" height="400" layout="fixed-height"></amp-iframe>',
			),

			'iframe_with_invalid_frameborder' => array(
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="no"></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_1_frameborder' => array(
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder=1></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="1" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'simple_iframe_with_sandbox' => array(
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-same-origin"></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_blacklisted_attribute' => array(
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" scrolling="auto"></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" scrolling="auto" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_sizes_attribute_is_overridden' => array(
				'<iframe src="https://example.com/iframe" width="500" height="281"></iframe>',
				'<amp-iframe src="https://example.com/iframe" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'iframe_with_id_attribute'                  => array(
				'<iframe src="https://example.com/iframe" id="myIframe"></iframe>',
				'<amp-iframe src="https://example.com/iframe" id="myIframe" sandbox="allow-scripts allow-same-origin" height="400" layout="fixed-height"></amp-iframe>',
			),

			'iframe_with_protocol_relative_url' => array(
				'<iframe src="//example.com/video/132886713"></iframe>',
				'<amp-iframe src="https://example.com/video/132886713" sandbox="allow-scripts allow-same-origin" height="400" layout="fixed-height"></amp-iframe>',
			),

			'multiple_same_iframe' => array(
				trim( '
<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
				' ),
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),

			'multiple_different_iframes' => array(
				trim( '
<iframe src="https://example.com/embed/12345" width="500" height="281"></iframe>
<iframe src="https://example.com/embed/67890" width="280" height="501"></iframe>
<iframe src="https://example.com/embed/11111" width="700" height="601"></iframe>
				' ),
				'<amp-iframe src="https://example.com/embed/12345" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/embed/67890" width="280" height="501" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/embed/11111" width="700" height="601" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),
			'iframe_in_p_tag' => array(
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe></p>',
				'<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),
			'multiple_iframes_in_p_tag' => array(
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),
			'multiple_iframes_and_contents_in_p_tag' => array(
				'<p>contents<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'<p>contents</p><amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe><amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
			),
			'iframe_src_with_commas_and_colons'         => array(
				'<iframe src="https://www.geoportail.gouv.fr/embed/visu.html?c=3.9735668054865076,43.90558192721261&z=15&l0=GEOLOGY.GEOLOGY::EXTERNAL:OGC:EXTERNALWMS(1)&permalink=yes" width="100" height="200" sandbox="allow-forms allow-scripts allow-same-origin" allowfullscreen=""></iframe>',
				'<amp-iframe src="https://www.geoportail.gouv.fr/embed/visu.html?c=3.9735668054865076,43.90558192721261&amp;z=15&amp;l0=GEOLOGY.GEOLOGY::EXTERNAL:OGC:EXTERNALWMS(1)&amp;permalink=yes" width="100" height="200" sandbox="allow-forms allow-scripts allow-same-origin" allowfullscreen="" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
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

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}

	public function test__https_required() {
		$source = '<iframe src="http://example.com/embed/132886713"></iframe>';
		$expected = '';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom, array(
			'add_placeholder' => true,
			'require_https_src' => true,
		) );
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

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	public function test_get_scripts__did_convert() {
		$source = '<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>';
		$expected = array( 'amp-iframe' => true );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	public function test__args__placeholder() {
		$source = '<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe>';
		$expected = '<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-scripts allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes"><div placeholder="" class="amp-wp-iframe-placeholder"></div></amp-iframe>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom, array(
			'add_placeholder' => true,
		) );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

}
