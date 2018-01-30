<?php
/**
 * Test AMP_Style_Sanitizer.
 *
 * @package AMP
 */

/**
 * Test AMP_Style_Sanitizer.
 */
class AMP_Style_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get data.
	 *
	 * @return array
	 */
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
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e { color:#00ff00; }',
				),
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e">This is green.</span>',
				array(
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e { color:#00ff00; }',
				),
			),

			'span_two_styles_reversed' => array(
				'<span style="color: #00ff00; background-color: #000; ">This is green.</span>',
				'<span class="amp-wp-inline-58550689c128f3d396444313296e4c47">This is green.</span>',
				array(
					'.amp-wp-inline-58550689c128f3d396444313296e4c47 { background-color:#000; color:#00ff00; }',
				),
			),

			'width_to_max-width' => array(
				'<figure style="width: 300px"></figure>',
				'<figure class="amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4"></figure>',
				array(
					'.amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4 { max-width:300px; }',
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

			'!important_not_allowed' => array(
				'<span style="margin: 1px!important;">!important not allowed.</span>',
				'<span class="amp-wp-inline-b370df7c42957a3192cac40a8ddcff79">!important not allowed.</span>',
				array(
					'.amp-wp-inline-b370df7c42957a3192cac40a8ddcff79 { margin:1px; }',
				),
			),

			'!important_with_spaces_not_allowed' => array(
				'<span style="color: red  !  important;">!important not allowed.</span>',
				'<span class="amp-wp-inline-5b88d03e432f20476a218314084d3a05">!important not allowed.</span>',
				array(
					'.amp-wp-inline-5b88d03e432f20476a218314084d3a05 { color:red; }',
				),
			),

			'!important_multiple_not_allowed' => array(
				'<span style="color: red !important; background: blue!important;">!important not allowed.</span>',
				'<span class="amp-wp-inline-ef4329d562b6b3486a8a661df5c5280f">!important not allowed.</span>',
				array(
					'.amp-wp-inline-ef4329d562b6b3486a8a661df5c5280f { background:blue; color:red; }',
				),
			),

			'two_nodes' => array(
				'<span style="color: #00ff00;"><span style="color: #ff0000;">This is red.</span></span>',
				'<span class="amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e"><span class="amp-wp-inline-f146f9bb819d875bbe5cf83e36368b44">This is red.</span></span>',
				array(
					'.amp-wp-inline-ad0e57ab02197f7023aa5b93bcf6c97e { color:#00ff00; }',
					'.amp-wp-inline-f146f9bb819d875bbe5cf83e36368b44 { color:#ff0000; }',
				),
			),

			'existing_class_attribute' => array(
				'<figure class="alignleft" style="background: #000"></figure>',
				'<figure class="alignleft amp-wp-inline-3be9b2f79873ad78941ba2b3c03025a3"></figure>',
				array(
					'.amp-wp-inline-3be9b2f79873ad78941ba2b3c03025a3 { background:#000; }',
				),

			),

			'inline_style_element_with_multiple_rules_containing_selectors_is_removed' => array(
				'<style>div > span { font-weight:bold !important; overflow: scroll; font-style: italic; }</style><div><span>bold!</span></div>',
				'<div><span>bold!</span></div>',
				array(
					'div > span { font-weight:bold; font-style: italic; }',
				),
			),
		);
	}

	/**
	 * Test sanitizer.
	 *
	 * @dataProvider get_data
	 * @param string $source               Source.
	 * @param string $expected_content     Expected content.
	 * @param string $expected_stylesheets Expected stylesheets.
	 */
	public function test_sanitizer( $source, $expected_content, $expected_stylesheets ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		// Test content.
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected_content, $content );

		// Test stylesheet.
		$this->assertEquals( $expected_stylesheets, array_values( $sanitizer->get_stylesheets() ) );
	}
}
