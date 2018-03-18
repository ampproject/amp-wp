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
	public function test_utf8_content() {
		$source = '<p>Iñtërnâtiônàlizætiøn</p>';
		$expected = '<p>Iñtërnâtiônàlizætiøn</p>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test_add_attributes_to_node__no_attributes() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node = $dom->createElement( 'b' );
		AMP_DOM_Utils::add_attributes_to_node( $node, array() );
		$this->assertFalse( $node->hasAttributes() );
	}

	public function test_add_attributes_to_node__attribute_without_value() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node = $dom->createElement( 'div' );
		$attributes = array( 'placeholder' => '' );
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	public function test_add_attributes_to_node__attribute_with_value() {
		$dom = AMP_DOM_Utils::get_dom_from_content( '<p>Hello World</p>' );
		$node = $dom->createElement( 'div' );
		$attributes = array( 'class' => 'myClass', 'id' => 'myId' );
		AMP_DOM_Utils::add_attributes_to_node( $node, $attributes );

		$this->assertTrue( $node->hasAttributes() );
		$this->check_node_has_attributes( $node, $attributes );
	}

	protected function check_node_has_attributes( $node, $attributes ) {
		$this->assertEquals( count( $attributes ), $node->attributes->length );
		foreach ( $node->attributes as $attr ) {
			$name = $attr->nodeName;
			$value = $attr->nodeValue;

			$this->assertTrue( array_key_exists( $name, $attributes ), sprintf( 'Attribute "%s" not found.', $name ) );
			$this->assertEquals( $attributes[ $name ], $value, sprintf( 'Attribute "%s" does not have expected value.', $name ) );
		}
	}

	public function test__is_node_empty__yes() {
		$source = '<p></p>';
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$node = $dom->getElementsByTagName( 'p' )->item( 0 );

		$this->assertTrue( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	public function test__is_node_empty__no__has_text() {
		$source = '<p>Hello</p>';
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$node = $dom->getElementsByTagName( 'p' )->item( 0 );

		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	public function test__is_node_empty__no__has_child() {
		$source = '<p><b></b></p>';
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$node = $dom->getElementsByTagName( 'p' )->item( 0 );

		$this->assertFalse( AMP_DOM_Utils::is_node_empty( $node ) );
	}

	public function test__get_content_from_dom__br_no_closing_tag() {
		$source   = '<br>';
		$expected = '<br>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test convert_amp_bind_attributes.
	 *
	 * @covers \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @covers \AMP_DOM_Utils::restore_amp_bind_attributes()
	 * @covers \AMP_DOM_Utils::get_amp_bind_placeholder_prefix()
	 */
	public function test_amp_bind_conversion() {
		$original  = '<amp-img width=300 height="200" data-foo="bar" selected src="/img/dog.jpg" [src]="myAnimals[currentAnimal].imageUrl"></amp-img>';
		$converted = AMP_DOM_Utils::convert_amp_bind_attributes( $original );
		$this->assertNotEquals( $converted, $original );
		$this->assertContains( AMP_DOM_Utils::get_amp_bind_placeholder_prefix() . 'src="myAnimals[currentAnimal].imageUrl"', $converted );
		$this->assertContains( 'width=300 height="200" data-foo="bar" selected', $converted );
		$restored = AMP_DOM_Utils::restore_amp_bind_attributes( $converted );
		$this->assertEquals( $original, $restored );

		// Test malformed.
		$malformed_html = array(
			'<amp-img width="123" [text]="..."</amp-img>',
			'<amp-img width="123" [text] data-test="asd"></amp-img>',
			'<amp-img width="123" [text]="..." *bad*></amp-img>',
		);
		foreach ( $malformed_html as $html ) {
			$converted = AMP_DOM_Utils::convert_amp_bind_attributes( $html );
			$this->assertNotContains( AMP_DOM_Utils::get_amp_bind_placeholder_prefix(), $converted );
		}
	}

	/**
	 * Test handling of empty elements.
	 *
	 * @covers \AMP_DOM_Utils::get_dom()
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
	 * Get Table Row Iterations
	 *
	 * @return array An array of arrays holding an integer representation of iterations.
	 */
	public function get_table_row_iterations() {
		return array(
			array( 1 ),
			array( 10 ),
			array( 100 ),
			array( 1000 ),
		);
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
}
