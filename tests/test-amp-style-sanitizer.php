<?php
/**
 * Test AMP_Style_Sanitizer.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Test AMP_Style_Sanitizer.
 */
class AMP_Style_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get data for tests.
	 *
	 * @return array
	 */
	public function get_body_style_attribute_data() {
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

			'span_display_none' => array(
				'<span style="display: none;">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				'<span class="amp-wp-inline-0f1bf07c72fdf1784fff2e164d9dca98">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				array(
					'.amp-wp-inline-0f1bf07c72fdf1784fff2e164d9dca98 { display:none; }',
				),
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
	 * Test sanitizer for style attributes that appear in the body.
	 *
	 * @dataProvider get_body_style_attribute_data
	 * @param string $source               Source.
	 * @param string $expected_content     Expected content.
	 * @param string $expected_stylesheets Expected stylesheets.
	 */
	public function test_body_style_attribute_sanitizer( $source, $expected_content, $expected_stylesheets ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$sanitizer->sanitize();

		// Test content.
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected_content, $content );

		// Test stylesheet.
		$this->assertEquals( $expected_stylesheets, array_values( $sanitizer->get_stylesheets() ) );
	}

	/**
	 * Get link and style test data.
	 *
	 * @return array
	 */
	public function get_link_and_style_test_data() {
		return array(
			'multiple_amp_custom_and_other_styles' => array(
				'<html amp><head><meta charset="utf-8"><style amp-custom>b {color:red !important}</style><style amp-custom>i {color:blue}</style><style type="text/css">u {color:green}</style></head><body><style>s {color:yellow} /* So !important! */</style></body></html>',
				array(
					'b {color:red}',
					'i {color:blue}',
					'u {color:green}',
					's {color:yellow}',
				),
			),
			'style_elements_with_link_elements' => array(
				sprintf(
					'<html amp><head><meta charset="utf-8"><style type="text/css">strong.before-dashicon {color:green}</style><link rel="stylesheet" href="%s"><style type="text/css">strong.after-dashicon {color:green}</style></head><body><style>s {color:yellow !important}</style></body></html>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					includes_url( 'css/dashicons.css' )
				),
				array(
					'strong.before-dashicon',
					'.dashicons-dashboard:before',
					'strong.after-dashicon',
					's {color:yellow}',
				),
			),
			'style_with_no_head' => array(
				'<html amp><body>Not good!<style>body{color:red;overflow:auto;overflow-x:scroll;overflow-y:scroll;}</style></body>',
				array(
					'body{color:red;}',
				),
			),
		);
	}

	/**
	 * Test style elements and link elements.
	 *
	 * @dataProvider get_link_and_style_test_data
	 * @param string $source               Source.
	 * @param array  $expected_stylesheets Expected stylesheets.
	 */
	public function test_link_and_style_elements( $source, $expected_stylesheets ) {
		$dom = AMP_DOM_Utils::get_dom( $source );

		$sanitizer = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element' => true,
		) );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, array(
			'use_document_element' => true,
		) );
		$whitelist_sanitizer->sanitize();

		$sanitized_html     = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertCount( count( $expected_stylesheets ), $actual_stylesheets );
		foreach ( $expected_stylesheets as $i => $expected_stylesheet ) {
			$this->assertContains( $expected_stylesheet, $actual_stylesheets[ $i ] );
			$this->assertContains( $expected_stylesheet, $sanitized_html );
		}
	}

	/**
	 * Get amp-keyframe styles.
	 *
	 * @return array
	 */
	public function get_keyframe_data() {
		$keyframes_max_size = 10;
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && 'style[amp-keyframes]' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$keyframes_max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}

		return array(
			'style_amp_keyframes'              => array(
				'<style amp-keyframes>@keyframes anim1 {} @media (min-width: 600px) { @keyframes foo {} }</style>',
				null, // No Change.
			),

			'style_amp_keyframes_max_overflow' => array(
				'<style amp-keyframes>@keyframes anim1 {} @media (min-width: 600px) { @keyframes ' . str_repeat( 'a', $keyframes_max_size + 1 ) . ' {} }</style>',
				'',
			),

			'style_amp_keyframes_last_child'   => array(
				'<style amp-keyframes>@keyframes anim1 {} @media (min-width: 600px) { @keyframes foo {} }</style> as <b>after</b>',
				' as <b>after</b>',
			),
		);
	}

	/**
	 * Test amp-keyframe styles.
	 *
	 * @dataProvider get_keyframe_data
	 * @param string $source   Markup to process.
	 * @param string $expected The markup to expect.
	 */
	public function test_keyframe_sanitizer( $source, $expected = null ) {
		$expected  = isset( $expected ) ? $expected : $source;
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Get stylesheet URLs.
	 *
	 * @returns array Stylesheet URL data.
	 */
	public function get_stylesheet_urls() {
		return array(
			'theme_stylesheet_without_host' => array(
				'/wp-content/themes/twentyseventeen/style.css',
				WP_CONTENT_DIR . '/themes/twentyseventeen/style.css',
			),
			'theme_stylesheet_with_host' => array(
				WP_CONTENT_URL . '/themes/twentyseventeen/style.css',
				WP_CONTENT_DIR . '/themes/twentyseventeen/style.css',
			),
			'dashicons_without_host' => array(
				'/wp-includes/css/dashicons.css',
				ABSPATH . WPINC . '/css/dashicons.css',
			),
			'dashicons_with_host' => array(
				includes_url( 'css/dashicons.css' ),
				ABSPATH . WPINC . '/css/dashicons.css',
			),
			'admin_without_host' => array(
				'/wp-admin/css/common.css',
				ABSPATH . 'wp-admin/css/common.css',
			),
			'admin_with_host' => array(
				admin_url( 'css/common.css' ),
				ABSPATH . 'wp-admin/css/common.css',
			),
			'amp_css_bad_file_extension' => array(
				content_url( 'themes/twentyseventeen/index.php' ),
				null,
				'amp_css_bad_file_extension',
			),
			'amp_css_path_not_found' => array(
				content_url( 'themes/twentyseventeen/404.css' ),
				null,
				'amp_css_path_not_found',
			),
		);
	}

	/**
	 * Tests get_validated_css_file_path.
	 *
	 * @dataProvider get_stylesheet_urls
	 * @covers AMP_Style_Sanitizer::get_validated_css_file_path()
	 * @param string      $source     Source URL.
	 * @param string|null $expected   Expected path or null if error.
	 * @param string      $error_code Error code. Optional.
	 */
	public function test_get_validated_css_file_path( $source, $expected, $error_code = null ) {
		$dom = AMP_DOM_Utils::get_dom( '<html></html>' );

		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$actual    = $sanitizer->get_validated_css_file_path( $source );
		if ( isset( $error_code ) ) {
			$this->assertInstanceOf( 'WP_Error', $actual );
			$this->assertEquals( $error_code, $actual->get_error_code() );
		} else {
			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * Get font url test data.
	 *
	 * @return array Data.
	 */
	public function get_font_urls() {
		return array(
			'tangerine'   => array(
				'https://fonts.googleapis.com/css?family=Tangerine',
				true,
			),
			'typekit'     => array(
				'https://use.typekit.net/abc.css',
				true,
			),
			'fontscom'    => array(
				'https://fast.fonts.net/abc.css',
				true,
			),
			'fontawesome' => array(
				'https://maxcdn.bootstrapcdn.com/font-awesome/123/css/font-awesome.min.css',
				true,
			),
			'fontbad' => array(
				'https://bad.example.com/font.css',
				false,
			),
		);
	}

	/**
	 * Tests that font URLs get validated.
	 *
	 * @dataProvider get_font_urls
	 * @param string $url  Font URL.
	 * @param bool   $pass Whether the font URL is ok.
	 */
	public function test_font_urls( $url, $pass ) {
		$dom = AMP_DOM_Utils::get_dom( sprintf( '<html><head><link rel="stylesheet" href="%s"></head></html>', $url ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

		$sanitizer = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element' => true,
		) );
		$sanitizer->sanitize();

		$link = $dom->getElementsByTagName( 'link' )->item( 0 );
		if ( $pass ) {
			$this->assertInstanceOf( 'DOMElement', $link );
			$this->assertEquals( $url, $link->getAttribute( 'href' ) );
		} else {
			$this->assertEmpty( $link );
		}
	}
}
