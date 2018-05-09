<?php
/**
 * Test Gfycat embed.
 *
 * @package AMP.
 */

/**
 * Class AMP_Gfycat_Embed_Test
 */
class AMP_Gfycat_Embed_Test extends WP_UnitTestCase {

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return array(
			'no_embed'   => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),

			'url_simple' => array(
				'https://gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				'<p><amp-gfycat width="500" height="750" data-gfyid="tautwhoppingcougar" layout="responsive"></amp-gfycat></p>' . PHP_EOL,
			),

		);
	}

	/**
	 * Test conversion.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Gfycat_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array
	 */
	public function get_scripts_data() {
		return array(
			'not_converted' => array(
				'<p>Hello World.</p>',
				array(),
			),
			'converted'     => array(
				'https://www.gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				array( 'amp-gfycat' => true ),
			),
		);
	}

	/**
	 * Test scripts.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Gfycat_Embed_Handler();
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
