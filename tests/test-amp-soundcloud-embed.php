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
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		if ( function_exists( 'soundcloud_shortcode' ) ) {
			add_shortcode( 'soundcloud', 'soundcloud_shortcode' );
		}
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$data = array(
			'no_embed' => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),

			'url_simple' => array(
				'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor' . PHP_EOL,
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
					'shortcode_with_id' => array(
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
			'converted' => array(
				'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor' . PHP_EOL,
				array( 'amp-soundcloud' => 'https://cdn.ampproject.org/v0/amp-soundcloud-0.1.js' ),
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
		apply_filters( 'the_content', $source );
		$scripts = $embed->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
