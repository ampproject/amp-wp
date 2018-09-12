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
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-bb01159{color:#0f0}',
				),
			),

			'span_one_style_bad_format' => array(
				'<span style="color  :   #00ff00">This is green.</span>',
				'<span class="amp-wp-0837823">This is green.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-0837823{color:#0f0}',
				),
			),

			'span_two_styles_reversed' => array(
				'<span style="color: #00ff00; background-color: #000; ">This is green.</span>',
				'<span class="amp-wp-c71affe">This is green.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-c71affe{color:#0f0;background-color:#000}',
				),
			),

			'span_display_none' => array(
				'<span style="display: none;">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				'<span class="amp-wp-224b51a">Kses-banned properties are allowed since Kses will have already applied if user does not have unfiltered_html.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-224b51a{display:none}',
				),
			),

			'!important_is_ok' => array(
				'<span style="padding:1px; margin: 2px !important; outline: 3px;">!important is converted.</span>',
				'<span class="amp-wp-6a75598">!important is converted.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-6a75598{padding:1px;outline:3px}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-6a75598{margin:2px}',
				),
			),

			'!important_with_spaces_also_converted' => array(
				'<span style="color: red  !  important;">!important is converted.</span>',
				'<span class="amp-wp-952600b">!important is converted.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-952600b{color:red}',
				),
			),

			'!important_multiple_is_converted' => array(
				'<span style="color: red !important; background: blue!important;">!important is converted.</span>',
				'<span class="amp-wp-1e2bfaa">!important is converted.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-1e2bfaa{color:red;background:blue}',
				),
			),

			'!important_takes_precedence_over_inline' => array(
				'<header id="header" style="display: none;"><h1>This is the header.</h1></header><style>#header { display: block !important;width: 100%;background: #fff; }',
				'<header id="header" class="amp-wp-224b51a"><h1>This is the header.</h1></header>',
				array(
					'#header{width:100%;background:#fff}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) #header{display:block}',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-224b51a{display:none}',
				),
			),

			'two_nodes' => array(
				'<span style="color: #00ff00;"><span style="color: #ff0000;">This is red.</span></span>',
				'<span class="amp-wp-bb01159"><span class="amp-wp-cc68ddc">This is red.</span></span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-bb01159{color:#0f0}',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-cc68ddc{color:#f00}',
				),
			),

			'existing_class_attribute' => array(
				'<figure class="alignleft" style="background: #000"></figure>',
				'<figure class="alignleft amp-wp-2864855"></figure>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-2864855{background:#000}',
				),
			),

			'inline_style_element_with_multiple_rules_containing_selectors_is_removed' => array(
				'<style>div > span { font-weight:bold !important; font-style: italic; } @media screen and ( max-width: 640px ) { div > span { font-weight:normal !important; font-style: normal; } }</style><div><span>bold!</span></div>',
				'<div><span>bold!</span></div>',
				array(
					'div > span{font-style:italic}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) div > span{font-weight:bold}@media screen and ( max-width: 640px ){div > span{font-style:normal}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) div > span{font-weight:normal}}',
				),
			),

			'illegal_unsafe_properties' => array(
				'<style>button { behavior: url(hilite.htc) /* IE only */; font-weight:bold; -moz-binding: url(http://www.example.org/xbl/htmlBindings.xml#checkbox); /*XBL*/ }</style><style> @media screen { button { behavior: url(hilite.htc) /* IE only */; font-weight:bold; -moz-binding: url(http://www.example.org/xbl/htmlBindings.xml#checkbox); /*XBL*/ } }</style><button>Click</button>',
				'<button>Click</button>',
				array(
					'button{font-weight:bold}',
					'@media screen{button{font-weight:bold}}',
				),
				array( 'illegal_css_property', 'illegal_css_property', 'illegal_css_property', 'illegal_css_property' ),
			),

			'illegal_at_rule_in_style_attribute' => array(
				'<span style="color:brown; @media screen { color:green }">invalid @-rule omitted.</span>',
				'<span class="amp-wp-481af57">invalid @-rule omitted.</span>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-481af57{color:brown}',
				),
				array(),
			),

			'illegal_at_rules_removed' => array(
				'<style>@charset "utf-8"; @namespace svg url(http://www.w3.org/2000/svg); @page { margin: 1cm; } @viewport { width: device-width; } @counter-style thumbs { system: cyclic; symbols: "\1F44D"; suffix: " "; } body { color: black; }</style>',
				'',
				array(
					'body{color:black}',
				),
				array( 'illegal_css_at_rule', 'illegal_css_at_rule', 'illegal_css_at_rule', 'illegal_css_at_rule', 'illegal_css_at_rule' ),
			),

			'allowed_at_rules_retained' => array(
				'<style>@media screen and ( max-width: 640px ) { body { font-size: small; } } @font-face { font-family: "Open Sans"; src: url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2"); } @supports (display: grid) { div { display: grid; } } @-moz-keyframes appear { from { opacity: 0.0; } to { opacity: 1.0; } } @keyframes appear { from { opacity: 0.0; } to { opacity: 1.0; } }</style>',
				'',
				array(
					'@media screen and ( max-width: 640px ){body{font-size:small}}@font-face{font-family:"Open Sans";src:url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2")}@supports (display: grid){div{display:grid}}@-moz-keyframes appear{from{opacity:0}to{opacity:1}}@keyframes appear{from{opacity:0}to{opacity:1}}',
				),
			),

			'selector_specificity' => array(
				'<style>#child {color:red !important} #parent #child {color:pink !important} .foo { color:blue !important; } #me .foo { color: green !important; }</style><div id="parent"><span id="child" class="foo bar baz">one</span><span style="color: yellow;">two</span><span style="color: purple !important;">three</span></div>',
				'<div id="parent"><span id="child" class="foo bar baz">one</span><span class="amp-wp-64b4fd4">two</span><span class="amp-wp-ab79d9e">three</span></div>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) #child{color:red}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) #parent #child{color:pink}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) .foo{color:blue}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) #me .foo{color:green}',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-64b4fd4{color:yellow}',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-ab79d9e{color:purple}',
				),
			),

			'grid_lines'                                  => array(
				'<style>.wrapper {display: grid;grid-template-columns: [main-start] 1fr [content-start] 1fr [content-end] 1fr [main-end];grid-template-rows: [main-start] 100px [content-start] 100px [content-end] 100px [main-end];}</style><div class="wrapper"></div>',
				'<div class="wrapper"></div>',
				array(
					'.wrapper{display:grid;grid-template-columns:[main-start] 1fr [content-start] 1fr [content-end] 1fr [main-end];grid-template-rows:[main-start] 100px [content-start] 100px [content-end] 100px [main-end]}',
				),
			),

			'col_with_width_attribute' => array(
				'<table><colgroup><col width="253"/></colgroup></table>',
				'<table><colgroup><col class="amp-wp-cbcb5c2"></colgroup></table>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-cbcb5c2{width:253px}',
				),
			),

			'col_with_percent_width_attribute' => array(
				'<table><colgroup><col width="50%"/></colgroup></table>',
				'<table><colgroup><col class="amp-wp-cd7753e"></colgroup></table>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-cd7753e{width:50%}',
				),
			),

			'col_with_star_width_attribute' => array(
				'<table><colgroup><col width="0*"/></colgroup></table>',
				'<table><colgroup><col width="0*"></colgroup></table>',
				array(),
			),

			'col_with_width_attribute_and_existing_style' => array(
				'<table><colgroup><col width="50" style="background-color: red; width: 60px"/></colgroup></table>',
				'<table><colgroup><col class="amp-wp-c8aa9e9"></colgroup></table>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-c8aa9e9{width:50px;width:60px;background-color:red}',
				),
			),

			'multi_selector_in_not_pseudo_class'         => array(
				'<style>.widget:not(.widget_text,.jetpack_widget_social_icons[title="a,b"]) ul { color:red; }</style><div class="widget"></div>',
				'<div class="widget"></div>',
				array(
					'.widget:not(.widget_text,.jetpack_widget_social_icons[title="a,b"]) ul{color:red}',
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
	 * @param array  $expected_errors      Expected error codes.
	 */
	public function test_body_style_attribute_sanitizer( $source, $expected_content, $expected_stylesheets, $expected_errors = array() ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$error_codes = array();
		$args        = array(
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		);

		$sanitizer = new AMP_Style_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		// Test content.
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected_content, $content );

		// Test stylesheet.
		$this->assertEquals( $expected_stylesheets, array_values( $sanitizer->get_stylesheets() ) );

		$this->assertEquals( $expected_errors, $error_codes );
	}

	/**
	 * Get link and style test data.
	 *
	 * @return array
	 */
	public function get_link_and_style_test_data() {
		return array(
			'multiple_amp_custom_and_other_styles' => array(
				'<html amp><head><meta charset="utf-8"><style amp-custom>b {color:red !important}</style><style amp-custom>i {color:blue}</style><style type="text/css">u {color:green; text-decoration: underline !important}</style></head><body><style>s {color:yellow} /* So !important! */</style><b>1</b><i>i</i><u>u</u><s>s</s></body></html>',
				array(
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) b{color:red}',
					'i{color:blue}',
					'u{color:green}:root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) u{text-decoration:underline}',
					's{color:yellow}',
				),
				array(),
			),
			'style_elements_with_link_elements' => array(
				sprintf(
					'<html amp><head><meta charset="utf-8"><style type="text/css">strong.before-dashicon {color:green}</style><link rel="stylesheet" href="%s"><style type="text/css">strong.after-dashicon {color:green}</style></head><body><style>s {color:yellow !important}</style><s class="before-dashicon"></s><strong class="dashicons-dashboard"></strong><strong class="after-dashicon"></strong></body></html>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					includes_url( 'css/dashicons.css' )
				),
				array(
					'strong.before-dashicon',
					'.dashicons-dashboard:before',
					'strong.after-dashicon',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_) s{color:yellow}',
				),
				array(),
			),
			'style_with_no_head' => array(
				'<html amp><body>Not good!<style>body{color:red}</style></body></html>',
				array(
					'body{color:red}',
				),
				array(),
			),
			'style_with_not_selectors' => array(
				'<html amp><head><meta charset="utf-8"><style amp-custom>body.bar > p:not(.baz) { color:red; } body.foo:not(.bar) > p { color:blue; } body.foo:not(.bar) p:not(.baz) { color:green; } body.foo p { color:yellow; }</style></head><body class="foo"><p>Hello</p></body></html>',
				array(
					'body.foo:not(.bar) > p{color:blue}body.foo:not(.bar) p:not(.baz){color:green}body.foo p{color:yellow}',
				),
				array(),
			),
			'style_with_attribute_selectors' => array(
				'<html amp><head><meta charset="utf-8"><style amp-custom>.social-navigation a[href*="example.com"] { color:red; } .social-navigation a.examplecom { color:blue; }</style></head><body class="foo"><nav class="social-navigation"><a href="https://example.com/">Example</a></nav></body></html>',
				array(
					'.social-navigation a[href*="example.com"]{color:red}',
				),
				array(),
			),
			'style_on_root_element' => array(
				'<html amp style="color:red;"><head><meta charset="utf-8"><style amp-custom>html { background-color: blue !important; }</style></head><body>Hi</body></html>',
				array(
					'html:not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_):not(#_){background-color:blue}',
					':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-10b06ba{color:red}',
				),
				array(),
			),
			'styles_with_dynamic_elements' => array(
				implode( '', array(
					'<html amp><head><meta charset="utf-8">',
					'<style amp-custom>b.foo, form [submit-success] b, div[submit-failure] b, form.unused b { color: green }</style>',
					'<style amp-custom>.dead-list li .highlighted, amp-live-list li .highlighted { background: yellow }</style>',
					'<style amp-custom>article.missing amp-live-list li .highlighted { background: yellow }</style>',
					'<style amp-custom>body amp-list .portland { color:blue; }</style>',
					'</head><body>',
					'<form method="post" action-xhr="https://example.com/subscribe" target="_top"><div submit-success><template type="amp-mustache"><b>Thanks</b>, {{name}}}</template></div></form>',
					'<amp-live-list id="my-live-list" data-poll-interval="15000" data-max-items-per-page="20"><button update on="tap:my-live-list.update">You have updates!</button><ul items><li id="live-list-2-item-2" data-sort-time="1464281932879">Hello</li></ul></amp-live-list>',
					'<amp-list width="auto" height="100" layout="fixed-height" src="https://ampproject-b5f4c.firebaseapp.com/examples/data/amp-list-urls.json"> <template type="amp-mustache"> <div class="url-entry"> <a href="{{url}}" class="{{class}}">{{title}}</a> </div> </template> </amp-list>',
					'</body></html>',
				) ),
				array(
					'form [submit-success] b,div[submit-failure] b{color:green}',
					'amp-live-list li .highlighted{background:yellow}',
					'',
					'body amp-list .portland{color:blue}',
				),
				array(),
			),
			'styles_with_calc_functions' => array(
				implode( '', array(
					'<html amp><head>',
					'<style amp-custom>body { color: red; width: calc( 1px + calc( 2vh / 3 ) - 2px * 5 ); outline: solid 1px blue; }</style>',
					'<style amp-custom>.alignwide{ max-width: -webkit-calc(50% + 22.5rem); border: solid 1px red; }</style>',
					'<style amp-custom>.alignwide{ height: calc(10% + ( 1px ); color: red; content: ")"}</style>', // Test unbalanced parentheses.
					'</head><body><div class="alignwide"></div></body></html>',
				) ),
				array(
					'body{color:red;width:calc(1px + calc(2vh / 3) - 2px * 5);outline:solid 1px blue}',
					'.alignwide{max-width:-webkit-calc(50% + 22.5rem);border:solid 1px red}',
					'.alignwide{color:red;content:")"}',
				),
				array(),
			),
			'style_with_media_element' => array(
				'<html amp><head><meta charset="utf-8"><style media="print">.print { display:none; }</style></head><body><button class="print" on="tap:AMP.print()"></button></body></html>',
				array(
					'@media print{.print{display:none}}',
				),
				array(),
			),
			'selectors_with_ie_hacks_removed' => array(
				'<html amp><head><meta charset="utf-8"><style>* html span { color:red; background: blue !important; } span { text-decoration:underline; } *+html span { border: solid green; }</style></head><body><span>Test</span></body></html>',
				array(
					'span{text-decoration:underline}',
				),
				array(),
			),
			'unamerican_selectors_removed' => array( // USA is used for convenience here. No political statement intended.
				'<html amp><head><meta charset="utf-8"><style>html[lang=en-US] {color:red} html[lang="en-US"] {color:white} html[lang^=en] {color:blue} html[lang="en-CA"] {color:red}  html[lang^=ar] { color:green; } html[lang="es-MX"] { color:green; }</style></head><body><span>Test</span></body></html>',
				array(
					'html[lang=en-US]{color:red}html[lang="en-US"]{color:white}html[lang^=en]{color:blue}',
				),
				array(),
			),
			'external_link_without_css_file_extension' => array(
				'<html amp><head><meta charset="utf-8"><link rel="stylesheet" href="https://example.com/_static/??-eJx9kN1SAyEMhV9Iip3aOl44Pgs"></head><body><span>externally-styled</span></body></html>', // phpcs:ignore
				array(
					'span:before{content:"Returned from: https://example.com/_static/??-eJx9kN1SAyEMhV9Iip3aOl44Pgs"}',
				),
				array(),
			),
		);
	}

	/**
	 * Test style elements and link elements.
	 *
	 * @dataProvider get_link_and_style_test_data
	 * @param string $source               Source.
	 * @param array  $expected_stylesheets Expected stylesheets.
	 * @param array  $expected_errors      Expected error codes.
	 */
	public function test_link_and_style_elements( $source, $expected_stylesheets, $expected_errors = array() ) {
		add_filter( 'locale', function() {
			return 'en_US';
		} );
		add_filter( 'pre_http_request', function( $preempt, $request, $url ) {
			unset( $request, $preempt );
			$preempt = array(
				'response' => array(
					'code' => 200,
				),
				'body' => sprintf( 'span:before { content: "Returned from: %s"; }', $url ),
			);
			return $preempt;
		}, 10, 3 );
		$dom = AMP_DOM_Utils::get_dom( $source );

		$error_codes = array();
		$args        = array(
			'use_document_element'      => true,
			'remove_unused_rules'       => 'always',
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		);

		$sanitizer = new AMP_Style_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, $args );
		$whitelist_sanitizer->sanitize();

		$sanitized_html     = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertCount( count( $expected_stylesheets ), $actual_stylesheets );
		foreach ( $expected_stylesheets as $i => $expected_stylesheet ) {
			if ( empty( $expected_stylesheet ) ) {
				$this->assertEmpty( $actual_stylesheets[ $i ] );
				continue;
			}

			if ( false === strpos( $expected_stylesheet, '{' ) ) {
				$this->assertContains( $expected_stylesheet, $actual_stylesheets[ $i ] );
			} else {
				$this->assertEquals( $expected_stylesheet, $actual_stylesheets[ $i ] );
			}
			$this->assertContains( $expected_stylesheet, $sanitized_html );
		}

		$this->assertEquals( $expected_errors, $error_codes );
	}

	/**
	 * Data for testing AMP selector conversion.
	 *
	 * @return array
	 */
	public function get_amp_selector_data() {
		return array(
			'img' => array(
				sprintf( '<div><img class="logo" src="%s" width="200" height="100"></div>', admin_url( 'images/wordpress-logo.png' ) ),
				'div img.logo{border:solid 1px red}',
				'div amp-img.logo{border:solid 1px red}', // Note amp-anim is still tree-shaken because it doesn't occur in the DOM.
			),
			'img-missing-class' => array(
				sprintf( '<div><img class="logo" src="%s" width="200" height="100"></div>', admin_url( 'images/wordpress-logo.png' ) ),
				'div img.missing{border:solid 1px red}',
				'', // Tree-shaken because missing class doesn't occur in the DOM.
			),
			'img-and-anim' => array(
				sprintf( '<div><img class="logo" src="%s" width="200" height="100"><img class="spinner" src="%s" width="200" height="100"></div>', admin_url( 'images/wordpress-logo.png' ), admin_url( 'images/spinner-2x.gif' ) ),
				'div img{border:solid 1px red}',
				'div amp-img,div amp-anim{border:solid 1px red}',
			),
			'img_with_amp_img' => array(
				'<amp-img></amp-img>',
				'amp-img img{background-color:red}',
				'amp-img img{background-color:red}',
			),
			'img-cover' => array(
				sprintf( '<div><amp-img class="logo" src="%s" width="200" height="100"></amp-img></div>', admin_url( 'images/wordpress-logo.png' ) ),
				'div amp-img.logo img{object-fit:cover}',
				'div amp-img.logo img{object-fit:cover}',
			),
			'img-tree-shaking' => array(
				sprintf( '<article><img class="logo" src="%s" width="200" height="100"></article>', admin_url( 'images/wordpress-logo.png' ) ),
				'div img.logo{border:solid 1px red}',
				'', // The selector is removed because there is no div element.
			),
			'playbuzz' => array(
				'<p>hello</p><div class="pb_feed" data-item="226dd4c0-ef13-4fee-850b-7be32bf6d121"></div>',
				'p + div.pb_feed{border:solid 1px blue}',
				'p + amp-playbuzz.pb_feed{border:solid 1px blue}',
			),
			'video' => array(
				'<article><video src="http://example.com" height="100" width="200"></video></article>',
				'article>video{border:solid 1px green}',
				'article>amp-video{border:solid 1px green}',
			),
			'video_with_amp_video' => array(
				'<amp-video class="video"></amp-video>',
				'amp-video.video video{border:solid 1px green}',
				'amp-video.video video{border:solid 1px green}',
			),
			'iframe' => array(
				'<p><b>purple</b><iframe src="http://example.com" height="100" width="200"></iframe></p>',
				'p>*:not(iframe){color:purple}',
				'p>*:not(amp-iframe){color:purple}',
			),
			'audio' => array(
				'<audio src="http://example.com/foo.mp3" height="100" width="200"></audio>',
				'audio{border:solid 1px yellow}',
				'amp-audio{border:solid 1px yellow}',
			),
			'keyframes' => array(
				'<div>test</div>',
				'span {color:red;} @keyframes foo { from: { opacity:0; } 50% {opacity:0.5} 75%,80% { opacity:0.6 } to { opacity:1 }  }',
				'@keyframes foo{from:{opacity:0}50%{opacity:.5}75%,80%{opacity:.6}to{opacity:1}}',
			),
		);
	}

	/**
	 * Test AMP selector conversion.
	 *
	 * @dataProvider get_amp_selector_data
	 * @param string $markup Markup.
	 * @param string $input  Input stylesheet.
	 * @param string $output Output stylesheet.
	 */
	public function test_amp_selector_conversion( $markup, $input, $output ) {
		$html = "<html amp><head><meta charset=utf-8><style amp-custom>$input</style></head><body>$markup</body></html>";
		$dom  = AMP_DOM_Utils::get_dom( $html );

		$sanitizer_classes = amp_get_content_sanitizers();
		$sanitizer_classes['AMP_Style_Sanitizer']['remove_unused_rules'] = 'always';
		$sanitized   = AMP_Content_Sanitizer::sanitize_document( $dom, $sanitizer_classes, array(
			'use_document_element' => true,
		) );
		$stylesheets = array_values( $sanitized['stylesheets'] );
		$this->assertEquals( $output, $stylesheets[0] );
	}

	/**
	 * Data for testing CSS hack removal.
	 *
	 * @return array
	 */
	public function get_amp_css_hacks_data() {
		return array(
			array(
				'.selector { !property: value; }',
			),
			array(
				'.selector { $property: value; }',
			),
			array(
				'.selector { &property: value; }',
			),
			array(
				'.selector { *property: value; }',
			),
			array(
				'.selector { )property: value; }',
			),
			array(
				'.selector { =property: value; }',
			),
			array(
				'.selector { %property: value; }',
			),
			array(
				'.selector { +property: value; }',
			),
			array(
				'.selector { @property: value; }',
			),
			array(
				'.selector { ,property: value; }',
			),
			array(
				'.selector { .property: value; }',
			),
			array(
				'.selector { /property: value; }',
			),
			array(
				'.selector { `property: value; }',
			),
			array(
				'.selector { ]property: value; }',
			),
			array(
				'.selector { #property: value; }',
			),
			array(
				'.selector { ~property: value; }',
			),
			array(
				'.selector { ?property: value; }',
			),
			array(
				'.selector { :property: value; }',
			),
			array(
				'.selector { |property: value; }',
			),
			array(
				'_::selection, .selector:not([attr*=\'\']) {}',
			),
			array(
				':root .selector {}',
			),
			array(
				'body:last-child .selector {}',
			),
			array(
				'body:nth-of-type(1) .selector {}',
			),
			array(
				'body:first-of-type .selector {}',
			),
			array(
				'.selector:not([attr*=\'\']) {}',
			),
			array(
				'.selector:not(*:root) {}',
			),
			array(
				'.selector:not(*:root) {}',
			),
			array(
				'body:empty .selector {}',
			),
			array(
				'body:last-child .selector, x:-moz-any-link {}',
			),
			array(
				'body:last-child .selector, x:-moz-any-link, x:default {}',
			),
			array(
				'body:not(:-moz-handler-blocked) .selector {}',
			),
			array(
				'_::-moz-progress-bar, body:last-child .selector {}',
			),
			array(
				'_::-moz-range-track, body:last-child .selector {}',
			),
			array(
				'_:-moz-tree-row(hover), .selector {}',
			),
			array(
				'_::selection, .selector:not([attr*=\'\']) {}',
			),
			array(
				'* html .selector  {}',
			),
			array(
				'.unused-class.selector {}',
			),
			array(
				'html > body .selector {}',
			),
			array(
				'.selector, {}',
			),
			array(
				'*:first-child+html .selector {}',
			),
			array(
				'.selector, x:-IE7 {}',
			),
			array(
				'*+html .selector {}',
			),
			array(
				'body*.selector {}',
			),
			array(
				'.selector\ {}',
			),
			array(
				'html > /**/ body .selector {}',
			),
			array(
				'head ~ /**/ body .selector {}',
			),
			array(
				'_::selection, .selector:not([attr*=\'\']) {}',
			),
			array(
				':root .selector {}',
			),
			array(
				'body:last-child .selector {}',
			),
			array(
				'body:nth-of-type(1) .selector {}',
			),
			array(
				'body:first-of-type .selector {}',
			),
			array(
				'.selector:not([attr*=\'\']) {}',
			),
		);
	}

	/**
	 * Test removal of IE and Other Browser CSS Hacks
	 *
	 * @dataProvider get_amp_css_hacks_data
	 * @param string $input  Hack input CSS rule.
	 */
	public function test_browser_css_hacks( $input ) {
		$html = "<html amp><head><meta charset=utf-8><style amp-custom>$input</style></head><body></body></html>";
		$dom  = AMP_DOM_Utils::get_dom( $html );

		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'remove_unused_rules'       => 'never',
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertEmpty( $actual_stylesheets[0] );
	}

	/**
	 * Test handling of stylesheets with @font-face that have data: url source.
	 *
	 * Also confirm that class-based tree-shaking is working.
	 *
	 * @covers AMP_Style_Sanitizer::process_font_face_at_rule()
	 */
	public function test_font_data_url_handling() {
		$html  = '<html amp><head><meta charset="utf-8">';
		$html .= '<link rel="stylesheet" href="' . esc_url( includes_url( 'css/dashicons.css' ) ) . '">'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$html .= '</head><body><span class="b dashicons dashicons-admin-appearance"></span></body></html>';

		// Test with tree-shaking.
		$dom         = AMP_DOM_Utils::get_dom( $html );
		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'remove_unused_rules'       => 'always',
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$this->assertEquals( array(), $error_codes );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertCount( 1, $actual_stylesheets );
		$this->assertContains( 'dashicons.woff") format("woff")', $actual_stylesheets[0] );
		$this->assertNotContains( 'data:application/font-woff;', $actual_stylesheets[0] );
		$this->assertContains( '.dashicons{', $actual_stylesheets[0] );
		$this->assertContains( '.dashicons-admin-appearance:before{', $actual_stylesheets[0] );
		$this->assertNotContains( '.dashicons-format-chat:before', $actual_stylesheets[0] );

		// Test with rule-removal not forced, since dashicons alone is not larger than 50KB.
		$dom         = AMP_DOM_Utils::get_dom( $html );
		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'remove_unused_rules'       => 'sometimes',
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$this->assertEquals( array(), $error_codes );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertContains( 'dashicons.woff") format("woff")', $actual_stylesheets[0] );
		$this->assertNotContains( 'data:application/font-woff;', $actual_stylesheets[0] );
		$this->assertContains( '.dashicons,.dashicons-before:before{', $actual_stylesheets[0] );
		$this->assertContains( '.dashicons-admin-appearance:before{', $actual_stylesheets[0] );
		$this->assertContains( '.dashicons-format-chat:before', $actual_stylesheets[0] );
	}

	/**
	 * Test that auto-removal (tree shaking) does not remove rules for classes mentioned in class and [class] attributes.
	 *
	 * @covers AMP_Style_Sanitizer::get_used_class_names()
	 * @covers AMP_Style_Sanitizer::finalize_stylesheet_set()
	 */
	public function test_class_amp_bind_preservation() {
		ob_start();
		?>
		<html amp>
			<head>
				<meta charset="utf-8">
				<style>.sidebar1 { display:none }</style>
				<style>.sidebar1.expanded { display:block }</style>
				<style>.sidebar2{ visibility:hidden }</style>
				<style>.sidebar2.visible { display:block }</style>
				<style>.nothing { visibility:hidden; }</style>
			</head>
			<body>
				<amp-state id="mySidebar">
					<script type="application/json">
						{
							"expanded": false
						}
					</script>
				</amp-state>
				<aside class="sidebar1" [class]="! mySidebar.expanded ? '' : 'expanded'">...</aside>
				<aside class="sidebar2" [class]='mySidebar.expanded ? "visible" : ""'>...</aside>
			</body>
		</html>
		<?php
		$dom = AMP_DOM_Utils::get_dom( ob_get_clean() );

		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'remove_unused_rules'       => 'always',
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$this->assertEquals( array(), $error_codes );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertEquals( '.sidebar1{display:none}', $actual_stylesheets[0] );
		$this->assertEquals( '.sidebar1.expanded{display:block}', $actual_stylesheets[1] );
		$this->assertEquals( '.sidebar2{visibility:hidden}', $actual_stylesheets[2] );
		$this->assertEquals( '.sidebar2.visible{display:block}', $actual_stylesheets[3] );
		$this->assertEmpty( $actual_stylesheets[4] );
	}

	/**
	 * Test that auto-removal is performed when remove_unused_rules=sometimes (the default), and that excessive CSS will be removed entirely.
	 *
	 * @covers AMP_Style_Sanitizer::finalize_stylesheet_set()
	 */
	public function test_large_custom_css_and_rule_removal() {
		$custom_max_size = null;
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && 'style amp-custom' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$custom_max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}
		$this->assertNotNull( $custom_max_size );

		$html  = '<html amp><head><meta charset="utf-8">';
		$html .= '<style>.' . str_repeat( 'a', $custom_max_size - 50 ) . '{ color:red } .b{ color:blue; }</style>';
		$html .= '<style>.b[data-value="' . str_repeat( 'c', $custom_max_size ) . '"] { color:green }</style>';
		$html .= '<style>#nonexists { color:black; } #exists { color:white; }</style>';
		$html .= '<style>div { color:black; } span { color:white; } </style>';
		$html .= '<style>@media only screen and (min-width: 1280px) { .not-exists-selector { margin: 0 auto; } } .b { background: lightblue; }</style>';
		$html .= '<style>@media screen and (max-width: 1000px) {
  @supports (display: grid) {
    .b::before {
      content: "@media screen and (max-width: 1000px) {";
    }
    .b::after {
      content: "}";
    }
  }
}
@media screen and (min-width: 750px) and (max-width: 999px) {
  .b::before {
    content: "@media screen and (max-width: 1000px) {}";
    content: \'@media screen and (max-width: 1000px) {}\';
  }
}
@media screen {}</style>';
		$html .= '</head><body><span class="b">...</span><span id="exists"></span></body></html>';
		$dom   = AMP_DOM_Utils::get_dom( $html );

		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();

		$this->assertEquals(
			array(
				'.b{color:blue}',
				'#exists{color:white}',
				'span{color:white}',
				'.b{background:lightblue}',
				'@media screen and (max-width: 1000px){@supports (display: grid){.b::before{content:"@media screen and (max-width: 1000px) {"}.b::after{content:"}"}}}@media screen and (min-width: 750px) and (max-width: 999px){.b::before{content:"@media screen and (max-width: 1000px) {}";content:"@media screen and (max-width: 1000px) {}"}}',
			),
			array_values( $sanitizer->get_stylesheets() )
		);

		$this->assertEquals(
			array( 'removed_unused_css_rules', 'excessive_css' ),
			$error_codes
		);

		// Make sure the accept_tree_shaking option results in no removed_unused_css_rules error being raised.
		$error_codes = array();
		$dom         = AMP_DOM_Utils::get_dom( $html );
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'accept_tree_shaking'       => true,
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$this->assertEquals(
			array( 'excessive_css' ),
			$error_codes
		);
	}

	/**
	 * Test handling of stylesheets with relative background-image URLs.
	 *
	 * @covers AMP_Style_Sanitizer::real_path_urls()
	 */
	public function test_relative_background_url_handling() {
		$html = '<html amp><head><meta charset="utf-8"><link rel="stylesheet" href="' . esc_url( admin_url( 'css/common.css' ) ) . '"></head><body><span class="spinner"></span></body></html>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$dom  = AMP_DOM_Utils::get_dom( $html );

		$sanitizer = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element' => true,
		) );
		$sanitizer->sanitize();
		AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
		$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertCount( 1, $actual_stylesheets );
		$stylesheet = $actual_stylesheets[0];

		$this->assertNotContains( '../images/spinner', $stylesheet );
		$this->assertContains( sprintf( '.spinner{background-image:url("%s")', admin_url( 'images/spinner-2x.gif' ) ), $stylesheet );
	}

	/**
	 * Test handling external stylesheet.
	 *
	 * @covers AMP_Style_Sanitizer::process_link_element()
	 */
	public function test_external_stylesheet_handling() {
		$test_case = $this; // For PHP 5.3.
		$href      = 'https://stylesheets.example.com/style.css';
		$count     = 0;
		add_filter( 'pre_http_request', function( $preempt, $request, $url ) use ( $href, &$count ) {
			unset( $request );
			if ( $url === $href ) {
				$count++;
				$preempt = array(
					'response' => array(
						'code' => 200,
					),
					'body' => 'html { background-color:lightblue; }',
				);
			}
			return $preempt;
		}, 10, 3 );

		$sanitize_and_get_stylesheet = function() use ( $href, $test_case ) {
			$html = sprintf( '<html amp><head><meta charset="utf-8"><link rel="stylesheet" href="%s"></head><body></body></html>', esc_url( $href ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$dom  = AMP_DOM_Utils::get_dom( $html );

			$sanitizer = new AMP_Style_Sanitizer( $dom, array(
				'use_document_element' => true,
			) );
			$sanitizer->sanitize();
			AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
			$actual_stylesheets = array_values( $sanitizer->get_stylesheets() );
			$test_case->assertCount( 1, $actual_stylesheets );
			return $actual_stylesheets[0];
		};

		$this->assertEquals( 0, $count );
		$this->assertContains( 'background-color:lightblue', $sanitize_and_get_stylesheet() );
		$this->assertEquals( 1, $count );
		$this->assertContains( 'background-color:lightblue', $sanitize_and_get_stylesheet() );
		$this->assertEquals( 1, $count );
	}

	/**
	 * Get amp-keyframe styles.
	 *
	 * @return array
	 */
	public function get_keyframe_data() {
		$keyframes_max_size = null;
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && 'style[amp-keyframes]' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$keyframes_max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}
		$this->assertNotNull( $keyframes_max_size );

		return array(
			'style_amp_keyframes'              => array(
				'<style amp-keyframes>@keyframes anim1 { from { opacity:0.0 } to { opacity:0.5 } } @media (min-width: 600px) {@keyframes anim1 { from { opacity:0.5 } to { opacity:1.0 } } }</style>',
				'<style amp-keyframes="">@keyframes anim1{from{opacity:0}to{opacity:.5}}@media (min-width: 600px){@keyframes anim1{from{opacity:.5}to{opacity:1}}}</style>',
				array(),
			),

			'style_amp_keyframes_max_overflow' => array(
				'<style amp-keyframes>@keyframes anim1 {} @media (min-width: 600px) {@keyframes ' . str_repeat( 'a', $keyframes_max_size + 1 ) . ' {} }</style>',
				'',
				array( 'excessive_css' ),
			),

			'style_amp_keyframes_last_child'   => array(
				'<b>before</b> <style amp-keyframes>@keyframes anim1 {}</style> between <style amp-keyframes>@keyframes anim2 {}</style> as <b>after</b>',
				'<b>before</b>  between  as <b>after</b><style amp-keyframes="">@keyframes anim1{}@keyframes anim2{}</style>',
				array(),
			),

			'blacklisted_and_whitelisted_keyframe_properties' => array(
				'<style amp-keyframes>@keyframes anim1 { 50% { width: 50%; animation-timing-function: ease; opacity: 0.5; height:10%; offset-distance: 50%; visibility: visible; transform: rotate(0.5turn); -webkit-transform: rotate(0.5turn); color:red; } }</style>',
				'<style amp-keyframes="">@keyframes anim1{50%{animation-timing-function:ease;opacity:.5;offset-distance:50%;visibility:visible;transform:rotate(.5 turn);-webkit-transform:rotate(.5 turn)}}</style>',
				array( 'illegal_css_property', 'illegal_css_property', 'illegal_css_property' ),
			),

			'style_amp_keyframes_with_disallowed_rules' => array(
				'<style amp-keyframes>body { color:red; opacity:1; } @keyframes anim1 { 50% { opacity:0.5 !important; } } @font-face { font-family: "Open Sans"; src: url("/fonts/OpenSans-Regular-webfont.woff2") format("woff2"); }</style>',
				'<style amp-keyframes="">@keyframes anim1{50%{opacity:.5}}</style>',
				array( 'unrecognized_css', 'illegal_css_important', 'illegal_css_at_rule' ),
			),
		);
	}

	/**
	 * Test amp-keyframe styles.
	 *
	 * @dataProvider get_keyframe_data
	 * @param string $source   Markup to process.
	 * @param string $expected The markup to expect.
	 * @param array  $expected_errors      Expected error codes.
	 */
	public function test_keyframe_sanitizer( $source, $expected = null, $expected_errors = array() ) {
		$expected    = isset( $expected ) ? $expected : $source;
		$dom         = AMP_DOM_Utils::get_dom_from_content( $source );
		$error_codes = array();
		$sanitizer   = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element' => true,
			'validation_error_callback' => function( $error ) use ( &$error_codes ) {
				$error_codes[] = $error['code'];
			},
		) );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '#\s+(?=@keyframes)#', '', $content );
		$content = preg_replace( '#\s+(?=</style>)#', '', $content );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );

		$this->assertEquals( $expected_errors, $error_codes );
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
			'admin_with_host_https' => array(
				set_url_scheme( admin_url( 'css/common.css' ), 'https' ),
				ABSPATH . 'wp-admin/css/common.css',
			),
			'admin_with_host_http' => array(
				set_url_scheme( admin_url( 'css/common.css' ), 'http' ),
				ABSPATH . 'wp-admin/css/common.css',
			),
			'admin_with_no_host_scheme' => array(
				preg_replace( '#^\w+:(?=//)#', '', admin_url( 'css/common.css' ) ),
				ABSPATH . 'wp-admin/css/common.css',
			),
			'amp_disallowed_file_extension' => array(
				content_url( 'themes/twentyseventeen/index.php' ),
				null,
				'disallowed_file_extension',
			),
			'amp_file_path_not_found' => array(
				content_url( 'themes/twentyseventeen/404.css' ),
				null,
				'file_path_not_found',
			),
			'amp_file_path_illegal_linux' => array(
				content_url( '../../../../../../../../../../../../../../../bad.css' ),
				null,
				'file_path_not_allowed',
			),
			'amp_file_path_illegal_windows' => array(
				content_url( '..\..\..\..\..\..\..\..\..\..\..\..\..\..\..\bad.css' ),
				null,
				'file_path_not_allowed',
			),
			'amp_file_path_illegal_location' => array(
				site_url( 'outside/root.css' ),
				null,
				'file_path_not_allowed',
			),
			'amp_external_file' => array(
				'//s.w.org/wp-includes/css/dashicons.css',
				false,
				'external_file_url',
			),
		);
	}

	/**
	 * Tests get_validated_url_file_path.
	 *
	 * @dataProvider get_stylesheet_urls
	 * @covers AMP_Style_Sanitizer::get_validated_url_file_path()
	 *
	 * @param string      $source     Source URL.
	 * @param string|null $expected   Expected path or null if error.
	 * @param string      $error_code Error code. Optional.
	 */
	public function test_get_validated_url_file_path( $source, $expected, $error_code = null ) {
		$dom = AMP_DOM_Utils::get_dom( '<html></html>' );

		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$actual    = $sanitizer->get_validated_url_file_path( $source, array( 'css' ) );
		if ( isset( $error_code ) ) {
			$this->assertInstanceOf( 'WP_Error', $actual );
			$this->assertEquals( $error_code, $actual->get_error_code() );
		} else {
			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * Get data URLs.
	 *
	 * @returns array data: URL data.
	 */
	public function get_data_urls() {
		return array(
			'url_with_spaces'      => array(
				'html { background-image:url(url with spaces.png); }',
				'html{background-image:url("urlwithspaces.png")}',
			),
			'data_url_with_spaces' => array(
				'html { background: url(data:image/png; base64, ivborw0kggoaaaansuheugaaacwaaaascamaaaapwqozaaaabgdbtueaalgpc/xhbqaaaafzukdcak7ohokaaaamuexurczmzpf399fx1+bm5mzy9amaaadisurbvdjlvzxbesmgces5/p8/t9furvcrmu73jwlzosgsiizurcjo/ad+eqjjb4hv8bft+idpqocx1wjosbfhh2xssxeiyn3uli/6mnree07uiwjev8ueowds88ly97kqytlijkktuybbruayvh5wohixmpi5we58ek028czwyuqdlkpg1bkb4nnm+veanfhqn1k4+gpt6ugqcvu2h2ovuif/gwufyy8owepdyzsa3avcqpvovvzzz2vtnn2wu8qzvjddeto90gsy9mvlqtgysy231mxry6i2ggqjrty0l8fxcxfcbbhwrsyyaaaaaelftksuqmcc); }',
				'html{background:url("data:image/png;base64,ivborw0kggoaaaansuheugaaacwaaaascamaaaapwqozaaaabgdbtueaalgpc/xhbqaaaafzukdcak7ohokaaaamuexurczmzpf399fx1+bm5mzy9amaaadisurbvdjlvzxbesmgces5/p8/t9furvcrmu73jwlzosgsiizurcjo/ad+eqjjb4hv8bft+idpqocx1wjosbfhh2xssxeiyn3uli/6mnree07uiwjev8ueowds88ly97kqytlijkktuybbruayvh5wohixmpi5we58ek028czwyuqdlkpg1bkb4nnm+veanfhqn1k4+gpt6ugqcvu2h2ovuif/gwufyy8owepdyzsa3avcqpvovvzzz2vtnn2wu8qzvjddeto90gsy9mvlqtgysy231mxry6i2ggqjrty0l8fxcxfcbbhwrsyyaaaaaelftksuqmcc")}',
			),
		);
	}

	/**
	 * Test handling of stylesheets with spaces in the background-image URLs.
	 *
	 * @dataProvider get_data_urls
	 * @covers AMP_Style_Sanitizer::remove_spaces_from_data_urls()
	 *
	 * @param string      $source     Source URL string.
	 * @param string|null $expected   Expected normalized URL string.
	 */
	public function test_remove_spaces_from_data_urls( $source, $expected ) {
		$html  = '<html><head><style>';
		$html .= $source;
		$html .= '</style></head</html>';

		$dom = AMP_DOM_Utils::get_dom( $html );

		$sanitizer = new AMP_Style_Sanitizer( $dom );
		$sanitizer->sanitize();

		$stylesheets = array_values( $sanitizer->get_stylesheets() );

		$this->assertContains( $expected, $stylesheets[0] );
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
				array(),
			),
			'tangerine2'  => array(
				'//fonts.googleapis.com/css?family=Tangerine',
				array(),
			),
			'tangerine3'  => array(
				'http://fonts.googleapis.com/css?family=Tangerine',
				array(),
			),
			'typekit'     => array(
				'https://use.typekit.net/abc.css',
				array(),
			),
			'fontscom'    => array(
				'https://fast.fonts.net/abc.css',
				array(),
			),
			'fontawesome' => array(
				'https://maxcdn.bootstrapcdn.com/font-awesome/123/css/font-awesome.min.css',
				array(),
			),
			'bad_ext'    => array(
				home_url( '/bad.php' ),
				array( 'disallowed_file_extension' ),
			),
			'bad_file'    => array(
				home_url( '/bad.css' ),
				array( 'file_path_not_allowed' ),
			),
		);
	}

	/**
	 * Tests that font URLs get validated.
	 *
	 * @dataProvider get_font_urls
	 * @param string $url         Font URL.
	 * @param array  $error_codes Error codes.
	 */
	public function test_font_urls( $url, $error_codes ) {
		$dom = AMP_DOM_Utils::get_dom( sprintf( '<html><head><link rel="stylesheet" href="%s"></head></html>', $url ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

		$validation_errors = array();

		$sanitizer = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element'      => true,
			'validation_error_callback' => function( $error ) use ( &$validation_errors ) {
				$validation_errors[] = $error;
			},
		) );
		$sanitizer->sanitize();

		$this->assertEqualSets( $error_codes, wp_list_pluck( $validation_errors, 'code' ) );

		$link = $dom->getElementsByTagName( 'link' )->item( 0 );
		if ( empty( $error_codes ) ) {
			$this->assertInstanceOf( 'DOMElement', $link );
			$this->assertEquals(
				preg_replace( '#^(http:)?(?=//)#', 'https:', $url ),
				$link->getAttribute( 'href' )
			);
			$this->assertEquals( 'anonymous', $link->getAttribute( 'crossorigin' ) );
		} else {
			$this->assertEmpty( $link );
		}
	}

	/**
	 * Test addition of crossorigin attribute to external stylesheet links.
	 *
	 * @covers AMP_Style_Sanitizer::process_link_element()
	 */
	public function test_cors_enabled_stylesheet_url() {

		// Test supplying crossorigin attribute.
		$document  = AMP_DOM_Utils::get_dom( '<html><head><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine"></head></html>' ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$sanitizer = new AMP_Style_Sanitizer( $document, array( 'use_document_element' => true ) );
		$sanitizer->sanitize();
		$link = $document->getElementsByTagName( 'link' )->item( 0 );
		$this->assertInstanceOf( 'DOMElement', $link );
		$this->assertEquals( 'anonymous', $link->getAttribute( 'crossorigin' ) );

		// Test that existing crossorigin attribute is not overridden.
		$document  = AMP_DOM_Utils::get_dom( '<html><head><link rel="stylesheet" crossorigin="use-credentials" href="https://fonts.googleapis.com/css?family=Tangerine"></head></html>' ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$sanitizer = new AMP_Style_Sanitizer( $document, array( 'use_document_element' => true ) );
		$sanitizer->sanitize();
		$link = $document->getElementsByTagName( 'link' )->item( 0 );
		$this->assertInstanceOf( 'DOMElement', $link );
		$this->assertEquals( 'use-credentials', $link->getAttribute( 'crossorigin' ) );
	}

	/**
	 * Test CSS imports.
	 *
	 * @expectedIncorrectUsage wp_enqueue_style
	 * @covers AMP_Style_Sanitizer::parse_import_stylesheet()
	 */
	public function test_css_import() {
		$local_css_url   = admin_url( 'css/login.css' );
		$import_font_url = 'https://fonts.googleapis.com/css?family=Merriweather:300|PT+Serif:400i|Open+Sans:800|Zilla+Slab:300,400,500|Montserrat:800|Muli:400&subset=cyrillic-ext,latin-ext,cyrillic,greek,greek-ext,vietnamese';
		$import_css_url  = 'https://stylesheets.example.com/style.css';
		$import_css_url2 = 'https://stylesheets.example.com/dynamic-css/';
		$markup          = sprintf(
			'<html><head><link rel="stylesheet" href="%s"><style>@import url("%s"); body { color:red; }</style><style>@import "%s";</style><style>@import "%s";</style></head><body>hello</body></html>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$local_css_url,
			$import_css_url,
			$import_font_url,
			$import_css_url2
		);

		add_filter( 'pre_http_request', function( $preempt, $request, $url ) use ( $import_css_url, $import_css_url2 ) {
			unset( $request );
			if ( $url === $import_css_url ) {
				$preempt = array(
					'response' => array(
						'code' => 200,
					),
					'body' => 'html { background-color:lightblue; }',
				);
			} elseif ( $url === $import_css_url2 ) {
				$preempt = array(
					'response' => array(
						'code' => 200,
					),
					'body' => 'strong { background-color:red; }',
				);
			}
			return $preempt;
		}, 10, 3 );

		$dom       = AMP_DOM_Utils::get_dom( $markup );
		$sanitizer = new AMP_Style_Sanitizer( $dom, array(
			'use_document_element' => true,
			'remove_unused_rules'  => 'never',
		) );
		$sanitizer->sanitize();
		$stylesheets = array_values( $sanitizer->get_stylesheets() );
		$this->assertCount( 4, $stylesheets );
		$this->assertRegExp(
			'/' . implode( '.*', array(
				preg_quote( 'input[type="checkbox"]:disabled' ),
				preg_quote( 'body.rtl' ),
				preg_quote( '.login .message' ),
			) ) . '/s',
			$stylesheets[0]
		);
		$this->assertRegExp(
			'/' . implode( '.*', array(
				preg_quote( 'html{background-color:lightblue}' ),
				preg_quote( 'body{color:red}' ),
			) ) . '/s',
			$stylesheets[1]
		);

		$this->assertEmpty( $stylesheets[2] ); // Since it was importing a font CDN URL.
		$this->assertEquals( 'strong{background-color:red}', $stylesheets[3] );

		$this->assertNotContains( '@import', $dom->getElementsByTagName( 'style' )->item( 0 )->textContent );

		$links = $dom->getElementsByTagName( 'link' );
		$this->assertEquals( 1, $links->length );
		$link = $links->item( 0 );
		$this->assertEquals( $import_font_url, $link->getAttribute( 'href' ) );
		$this->assertEquals( 'stylesheet', $link->getAttribute( 'rel' ) );
	}

	/**
	 * Test CSS with Unicode characters.
	 *
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 */
	public function test_unicode_stylesheet() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		ob_start();
		?>
		<!DOCTYPE html>
		<html amp>
			<head>
				<meta charset="utf-8">
				<?php wp_print_styles( array( 'dashicons' ) ); ?>
				<style>span::after { content:"⚡️"; }</style>
			</head>
			<body>
				<span class="dashicons dashicons-admin-customizer"></span>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		$this->assertContains( ".dashicons-admin-customizer:before{content:\"\xEF\x95\x80\"}", $sanitized_html );
		$this->assertContains( 'span::after{content:"⚡️"}', $sanitized_html );
	}

	/**
	 * Test method which remove media queries without any styles.
	 *
	 * @dataProvider get_styles_with_media_queries
	 *
	 * @covers AMP_Style_Sanitizer::remove_empty_media_queries
	 *
	 * @param string $css Input CSS.
	 * @param string $expected_css CSS expected after removing media queries.
	 */
	public function test_remove_empty_media_queries( $css, $expected_css ) {
		$dom             = AMP_DOM_Utils::get_dom_from_content( '' );
		$style_sanitizer = new AMP_Style_Sanitizer( $dom );
		$result          = $style_sanitizer->remove_empty_media_queries( $css );

		$this->assertSame( $expected_css, $result );
	}

	/**
	 * Return CSS styles with empty media queries.
	 *
	 * @return array CSS Styles.
	 */
	public function get_styles_with_media_queries() {
		return array(
			'not_empty_media_query' => array(
				'.a{color:gray;}@media only screen and(max-width:940px){.b{margin:0 auto;}}.c{display:block;}',
				'.a{color:gray;}@media only screen and(max-width:940px){.b{margin:0 auto;}}.c{display:block;}',
			),
			'two_media_queries' => array(
				'.a{color:gray;}@media only screen and(max-width:940px){}@media only screen and(max-width:940px){.b{margin:0 auto;}}.c{display:block;}',
				'.a{color:gray;}@media only screen and(max-width:940px){.b{margin:0 auto;}}.c{display:block;}',
			),
			'media_query_in_content_property' => array(
				'@media screen {}
@media screen and (max-width: 1000px) {
  @supports (display: grid) {
    .b::before {
      content: "@media screen and (max-width: 1000px) {";
    }
    .b::after {
      content: "}";
    }
  }
}
@media screen {}
@media screen and (min-width: 750px) and (max-width: 999px) {
  .b::before {
    content: "@media screen and (max-width: 1000px) {}";
    content: \'@media screen and (max-width: 1000px) {}\';
  }
}
@media screen {}',
				'@media screen and (max-width: 1000px) {
  @supports (display: grid) {
    .b::before {
      content: "@media screen and (max-width: 1000px) {";
    }
    .b::after {
      content: "}";
    }
  }
}@media screen and (min-width: 750px) and (max-width: 999px) {
  .b::before {
    content: "@media screen and (max-width: 1000px) {}";
    content: \'@media screen and (max-width: 1000px) {}\';
  }
}',
			),
		);
	}
}
