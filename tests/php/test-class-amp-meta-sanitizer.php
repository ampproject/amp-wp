<?php
/**
 * Tests for AMP_Meta_Sanitizer.
 *
 * @package AMP
 */

use Amp\Dom\Document;

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
		$script1 = 'document.body.textContent += "First!";';
		$script2 = 'document.body.textContent += "Second!";';
		$script3 = 'document.body.textContent += "Third!";';
		$script4 = 'document.body.textContent += "Fourth! (And forbidden because no amp-script-src meta in head.)";';

		$script1_hash = amp_generate_script_hash( $script1 );
		$script2_hash = amp_generate_script_hash( $script2 );
		$script3_hash = amp_generate_script_hash( $script3 );
		$script4_hash = amp_generate_script_hash( $script4 );

		return [
			// Don't break the correct charset tag.
			[
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
			],

			// Don't break the correct viewport tag.
			[
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1"></head><body></body></html>',
			],

			// Move charset and viewport tags from body to head.
			[
				'<!DOCTYPE html><html><head></head><body><meta charset="utf-8"><meta name="viewport" content="width=device-width"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
			],

			// Add default charset tag if none is present.
			[
				'<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
			],

			// Add default viewport tag if none is present.
			[
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
			],

			// Make sure charset is the first meta tag.
			[
				'<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width"><meta charset="utf-8"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head><body></body></html>',
			],

			// Concatenate and reposition script hashes.
			[
				'<!DOCTYPE html><html><head><meta name="amp-script-src" content="' . esc_attr( $script1_hash ) . '"><meta charset="utf-8"><meta name="amp-script-src" content="' . esc_attr( $script2_hash ) . '"><meta name="viewport" content="width=device-width"><meta name="amp-script-src" content="' . esc_attr( $script3_hash ) . '"></head><body><meta name="amp-script-src" content="' . esc_attr( $script4_hash ) . '"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta name="amp-script-src" content="' . esc_attr( $script1_hash ) . ' ' . esc_attr( $script2_hash ) . ' ' . esc_attr( $script3_hash ) . ' ' . esc_attr( $script4_hash ) . '"></head><body></body></html>',
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
		$dom       = Document::fromHtml( $source_content );
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
