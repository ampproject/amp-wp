<?php
/**
 * Class AMP_WordPress_Embed_Handler_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_WordPress_Embed_Handler_Test
 *
 * @covers AMP_WordPress_Embed_Handler
 */
class AMP_WordPress_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering;

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'   => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],
			'simple_url' => [
				'https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/' . PHP_EOL,
				'<p><amp-wordpress-embed height="200" title="&#8220;Proposal for a Performance team&#8221; &#8212; Make WordPress Core" data-url="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/"></p>' . PHP_EOL .
				'<blockquote class="wp-embedded-content" data-secret="%s" placeholder><p><a href="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/">Proposal for a Performance team</a></p></blockquote>' . PHP_EOL .
				'<p><button overflow type="button">Expand</button></amp-wordpress-embed></p>' . PHP_EOL,
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
		$embed = new AMP_WordPress_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		// Extract "data-secret" attr value.
		preg_match( '/data-secret="(?P<secret>[^"]*)"/', $filtered_content, $matches );
		if ( isset( $matches['secret'] ) ) {
			$expected = sprintf( $expected, $matches['secret'] );
		}

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
				'https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/' . PHP_EOL,
				[ 'amp-wordpress-embed' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 * @covers AMP_WordPress_Embed_Handler::get_scripts()
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_WordPress_Embed_Handler();
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
