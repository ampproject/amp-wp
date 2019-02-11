<?php
/**
 * Class AMP_YouTube_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_YouTube_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds.
 */
class AMP_YouTube_Embed_Handler extends AMP_Base_Embed_Handler {
	const SHORT_URL_HOST = 'youtu.be';
	// Only handling single videos. Playlists are handled elsewhere.
	const URL_PATTERN = '#https?://(?:www\.)?(?:youtube.com/(?:v/|e/|embed/|watch[/\#?])|youtu\.be/).*#i';
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
	 * AMP_YouTube_Embed_Handler constructor.
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
		wp_embed_register_handler( 'amp-youtube', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'youtube', array( $this, 'shortcode' ) );
		add_filter( 'wp_video_shortcode_override', array( $this, 'video_override' ), 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-youtube', -1 );
		remove_shortcode( 'youtube' );
	}

	/**
	 * Gets AMP-compliant markup for the YouTube shortcode.
	 *
	 * @param array $attr The YouTube attributes.
	 * @return string YouTube shortcode markup.
	 */
	public function shortcode( $attr ) {
		$url      = false;
		$video_id = false;
		if ( isset( $attr[0] ) ) {
			$url = ltrim( $attr[0], '=' );
		} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
			$url = shortcode_new_to_old_params( $attr );
		}

		if ( empty( $url ) ) {
			return '';
		}

		$video_id = $this->get_video_id_from_url( $url );

		return $this->render(
			array(
				'url'      => $url,
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
		return $this->shortcode( array( $url ) );
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
			'amp-youtube',
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
		$video_id   = false;
		$parsed_url = wp_parse_url( $url );

		if ( self::SHORT_URL_HOST === substr( $parsed_url['host'], -strlen( self::SHORT_URL_HOST ) ) ) {
			/* youtu.be/{id} */
			$parts = explode( '/', $parsed_url['path'] );
			if ( ! empty( $parts ) ) {
				$video_id = $parts[1];
			}
		} else {
			/* phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			The query looks like ?v={id} or ?list={id} */
			parse_str( $parsed_url['query'], $query_args );

			if ( isset( $query_args['v'] ) ) {
				$video_id = $this->sanitize_v_arg( $query_args['v'] );
			}
		}

		if ( empty( $video_id ) ) {
			/* The path looks like /(v|e|embed)/{id} */
			$parts = explode( '/', $parsed_url['path'] );

			if ( in_array( $parts[1], array( 'v', 'e', 'embed' ), true ) ) {
				$video_id = $parts[2];
			}
		}

		return $video_id;
	}

	/**
	 * Sanitize the v= argument in the URL.
	 *
	 * @param string $value query parameters.
	 * @return string First set of query parameters.
	 */
	private function sanitize_v_arg( $value ) {
		// Deal with broken params like `?v=123?rel=0`.
		if ( false !== strpos( $value, '?' ) ) {
			$value = strtok( $value, '?' );
		}

		return $value;
	}

	/**
	 * Override the output of YouTube videos.
	 *
	 * This overrides the value in wp_video_shortcode().
	 * The pattern matching is copied from WP_Widget_Media_Video::render().
	 *
	 * @param string $html Empty variable to be replaced with shortcode markup.
	 * @param array  $attr The shortcode attributes.
	 * @return string|null $markup The markup to output.
	 */
	public function video_override( $html, $attr ) {
		if ( ! isset( $attr['src'] ) ) {
			return $html;
		}
		$src             = $attr['src'];
		$youtube_pattern = '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#';
		if ( 1 === preg_match( $youtube_pattern, $src ) ) {
			return $this->shortcode( array( $src ) );
		}
		return $html;
	}

}
