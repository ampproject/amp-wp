<?php
/**
 * Tests for Amp\AmpWP\Dom\Document.
 *
 * @package AMP
 */

use Amp\AmpWP\Dom\Document;

/**
 * Tests for Amp\AmpWP\Dom\Document.
 *
 * @covers Amp\AmpWP\Dom\Document
 */
class Test_AMP_DOM_Document extends WP_UnitTestCase {

	/**
	 * Data for Amp\AmpWP\Dom\Document test.
	 *
	 * @return array Data.
	 */
	public function data_dom_document() {
		$head = '<head><meta charset="utf-8"></head>';

		return [
			'minimum_valid_document'                   => [
				'utf-8',
				'<!DOCTYPE html><html><head></head><body></body></html>',
				'<!DOCTYPE html><html>' . $head . '<body></body></html>',
			],
			'valid_document_with_attributes'           => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
			],
			'html_attributes'                          => [
				'utf-8',
				'<!DOCTYPE html><html lang="en-US" class="no-js">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html lang="en-US" class="no-js">' . $head . '<body></body></html>',
			],
			'head_attributes'                          => [
				'utf-8',
				'<!DOCTYPE html><html><head itemscope itemtype="http://schema.org/WebSite"></head><body></body></html>',
				'<!DOCTYPE html><html><head itemscope itemtype="http://schema.org/WebSite"><meta charset="utf-8"></head><body></body></html>',
			],
			'missing_head'                             => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
			],
			'multiple_heads'                           => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head itemscope itemtype="http://schema.org/WebSite"><meta name="first" content="something"></head><head data-something="else"><meta name="second" content="something-else"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head itemscope itemtype="http://schema.org/WebSite" data-something="else"><meta charset="utf-8"><meta name="first" content="something"><meta name="second" content="something-else"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'missing_body'                             => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body><p>Text</p></body></html>',
			],
			'multiple_bodies'                          => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="no-js"><p>Text</p></body><body data-some-attribute="to keep"><p>Yet another Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="no-js" data-some-attribute="to keep"><p>Text</p><p>Yet another Text</p></body></html>',
			],
			'missing_head_and_body'                    => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body><p>Text</p></body></html>',
			],
			'missing_html_and_head_and_body'           => [
				'utf-8',
				'<!DOCTYPE html><p>Text</p>',
				'<!DOCTYPE html><html>' . $head . '<body><p>Text</p></body></html>',
			],
			'content_only'                             => [
				'utf-8',
				'<p>Text</p>',
				'<!DOCTYPE html><html>' . $head . '<body><p>Text</p></body></html>',
			],
			'missing_doctype'                          => [
				'utf-8',
				'<html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
			],
			'html_4_doctype'                           => [
				'utf-8',
				'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
			],
			'html_with_xmlns_and_xml_lang'             => [
				'utf-8',
				'<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
			],
			'html_with_xmlns_value_that_should_remain' => [
				'utf-8',
				'<!DOCTYPE html><html xmlns="http://www.w3.org/TR/html4/">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html xmlns="http://www.w3.org/TR/html4/">' . $head . '<body></body></html>',
			],
			'html_with_lang_and_xml_lang'              => [
				'utf-8',
				'<!DOCTYPE html><html lang="es" xml:lang="fr">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
			],
			'html_with_empty_xml_lang'                 => [
				'utf-8',
				'<!DOCTYPE html><html xml:lang="">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html>' . $head . '<body></body></html>',
			],
			'html_with_empty_lang'                     => [
				'utf-8',
				'<!DOCTYPE html><html lang="" xml:lang="es">' . $head . '<body></body></html>',
				'<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
			],
			'slashes_on_closing_tags'                  => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8" /></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'lots_of_whitespace'                       => [
				'utf-8',
				" \n <!DOCTYPE \n html \n > \n <html \n amp \n lang=\"en\"   \n  >  \n   <head >   \n<meta \n   charset=\"utf-8\">  \n  </head>  \n  <body   \n class=\"some-class\"  \n >  \n  <p>  \n  Text  \n  </p>  \n\n  </body  >  \n  </html  >  \n  ",
				'<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p> Text </p></body></html>',
			],
			'utf_8_encoding_predefined'                => [
				'utf-8',
				'<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
				'<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'utf_8_encoding_guessed_via_charset'       => [
				'',
				'<html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body>',
				'<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'utf_8_encoding_guessed_via_content'       => [
				'',
				'<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
				'<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'iso_8859_1_encoding_predefined'           => [
				'iso-8859-1',
				utf8_decode( '<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
			],
			'iso_8859_1_encoding_guessed_via_charset'  => [
				'',
				utf8_decode( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
			],
			'iso_8859_1_encoding_guessed_via_content'  => [
				'',
				utf8_decode( '<!DOCTYPE html><html><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
			],
			'raw_iso_8859_1'                           => [
				'',
				utf8_decode( 'ÄÖÜ' ),
				'<!DOCTYPE html><html>' . $head . '<body>ÄÖÜ</body></html>',
			],
			// Make sure we correctly identify the ISO-8859 sub-charsets ("€" does not exist in ISO-8859-1).
			'iso_8859_15_encoding_predefined'          => [
				'iso-8859-1',
				mb_convert_encoding( '<!DOCTYPE html><html><head></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
			],
			'iso_8859_15_encoding_guessed_via_charset' => [
				'',
				mb_convert_encoding( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15" /></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
			],
			'iso_8859_15_encoding_guessed_via_content' => [
				'',
				mb_convert_encoding( '<!DOCTYPE html><html><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
			],
			'raw_iso_8859_15'                          => [
				'',
				mb_convert_encoding( '€', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html>' . $head . '<body>€</body></html>',
			],
			'comments_around_main_elements'            => [
				'utf-8',
				' <!-- comment 1 --> <!doctype html> <!-- comment 2 --> <html> <!-- comment 3 --> <head></head> <!-- comment 4 --> <body></body> <!-- comment 5 --></html>',
				' <!-- comment 1 --> <!doctype html> <!-- comment 2 --> <html> <!-- comment 3 --> ' . $head . ' <!-- comment 4 --> <body></body> <!-- comment 5 --></html>',
			],
			'ie_conditional_comments'                  => [
				'utf-8',
				'<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]--><!--[if IE 7]> <html class="lt-ie9 lt-ie8"> <![endif]--><!--[if IE 8]> <html class="lt-ie9"> <![endif]--><!--[if gt IE 8]><!--> <html class=""> <!--<![endif]--></html>',
				'<!DOCTYPE html><html class="">' . $head . '<body></body></html>',
			],
			'profile_attribute_in_head_moved_to_link'  => [
				'utf-8',
				'<!DOCTYPE html><html><head profile="https://example.com"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><link rel="profile" href="https://example.com"></head><body></body></html>',
			],
			'profile_attribute_in_head_empty_string'   => [
				'utf-8',
				'<!DOCTYPE html><html><head profile=""></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
		];
	}

	/**
	 * Tests loading and saving the content via Amp\AmpWP\Dom\Document.
	 *
	 * @param string $charset  Charset to use.
	 * @param string $source   Source content.
	 * @param string $expected Expected target content.
	 *
	 * @dataProvider data_dom_document
	 * @covers Amp\AmpWP\Dom\Document::loadHTML()
	 * @covers Amp\AmpWP\Dom\Document::saveHTML()
	 */
	public function test_dom_document( $charset, $source, $expected ) {
		$document = Document::from_html( $source, $charset );
		$this->assertEqualMarkup( $expected, $document->saveHTML() );
	}

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/\s+/', ' ', $actual );
		$expected = preg_replace( '/\s+/', ' ', $expected );
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}

	/**
	 * Test convert_amp_bind_attributes.
	 *
	 * @covers Amp\AmpWP\Dom\Document::convert_amp_bind_attributes()
	 */
	public function test_amp_bind_conversion() {
		$original  = '<amp-img width=300 height="200" data-foo="bar" selected src="/img/dog.jpg" [src]="myAnimals[currentAnimal].imageUrl"></amp-img>';
		$converted = Document::from_html( $original )->saveHTML();
		$this->assertNotEquals( $original, $converted );
		$this->assertContains( Document::AMP_BIND_DATA_ATTR_PREFIX . 'src="myAnimals[currentAnimal].imageUrl"', $converted );
		$this->assertContains( 'width="300" height="200" data-foo="bar" selected', $converted );

		// Check tag with self-closing attribute.
		$original  = '<input type="text" role="textbox" class="calc-input" id="liens" name="liens" [value]="(result1 != null) ? result1.liens : \'verifying…\'" />';
		$converted = Document::from_html( $original )->saveHTML();
		$this->assertNotEquals( $original, $converted );

		// Preserve trailing slash that is actually the attribute value.
		$original  = '<a href=/>Home</a>';
		$dom       = Document::from_html( $original );
		$converted = $dom->saveHTML( $dom->body->firstChild );
		$this->assertEquals( '<a href="/">Home</a>', $converted );

		// Test malformed.
		$malformed_html = [
			'<amp-img width="123" [text]="..."</amp-img>',
			'<amp-img width="123" [text="..."]></amp-img>',
			'<amp-img width="123" [text]="..." *bad*></amp-img>',
		];
		foreach ( $malformed_html as $html ) {
			$converted = Document::from_html( $html )->saveHTML();
			$this->assertNotContains( Document::AMP_BIND_DATA_ATTR_PREFIX, $converted, "Source: {$html}" );
		}
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

		Document::from_html( $to_convert )->saveHTML();

		$this->assertSame( PREG_NO_ERROR, preg_last_error(), 'Probably failed when backtrack limit was exhausted.' );
	}

	/**
	 * Test that HEAD and BODY elements are always present.
	 *
	 * @covers Amp\AmpWP\Dom\Document::normalize_document_structure()
	 */
	public function test_ensuring_head_body() {
		// The meta charset tag that is automatically added needs to always be taken into account.

		$html = '<html><body><p>Hello</p></body></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( 1, $dom->head->childNodes->length );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$this->assertEquals( $dom->body, $dom->getElementsByTagName( 'p' )->item( 0 )->parentNode );

		$html = '<html><head><title>foo</title></head></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( $dom->head->firstChild, $dom->getElementsByTagName( 'meta' )->item( 0 ) );
		$this->assertEquals( $dom->head->firstChild->nextSibling, $dom->getElementsByTagName( 'title' )->item( 0 ) );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$this->assertEquals( 0, $dom->body->childNodes->length );

		$html = '<html><head><title>foo</title></head><p>no body</p></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( $dom->head->firstChild, $dom->getElementsByTagName( 'meta' )->item( 0 ) );
		$this->assertEquals( $dom->head->firstChild->nextSibling, $dom->getElementsByTagName( 'title' )->item( 0 ) );
		$p = $dom->getElementsByTagName( 'p' )->item( 0 );
		$this->assertEquals( $dom->body, $p->parentNode );
		$this->assertEquals( 'no body', $p->textContent );

		$html = 'Hello world';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'head', $dom->documentElement->firstChild->nodeName );
		$this->assertEquals( 1, $dom->head->childNodes->length );
		$this->assertEquals( 'body', $dom->documentElement->lastChild->nodeName );
		$this->assertEquals( 'Hello world', $dom->body->lastChild->textContent );
	}

	/**
	 * Test that invalid head nodes are moved to body.
	 *
	 * @covers Amp\AmpWP\Dom\Document::move_invalid_head_nodes_to_body()
	 */
	public function test_invalid_head_nodes() {
		// The meta charset tag that is automatically added needs to always be taken into account.

		// Text node.
		$html = '<html><head>text</head><body><span>end</span></body></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'meta', $dom->head->firstChild->tagName );
		$this->assertNull( $dom->head->firstChild->nextSibling );
		$body_first_child = $dom->body->firstChild;
		$this->assertInstanceOf( 'DOMElement', $body_first_child );
		$this->assertEquals( 'text', $body_first_child->textContent );

		// Valid nodes.
		$html = '<html><head><!--foo--><title>a</title><base href="/"><meta name="foo" content="bar"><link rel="test" href="/"><style></style><noscript><img src="http://example.com/foo.png"></noscript><script></script></head><body></body></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 9, $dom->head->childNodes->length );
		$this->assertNull( $dom->body->firstChild );

		// Invalid nodes.
		$html = '<html><head><?pi ?><span></span><div></div><p>hi</p><img src="https://example.com"><iframe src="/"></iframe></head><body></body></html>';
		$dom  = Document::from_html( $html );
		$this->assertEquals( 'meta', $dom->head->firstChild->tagName );
		$this->assertNull( $dom->head->firstChild->nextSibling );
		$this->assertEquals( 6, $dom->body->childNodes->length );
	}

	/**
	 * Get head node data.
	 *
	 * @return array Head node data.
	 */
	public function get_head_node_data() {
		$dom = new Document();
		return [
			[
				$dom,
				AMP_DOM_Utils::create_node( $dom, 'title', [] ),
				true,
			],
			[
				$dom,
				AMP_DOM_Utils::create_node(
					$dom,
					'base',
					[ 'href' => '/' ]
				),
				true,
			],
			[
				$dom,
				AMP_DOM_Utils::create_node(
					$dom,
					'script',
					[ 'src' => 'http://example.com/test.js' ]
				),
				true,
			],
			[
				$dom,
				AMP_DOM_Utils::create_node( $dom, 'style', [ 'media' => 'print' ] ),
				true,
			],
			[
				$dom,
				AMP_DOM_Utils::create_node( $dom, 'noscript', [] ),
				true,
			],
			[
				$dom,
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
				$dom,
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
				$dom,
				$dom->createTextNode( " \n\t" ),
				true,
			],
			[
				$dom,
				$dom->createTextNode( 'no' ),
				false,
			],
			[
				$dom,
				$dom->createComment( 'hello world' ),
				true,
			],
			[
				$dom,
				$dom->createProcessingInstruction( 'test' ),
				false,
			],
			[
				$dom,
				$dom->createCDATASection( 'nope' ),
				false,
			],
			[
				$dom,
				$dom->createEntityReference( 'bad' ),
				false,
			],
			[
				$dom,
				$dom->createElementNS( 'http://www.w3.org/2000/svg', 'svg' ),
				false,
			],
			[
				$dom,
				AMP_DOM_Utils::create_node( $dom, 'span', [] ),
				false,
			],
		];
	}

	/**
	 * Test is_valid_head_node().
	 *
	 * @dataProvider get_head_node_data
	 * @covers       Amp\AmpWP\Dom\Document::is_valid_head_node()
	 *
	 * @param Document $dom   DOM document to use.
	 * @param DOMNode  $node  Node.
	 * @param bool     $valid Expected valid.
	 */
	public function test_is_valid_head_node( $dom, $node, $valid ) {
		$this->assertEquals( $valid, $dom->is_valid_head_node( $node ) );
	}
}
