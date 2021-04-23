<?php
/**
 * Class AMP_Object_Sanitizer_Test.
 *
 * @package AMP
 */

/**
 * Class AMP_Audio_Converter_Test
 *
 * @coversDefaultClass \AMP_Object_Sanitizer
 */
class AMP_Object_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * @covers ::add_buffering_hooks()
	 */
	public function test_add_buffering_hooks() {
		AMP_Object_Sanitizer::add_buffering_hooks();

		do_blocks(
			'
			<!-- wp:file {"id":42,"href":"https://example.com/content/uploads/2021/04/example.pdf","displayPreview":true} -->
				<div class="wp-block-file">
					<object class="wp-block-file__embed" data="https://example.com/content/uploads/2021/04/example.pdf" type="application/pdf" style="width:100%;height:600px" aria-label="Embed of example."></object>
					<a href="https://example.com/content/uploads/2021/04/example.pdf">example</a>
					<a href="https://example.com/content/uploads/2021/04/example.pdf" class="wp-block-file__button" download>Download</a>
				</div>
			<!-- /wp:file -->
		' 
		);

		ob_start();
		do_action( 'wp_print_footer_scripts' );
		ob_end_clean();

		$this->assertFalse( wp_script_is( 'wp-block-library-file' ) );
	}

	/**
	 * Data for converter test.
	 *
	 * @return array Data.
	 */
	public function get_data() {
		return [
			'no object element'                        => [
				'<p>Hello World.</p>',
				'<p>Hello World.</p>',
			],

			'object element with no type attribute'    => [
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf"></object>',
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf"></object>',
			],

			'object element with non-PDF content type' => [
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf" type="application/example"></object>',
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf" type="application/example"></object>',
			],

			'object element with PDF content type'     => [
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf" type="application/pdf"></object>',
				'<amp-google-document-embed layout="fixed-height" height="600" src="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf"></amp-google-document-embed>',
			],

			'object element with PDF content type and height' => [
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf" type="application/pdf" style="height: 1000px"></object>',
				'<amp-google-document-embed layout="fixed-height" height="1000px" src="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf"></amp-google-document-embed>',
			],
		];
	}

	/**
	 * @covers ::sanitize()
	 * @covers ::sanitize_pdf()
	 * @dataProvider get_data()
	 */
	public function test_sanitize( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Object_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function get_scripts_data() {
		return [
			'not converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'<object data="https://planetpdf.com/planetpdf/pdfs/warnock_camelot.pdf" type="application/pdf"></object>',
				[ 'amp-google-document-embed' => true ],
			],
		];
	}

	/**
	 * @covers ::get_scripts()
	 * @dataProvider get_scripts_data
	 */
	public function test_get_scripts( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Object_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}
