<?php

/**
 * Class AMP_DOM_Utils_Test
 *
 * These are here because PhpStorm cannot find them because of phpunit6-compat.php
 *
 * @method void assertEquals( mixed $expected, mixed $actual, string $errorMessage=null )
 * @method void assertTrue( bool $expectsTrue, string $errorMessage=null )
 * @method void assertFalse( bool $expectsFalse, string $errorMessage=null )
 */
class AMP_DOM_Utils_Test extends WP_UnitTestCase {

	/**
	 * Test UTF-8 content.
	 *
	 * @covers AMP_DOM_Utils::get_dom_from_content()
	 * @covers AMP_DOM_Utils::get_content_from_dom()
	 */
	public function test_utf8_content() {
		$source   = '<p>Iñtërnâtiônàlizætiøn</p>';
		$expected = '<p>Iñtërnâtiônàlizætiøn</p>';

		$dom     = AMP_DOM_Utils::get_dom_from_content( $source );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test adding no attributes to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__no_attributes() {
		$dom  = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node = $dom->createElement( 'b' );
		AMP_DOM_Utils::add_attributes_to_node( $node, [] );
		$this->assertFalse( $node->hasAttributes() );
	}

	/**
	 * Test adding attribute with no value to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__attribute_without_value() {
		$dom        = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node       = $dom->createElement( 'div' );
		$attributes = [ 'placeholder' => '' ];
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	/**
	 * Test adding attribute with value to node.
	 *
	 * @covers AMP_DOM_Utils::add_attributes_to_node()
	 */
	public function test_add_attributes_to_node__attribute_with_value() {
		$dom        = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node       = $dom->createElement( 'div' );
		$attributes = [
			'class' => 'myClass',
			'id'    => 'myId',
		];
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	/**
	 * Assert node has the expected attributes.
	 *
	 * @param DOMElement $node        Element.
	 * @param string[]   $attributes Attributes.
	 */
	protected function check_node_has_attributes( $node, $attributes ) {
		$this->assertEquals( count( $attributes ), $node->attributes->length );
		foreach ( $node->attributes as $attr ) {
			$name  = $attr->nodeName;
			$value = $attr->nodeValue;

			$this->assertTrue( array_key_exists( $name, $attributes ), sprintf( 'Attribute "%s" not found.', $name ) );
			$this->assertEquals( $attributes[ $name ], $value, sprintf( 'Attribute "%s" does not have expected value.', $name ) );
		}
	}

	/**
	 * Test that node is empty.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__yes() {
		$source = '<p></p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertTrue( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that node with text is not empty.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__no__has_text() {
		$source = '<p>Hello</p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that element is not empty when it has a child.
	 *
	 * @covers AMP_DOM_Utils::is_node_empty()
	 */
	public function test__is_node_empty__no__has_child() {
		$source = '<p><b></b></p>';
		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$node   = $dom->getElementsByTagName( 'p' )->item( 0 );

		/**
		 * Element.
		 *
		 * @var DOMElement $node
		 */
		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	/**
	 * Test that empty tag is parsed and serialized without changes.
	 *
	 * @covers AMP_DOM_Utils::get_dom_from_content()
	 * @covers AMP_DOM_Utils::get_content_from_dom()
	 */
	public function test__get_content_from_dom__br_no_closing_tag() {
		$source   = '<br>';
		$expected = '<br>';

		$dom    = AMP_DOM_Utils::get_dom_from_content( $source );
		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test convert_amp_bind_attributes.
	 *
	 * @covers \AMP_DOM_Utils::convert_amp_bind_attributes()
	 */
	public function test_amp_bind_conversion() {
		$original  = '<amp-img width=300 height="200" data-foo="bar" selected src="/img/dog.jpg" [src]="myAnimals[currentAnimal].imageUrl"></amp-img>';
		$converted = AMP_DOM_Utils::convert_amp_bind_attributes( $original );
		$this->assertNotEquals( $converted, $original );
		$this->assertContains( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'src="myAnimals[currentAnimal].imageUrl"', $converted );
		$this->assertContains( 'width=300 height="200" data-foo="bar" selected', $converted );

		// Check tag with self-closing attribute.
		$original  = '<input type="text" role="textbox" class="calc-input" id="liens" name="liens" [value]="(result1 != null) ? result1.liens : \'verifying…\'" />';
		$converted = AMP_DOM_Utils::convert_amp_bind_attributes( $original );
		$this->assertNotEquals( $converted, $original );

		// Preserve trailing slash that is actually the attribute value.
		$original = '<a href=/>Home</a>';
		$this->assertEquals( AMP_DOM_Utils::convert_amp_bind_attributes( $original ), $original );

		// Test malformed.
		$malformed_html = [
			'<amp-img width="123" [text]="..."</amp-img>',
			'<amp-img width="123" [text="..."]></amp-img>',
			'<amp-img width="123" [text]="..." *bad*></amp-img>',
		];
		foreach ( $malformed_html as $html ) {
			$converted = AMP_DOM_Utils::convert_amp_bind_attributes( $html );
			$this->assertNotContains( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX, $converted, "Source: $html" );
		}
	}

	/**
	 * Test handling of empty elements.
	 *
	 * @covers \AMP_DOM_Utils::get_dom()
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 */
	public function test_html5_empty_elements() {
		$original  = '<amp-video width="432" height="987">';
		$original .= '<track kind="subtitles" src="https://example.com/sampleChapters.vtt" srclang="en">';
		$original .= '<source src="foo.webm" type="video/webm">';
		$original .= '<source src="foo.ogg" type="video/ogg" />';
		$original .= '<source src="foo.mpg" type="video/mpeg"></source>';
		$original .= '<div placeholder>Placeholder</div>';
		$original .= '<span fallback>Fallback</span>';
		$original .= '</amp-video>';
		$document  = AMP_DOM_Utils::get_dom_from_content( $original );

		$video = $document->getElementsByTagName( 'amp-video' )->item( 0 );
		$this->assertNotEmpty( $video );
		$this->assertEquals( 6, $video->childNodes->length );
		$this->assertEquals( 'track', $video->childNodes->item( 0 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 1 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 2 )->nodeName );
		$this->assertEquals( 'source', $video->childNodes->item( 3 )->nodeName );
		$this->assertEquals( 'div', $video->childNodes->item( 4 )->nodeName );
		$this->assertEquals( 'span', $video->childNodes->item( 5 )->nodeName );
	}

	/**
	 * Test parsing DOM with Mustache or Mustache-like templates.
	 *
	 * @covers \AMP_DOM_Utils::get_dom()
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 */
	public function test_mustache_replacements() {

		$data = [
			'foo' => [
				'bar' => [
					'baz' => [],
				],
			],
		];

		$html = implode(
			"\n",
			[
				'<!--amp-source-stack {"block_name":"core\/columns"}-->',
				'<div class="wp-block-columns has-2-columns">',
				'<!--amp-source-stack {"block_name":"core\/quote","block_attrs":{"layout":"column-1"}}-->',
				'<blockquote class="wp-block-quote layout-column-1"><p>Quote</p><cite>Famous</cite></blockquote>',
				'<!--/amp-source-stack {"block_name":"core\/quote","block_attrs":{"layout":"column-1"}}-->',
				'<!-- wp:paragraph -->',
				'<p><a href="https://example.com/"><img src="https://example.com/img.jpg"></a></p>',
				'<!-- /wp:paragraph -->',
				'</div>',
				'<!--/amp-source-stack {"block_name":"core\/columns"}-->',
				'<!-- wp:html {} -->',
				'<script type="application/json">' . wp_json_encode( $data ) . '</script>',
				'<template type="amp-mustache">Hello {{world}}! <a href="{{href}}" title="Hello {{name}}"><img src="{{src}}"></a><blockquote cite="{{cite}}">{{quote}}</blockquote></template>',
				'<!-- /wp:html -->',
			]
		);

		$dom   = AMP_DOM_Utils::get_dom_from_content( $html );
		$xpath = new DOMXPath( $dom );

		// Ensure that JSON in scripts are left intact.
		$script = $xpath->query( '//script' )->item( 0 );
		$this->assertEquals(
			$data,
			json_decode( $script->nodeValue, true )
		);

		// Ensure that mustache var in a[href] attribute is intact.
		/**
		 * Elements
		 *
		 * @var DOMElement $template_link
		 * @var DOMElement $template_img
		 * @var DOMElement $template_blockquote
		 */
		$template_link = $xpath->query( '//template/a' )->item( 0 );
		$this->assertSame( '{{href}}', $template_link->getAttribute( 'href' ) );
		$this->assertEquals( 'Hello {{name}}', $template_link->getAttribute( 'title' ) );

		// Ensure that mustache var in img[src] attribute is intact.
		$template_img = $xpath->query( '//template/a/img' )->item( 0 );
		$this->assertEquals( '{{src}}', $template_img->getAttribute( 'src' ) );

		// Ensure that mustache var in blockquote[cite] is not changed.
		$template_blockquote = $xpath->query( '//template/blockquote' )->item( 0 );
		$this->assertEquals( '{{cite}}', $template_blockquote->getAttribute( 'cite' ) );

		$serialized_html = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		$this->assertContains( '<a href="{{href}}" title="Hello {{name}}">', $serialized_html );
		$this->assertContains( '<img src="{{src}}">', $serialized_html );
		$this->assertContains( '<blockquote cite="{{cite}}">', $serialized_html );
		$this->assertContains( '"block_attrs":{"layout":"column-1"}}', $serialized_html );
	}

	/**
	 * Test encoding.
	 *
	 * @covers \AMP_DOM_Utils::get_dom()
	 */
	public function test_get_dom_encoding() {
		$html  = '<!DOCTYPE html><html><head><title>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</title><meta charset="UTF-8"></head><body>';
		$html .= '<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>';
		$html .= '<p>&#x645;&#x631;&#x62D;&#x628;&#x627; &#x628;&#x627;&#x644;&#x639;&#x627;&#x644;&#x645;! Check out &#8216;this&#8217; and &#8220;that&#8221; and&#8212;other things.</p>';
		$html .= '<p>&#x645;&#x631;&#x62D;&#x628;&#x627; &#x628;&#x627;&#x644;&#x639;&#x627;&#x644;&#x645;! Check out &lsquo;this&rsquo; and &ldquo;that&rdquo; and&mdash;other things.</p>';
		$html .= '</body></html>';

		$document = AMP_DOM_Utils::get_dom( $html );

		$this->assertEquals( 'UTF-8', $document->encoding );
		$paragraphs = $document->getElementsByTagName( 'p' );
		$this->assertSame( 3, $paragraphs->length );
		$this->assertSame( $paragraphs->item( 0 )->textContent, $paragraphs->item( 1 )->textContent );
		$this->assertSame( $paragraphs->item( 1 )->textContent, $paragraphs->item( 2 )->textContent );
		$this->assertSame( $document->getElementsByTagName( 'title' )->item( 0 )->textContent, $paragraphs->item( 2 )->textContent );
	}

	/**
	 * Get Table Row Iterations
	 *
	 * @return array An array of arrays holding an integer representation of iterations.
	 */
	public function get_table_row_iterations() {
		return [
			[ 1 ],
			[ 10 ],
			[ 100 ],
			[ 1000 ],
			[ 10000 ],
			[ 100000 ],
		];
	}

	/**
	 * Tests attribute conversions on content with iframe srcdocs of variable lengths.
	 *
	 * @dataProvider get_table_row_iterations
	 *
	 * @param int $iterations The number of table rows to append to iframe srcdoc.
	 */
	public function test_attribute_conversion_on_long_iframe_srcdocs( $iterations ) {
		$html = '<html amp><head><meta charset="utf-8"></head><body><table>';

		for ( $i = 0; $i < $iterations; $i++ ) {
			$html .= '
				<tr>
				<td class="rank" style="width:2%;">1453</td>
				<td class="text" style="width:10%;">1947</td>
				<td class="text">Pittsburgh Ironmen</td>
				<td class="boolean" style="width:10%;text-align:center;"></td>
				<td class="number" style="width:10%;">1242</td>
				<td class="number">1192</td>
				<td class="number">1111</td>
				<td class="number highlight">1182</td>
				</tr>
			';
		}

		$html .= '</table></body></html>';

		$to_convert = sprintf(
			'<amp-iframe sandbox="allow-scripts" srcdoc="%s"> </amp-iframe>',
			htmlentities( $html )
		);

		AMP_DOM_Utils::convert_amp_bind_attributes( $to_convert );

		$this->assertSame( PREG_NO_ERROR, preg_last_error(), 'Probably failed when backtrack limit was exhausted.' );
	}

	/**
	 * Test preserving whitespace when serializing DOMDocument as HTML string.
	 *
	 * @covers \AMP_DOM_Utils::get_content_from_dom_node()
	 * @covers \AMP_DOM_Utils::get_content_from_dom()
	 * @link https://github.com/ampproject/amp-wp/issues/1304
	 */
	public function test_whitespace_preservation() {
		$body = " start <ul><li>First</li><li>Second</li></ul><style>pre::before { content:'⚡️'; }</style><script type=\"application/json\">\"⚡️\"</script><pre>\t* one\n\t* two\n\t* three</pre> end ";
		$html = "<html><head><meta charset=\"utf-8\"></head><body data-foo=\"&gt;\">$body</body></html>";

		$dom = AMP_DOM_Utils::get_dom( "<!DOCTYPE html>$html" );

		$output = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );
		$this->assertEquals( $html, $output );

		$output = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $body, $output );
	}

	/**
	 * Test that HEAD and BODY elements are always present.
	 *
	 * @covers \AMP_DOM_Utils::get_dom()
	 */
	public function test_ensuring_head_body() {
		$html = '<html><body><p>Hello</p></body></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( 0, $dom->documentElement->firstChild->childNodes->length );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$this->assertEquals( $dom->documentElement->lastChild, $dom->getElementsByTagName( 'p' )->item( 0 )->parentNode );

		$html = '<html><head><title>foo</title></head></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( $dom->documentElement->firstChild, $dom->getElementsByTagName( 'title' )->item( 0 )->parentNode );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$this->assertEquals( 0, $dom->documentElement->lastChild->childNodes->length );

		$html = '<html><head><title>foo</title></head><p>no body</p></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( $dom->documentElement->firstChild, $dom->getElementsByTagName( 'title' )->item( 0 )->parentNode );
		$p = $dom->getElementsByTagName( 'p' )->item( 0 );
		$this->assertEquals( $dom->documentElement->lastChild, $p->parentNode );
		$this->assertEquals( 'no body', $p->textContent );

		$html = 'Hello world';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( 0, $dom->documentElement->firstChild->childNodes->length );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$p = $dom->getElementsByTagName( 'p' )->item( 0 );
		$this->assertEquals( $dom->documentElement->lastChild, $p->parentNode );
		$this->assertEquals( 'Hello world', $p->textContent );
	}

	/**
	 * Get head node data.
	 *
	 * @return array Head node data.
	 */
	public function get_head_node_data() {
		$dom = new DOMDocument();
		return [
			[
				AMP_DOM_Utils::create_node( $dom, 'title', [] ),
				true,
			],
			[
				AMP_DOM_Utils::create_node(
					$dom,
					'base',
					[ 'href' => '/' ]
				),
				true,
			],
			[
				AMP_DOM_Utils::create_node(
					$dom,
					'script',
					[ 'src' => 'http://example.com/test.js' ]
				),
				true,
			],
			[
				AMP_DOM_Utils::create_node( $dom, 'style', [ 'media' => 'print' ] ),
				true,
			],
			[
				AMP_DOM_Utils::create_node( $dom, 'noscript', [] ),
				true,
			],
			[
				AMP_DOM_Utils::create_node(
					$dom,
					'link',
					[
						'rel'  => 'stylesheet',
						'href' => 'https://example.com/foo.css',
					]
				),
				true,
			],
			[
				AMP_DOM_Utils::create_node(
					$dom,
					'meta',
					[
						'name'    => 'foo',
						'content' => 'https://example.com/foo.css',
					]
				),
				true,
			],
			[
				$dom->createTextNode( " \n\t" ),
				true,
			],
			[
				$dom->createTextNode( 'no' ),
				false,
			],
			[
				$dom->createComment( 'hello world' ),
				true,
			],
			[
				$dom->createProcessingInstruction( 'test' ),
				false,
			],
			[
				$dom->createCDATASection( 'nope' ),
				false,
			],
			[
				$dom->createEntityReference( 'bad' ),
				false,
			],
			[
				$dom->createElementNS( 'http://www.w3.org/2000/svg', 'svg' ),
				false,
			],
			[
				AMP_DOM_Utils::create_node( $dom, 'span', [] ),
				false,
			],
		];
	}

	/**
	 * Test is_valid_head_node().
	 *
	 * @dataProvider get_head_node_data
	 * @covers \AMP_DOM_Utils::is_valid_head_node()
	 *
	 * @param DOMNode $node  Node.
	 * @param bool    $valid Expected valid.
	 */
	public function test_is_valid_head_node( $node, $valid ) {
		$this->assertEquals( $valid, AMP_DOM_Utils::is_valid_head_node( $node ) );
	}

	/**
	 * Test that invalid head nodes are moved to body.
	 *
	 * @covers \AMP_DOM_Utils::move_invalid_head_nodes_to_body()
	 */
	public function test_invalid_head_nodes() {

		// Text node.
		$html = '<html><head>text</head><body><span>end</span></body></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertNull( $dom->getElementsByTagName( 'head' )->item( 0 )->firstChild );
		$body_first_child = $dom->getElementsByTagName( 'body' )->item( 0 )->firstChild;
		$this->assertInstanceOf( 'DOMElement', $body_first_child );
		$this->assertEquals( 'text', $body_first_child->textContent );

		// Valid nodes.
		$html = '<html><head><!--foo--><title>a</title><base href="/"><meta name="foo" content="bar"><link rel="test" href="/"><style></style><noscript><img src="http://example.com/foo.png"></noscript><script></script></head><body></body></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertEquals( 8, $dom->getElementsByTagName( 'head' )->item( 0 )->childNodes->length );
		$this->assertNull( $dom->getElementsByTagName( 'body' )->item( 0 )->firstChild );

		// Invalid nodes.
		$html = '<html><head><?pi ?><span></span><div></div><p>hi</p><img src="https://example.com"><iframe src="/"></iframe></head><body></body></html>';
		$dom  = AMP_DOM_Utils::get_dom( $html );
		$this->assertNull( $dom->getElementsByTagName( 'head' )->item( 0 )->firstChild );
		$this->assertEquals( 6, $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes->length );
	}
}
