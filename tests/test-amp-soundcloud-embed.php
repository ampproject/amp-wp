<?php

class AMP_SoundCloud_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL
			),

			'url_simple' => array(
				'https://api.soundcloud.com/tracks/89299804' . PHP_EOL,
				'<p><amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud></p>' . PHP_EOL
			),

			'shortcode_unnamed_attr_as_url' => array(
				'[soundcloud https://api.soundcloud.com/tracks/89299804]' . PHP_EOL,
				'<amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud>' . PHP_EOL
			),

			'shortcode_named_attr_url' => array(
				'[soundcloud url=https://api.soundcloud.com/tracks/89299804]' . PHP_EOL,
				'<amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud>' . PHP_EOL
			),

			'shortcode_named_attr_url' => array(
				'[soundcloud id=89299804]' . PHP_EOL,
				'<amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud>' . PHP_EOL
			),

		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_SoundCloud_Embed_Handler();
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
				'https://api.soundcloud.com/tracks/89299804' . PHP_EOL,
				array( 'amp-soundcloud' => 'https://cdn.ampproject.org/v0/amp-soundcloud-0.1.js' )
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_SoundCloud_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
