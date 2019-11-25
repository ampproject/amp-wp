<?php
/**
 * Tests for AMP_DOM_Document.
 *
 * @package AMP
 */

/**
 * Tests for AMP_DOM_Document.
 *
 * @covers AMP_DOM_Document
 */
class Test_AMP_DOM_Document extends WP_UnitTestCase {

	/**
	 * Data for AMP_DOM_Document test.
	 *
	 * @return array Data.
	 */
	public function data_dom_document() {
		return [
			'minimum_valid_document'         => [
				'utf-8',
				'<!DOCTYPE html><html><head></head><body></body></html>',
				'<!DOCTYPE html><html><head></head><body></body></html>',
			],
			'valid_document_with_attributes' => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'missing_head'                   => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head></head><body class="some-class"><p>Text</p></body></html>',
			],
			'missing_body'                   => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body><p>Text</p></body></html>',
			],
			'missing_head_and_body'          => [
				'utf-8',
				'<!DOCTYPE html><html amp lang="en"><p>Text</p></html>',
				'<!DOCTYPE html><html amp lang="en"><head></head><body><p>Text</p></body></html>',
			],
			// @todo This one is still broken.
			/*'content_only' => [
				'utf-8',
				'<p>Text</p>',
				'<!DOCTYPE html><html><head></head><body><p>Text</p></body></html>',
			],*/
			'missing_doctype'                => [
				'utf-8',
				'<html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
			'html-4_doctype'                 => [
				'utf-8',
				'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
				'<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
			],
		];
	}

	/**
	 * Tests loading and saving the content via AMP_DOM_Document.
	 *
	 * @param string $charset  Charset to use.
	 * @param string $source   Source content.
	 * @param string $expected Expected target content.
	 *
	 * @dataProvider data_dom_document
	 * @covers AMP_DOM_Document::loadHTML()
	 * @covers AMP_DOM_Document::saveHTML()
	 */
	public function test_dom_document( $charset, $source, $expected ) {
		$document = new AMP_DOM_Document( '', $charset );
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
