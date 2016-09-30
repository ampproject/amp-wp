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
				'<span style="color: #00ff00; background-color: #000; ">This is green.</span>',
				'<span class="amp-wp-inline-58550689c128f3d396444313296e4c47">This is green.</span>',
				array(
					'.amp-wp-inline-58550689c128f3d396444313296e4c47' => array(
						'background-color:#000',
						'color:#00ff00',
					),
				),
			),

			'width_to_max-width' => array(
				'<figure style="width: 300px"></figure>',
				'<figure class="amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4"></figure>',
				array(
					'.amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4' => array(
						'max-width:300px',
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

			'existing_class_attribute' => array(
				'<figure class="alignleft" style="background: #000"></figure>',
				'<figure class="alignleft amp-wp-inline-3be9b2f79873ad78941ba2b3c03025a3"></figure>',
				array(
					'.amp-wp-inline-3be9b2f79873ad78941ba2b3c03025a3' => array(
						'background:#000',
					),
				)

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
