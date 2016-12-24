<?php

class AMP_Tag_And_Attribute_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'empty' => array(
				'',
				''
			),

			'attribute_value_blacklisted_by_regex_removed' => array(
				'<a href="__amp_source_origin">Click me.</a>',
				'<a>Click me.</a>'
			),

			'host_relative_url_allowed' => array(
				'<a href="/path/to/content">Click me.</a>',
				'<a href="/path/to/content">Click me.</a>'
			),

			'protocol_relative_url_allowed' => array(
				'<a href="//example.com/path/to/content">Click me.</a>',
				'<a href="//example.com/path/to/content">Click me.</a>'
			),

			'node_with_whiteilsted_protocol_http_allowed' => array(
				'<a href="http://example.com/path/to/content">Click me.</a>',
				'<a href="http://example.com/path/to/content">Click me.</a>',
			),

			'node_with_whiteilsted_protocol_https_allowed' => array(
				'<a href="https://example.com/path/to/content">Click me.</a>',
				'<a href="https://example.com/path/to/content">Click me.</a>',
			),

			'node_with_whiteilsted_protocol_fb-messenger_allowed' => array(
				'<a href="fb-messenger://example.com/path/to/content">Click me.</a>',
				'<a href="fb-messenger://example.com/path/to/content">Click me.</a>',
			),

			'attribute_with_disallowed_protocol_removed' => array(
				'<a href="evil://example.com/path/to/content">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_with_blacklisted_regex_removed' => array(
				'<a rel="import">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_with_blacklisted_multi-part_regex_removed' => array(
				'<a rel="something else import">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_with_required_regex' => array(
				'<a target="_blank">Click me.</a>',
				'<a target="_blank">Click me.</a>',
			),

			'attribute_value_with_disallowed_required_regex_removed' => array(
				'<a target="_not_blank">Click me.</a>',
				'<a>Click me.</a>',
			),

			'attribute_value_with_required_value_casei_lower' => array(
				'<a type="text/html">Click.me.</a>',
				'<a type="text/html">Click.me.</a>',
			),

			'attribute_value_with_required_value_casei_upper' => array(
				'<a type="TEXT/HTML">Click.me.</a>',
				'<a type="TEXT/HTML">Click.me.</a>',
			),

			'attribute_value_with_required_value_casei_mixed' => array(
				'<a type="TeXt/HtMl">Click.me.</a>',
				'<a type="TeXt/HtMl">Click.me.</a>',
			),

			'attribute_value_with_bad_value_casei_removed' => array(
				'<a type="bad_type">Click.me.</a>',
				'<a>Click.me.</a>',
			),

			'attribute_value_with_value_regex_casei_lower' => array(
				'<amp-dailymotion data-videoid="abc"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="abc"></amp-dailymotion>',
			),

			'attribute_value_with_value_regex_casei_upper' => array(
				'<amp-dailymotion data-videoid="ABC"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="ABC"></amp-dailymotion>',
			),

			'attribute_value_with_bad_value_regex_casei_removed' => array(
				'<amp-dailymotion data-videoid="###"></amp-dailymotion>',
				'<amp-dailymotion></amp-dailymotion>',
			),

			'atribute_bad_attr_with_no_value_removed' => array(
				'<amp-ad bad-attr-no-value>something here</amp-alt>',
				'<amp-ad>something here</amp-ad>'
			),

			'atribute_bad_attr_with_value_removed' => array(
				'<amp-ad bad-attr="some-value">something here</amp-alt>',
				'<amp-ad>something here</amp-ad>'
			),

			'nodes_with_non_whitelisted_tags_replaced_by_children' => array(
				'<invalid_tag>this is some text inside the invalid node</invalid_tag>',
				'this is some text inside the invalid node',
			),

			'empty_parent_nodes_of_non_whitelisted_tags_removed' => array(
				'<div><span><span><invalid_tag></invalid_tag></span></span></div>',
				'',
			),

			'replace_non_whitelisted_node_with_children' => array(
				'<p>This is some text <invalid_tag>with a disallowed tag</invalid_tag> in the middle of it.</p>',
				'<p>This is some text with a disallowed tag in the middle of it.</p>',
			),

			'remove_attribute_on_node_with_missing_mandatory_parent' => array(
				'<div submit-success>This is a test.</div>',
				'<div>This is a test.</div>',
			),

			'leave_attribute_on_node_with_present_mandatory_parent' => array(
				'<form><div submit-success>This is a test.</div></form>',
				'<form><div submit-success="">This is a test.</div></form>',
			),

			'disallowed_empty_attr_removed' => array(
				'<amp-user-notification data-dismiss-href></amp-user-notification>',
				'<amp-user-notification></amp-user-notification>',
			),

			'allowed_empty_attr' => array(
				'<a border=""></a>',
				'<a border=""></a>',
			),

			'remove_node_with_disallowed_ancestor' => array(
				'<amp-sidebar>The sidebar<amp-ad>This node is not allowed here.</amp-ad></amp-sidebar>',
				'<amp-sidebar>The sidebar</amp-sidebar>',
			),

			'remove_node_without_mandatory_ancestor' => array(
				'<div>All I have is this div, when all you want is a noscript tag.<audio>Sweet tunes</audio></div>',
				'<div>All I have is this div, when all you want is a noscript tag.</div>',
			),


			// Test Cases from test-amp-blacklist-sanitizer.php

			'disallowed_tag_with_innertext' => array(
				'<script>alert("")</script>',
				''
			),

			'multiple_disallowed_tags_only' => array(
				'<clearly_not_allowed /><script>alert("")</script><style>body{ color: red; }</style>',
				''
			),

			'multiple_disallowed_tags_only_in_child' => array(
				'<p><clearly_not_allowed /><script>alert("")</script><style>body{ color: red; }</style></p>',
				''
			),

			'allowed_tag_only' => array(
				'<p>Text</p><img src="/path/to/file.jpg" />',
				'<p>Text</p>'
			),

			'disallowed_attributes' => array(
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

			'multiple_disallowed_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			),

			'javascript_protocol' => array(
				'<a href="javascript:alert(\'Hello\');">Click</a>',
				'<a>Click</a>'
			),

			'attribute_recursive' => array(
				'<div style="border: 1px solid red;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>',
				'<div><a href="/path/to/file.jpg">Hello World</a></div>'
			),

			'mixed_tags' => array(
				'<input type="text"/><p>Text</p><script>alert("")</script><style>body{ color: red; }</style>',
				'<input type="text"/><p>Text</p>'
			),

			'no_strip_amp_tags' => array(
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>',
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>'
			),

			'a_with_attachment_rel' => array(
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
			),

			'a_with_attachment_rel_plus_another_valid_value' => array(
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
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
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_new' => array(
				'<a href="http://example.com" target="_new">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_self' => array(
				'<a href="http://example.com" target="_self">Link</a>',
				'<a href="http://example.com" target="_self">Link</a>',
			),

			'a_with_target_invalid' => array(
				'<a href="http://example.com" target="boom">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_href_invalid' => array(
				'<a href="some random text">Link</a>',
				'<a href="some random text">Link</a>',
			),

			'a_with_href_scheme_invalid' => array(
				'<a href="wp://alinktosomething">Link</a>',
				'<a>Link</a>',
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
				'<a>Hello World</a>',
			),

			'a_empty_with_children_with_restricted_attributes' => array(
				'<a><span style="color: red;">Red</span>&amp;<span style="color: blue;">Orange</span></a>',
				'<a><span>Red</span>&amp;<span>Orange</span></a>'
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
	 * @group allowed-tags
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}
}
