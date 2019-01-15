<?php

class AMP_Facebook_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed'         => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),
			'simple_url_https' => array(
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),
			'simple_url_http'  => array(
				'http://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="http://www.facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),
			'no_dubdubdub'     => array(
				'https://facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="https://facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),
			'notes_url'        => array(
				'https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),
			'photo_url'        => array(
				'https://www.facebook.com/photo.php?fbid=10102533316889441&set=a.529237706231.2034669.4&type=3&theater' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/photo.php?fbid=10102533316889441&amp;set=a.529237706231.2034669.4&amp;type=3&amp;theater" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),
			'notes_url'        => array(
				'https://www.facebook.com/zuck/videos/10102509264909801/' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/zuck/videos/10102509264909801/" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL,
			),

		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Facebook_Embed_Handler();
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
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				array( 'amp-facebook' => true ),
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Facebook_Embed_Handler();
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

	/**
	 * Data for test__raw_embed_sanitizer.
	 *
	 * @return array
	 */
	public function get_raw_embed_dataset() {
		return array(
			'no_embed_blockquote'   => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			),
			'div_without_instagram' => array(
				'<div>lorem ipsum</div>',
				'<div>lorem ipsum</div>',
			),

			'post_embed'            => array(
				'<div class="fb-post" data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/"></div>',
				'<amp-facebook data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/" data-embed-as="post" layout="responsive" width="600" height="400"></amp-facebook>',
			),
			'video_embed'           => array(
				'<div class="fb-video" data-href="https://www.facebook.com/amanda.orr.56/videos/10212156330049017/" data-show-text="false"></div>',
				'<amp-facebook data-href="https://www.facebook.com/amanda.orr.56/videos/10212156330049017/" data-embed-as="video" layout="responsive" width="600" height="400"></amp-facebook>',
			),
		);
	}


	/**
	 * Test raw_embed_sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_raw_embed_dataset
	 * @covers AMP_Facebook_Embed_Handler::sanitize_raw_embeds()
	 */
	public function test__raw_embed_sanitizer( $source, $expected ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( $source );
		$embed = new AMP_Facebook_Embed_Handler();

		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}
}
