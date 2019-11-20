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
		add_filter( 'wp_video_shortcode_override', [ $this, 'video_override' ], 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
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

		$props = $this->parse_props( $cache, $url, $id );
		if ( empty( $props ) ) {
			return $cache;
		}

		$props['video_id'] = $id;
		return $this->render( $props, $url );
	}

	/**
	 * Parse AMP component from iframe.
	 *
	 * @param string $html     HTML.
	 * @param string $url      Embed URL, for fallback purposes.
	 * @param string $video_id YouTube video ID.
	 * @return array|null Props for rendering the component, or null if unable to parse.
	 */
	private function parse_props( $html, $url, $video_id ) {
		$props = $this->match_element_attributes( $html, 'iframe', [ 'title', 'height', 'width' ] );
		if ( ! isset( $props ) ) {
			return null;
		}

		$img_attributes = [
			'src'        => esc_url_raw( sprintf( 'https://i.ytimg.com/vi/%s/hqdefault.jpg', $video_id ) ),
			'layout'     => 'fill',
			'object-fit' => 'cover',
		];
		if ( ! empty( $props['title'] ) ) {
			$img_attributes['alt'] = $props['title'];
		}
		$img = AMP_HTML_Utils::build_tag( 'img', $img_attributes );

		$props['placeholder'] = AMP_HTML_Utils::build_tag(
			'a',
			[
				'placeholder' => '',
				'href'        => esc_url_raw( $url ),
			],
			$img
		);

		return $props;
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
				'video_id'    => false,
				'layout'      => 'responsive',
				'width'       => $this->args['width'],
				'height'      => $this->args['height'],
				'placeholder' => '',
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

		$attributes = array_merge(
			[ 'data-videoid' => $args['video_id'] ],
			wp_array_slice_assoc( $args, [ 'layout', 'width', 'height' ] )
		);
		if ( ! empty( $args['title'] ) ) {
			$attributes['title'] = $args['title'];
		}

		return AMP_HTML_Utils::build_tag( 'amp-youtube', $attributes, $args['placeholder'] );
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @param string $url URL.
	 * @return integer|false Video ID, or false if none could be retrieved.
	 */
	private function get_video_id_from_url( $url ) {
		if ( preg_match( '/(?:watch\?v=|embed\/|youtu.be\/)(?P<id>\w*)/', $url, $match ) ) {
			return $match['id'];
		}

		return false;
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
}
