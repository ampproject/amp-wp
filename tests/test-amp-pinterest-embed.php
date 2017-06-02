<?php

class AMP_Pinterest_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),
			'simple_url_https' => array(
				'https://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="https://www.pinterest.com/pin/606156431067611861/"></amp-pinterest></p>' . PHP_EOL,
			),
			'simple_url_http' => array(
				'http://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				'<p><amp-pinterest width="450" height="750" data-do="embedPin" data-url="http://www.pinterest.com/pin/606156431067611861/"></amp-pinterest></p>' . PHP_EOL,
			)
		);
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
		return array(
			'not_converted' => array(
				'<p>Hello World.</p>',
				array(),
			),
			'converted' => array(
				'https://www.pinterest.com/pin/606156431067611861/' . PHP_EOL,
				array( 'amp-pinterest' => 'https://cdn.ampproject.org/v0/amp-pinterest-0.1.js' ),
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Pinterest_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
