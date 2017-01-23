<?php

class AMP_Blacklist_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				'',
				''
			),

			'blacklisted_tag_with_innertext' => array(
				'<script>alert("")</script>',
				''
			),

			'multiple_blacklisted_tags_only' => array(
				'<input type="text" /><script>alert("")</script><style>body{ color: red; }</style><label>This is a label</label>',
				''
			),

			'multiple_blacklisted_tags_only_in_child' => array(
				'<p><input type="text" /><script>alert("")</script><style>body{ color: red; }</style></p>',
				''
			),

			'whitelisted_tag_only' => array(
				'<p>Text</p><img src="/path/to/file.jpg" />',
				'<p>Text</p><img src="/path/to/file.jpg"/>' // LIBXML_NOEMPTYTAG
			),

			'blacklisted_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'onclick_attribute' => array(
				'<a href="/path/to/file.jpg" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'on_attribute' => array(
				'<button on="tap:my-lightbox">Tap Me</button>',
				'<button on="tap:my-lightbox">Tap Me</button>'
			),

			'multiple_blacklisted_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			),

			'javascript_protocol' => array(
				'<a href="javascript:alert(\'Hello\');">Click</a>',
				'Click'
			),

			'attribute_recursive' => array(
				'<div style="border: 1px solid red;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>',
				'<div><a href="/path/to/file.jpg">Hello World</a></div>'
			),

			'mixed_tags' => array(
				'<input type="text" /><p>Text</p><script>alert("")</script><style>body{ color: red; }</style>',
				'<p>Text</p>'
			),

			'no_strip_amp_tags' => array(
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>',
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>'
			),

			'a_with_attachment_rel' => array(
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_attachment_rel_plus_another_valid_value' => array(
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
				'<a href="http://example.com" rel="attachment">Link</a>',
			),

			'a_with_rev' => array(
				'<a href="http://example.com" rev="footnote">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_blank' => array(
				'<a href="http://example.com" target="_blank">Link</a>',
				'<a href="http://example.com" target="_blank">Link</a>',
			),

			'a_with_target_uppercase_blank' => array(
				'<a href="http://example.com" target="_BLANK">Link</a>',
				'<a href="http://example.com" target="_blank">Link</a>',
			),

			'a_with_target_new' => array(
				'<a href="http://example.com" target="_new">Link</a>',
				'<a href="http://example.com" target="_blank">Link</a>',
			),

			'a_with_target_self' => array(
				'<a href="http://example.com" target="_self">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_invalid' => array(
				'<a href="http://example.com" target="boom">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_href_invalid' => array(
				'<a href="some random text">Link</a>',
				'Link',
			),

			'a_with_href_scheme_invalid' => array(
				'<a href="wp://alinktosomething">Link</a>',
				'Link',
			),

			'a_with_href_scheme_tel' => array(
				'<a href="tel:4166669999">Call Me, Maybe</a>',
				'<a href="tel:4166669999">Call Me, Maybe</a>',
			),

			'a_with_href_scheme_sms' => array(
				'<a href="sms:4166669999">SMS Me, Maybe</a>',
				'<a href="sms:4166669999">SMS Me, Maybe</a>',
			),

			'a_with_href_scheme_mailto' => array(
				'<a href="mailto:email@example.com">Email Me, Maybe</a>',
				'<a href="mailto:email@example.com">Email Me, Maybe</a>',
			),

			'a_with_href_relative' => array(
				'<a href="/home">Home</a>',
				'<a href="/home">Home</a>',
			),

			'a_with_anchor' => array(
				'<a href="#section2">Home</a>',
				'<a href="#section2">Home</a>',
			),

			'a_is_anchor' => array(
				'<a name="section2"></a>',
				'<a name="section2"></a>',
			),

			'a_is_achor_with_id' => array(
				'<a id="section3"></a>',
				'<a id="section3"></a>',
			),

			'a_empty' => array(
				'<a>Hello World</a>',
				'Hello World',
			),

			'a_empty_with_children_with_restricted_attributes' => array(
				'<a><span style="color: red;">Red</span>&amp;<span style="color: blue;">Orange</span></a>',
				'<span>Red</span>&amp;<span>Orange</span>'
			),

			'a_with_mustache_value_for_href' => array(
				'<template type="amp-mustache"><a href="{{url}}">clickety clack</a></template>',
				'<template type="amp-mustache"><a href="{{url}}">clickety clack</a></template>',
			),

			'a_with_mustache_value_for_href_not_in_template' => array(
				'<a href="{{url}}">clickety clack</a>',
				'clickety clack',
			),

			'h1_with_size' => array(
				'<h1 size="1">Headline</h1>',
				'<h1>Headline</h1>',
			),

			'font' => array(
				'<font size="1">Headline</font>',
				'Headline',
			),

			// font is removed so we should check that other elements are checked as well
			'font_with_other_bad_elements' => array(
				'<font size="1">Headline</font><span style="color: blue">Span</span>',
				'Headline<span>Span</span>',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Blacklist_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}
}
