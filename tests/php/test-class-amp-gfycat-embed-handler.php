<?php
/**
 * Test Gfycat embed.
 *
 * @package AMP.
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_Gfycat_Embed_Handler_Test
 *
 * @covers AMP_Gfycat_Embed_Handler
 */
class AMP_Gfycat_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering {
		setUp as public prevent_block_pre_render;
	}

	/**
	 * Set up.
	 */
	public function setUp() {
		$this->prevent_block_pre_render();

		// Mock the HTTP request.
		add_filter(
			'pre_oembed_result',
			static function( $pre, $url ) {
				if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
					return $pre;
				}

				if ( false === strpos( $url, 'tautwhoppingcougar' ) ) {
					return $pre;
				}
				return '<iframe src=\'https://gfycat.com/ifr/tautwhoppingcougar\' frameborder=\'0\' scrolling=\'no\' width=\'100\' height=\'100\'  allowfullscreen></iframe>';
			},
			10,
			2
		);
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'        => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'      => [
				'https://gfycat.com/tautwhoppingcougar' . PHP_EOL,
				'<p><amp-gfycat width="100" height="100" data-gfyid="tautwhoppingcougar"></amp-gfycat></p>' . PHP_EOL,
			],

			'url_with_detail' => [
				'https://gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				'<p><amp-gfycat width="100" height="100" data-gfyid="tautwhoppingcougar"></amp-gfycat></p>' . PHP_EOL,
			],

			'url_with_params' => [
				'https://gfycat.com/gifs/detail/tautwhoppingcougar?foo=bar' . PHP_EOL,
				'<p><amp-gfycat width="100" height="100" data-gfyid="tautwhoppingcougar"></amp-gfycat></p>' . PHP_EOL,
			],

		];
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
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				[ 'amp-gfycat' => true ],
			],
		];
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

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}
