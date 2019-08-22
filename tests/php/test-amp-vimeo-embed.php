<?php
/**
 * Class AMP_Vimeo_Embed_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Vimeo_Embed_Test
 *
 * @covers AMP_Vimeo_Embed_Handler
 */
class AMP_Vimeo_Embed_Test extends WP_UnitTestCase {

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'                      => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'                    => [
				'https://vimeo.com/172355597' . PHP_EOL,
				'<p><amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo></p>' . PHP_EOL,
			],

			'url_unlisted'                  => [
				'https://vimeo.com/172355597/abcdef0123' . PHP_EOL,
				'<p><amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo></p>' . PHP_EOL,
			],

			'shortcode_unnamed_attr_as_url' => [
				'[vimeo https://vimeo.com/172355597]' . PHP_EOL,
				'<amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo>' . PHP_EOL,
			],

			'shortcode_named_attr_url'      => [
				'[vimeo url=https://vimeo.com/172355597]' . PHP_EOL,
				'<amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo>' . PHP_EOL,
			],

			'shortcode_named_attr_id'       => [
				'[vimeo id=172355597]' . PHP_EOL,
				'<amp-vimeo data-videoid="172355597" layout="responsive" width="600" height="338"></amp-vimeo>' . PHP_EOL,
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
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Vimeo_Embed_Handler();
		$embed->register_embed();
		$source = apply_filters( 'the_content', $source );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}
