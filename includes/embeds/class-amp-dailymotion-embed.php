<?php
/**
 * Class AMP_DailyMotion_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_DailyMotion_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 */
class AMP_DailyMotion_Embed_Handler extends AMP_Base_Embed_Handler {

	const URL_PATTERN = '#https?:\/\/(www\.)?dailymotion\.com\/video\/.*#i';
	const RATIO       = 0.5625;

	/**
	 * Default width.
	 *
	 * @var int
	 */
	protected $DEFAULT_WIDTH = 600;

	/**
	 * Default height.
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 338;

	/**
	 * AMP_DailyMotion_Embed_Handler constructor.
	 *
	 * @param array $args Height, width and maximum width for embed.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		if ( isset( $this->args['content_max_width'] ) ) {
			$max_width            = $this->args['content_max_width'];
			$this->args['width']  = $max_width;
			$this->args['height'] = round( $max_width * self::RATIO );
		}
	}

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'amp-dailymotion', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'dailymotion', array( $this, 'shortcode' ) );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-dailymotion', -1 );
		remove_shortcode( 'dailymotion' );
	}

	/**
	 * Gets AMP-compliant markup for the Dailymotion shortcode.
	 *
	 * @param array $attr The Dailymotion attributes.
	 * @return string Dailymotion shortcode markup.
	 */
	public function shortcode( $attr ) {
		$video_id = false;

		if ( isset( $attr['id'] ) ) {
			$video_id = $attr['id'];
		} elseif ( isset( $attr[0] ) ) {
			$video_id = $attr[0];
		} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
			$video_id = shortcode_new_to_old_params( $attr );
		}

		if ( empty( $video_id ) ) {
			return '';
		}

		return $this->render(
			array(
				'video_id' => $video_id,
			)
		);
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Shortcode attribues.
	 * @param string $url     URL.
	 * @param string $rawattr Unmodified shortcode attributes.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches, $attr, $url, $rawattr ) {
		$video_id = $this->get_video_id_from_url( $url );
		return $this->render(
			array(
				'video_id' => $video_id,
			)
		);
	}

	/**
	 * Render.
	 *
	 * @param array $args Args.
	 * @return string Rendered.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'video_id' => false,
			)
		);

		if ( empty( $args['video_id'] ) ) {
			return AMP_HTML_Utils::build_tag(
				'a',
				array(
					'href'  => esc_url( $args['url'] ),
					'class' => 'amp-wp-embed-fallback',
				),
				esc_html( $args['url'] )
			);
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-dailymotion',
			array(
				'data-videoid' => $args['video_id'],
				'layout'       => 'responsive',
				'width'        => $this->args['width'],
				'height'       => $this->args['height'],
			)
		);
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @param string $url URL.
	 * @return integer Video ID.
	 */
	private function get_video_id_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		parse_str( $parsed_url['path'], $path );
		$tok      = explode( '/', $parsed_url['path'] );
		$tok      = explode( '_', $tok[2] );
		$video_id = $tok[0];

		return $video_id;
	}
}
