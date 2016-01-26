<?php

class AMP_Instagram_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL
			),
			'simple_url' => array(
				'https://instagram.com/p/7-l0z_p4A4/' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600"></amp-instagram></p>' . PHP_EOL
			),
			'short_url' => array(
				'https://instagr.am/p/7-l0z_p4A4' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600"></amp-instagram></p>' . PHP_EOL
			)
		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Instagram_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	public function get_scripts_data() {
		return array(
			'not_converted' => array(
				'<p>Hello World.</p>',
				array()
			),
			'converted' => array(
				'https://instagram.com/p/7-l0z_p4A4/' . PHP_EOL,
				array( 'amp-instagram' => 'https://cdn.ampproject.org/v0/amp-instagram-0.1.js' )
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Instagram_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
