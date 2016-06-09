<?php

class AMP_Twitter_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL
			),
			'url_simple' => array(
				'https://twitter.com/wordpress/status/118252236836061184' . PHP_EOL,
				'<p><amp-twitter data-tweetid="118252236836061184" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL
			),
			'url_with_big_tweet_id' => array(
				'https://twitter.com/wordpress/status/705219971425574912' . PHP_EOL,
				'<p><amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL
			),

			'shortcode_without_id' => array(
				'[tweet]' . PHP_EOL,
				'' . PHP_EOL,
			),
			'shortcode_simple' => array(
				'[tweet 118252236836061184]' . PHP_EOL,
				'<amp-twitter data-tweetid="118252236836061184" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL
			),
			'shortcode_with_tweet_attribute' => array(
				'[tweet tweet=118252236836061184]' . PHP_EOL,
				'<amp-twitter data-tweetid="118252236836061184" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL
			),
			'shortcode_with_big_tweet_id' => array(
				'[tweet 705219971425574912]' . PHP_EOL,
				'<amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL
			),
			'shortcode_with_url' => array(
				'[tweet https://twitter.com/wordpress/status/118252236836061184]' . PHP_EOL,
				'<amp-twitter data-tweetid="118252236836061184" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL
			),
			'shortcode_with_url_with_big_tweet_id' => array(
				'[tweet https://twitter.com/wordpress/status/705219971425574912]' . PHP_EOL,
				'<amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL
			),
			'shortcode_with_non_numeric_tweet_id' => array(
				'[tweet abcd]' . PHP_EOL,
				'' . PHP_EOL
			),
		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
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
				'https://twitter.com/altjoen/status/118252236836061184' . PHP_EOL,
				array( 'amp-twitter' => 'https://cdn.ampproject.org/v0/amp-twitter-0.1.js' )
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
