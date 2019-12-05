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
 * @covers Document
 */
class Test_AMP_DOM_Document extends WP_UnitTestCase {

	/**
	 * Data for Amp\AmpWP\Dom\Document test.
	 *
	 * @return array Data.
	 */
	public function data_dom_document() {
		return [
			'minimum_valid_document'                   => [
				'utf-8',
				'<!DOCTYPE html><html><head></head><body></body></html>',
				'<!DOCTYPE html><html><head></head><body></body></html>',
			],
			'valid_document_with_attributes'           => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'missing_head'                             => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head></head><body class="some-class"><p>Text</p></body></html>',
			],
			'missing_body'                             => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body><p>Text</p></body></html>',
			],
			'missing_head_and_body'                    => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en"><head></head><body><p>Text</p></body></html>',
			],
			'missing_html_and_head_and_body'           => [
				'utf-8',
				'<!DOCTYPE html><p>Text</p>',
				'<!DOCTYPE html><html><head></head><body><p>Text</p></body></html>',
			],
			'content_only'                             => [
				'utf-8',
				'<p>Text</p>',
				'<!DOCTYPE html><html><head></head><body><p>Text</p></body></html>',
			],
			'missing_doctype'                          => [
				'utf-8',
				'<html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'html_4_doctype'                           => [
				'utf-8',
				'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'lots_of_whitespace'                       => [
				'utf-8',
				" \n <!DOCTYPE \n html \n > \n <html \n amp \n lang=\"en\"   \n  >  \n   <head >   \n<meta \n   charset=\"utf-8\">  \n  </head>  \n  <body   \n class=\"some-class\"  \n >  \n  <p>  \n  Text  \n  </p>  \n\n  </body  >  \n  </html  >  \n  ",
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p> Text </p></body></html>',
			],
			'utf_8_encoding_predefined'                => [
				'utf-8',
				'<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
				'<!DOCTYPE html><html><head></head><body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'utf_8_encoding_guessed_via_charset'       => [
				'',
				'<html><head><meta charset="utf-8"></head><body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'utf_8_encoding_guessed_via_content'       => [
				'',
				'<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
				'<!DOCTYPE html><html><head></head><body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
			],
			'iso_8859_1_encoding_predefined'           => [
				'iso-8859-1',
				utf8_decode( '<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>',
			],
			'iso_8859_1_encoding_guessed_via_charset'  => [
				'',
				utf8_decode( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>',
			],
			'iso_8859_1_encoding_guessed_via_content'  => [
				'',
				utf8_decode( '<!DOCTYPE html><html><body><p>ÄÖÜ</p></body></html>' ),
				'<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>',
			],
			'raw_iso_8859_1'                           => [
				'',
				utf8_decode( 'ÄÖÜ' ),
				'<!DOCTYPE html><html><head></head><body>ÄÖÜ</body></html>',
			],
			// Make sure we correctly identify the ISO-8859 sub-charsets ("€" does not exist in ISO-8859-1).
			'iso_8859_15_encoding_predefined'          => [
				'iso-8859-1',
				mb_convert_encoding( '<!DOCTYPE html><html><head></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html><head></head><body><p>€</p></body></html>',
			],
			'iso_8859_15_encoding_guessed_via_charset' => [
				'',
				mb_convert_encoding( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15" /></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html><head></head><body><p>€</p></body></html>',
			],
			'iso_8859_15_encoding_guessed_via_content' => [
				'',
				mb_convert_encoding( '<!DOCTYPE html><html><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html><head></head><body><p>€</p></body></html>',
			],
			'raw_iso_8859_15'                          => [
				'',
				mb_convert_encoding( '€', 'ISO-8859-15', 'UTF-8' ),
				'<!DOCTYPE html><html><head></head><body>€</body></html>',
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
	 * @covers Document::loadHTML()
	 * @covers Document::saveHTML()
	 */
	public function test_dom_document( $charset, $source, $expected ) {
		$document = new Document( '', $charset );
		$document->loadHTML( $source );

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
}
