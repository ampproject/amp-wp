<?php
/**
 * Tests for AMP_Meta_Sanitizer.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Meta_Sanitizer.
 */
class Test_AMP_Meta_Sanitizer extends WP_UnitTestCase {

	/**
	 * Provide data to the test_sanitize method.
	 *
	 * @return array[] Array of arrays with test data.
	 */
	public function get_data_for_sanitize() {
		return [
			// Don't break the correct charset tag.
			[
				'<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body></body></html>',
			],
			// Add default charset tag if none is present.
			[
				'<!DOCTYPE html><html><head></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
			// Turn HTML 4 charset tag into HTML 5 charset tag.
			[
				'<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
			[
				'<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
			[
				'<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" charset="UTF-8"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
		];
	}

	/**
	 * Tests the sanitize method.
	 *
	 * @dataProvider get_data_for_sanitize
	 * @covers \AMP_Meta_Sanitizer::sanitize()
	 *
	 * @param string  $source_content   Source DOM content.
	 * @param string  $expected_content Expected content after sanitization.
	 */
	public function test_sanitize( $source_content, $expected_content ) {
		$dom       = AMP_DOM_Utils::get_dom( $source_content );
		$sanitizer = new AMP_Meta_Sanitizer( $dom );
		$sanitizer->sanitize();

		$this->assertEqualMarkup( $expected_content, $dom->saveHTML() );
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
