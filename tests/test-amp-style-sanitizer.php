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
				'<span class="amp-wp-bb01159">This is green.</span>',
				array(
					'.amp-wp-bb01159{color:#0f0;}',
				),
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-0837823">This is green.</span>',
				array(
					'.amp-wp-0837823{color:#0f0;}',
				),
			),

			'span_two_styles_reversed' => array(
				'<span style="color: #00ff00; background-color: #000; ">This is green.</span>',
				'<span class="amp-wp-c71affe">This is green.</span>',
				array(
					'.amp-wp-c71affe{color:#0f0;background-color:#000;}',
				),
			),

			'width_to_max-width' => array(
				'<figure style="width: 300px"></figure>',
				'<figure class="amp-wp-343bce0"></figure>',
				array(
					'.amp-wp-343bce0{max-width:300px;}',
				),
			),

			'span_display_none' => array(
				'<span style="display: none;">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				'<span class="amp-wp-224b51a">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				array(
					'.amp-wp-224b51a{display:none;}',
				),
			),

			'!important_is_ok' => array(
				'<span style="padding:1px; margin: 2px !important; outline: 3px;">!important is converted.</span>',
				'<span class="amp-wp-6a75598">!important is converted.</span>',
				array(
					'.amp-wp-6a75598{padding:1px;outline:3px;}:root:not(#FK_ID) .amp-wp-6a75598{margin:2px;}',
				),
			),

			'!important_with_spaces_also_converted' => array(
				'<span style="color: red  !  important;">!important is converted.</span>',
				'<span class="amp-wp-952600b">!important is converted.</span>',
				array(
					':root:not(#FK_ID) .amp-wp-952600b{color:red;}',
				),
			),

			'!important_multiple_is_converted' => array(
				'<span style="color: red !important; background: blue!important;">!important is converted.</span>',
				'<span class="amp-wp-1e2bfaa">!important is converted.</span>',
				array(
					':root:not(#FK_ID) .amp-wp-1e2bfaa{color:red;background:blue;}',
				),
			),

			'two_nodes' => array(
				'<span style="color: #00ff00;"><span style="color: #ff0000;">This is red.</span></span>',
				'<span class="amp-wp-bb01159"><span class="amp-wp-cc68ddc">This is red.</span></span>',
				array(
					'.amp-wp-bb01159{color:#0f0;}',
					'.amp-wp-cc68ddc{color:#f00;}',
				),
			),

			'existing_class_attribute' => array(
				'<figure class="alignleft" style="background: #000"></figure>',
				'<figure class="alignleft amp-wp-2864855"></figure>',
				array(
					'.amp-wp-2864855{background:#000;}',
				),
			),

			'inline_style_element_with_multiple_rules_containing_selectors_is_removed' => array(
				'<style>div > span { font-weight:bold !important; font-style: italic; } @media screen and ( max-width: 640px ) { div > span { font-weight:normal !important; font-style: normal; } }</style><div><span>bold!</span></div>',
				'<div><span>bold!</span></div>',
				array(
					'div > span{font-style:italic;}@media screen and ( max-width: 640px ){div > span{font-style:normal;}:root:not(#FK_ID) div > span{font-weight:normal;}}:root:not(#FK_ID) div > span{font-weight:bold;}',
				),
			),

			'illegal_unsafe_properties' => array(
				'<style>button { behavior: url(hilite.htc) /* IE only */; font-weight:bold; -moz-binding: url(http://www.example.org/xbl/htmlBindings.xml#checkbox); /*XBL*/ } @media screen { button { behavior: url(hilite.htc) /* IE only */; font-weight:bold; -moz-binding: url(http://www.example.org/xbl/htmlBindings.xml#checkbox); /*XBL*/ } }</style><button>Click</button>',
				'<button>Click</button>',
				array(
					'button{font-weight:bold;}@media screen{button{font-weight:bold;}}',
				),
			),

			'illegal_at_rule_in_style_attribute' => array(
				'<span style="color:brown; @media screen { color:green }">Parse error.</span>',
				'<span>Parse error.</span>',
				array(),
			),

			'illegal_at_rules_removed' => array(
				'<style>@charset "utf-8"; @namespace svg url(http://www.w3.org/2000/svg); @page { margin: 1cm; } @viewport { width: device-width; } @counter-style thumbs { system: cyclic; symbols: "\1F44D"; suffix: " "; } body { color: black; }</style>',
				'',
				array(
					'body{color:black;}',
				),
			),

			'allowed_at_rules_retained' => array(
				'<style>@media screen and ( max-width: 640px ) { body { font-size: small; } } @font-face { font-family: "Open Sans"; src: url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2"); } @supports (display: grid) { div { display: grid; } } @-moz-keyframes appear { from { opacity: 0.0; } to { opacity: 1.0; } } @keyframes appear { from { opacity: 0.0; } to { opacity: 1.0; } }</style>',
				'',
				array(
					'@media screen and ( max-width: 640px ){body{font-size:small;}}@font-face{font-family:"Open Sans";src:url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2");}@supports (display: grid){div{display:grid;}}@-moz-keyframes appear{from{opacity:0;}to{opacity:1;}}@keyframes appear{from{opacity:0;}to{opacity:1;}}',
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
				'<html amp><head><meta charset="utf-8"><style amp-custom>b {color:red !important}</style><style amp-custom>i {color:blue}</style><style type="text/css">u {color:green; text-decoration: underline !important;}</style></head><body><style>s {color:yellow} /* So !important! */</style></body></html>',
				array(
					':root:not(#FK_ID) b{color:red;}',
					'i{color:blue;}',
					'u{color:green;}:root:not(#FK_ID) u{text-decoration:underline;}',
					's{color:yellow;}',
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
					':root:not(#FK_ID) s{color:yellow;}',
				),
			),
			'style_with_no_head' => array(
				'<html amp><body>Not good!<style>body{color:red;}</style></body>',
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
			if ( false === strpos( $expected_stylesheet, '{' ) ) {
				$this->assertContains( $expected_stylesheet, $actual_stylesheets[ $i ] );
			} else {
				$this->assertStringStartsWith( $expected_stylesheet, $actual_stylesheets[ $i ] );
			}
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
				'<style amp-keyframes>@keyframes anim1 { from { opacity:0.0 } to { opacity:0.5 } } @media (min-width: 600px) {@keyframes anim1 { from { opacity:0.5 } to { opacity:1.0 } } }</style>',
				'<style amp-keyframes="">@keyframes anim1{from{opacity:0;}to{opacity:.5;}}@media (min-width: 600px){@keyframes anim1{from{opacity:.5;}to{opacity:1;}}}</style>',
			),

			'style_amp_keyframes_max_overflow' => array(
				'<style amp-keyframes>@keyframes anim1 {} @media (min-width: 600px) {@keyframes ' . str_repeat( 'a', $keyframes_max_size + 1 ) . ' {} }</style>',
				'',
			),

			'style_amp_keyframes_last_child'   => array(
				'<b>before</b> <style amp-keyframes>@keyframes anim1 {}</style> between <style amp-keyframes>@keyframes anim2 {}</style> as <b>after</b>',
				'<b>before</b> between  as <b>after</b><style amp-keyframes="">@keyframes anim1{}@keyframes anim2{}</style>',
			),

			'blacklisted_and_whitelisted_keyframe_properties' => array(
				'<style amp-keyframes>@keyframes anim1 { 50% { width: 50%; animation-timing-function: ease; opacity: 0.5; height:10%; offset-distance: 50%; visibility: visible; transform: rotate(0.5turn); -webkit-transform: rotate(0.5turn); color:red; } }</style>',
				'<style amp-keyframes="">@keyframes anim1{50%{animation-timing-function:ease;opacity:.5;offset-distance:50%;visibility:visible;transform:rotate(.5 turn);-webkit-transform:rotate(.5 turn);}}</style>',
			),

			'style_amp_keyframes_with_disallowed_rules' => array(
				'<style amp-keyframes>body { color:red; opacity:1; } @keyframes anim1 { 50% { opacity:0.5 !important; } } @font-face { font-family: "Open Sans"; src: url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2"); }</style>',
				'<style amp-keyframes="">@keyframes anim1{50%{opacity:.5;}}</style>',
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
		$content = preg_replace( '#\s+(?=@keyframes)#', '', $content );
		$content = preg_replace( '#\s+(?=</style>)#', '', $content );
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
