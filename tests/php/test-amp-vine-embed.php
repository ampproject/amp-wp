<?php

class AMP_Vine_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed'   => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),
			'simple_url' => array(
				'https://vine.co/v/MdKjXez002d' . PHP_EOL,
				'<p><amp-vine data-vineid="MdKjXez002d" layout="responsive" width="400" height="400"></amp-vine></p>' . PHP_EOL,
			),
		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Vine_Embed_Handler();
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
			'converted'     => array(
				'https://vine.co/v/MdKjXez002d' . PHP_EOL,
				array( 'amp-vine' => true ),
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Vine_Embed_Handler();
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
