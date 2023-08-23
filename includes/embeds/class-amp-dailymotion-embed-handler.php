<?php
/**
 * Class AMP_DailyMotion_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Extension;
use AmpProject\Dom\Document;

/**
 * Class AMP_DailyMotion_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds
 *
 * @internal
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
	public function __construct( $args = [] ) {
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
		wp_embed_register_handler( 'amp-dailymotion', self::URL_PATTERN, [ $this, 'oembed' ], -1 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-dailymotion', -1 );
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Shortcode attributes.
	 * @param string $url     URL.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches, $attr, $url ) {
		$video_id = $this->get_video_id_from_url( $url );
		return $this->render(
			[
				'video_id' => $video_id,
			]
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
			[
				'video_id' => false,
			]
		);

		if ( empty( $args['video_id'] ) ) {
			return AMP_HTML_Utils::build_tag(
				'a',
				[
					'href'  => esc_url_raw( $args['url'] ),
					'class' => 'amp-wp-embed-fallback',
				],
				esc_html( $args['url'] )
			);
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-dailymotion',
			[
				'data-videoid' => $args['video_id'],
				'layout'       => 'responsive',
				'width'        => $this->args['width'],
				'height'       => $this->args['height'],
			]
		);
	}

	/**
	 * Sanitize raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		// If there were any previous embeds in the DOM that were wrapped by `wpautop()`, unwrap them.
		$this->unwrap_p_element_by_child_tag_name( $dom, Extension::DAILYMOTION );
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @param string $url URL.
	 * @return string Video ID.
	 */
	private function get_video_id_from_url( $url ) {
		$parsed_url = wp_parse_url( $url );
		parse_str( $parsed_url['path'], $path );
		$tok = explode( '/', $parsed_url['path'] );
		$tok = explode( '_', $tok[2] );

		return $tok[0];
	}
}
