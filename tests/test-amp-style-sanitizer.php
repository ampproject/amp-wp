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

			// This seems to be allowed in the current AMP spec.
			// 'width_to_max-width' => array(
			// 	'<figure style="width: 300px"></figure>',
			// 	'<figure class="amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4"></figure>',
			// 	array(
			// 		'.amp-wp-inline-2676cd1bfa7e8feb4f0e0e8086ae9ce4' => array(
			// 			'max-width:300px',
			// 		),
			// 	),
			// ),

			// This seems to be allowed in the current AMP spec.
			// 'div_kses_banned_style' => array(
			// 	'<span style="overflow-x: hidden;">Specific overflow axis not allowed.</span>',
			// 	'<span>Specific overflow axis not allowed.</span>',
			// 	array(),
			// ),

			// This seems to be allowed in the current AMP spec.			
			// 'div_amp_banned_style' => array(
			// 	'<span style="overflow: scroll;">Scrollbars not allowed.</span>',
			// 	'<span>Scrollbars not allowed.</span>',
			// 	array(),
			// ),

			// The AMP regex specification does not provide a way to distinguish which whether
			// the specific matched regex should be removed or whether the entire declaration that
			// contains a matched regex should be removed. Therefore, if a declaration matches
			// a blacklisted regex, the entire declaration must always be removed.
			// '!important_not_allowed' => array(
			// 	'<span style="margin: 1px!important;">!important not allowed.</span>',
			// 	'<span class="amp-wp-inline-b370df7c42957a3192cac40a8ddcff79">!important not allowed.</span>',
			// 	array(
			// 		'.amp-wp-inline-b370df7c42957a3192cac40a8ddcff79' => array(
			// 			'margin:1px',
			// 		),
			// 	),
			// ),

			// The AMP blacklisted cdata regex does not include a version of '!important' with spaces in it.
			// '!important_with_spaces_not_allowed' => array(
			// 	'<span style="color: red  !  important;">!important not allowed.</span>',
			// 	'<span class="amp-wp-inline-5b88d03e432f20476a218314084d3a05">!important not allowed.</span>',
			// 	array(
			// 		'.amp-wp-inline-5b88d03e432f20476a218314084d3a05' => array(
			// 			'color:red',
			// 		),
			// 	),
			// ),

			// Must remove entire declaration when '!important' is found.
			// '!important_multiple_not_allowed' => array(
			// 	'<span style="color: red !important; background: blue!important;">!important not allowed.</span>',
			// 	'<span class="amp-wp-inline-ef4329d562b6b3486a8a661df5c5280f">!important not allowed.</span>',
			// 	array(
			// 		'.amp-wp-inline-ef4329d562b6b3486a8a661df5c5280f' => array(
			// 			'background:blue',
			// 			'color:red',
			// 		),
			// 	),
			// ),

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

			// New tests for updated sanitizer
			'allow_data_URI' => array(
				'<div class = "background_image" style = "background:url(data:image/png;base64,iVBOR...QmCC);"></div>',
				'<div class="background_image amp-wp-inline-9e2b985c66acd167ae81a43e47b64b9c"></div>',
				array(
					'.amp-wp-inline-9e2b985c66acd167ae81a43e47b64b9c' => array(
						'background:url(data:image/png;base64,iVBOR...QmCC)',
					),
				),
			),

			'remove_declaration_with_disallowed_protocol' => array(
				'<div class = "background_image" style = "height:44px;background:url(disallowed://example.com);"></div>',
				'<div class="background_image amp-wp-inline-781ca55963bf9d14902efb4d514e1b1d"></div>',
				array(
					'.amp-wp-inline-781ca55963bf9d14902efb4d514e1b1d' => array(
						'background:url()',
						'height:44px',
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


	public function get_css_explode_data() {
		return array(
			'empty' => array(
				'',
				array(),
				array(),
			),
			'split_on_semicolon' => array(
				'property1:value1;property2:value2',
				array(),
				array(
					'property1:value1',
					'property2:value2',
				),
			),
			'split_on_semicolon_multiple_values' => array(
				'property1:value1;property2:value2;property3:value3;property4:value4;property5:value5;',
				array(),
				array(
					'property1:value1',
					'property2:value2',
					'property3:value3',
					'property4:value4',
					'property5:value5',
				),
			),
			'split_on_semicolon_multiple_values_remove_whitespace' => array(
				'property1:value1; property2:value2 ;  property3:value3  ;   
				property4:value4;
				property5:value5              ;',
				array(),
				array(
					'property1:value1',
					'property2:value2',
					'property3:value3',
					'property4:value4',
					'property5:value5',
				),
			),
			'split_on_semicolon_ignore_quoted' => array(
				'property1:value1;property2:"value2;property3:value3";property4:\'value4;property5:value5\';property6:url(Then;there;are;more;separators;here);',
				array(),
				array(
					'property1:value1',
					'property2:"value2;property3:value3"',
					'property4:\'value4;property5:value5\'',
					'property6:url(Then;there;are;more;separators;here)',
				),
			),
			'split_and_ignore_quoted_quotes' => array(
				'property1:"value;value=\'whatever;somethingelse\'"',
				array( 'separator' => ':' ),
				array(
					'property1',
					'"value;value=\'whatever;somethingelse\'"',
				),
			),
		);
	}

	/**
	 * @dataProvider get_css_explode_data
	 * @group css_explode
	 */
	public function test_css_explode( $string, $args, $expected ) {
		$got = AMP_Style_Sanitizer::css_explode( $string, $args );
		$this->assertEquals( $expected, $got );
	}
}
