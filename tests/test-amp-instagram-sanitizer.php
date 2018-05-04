<?php
/**
 * Class AMP_Instagram_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Instagram_Sanitizer_Test
 *
 * Tests that <blockquote class="instagram-media"> is transform to to <amp-instagram>
 */
class AMP_Instagram_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Testing Data
	 */
	public function get_data() {
		return array(
			'no_embed_blockquote'                => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'embed_blockquote_without_instagram' => array(
				'<blockquote>lorem ipsum</blockquote>',
				'<blockquote>lorem ipsum</blockquote>',
			),

			'embed_blockquote_with_instagram_no_embed_script' => array(
				'<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote>',
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600"></amp-instagram>',
			),
		);
	}

	/**
	 * Tests that when all scenarios for the transformation
	 *
	 * @param String $source initial markup.
	 * @param String $expected expected markup.
	 */
	public function test_converter( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Instagram_Sanitizer( $dom );

		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Tests that when a blockquote with no data-instrgrm-permalink found the amp-instragram script it's not included
	 */
	public function test_no_embed_no_instagram_script() {
		$source    = '<blockquote>lorem ipsum</blockquote>';
		$expected  = array();
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Instagram_Sanitizer( $dom );

		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Tests that when a blockquote with data-instrgrm-permalink found the amp-instragram script it's included
	 */
	public function test_when_blockquote_instagram_script() {
		$source = '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote>';

		$expected = array( 'amp-instagram' => 'https://cdn.ampproject.org/v0/amp-instagram-0.1.js' );

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Instagram_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}
}
