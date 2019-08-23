<?php
/**
 * Class AMP_Picture_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Picture_Sanitizer_Test
 *
 * @covers AMP_Picture_Sanitizer
 */
class AMP_Picture_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Data for test_converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'picture_element_without_source'                          => [
				'<picture><img alt="some image" src="https://placehold.it/20x200" /></picture>',
				'<amp-img alt="some image" src="https://placehold.it/20x200"></amp-img>',
			],
			'picture_element_with_single_source_type_and_single_srcset'    => [
				'<picture><source media="(min-width: 320px)" srcset="https://placehold.it/40x400"><img alt="some image" src="https://placehold.it/20x200" /></picture>',
				'<amp-img alt="some image" src="https://placehold.it/20x200" srcset="https://placehold.it/40x400 320w" layout="responsive"></amp-img>',
			],
			'picture_element_with_single_source_type_and_multiple_srcsets' => [
				'<picture><source media="(min-width: 768px)" srcset="https://placehold.it/60x600"><source media="(min-width: 320px)" srcset="https://placehold.it/40x400"><img alt="some image" src="https://placehold.it/20x200" /></picture>',
				'<amp-img alt="some image" src="https://placehold.it/20x200" srcset="https://placehold.it/60x600 768w, https://placehold.it/40x400 320w" layout="responsive"></amp-img>',
			],
		];
	}

	/**
	 * Test converter.
	 *
	 * @param string   $source               Source.
	 * @param string   $expected             Expected.
	 * @param array    $args                 Args.
	 * @param string[] $expected_error_codes Expected error codes.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null, $args = [], $expected_error_codes = [] ) {
		if ( ! $expected ) {
			$expected = $source;
		}

		$error_codes = [];

		$args = array_merge(
			[
				'use_document_element'      => true,
				'validation_error_callback' => static function( $error ) use ( &$error_codes ) {
					$error_codes[] = $error['code'];
				},
			],
			$args
		);

		$dom           = AMP_DOM_Utils::get_dom_from_content( $source );
		$picture_count = $dom->getElementsByTagName( 'picture' )->length;

		$sanitizer = new AMP_Picture_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

//		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, $args );
//		$sanitizer->sanitize();

		$this->assertEqualSets( $error_codes, $expected_error_codes );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );

		$xpath = new DOMXPath( $dom );
		$this->assertEquals( $picture_count ? 1 : 0, $xpath->query( '/html/head/meta[ @name = "amp-experiments-opt-in" ][ @content = "amp-img-auto-sizes" ]' )->length );
	}
}
