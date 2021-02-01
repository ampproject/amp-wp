<?php
/**
 * Class AMP_Vimeo_Embed_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;

/**
 * Class AMP_Vimeo_Embed_Handler_Test
 *
 * @covers AMP_Vimeo_Embed_Handler
 */
class AMP_Vimeo_Embed_Handler_Test extends WP_UnitTestCase {

	use WithoutBlockPreRendering;

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'     => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'   => [
				'https://vimeo.com/172355597' . PHP_EOL,
				'<p><amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo></p>' . PHP_EOL,
			],

			'url_unlisted' => [
				'https://vimeo.com/172355597/abcdef0123' . PHP_EOL,
				'<p><amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo></p>' . PHP_EOL,
			],

			'url_player'   => [
				'https://player.vimeo.com/video/172355597' . PHP_EOL,
				'<p><amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo></p>' . PHP_EOL,
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @dataProvider get_conversion_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Vimeo_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array Scripts data.
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://vimeo.com/172355597' . PHP_EOL,
				[ 'amp-vimeo' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 * @covers AMP_Vimeo_Embed_Handler::get_scripts()
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Vimeo_Embed_Handler();
		$embed->register_embed();
		$source = apply_filters( 'the_content', $source );

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Gets the test data for test_video_override().
	 *
	 * @return array The test data.
	 */
	public function get_video_override_data() {
		return [
			'no_src_empty_html'    => [
				'',
				[ 'foo' => 'baz' ],
				null,
			],
			'no_src_with_html'     => [
				'Initial HTML here',
				[ 'foo' => 'baz' ],
				null,
			],
			'non_vimeo_src'        => [
				'Initial HTML here',
				[ 'src' => 'https://youtube.com/1234567' ],
				null,
			],
			'non_numeric_video_id' => [
				'Initial HTML here',
				[ 'src' => 'https://vimeo.com/abcdefg' ],
				'',
			],
			'valid_video_id'       => [
				'Initial HTML here',
				[ 'src' => 'https://vimeo.com/1234567' ],
				'<amp-vimeo data-videoid="1234567" layout="responsive" width="600" height="338"></amp-vimeo>',
			],
			'http_url'             => [
				'Initial HTML here',
				[ 'src' => 'https://vimeo.com/1234567' ],
				'<amp-vimeo data-videoid="1234567" layout="responsive" width="600" height="338"></amp-vimeo>',
			],
		];
	}

	/**
	 * Test video_override().
	 *
	 * @dataProvider get_video_override_data
	 * @covers AMP_Vimeo_Embed_Handler::video_override()
	 *
	 * @param string $html     The initial HTML.
	 * @param array  $attr     The attributes of the shortcode.
	 * @param string $expected The expected return value.
	 */
	public function test_video_override( $html, $attr, $expected ) {
		if ( null === $expected ) {
			$expected = $html;
		}

		$embed = new AMP_Vimeo_Embed_Handler();
		$this->assertEquals( $expected, $embed->video_override( $html, $attr ) );
	}
}
