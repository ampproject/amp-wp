<?php

class AMP_Facebook_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL
			),
			'simple_url_https' => array(
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
			),
			'simple_url_http' => array(
				'http://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="http://www.facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
			),
			'no_dubdubdub' => array(
				'https://facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<p><amp-facebook data-href="https://facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
			),
			'notes_url' => array(
				'https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
			),
			'photo_url' => array(
				'https://www.facebook.com/photo.php?fbid=10102533316889441&set=a.529237706231.2034669.4&type=3&theater' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/photo.php?fbid=10102533316889441&amp;set=a.529237706231.2034669.4&amp;type=3&amp;theater" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
			),
			'notes_url' => array(
				'https://www.facebook.com/zuck/videos/10102509264909801/' . PHP_EOL,
				'<p><amp-facebook data-href="https://www.facebook.com/zuck/videos/10102509264909801/" layout="responsive" width="600" height="400"></amp-facebook></p>' . PHP_EOL
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
				array()
			),
			'converted' => array(
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				array( 'amp-facebook' => 'https://cdn.ampproject.org/v0/amp-facebook-0.1.js' )
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Facebook_Embed_Handler();
		$embed->register_embed();
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
