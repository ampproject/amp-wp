<?php
/**
 * Class AMP_SoundCloud_Embed_Test
 *
 * @package AMP
 */

/**
 * Class AMP_SoundCloud_Embed_Test
 *
 * @covers AMP_SoundCloud_Embed_Handler
 */
class AMP_SoundCloud_Embed_Test extends WP_UnitTestCase {

	/**
	 * The oEmbed URL.
	 *
	 * @var string
	 */
	protected $oembed_url = 'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor';

	/**
	 * Response for oEmbed request.
	 *
	 * @see AMP_SoundCloud_Embed_Test::$oembed_url
	 * @var string
	 */
	protected $oembed_response = '{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":400,"width":500,"title":"Mozart - Requiem in D minor Complete Full by Jack Villano Villano","description":"mass in D Minor ","thumbnail_url":"http://i1.sndcdn.com/artworks-000046826426-o7i9ki-t500x500.jpg","html":"\u003Ciframe width=\"500\" height=\"400\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F90097394\u0026show_artwork=true\u0026maxwidth=500\u0026maxheight=750\u0026dnt=1\"\u003E\u003C/iframe\u003E","author_name":"Jack Villano Villano","author_url":"https://soundcloud.com/jack-villano-villano"}';

	/**
	 * Set up.
	 *
	 * @global WP_Post $post
	 */
	public function setUp() {
		global $post;
		parent::setUp();

		/*
		 * As #34115 in 4.9 a post is not needed for context to run oEmbeds. Prior ot 4.9, the WP_Embed::shortcode()
		 * method would short-circuit when this is the case:
		 * https://github.com/WordPress/wordpress-develop/blob/4.8.4/src/wp-includes/class-wp-embed.php#L192-L193
		 * So on WP<4.9 we set a post global to ensure oEmbeds get processed.
		 */
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {
			$post = $this->factory()->post->create_and_get();
		}

		if ( function_exists( 'soundcloud_shortcode' ) ) {
			add_shortcode( 'soundcloud', 'soundcloud_shortcode' );
		}

		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10, 3 );
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_filter( 'pre_http_request', array( $this, 'mock_http_request' ) );
		parent::tearDown();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $preempt, $r, $url ) {
		unset( $r );
		if ( false !== strpos( $url, 'soundcloud.com' ) ) {
			return array(
				'body'          => $this->oembed_response,
				'headers'       => array(),
				'response'      => array(
					'code'    => 200,
					'message' => 'ok',
				),
				'cookies'       => array(),
				'http_response' => null,
			);
		}
		return $preempt;
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$data = array(
			'no_embed'   => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),

			'url_simple' => array(
				$this->oembed_url . PHP_EOL,
				'<p><amp-soundcloud data-trackid="90097394" layout="fixed-height" height="200"></amp-soundcloud></p>' . PHP_EOL,
			),
		);

		if ( defined( 'JETPACK__PLUGIN_DIR' ) ) {
			require_once JETPACK__PLUGIN_DIR . 'modules/shortcodes/soundcloud.php';
		}

		if ( function_exists( 'soundcloud_shortcode' ) ) {
			$data = array_merge(
				$data,
				array(
					// This is supported by Jetpack.
					'shortcode_unnamed_attr_as_url' => array(
						'[soundcloud url=https://api.soundcloud.com/tracks/89299804]' . PHP_EOL,
						'<amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud>' . PHP_EOL,
					),

					// This apparently only works on WordPress.com.
					'shortcode_with_id'             => array(
						'[soundcloud id=89299804]' . PHP_EOL,
						'<amp-soundcloud data-trackid="89299804" layout="fixed-height" height="200"></amp-soundcloud>' . PHP_EOL,
					),
				)
			);
		}

		return $data;
	}

	/**
	 * Test conversion.
	 *
	 * @covers AMP_SoundCloud_Embed_Handler::filter_embed_oembed_html()
	 * @covers AMP_SoundCloud_Embed_Handler::shortcode()
	 * @covers AMP_SoundCloud_Embed_Handler::render()
	 * @dataProvider get_conversion_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_SoundCloud_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array Scripts data.
	 */
	public function get_scripts_data() {
		return array(
			'not_converted' => array(
				'<p>Hello World.</p>',
				array(),
			),
			'converted'     => array(
				$this->oembed_url . PHP_EOL,
				array( 'amp-soundcloud' => true ),
			),
		);
	}

	/**
	 * Test get scripts.
	 *
	 * @covers AMP_SoundCloud_Embed_Handler::get_scripts()
	 * @dataProvider get_scripts_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_SoundCloud_Embed_Handler();
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
