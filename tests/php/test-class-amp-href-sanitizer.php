<?php
/**
 * Class AMP_Href_Sanitizer_Test.
 *
 * @package AMP
 */

/**
 * Class AMP_Href_Sanitizer_Test.
 *
 * Tests to make sure all invalid URLs in href attributes are removed.
 *
 * @covers AMP_Iframe_Sanitizer
 */
class AMP_Href_Sanitizer_Test extends WP_UnitTestCase {

	public function get_href_test_data() {
		return [
			'no_href'                                                     => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			],
			'valid_href'                                                  => [
				'<a href="https://example.com/">Valid Link</a>',
				'<a href="https://example.com/">Valid Link</a>',
			],
			'invalid_href'                                                => [
				'<a href="https://foo@">Invalid Link</a>',
				'<a>Invalid Link</a>',
			],
			'multiple_valid_hrefs'                                        => [
				'<a href="https://example.com/a/">Valid Link A</a><a href="https://example.com/b/">Valid Link B</a><a href="https://example.com/c/">Valid Link C</a>',
				'<a href="https://example.com/a/">Valid Link A</a><a href="https://example.com/b/">Valid Link B</a><a href="https://example.com/c/">Valid Link C</a>',
			],
			'multiple_invalid_href'                                       => [
				'<a href="https://foo@">Invalid Link A</a><a href="http:///example.com">Invalid Link B</a><a href="h t t p : / / e x a m p l e . c o m">Invalid Link C</a>',
				'<a>Invalid Link A</a><a>Invalid Link B</a><a>Invalid Link C</a>',
			],
			'multiple_mixed_hrefs'                                        => [
				'<a href="https://example.com/">Valid Link</a><a href="https://foo@">Invalid Link</a>',
				'<a href="https://example.com/">Valid Link</a><a>Invalid Link</a>',
			],
			'relative_urls_are_valid'                                     => [
				'<link rel="dns-prefetch" href="//cdn.ampproject.org">',
				'<link rel="dns-prefetch" href="//cdn.ampproject.org">',
			],
			'anchor_links_are_valid'                                      => [
				'<a href="#section-a">Valid Link</a>',
				'<a href="#section-a">Valid Link</a>',
			],
			'unwanted_additional_attributes_are_kept_for_valid_urls'      => [
				'<a id="this-is-kept" href="http://example.com/" target="_blank" download rel="nofollow" rev="nofollow" hreflang="en" type="text/html" class="this-stays">Invalid Link</a>',
				'<a id="this-is-kept" href="http://example.com/" target="_blank" download rel="nofollow" rev="nofollow" hreflang="en" type="text/html" class="this-stays">Invalid Link</a>',
			],
			'unwanted_additional_attributes_are_omitted_for_invalid_urls' => [
				'<a id="this-is-kept" href="http://foo@" target="_blank" download rel="nofollow" rev="nofollow" hreflang="en" type="text/html" class="this-stays">Invalid Link</a>',
				'<a id="this-is-kept" class="this-stays">Invalid Link</a>',
			],
		];
	}

	/**
	 * @dataProvider get_href_test_data
	 */
	public function test_href_validation( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Href_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}
}
