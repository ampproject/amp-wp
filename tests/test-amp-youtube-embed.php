<?php

class AMP_YouTube_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),

			'url_simple' => array(
				'https://www.youtube.com/watch?v=kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube></p>' . PHP_EOL,
			),

			'url_short' => array(
				'https://youtu.be/kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube></p>' . PHP_EOL,
			),

			'url_with_querystring' => array(
				'http://www.youtube.com/watch?v=kfVsfOSbJY0&hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube></p>' . PHP_EOL,
			),

			// Several reports of invalid URLs that have multiple `?` in the URL.
			'url_with_querystring_and_extra_?' => array(
				'http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube></p>' . PHP_EOL,
			),

			'shortcode_unnamed_attr_as_url' => array(
				'[youtube http://www.youtube.com/watch?v=kfVsfOSbJY0]' . PHP_EOL,
				'<amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube>' . PHP_EOL,
			),

			'url_playlist' => array(
				'https://www.youtube.com/playlist?list=PLtn57NsUdLz7Phy4bzX0f3d5_eAQQ7FCA' . PHP_EOL,
				'<p><iframe width="660" height="371" src="https://www.youtube.com/embed/videoseries?list=PLtn57NsUdLz7Phy4bzX0f3d5_eAQQ7FCA" frameborder="0" allowfullscreen></iframe></p>' . PHP_EOL,
			),

			'url_playlist_broken' => array(
				'https://www.youtube.com/playlist?asdf=PLtn57NsUdLz7Phy4bzX0f3d5_eAQQ7FCA' . PHP_EOL,
				'<p>https://www.youtube.com/playlist?asdf=PLtn57NsUdLz7Phy4bzX0f3d5_eAQQ7FCA</p>' . PHP_EOL,
			),
		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_YouTube_Embed_Handler();
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
				'https://www.youtube.com/watch?v=kfVsfOSbJY0' . PHP_EOL,
				array( 'amp-youtube' => 'https://cdn.ampproject.org/v0/amp-youtube-0.1.js' ),
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_YouTube_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
