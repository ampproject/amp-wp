<?php

class AMP_Style_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				'',
				'',
				'',
			),

			'span_one_style' => array(
				'<span style="color: #00ff00;">This is green.</span>',
				'<span class="amp-wp-inline-bb01159393134c225a1df0d44226c3d2">This is green.</span>',
				"
/* Inline Styles */
.amp-wp-inline-bb01159393134c225a1df0d44226c3d2 { color: #00ff00; }

",
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-inline-bb01159393134c225a1df0d44226c3d2">This is green.</span>',
				"
/* Inline Styles */
.amp-wp-inline-bb01159393134c225a1df0d44226c3d2 { color: #00ff00; }

",
			),

			'span_two_styles_reversed' => array(
				'<span style="width: 350px; color: #00ff00;">This is green.</span>',
				'<span class="amp-wp-inline-858600a8f78ba6085b17e66e6fd02325">This is green.</span>',
				"
/* Inline Styles */
.amp-wp-inline-858600a8f78ba6085b17e66e6fd02325 { color: #00ff00; width: 350px; }

",
			),

			'div_kses_banned_style' => array(
				'<span style="overflow-x: hidden;">Specific overflow axis not allowed.</span>',
				'<span>Specific overflow axis not allowed.</span>',
				'',
			),

			'div_amp_banned_style' => array(
				'<span style="overflow: scroll;">Scrollbars not allowed.</span>',
				'<span>Scrollbars not allowed.</span>',
				'',
			),

			'two_nodes' => array(
				'<span style="color: #00ff00;"><span style="color: #ff0000;">This is red.</span></span>',
				'<span class="amp-wp-inline-bb01159393134c225a1df0d44226c3d2"><span class="amp-wp-inline-cc68ddc00c4fa7c4e4ea8d4351e25831">This is red.</span></span>',
				"
/* Inline Styles */
.amp-wp-inline-bb01159393134c225a1df0d44226c3d2 { color: #00ff00; }
.amp-wp-inline-cc68ddc00c4fa7c4e4ea8d4351e25831 { color: #ff0000; }

",
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