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
				'<span class="amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e">This is green.</span>',
				array(
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e' => array(
						'color:#00ff00',
					),
				),
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e">This is green.</span>',
				array(
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e' => array(
						'color:#00ff00',
					),
				),
			),

			'span_two_styles_reversed' => array(
				'<span style="width: 350px; color: #00ff00;">This is green.</span>',
				'<span class="amp-wp-inline-c83b149f3e9c3426a6eab43e21ac2fba">This is green.</span>',
				array(
					'.amp-wp-inline-c83b149f3e9c3426a6eab43e21ac2fba' => array(
						'color:#00ff00',
						'width:350px',
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
				'<span class="amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e"><span class="amp-wp-inline-f146f9bb819d875bbe5cf83e36368b44">This is red.</span></span>',
				array(
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e' => array(
						'color:#00ff00',
					),
					'.amp-wp-inline-f146f9bb819d875bbe5cf83e36368b44' => array(
						'color:#ff0000',
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