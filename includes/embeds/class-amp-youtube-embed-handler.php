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
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		if ( isset( $this->args['content_max_width'] ) ) {
			// Set default width/height; these will be overridden by whatever YouTube specifies.
			$max_width            = $this->args['content_max_width'];
			$this->args['width']  = $max_width;
			$this->args['height'] = round( $max_width * self::RATIO );
		}
	}

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
		add_shortcode( 'youtube', [ $this, 'shortcode' ] ); // @todo Deprecated. See <https://github.com/ampproject/amp-wp/issues/3309>.
		add_filter( 'wp_video_shortcode_override', [ $this, 'video_override' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
		remove_shortcode( 'youtube' ); // @todo Deprecated. See <https://github.com/ampproject/amp-wp/issues/3309>.
	}

	/**
	 * Filter oEmbed HTML for YouTube to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! in_array( $host, [ 'youtu.be', 'youtube.com', 'www.youtube.com' ], true ) ) {
			return $cache;
		}

		$id = $this->get_video_id_from_url( $url );
		if ( ! $id ) {
			return $cache;
		}

		$props = $this->parse_props( $cache, $url );

		$props['video_id'] = $id;
		return $this->render( $props, $url );
	}

	/**
	 * Parse AMP component from iframe.
	 *
	 * @param string      $html HTML.
	 * @param string|null $url  Embed URL, for fallback purposes.
	 * @return array Props for rendering the component.
	 */
	private function parse_props( $html, $url ) {
		$props = [];

		if ( preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+)"#s', $html, $matches ) ) {
			$props['title']    = $matches['title'];
			$props['fallback'] = sprintf(
				'<a fallback href="%s">%s</a>',
				esc_url( $url ),
				esc_html( $matches['title'] )
			);
		}

		if ( preg_match( '#<iframe[^>]*?height="(?P<height>\d+)"#s', $html, $matches ) ) {
			$props['height'] = (int) $matches['height'];
		}

		if ( preg_match( '#<iframe[^>]*?width="(?P<width>\d+)"#s', $html, $matches ) ) {
			$props['width'] = (int) $matches['width'];
		}

		return $props;
	}


	/**
	 * Gets AMP-compliant markup for the YouTube shortcode.
	 *
	 * @deprecated This should be moved to Jetpack. See <https://github.com/ampproject/amp-wp/issues/3309>.
	 *
	 * @param array $attr The YouTube attributes.
	 * @return string YouTube shortcode markup.
	 */
	public function shortcode( $attr ) {
		$url = false;

		if ( isset( $attr[0] ) ) {
			$url = ltrim( $attr[0], '=' );
		} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
			$url = shortcode_new_to_old_params( $attr );
		}

		if ( empty( $url ) ) {
			return '';
		}

		$video_id = $this->get_video_id_from_url( $url );

		return $this->render( compact( 'video_id' ), $url );
	}

	/**
	 * Render oEmbed.
	 *
	 * @see \WP_Embed::shortcode()
	 * @deprecated This is no longer being used.
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Shortcode attribues.
	 * @param string $url     URL.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches, $attr, $url ) {
		_deprecated_function( __METHOD__, '1.5.0' );
		return $this->shortcode( [ $url ] );
	}

	/**
	 * Render embed.
	 *
	 * @param array  $args Args.
	 * @param string $url  URL.
	 * @return string Rendered.
	 */
	public function render( $args, $url ) {
		$args = wp_parse_args(
			$args,
			[
				'video_id' => false,
				'layout'   => 'responsive',
				'width'    => $this->args['width'],
				'height'   => $this->args['height'],
				'fallback' => '',
			]
		);

		if ( empty( $args['video_id'] ) ) {
			return AMP_HTML_Utils::build_tag(
				'a',
				[
					'href'  => esc_url_raw( $url ),
					'class' => 'amp-wp-embed-fallback',
				],
				esc_html( $url )
			);
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-youtube',
			array_merge(
				[ 'data-videoid' => $args['video_id'] ],
				wp_array_slice_assoc( $args, [ 'layout', 'width', 'height', 'title' ] )
			),
			$args['fallback']
		);
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @todo Needs to be totally refactored.
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
			parse_str( $parsed_url['query'], $query_args ); // @todo Bug! See <https://github.com/ampproject/amp-wp/issues/3348>.

			if ( isset( $query_args['v'] ) ) {
				$video_id = $this->sanitize_v_arg( $query_args['v'] );
			}
		}

		if ( empty( $video_id ) ) {
			/* The path looks like /(v|e|embed)/{id} */
			$parts = explode( '/', $parsed_url['path'] );

			if ( in_array( $parts[1], [ 'v', 'e', 'embed' ], true ) ) {
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
			return $this->shortcode( [ $src ] );
		}
		return $html;
	}

}
