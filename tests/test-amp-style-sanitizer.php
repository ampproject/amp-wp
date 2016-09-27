<?php

class AMP_Style_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				'',
				'',
				array(),
			),

			'span_one_style' => array(
				'<span style="color: #00ff00;">This is green.</span>',
				'<span class="amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea">This is green.</span>',
				array(
					'.amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea' => array(
						'color: #00ff00',
					),
				),
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea">This is green.</span>',
				array(
					'.amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea' => array(
						'color: #00ff00',
					),
				),
			),

			'span_two_styles_reversed' => array(
				'<span style="width: 350px; color: #00ff00;">This is green.</span>',
				'<span class="amp-wp-inline-9e3a754064939f6cf521fb25f67f8a73">This is green.</span>',
				array(
					'.amp-wp-inline-9e3a754064939f6cf521fb25f67f8a73' => array(
						'color: #00ff00',
						'width: 350px',
					),
				),
			),

			'div_kses_banned_style' => array(
				'<span style="overflow-x: hidden;">Specific overflow axis not allowed.</span>',
				'<span>Specific overflow axis not allowed.</span>',
				array(),
			),

			'div_amp_banned_style' => array(
				'<span style="overflow: scroll;">Scrollbars not allowed.</span>',
				'<span>Scrollbars not allowed.</span>',
				array(),
			),

			'two_nodes' => array(
				'<span style="color: #00ff00;"><span style="color: #ff0000;">This is red.</span></span>',
				'<span class="amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea"><span class="amp-wp-inline-74ce01776d679398d58eba941c5c84bb">This is red.</span></span>',
				array(
					'.amp-wp-inline-846b203684c92aae7df71d5b8a6ba7ea' => array(
						'color: #00ff00',
					),
					'.amp-wp-inline-74ce01776d679398d58eba941c5c84bb' => array(
						'color: #ff0000',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_sanitizer( $source, $expected_content, $expected_stylesheet ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$sanitizer->sanitize();

		// Test content
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected_content, $content );

		// Test stylesheet
		$stylesheet = $sanitizer->get_styles();
		$this->assertEquals( $expected_stylesheet, $stylesheet );
	}
}