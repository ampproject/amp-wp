<?php

use AmpProject\AmpWP\Tests\Helpers\StubSanitizer;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Content_Sanitizer class.
 *
 * @package AMP
 */

/**
 * Class Test_AMP_Content_Sanitizer
 *
 * @covers AMP_Content_Sanitizer
 */
class Test_AMP_Content_Sanitizer extends TestCase {

	/**
	 * Test sanitize_document.
	 *
	 * @covers \AMP_Content_Sanitizer::sanitize_document()
	 */
	public function test_sanitize_document() {
		$source_html = '<video style="outline: solid 1px red;" src="https://example.com/foo.mp4" width="100" height="200"></video>';
		$document    = AMP_DOM_Utils::get_dom_from_content( $source_html );

		$sanitizers       = amp_get_content_sanitizers();
		$sanitize_results = AMP_Content_Sanitizer::sanitize_document(
			$document,
			$sanitizers,
			[]
		);

		$this->assertEqualSets( [ 'scripts', 'styles', 'stylesheets', 'sanitizers' ], array_keys( $sanitize_results ) );
		$this->assertEquals(
			[ 'amp-video' => true ],
			$sanitize_results['scripts']
		);
		$this->assertEquals(
			[ ':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-9f6e771{outline:solid 1px red}' ],
			$sanitize_results['stylesheets']
		);
		$this->assertEmpty( $sanitize_results['styles'] );
		$this->assertIsArray( $sanitize_results['sanitizers'] );
		$this->assertEqualSets( array_keys( $sanitizers ), array_keys( $sanitize_results['sanitizers'] ) );
		$this->assertEquals( 1, $document->getElementsByTagName( 'amp-video' )->length );
		foreach ( array_keys( $sanitizers ) as $sanitizer_class ) {
			$this->assertInstanceOf( $sanitizer_class, $sanitize_results['sanitizers'][ $sanitizer_class ] );
		}
	}

	/**
	 * Test sanitize no-op.
	 *
	 * @covers \AMP_Content_Sanitizer::sanitize()
	 */
	public function test_sanitize_noop() {
		$source_html     = '<b>Hello</b>';
		$expected_return = [ '<b>Hello</b>', [], [] ];

		$actual_return = AMP_Content_Sanitizer::sanitize( $source_html, [ StubSanitizer::class => [] ] );

		$this->assertEquals( $expected_return, $actual_return );
	}

	/**
	 * Test sanitize with all sanitizers.
	 *
	 * @covers \AMP_Content_Sanitizer::sanitize()
	 */
	public function test_sanitize_all() {
		$source_html     = '<video style="outline: solid 1px red;" src="https://example.com/foo.mp4" width="100" height="200"></video>';
		$expected_return = [
			'<amp-video src="https://example.com/foo.mp4" width="100" height="200" layout="responsive" data-amp-original-style="outline: solid 1px red;" class="amp-wp-9f6e771"><a href="https://example.com/foo.mp4" fallback="">https://example.com/foo.mp4</a><noscript><video src="https://example.com/foo.mp4" width="100" height="200"></video></noscript></amp-video>',
			[ 'amp-video' => true ],
			[ ':root:not(#_):not(#_):not(#_):not(#_):not(#_) .amp-wp-9f6e771{outline:solid 1px red}' ],
		];

		$actual_return = AMP_Content_Sanitizer::sanitize(
			$source_html,
			amp_get_content_sanitizers(),
			[ 'return_styles' => false ]
		);

		$this->assertEquals( $expected_return, $actual_return );
	}
}
