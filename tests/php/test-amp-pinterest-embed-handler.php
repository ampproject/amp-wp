<?php

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

class AMP_Pinterest_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering;

	public function get_conversion_data() {
		return [
			'no_embed'                           => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],
			'simple_url_https'                   => [
				'https://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="https://www.pinterest.com/pin/606156431067611861/"></amp-pinterest></p>' . PHP_EOL,
			],
			'simple_url_http'                    => [
				'http://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="http://www.pinterest.com/pin/606156431067611861/"></amp-pinterest></p>' . PHP_EOL,
			],
			'simple_url_without_subdomain'       => [
				'https://pinterest.com/pin/606156431067611861/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="https://pinterest.com/pin/606156431067611861/"></amp-pinterest></p>' . PHP_EOL,
			],
			'simple_url_with_regional_tld'       => [
				'https://pinterest.de/pin/8092474319950168/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="https://pinterest.de/pin/8092474319950168/"></amp-pinterest></p>' . PHP_EOL,
			],
			'simple_url_with_regional_subdomain' => [
				'https://de.pinterest.com/pin/8092474319950168' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="https://de.pinterest.com/pin/8092474319950168"></amp-pinterest></p>' . PHP_EOL,
			],
		];
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Pinterest_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				[ 'amp-pinterest' => true ],
			],
		];
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Pinterest_Embed_Handler();
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
