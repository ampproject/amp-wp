<?php
/**
 * Tests for AMP stories sanitization.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Story_Sanitizer_Test
 *
 * @group amp-comments
 * @group amp-form
 */
class AMP_Story_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->go_to( '/current-page/' );
	}

	/**
	 * Data strings for testing converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'story_without_cta' => array(
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer></amp-story-page>',
				null, // Same.
			),
			'story_with_cta_on_first_page' => array(
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer><amp-story-cta-layer><a href="">Foo</a></amp-story-cta-layer></amp-story-page>',
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer></amp-story-page>',
			),
			'story_with_cta_on_second_page' => array(
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer></amp-story-page><amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer><amp-story-cta-layer><a href="">Foo</a></amp-story-cta-layer></amp-story-page>',
				null, // Same.
			),
			'story_with_multiple_cta_on_second_page' => array(
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer></amp-story-page><amp-story-page><amp-story-grid-layer></amp-story-grid-layer><amp-story-cta-layer><a href="">Foo</a></amp-story-cta-layer><amp-story-cta-layer><a href="">Foo</a></amp-story-cta-layer></amp-story-page>',
				'<amp-story-page><amp-story-grid-layer><p>Lorem Ipsum Demet Delorit.</p></amp-story-grid-layer></amp-story-page><amp-story-page><amp-story-grid-layer></amp-story-grid-layer><amp-story-cta-layer><a href="">Foo</a></amp-story-cta-layer></amp-story-page>',
			),
		);
	}

	/**
	 * Test html conversion.
	 *
	 * @param string      $source   The source HTML.
	 * @param string|null $expected The expected HTML after conversion. Null means same as $source.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null ) {
		if ( is_null( $expected ) ) {
			$expected = $source;
		}
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );

		$sanitizer = new AMP_Story_Sanitizer( $dom );
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );

		$this->assertEquals( $expected, $content );
	}
}
