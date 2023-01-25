<?php
/**
 * Class AMP_YouTube_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\CssLength;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Layout;

/**
 * Class AMP_YouTube_Embed_Handler
 *
 * Much of this class is borrowed from Jetpack embeds.
 *
 * @internal
 */
class AMP_YouTube_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * URL pattern to match YouTube videos.
	 *
	 * Only handling single videos. Playlists are handled elsewhere.
	 *
	 * @deprecated No longer used.
	 * @internal
	 * @var string
	 */
	const URL_PATTERN = '#https?://(?:www\.)?(?:youtube.com/(?:v/|e/|embed/|watch[/\#?])|youtu\.be/).*#i';

	/**
	 * Ratio for calculating the default height from the content width.
	 *
	 * @var float
	 */
	const RATIO = 0.5625;

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
	 * List of domains that are applicable for this embed.
	 *
	 * @var string[]
	 */
	const APPLICABLE_DOMAINS = [ 'youtu.be', 'youtube.com', 'youtube-nocookie.com' ];

	/**
	 * Attributes from iframe which are copied to amp-youtube.
	 *
	 * @var string[]
	 */
	const IFRAME_ATTRIBUTES = [
		Attribute::TITLE,
		Attribute::HEIGHT,
		Attribute::WIDTH,
	];

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
		add_filter( 'wp_video_shortcode_override', [ $this, 'video_override' ], PHP_INT_MAX, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
		remove_filter( 'wp_video_shortcode_override', [ $this, 'video_override' ], PHP_INT_MAX );
	}

	/**
	 * Filter oEmbed HTML for YouTube to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 *
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {

		if ( empty( $cache ) || empty( $url ) ) {
			return $cache;
		}

		$video_id = $this->get_video_id_from_url( $url );

		if ( ! $video_id ) {
			return $cache;
		}

		return $this->render( $cache, $url, $video_id );
	}

	/**
	 * Convert YouTube iframe into AMP YouTube component.
	 *
	 * @param string $html     HTML markup of YouTube iframe.
	 * @param string $url      YouTube URL.
	 * @param string $video_id YouTube video ID.
	 *
	 * @return string HTML markup of AMP YouTube component.
	 */
	public function render( $html, $url, $video_id ) {

		$attributes = $this->prepare_attributes( $url, $video_id );

		$props = $this->match_element_attributes( $html, Tag::IFRAME, self::IFRAME_ATTRIBUTES );
		foreach ( self::IFRAME_ATTRIBUTES as $iframe_prop ) {
			if ( ! empty( $props[ $iframe_prop ] ) ) {
				$attributes[ $iframe_prop ] = $props[ $iframe_prop ];
			}
		}
		$attributes = $this->amend_fixed_height_layout( $attributes );

		$placeholder = $this->get_placeholder_markup( $url, $video_id, $attributes );

		return AMP_HTML_Utils::build_tag( Extension::YOUTUBE, $attributes, $placeholder );
	}

	/**
	 * Sanitize YouTube raw embeds.
	 *
	 * @param Document $dom Document.
	 *
	 * @return void
	 */
	public function sanitize_raw_embeds( Document $dom ) {

		$query_segments = array_map(
			static function ( $domain ) {
				return sprintf(
					'starts-with( @src, "https://www.%1$s/" ) or starts-with( @src, "https://%1$s/" ) or starts-with( @src, "http://www.%1$s/" ) or starts-with( @src, "http://%1$s/" )',
					$domain
				);
			},
			self::APPLICABLE_DOMAINS
		);

		$query = implode( ' or ', $query_segments );

		$nodes = $dom->xpath->query( sprintf( '//iframe[ %s ]', $query ) );

		/** @var Element $node */
		foreach ( $nodes as $node ) {

			$amp_youtube_component = $this->get_amp_component( $dom, $node );

			if ( ! empty( $amp_youtube_component ) ) {
				$node->parentNode->replaceChild( $amp_youtube_component, $node );
			}
		}
	}

	/**
	 * Parse YouTube iframe element and return an AMP YouTube component.
	 *
	 * @param Document $dom  Document DOM.
	 * @param Element  $node YouTube iframe element.
	 *
	 * @return Element|false AMP component, otherwise `false`.
	 */
	private function get_amp_component( Document $dom, Element $node ) {

		$url        = $node->getAttribute( Attribute::SRC );
		$video_id   = $this->get_video_id_from_url( $url );
		$attributes = $this->prepare_attributes( $url, $video_id );

		foreach ( self::IFRAME_ATTRIBUTES as $iframe_prop ) {
			if ( ! empty( $node->getAttribute( $iframe_prop ) ) ) {
				$attributes[ $iframe_prop ] = $node->getAttribute( $iframe_prop );
			}
		}

		if ( empty( $attributes[ Attribute::DATA_VIDEOID ] ) && empty( $attributes[ Attribute::DATA_LIVE_CHANNELID ] ) ) {
			return false;
		}

		$attributes = $this->amend_fixed_height_layout( $attributes );

		$amp_node = AMP_DOM_Utils::create_node(
			$dom,
			Extension::YOUTUBE,
			$attributes
		);

		if ( $video_id && $amp_node instanceof Element ) {
			$amp_node->appendChild(
				$this->get_placeholder_element( $amp_node, $video_id, $attributes )
			);
		}

		return $amp_node;
	}

	/**
	 * Amend attributes with fixed-height layout if there is a 100% width present.
	 *
	 * @param array $attributes Attributes.
	 * @return array Amended attributes.
	 */
	private function amend_fixed_height_layout( $attributes ) {
		if (
			isset( $attributes[ Attribute::WIDTH ] )
			&&
			( '100%' === $attributes[ Attribute::WIDTH ] || CssLength::AUTO === $attributes[ Attribute::WIDTH ] )
		) {
			$attributes[ Attribute::LAYOUT ] = Layout::FIXED_HEIGHT;
			$attributes[ Attribute::WIDTH ]  = CssLength::AUTO;
		}
		return $attributes;
	}

	/**
	 * Prepare attributes for amp-youtube component.
	 *
	 * @param string $url      YouTube video URL.
	 * @param string $video_id YouTube video ID.
	 *
	 * @return array prepared arguments for amp-youtube component.
	 */
	private function prepare_attributes( $url, $video_id = '' ) {

		$attributes = [
			Attribute::LAYOUT => Layout::RESPONSIVE,
			Attribute::WIDTH  => $this->args['width'],
			Attribute::HEIGHT => $this->args['height'],
		];

		if ( ! empty( $video_id ) ) {
			$attributes[ Attribute::DATA_VIDEOID ] = $video_id;
		}

		// Find start time of video.
		$start_time = $this->get_start_time_from_url( $url );
		if ( ! empty( $start_time ) && 0 < (int) $start_time ) {
			$attributes['data-param-start'] = (int) $start_time;
		}

		$query_vars  = [];
		$query_param = wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $query_param, $query_vars );
		$query_vars = ( is_array( $query_vars ) ) ? $query_vars : [];

		$excluded_param = [ 'start', 'v', 'vi', 'w', 'h' ];

		foreach ( $query_vars as $key => $value ) {

			if ( in_array( $key, $excluded_param, true ) ) {
				continue;
			}

			if ( in_array( $key, [ Attribute::AUTOPLAY, Attribute::LOOP ], true ) ) {
				$attributes[ $key ] = $value;
				continue;
			}

			if ( 'channel' === $key ) {
				$attributes[ Attribute::DATA_LIVE_CHANNELID ] = $value;
				continue;
			}

			$attributes[ sanitize_key( "data-param-$key" ) ] = $value;
		}

		return $attributes;
	}

	/**
	 * Placeholder element for AMP YouTube component in the DOM.
	 *
	 * @param Element $amp_component AMP component element.
	 * @param string  $video_id      Video ID.
	 * @param array   $attributes    YouTube attributes.
	 *
	 * @return Element Placeholder.
	 */
	private function get_placeholder_element( Element $amp_component, $video_id, $attributes ) {
		$dom = Document::fromNode( $amp_component );

		$img_attributes = [
			Attribute::SRC        => esc_url_raw( sprintf( 'https://i.ytimg.com/vi/%s/hqdefault.jpg', $video_id ) ),
			Attribute::LAYOUT     => Layout::FILL,
			Attribute::OBJECT_FIT => 'cover',
		];

		if ( ! empty( $attributes[ Attribute::TITLE ] ) ) {
			$img_attributes[ Attribute::ALT ] = $attributes[ Attribute::TITLE ];
		}

		$img_node = AMP_DOM_Utils::create_node(
			$dom,
			Tag::IMG,
			$img_attributes
		);

		$video_url = esc_url_raw( sprintf( 'https://www.youtube.com/watch?v=%s', $video_id ) );
		if ( array_key_exists( 'data-param-start', $attributes ) ) {
			$video_url .= '#t=' . $attributes['data-param-start'];
		}

		$placeholder = AMP_DOM_Utils::create_node(
			$dom,
			Tag::A,
			[
				Attribute::PLACEHOLDER => '',
				Attribute::HREF        => $video_url,
			]
		);

		$placeholder->appendChild( $img_node );

		return $placeholder;
	}

	/**
	 * To get placeholder for AMP component as constructed HTML string.
	 *
	 * @param string $url        YouTube URL.
	 * @param string $video_id   Video ID.
	 * @param array  $attributes YouTube attributes.
	 *
	 * @return string HTML string.
	 */
	private function get_placeholder_markup( $url, $video_id, $attributes ) {

		$img_attributes = [
			Attribute::SRC        => esc_url_raw( sprintf( 'https://i.ytimg.com/vi/%s/hqdefault.jpg', $video_id ) ),
			Attribute::LAYOUT     => Layout::FILL,
			Attribute::OBJECT_FIT => 'cover',
		];

		if ( ! empty( $attributes[ Attribute::TITLE ] ) ) {
			$img_attributes[ Attribute::ALT ] = $attributes[ Attribute::TITLE ];
		}

		$img = '<img ' . AMP_HTML_Utils::build_attributes_string( $img_attributes ) . '>';

		return AMP_HTML_Utils::build_tag(
			Tag::A,
			[
				Attribute::PLACEHOLDER => '',
				Attribute::HREF        => esc_url_raw( $url ),
			],
			$img
		);
	}

	/**
	 * Determine the video ID from the URL.
	 *
	 * @param string $url URL.
	 *
	 * @return string|false Video ID, or false if none could be retrieved.
	 */
	private function get_video_id_from_url( $url ) {

		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$domain = implode( '.', array_slice( explode( '.', $parsed_url['host'] ), - 2 ) );
		if ( ! in_array( $domain, self::APPLICABLE_DOMAINS, true ) ) {
			return false;
		}

		if ( ! isset( $parsed_url['path'] ) ) {
			return false;
		}

		$segments = explode( '/', trim( $parsed_url['path'], '/' ) );

		$query_vars = [];
		if ( isset( $parsed_url['query'] ) ) {
			wp_parse_str( $parsed_url['query'], $query_vars );

			// Handle video ID in v query param, e.g. <https://www.youtube.com/watch?v=XOY3ZUO6P0k>.
			// Support is also included for other query params which don't appear to be supported by YouTube anymore.
			if ( isset( $query_vars['v'] ) ) {
				return $query_vars['v'];
			} elseif ( isset( $query_vars['vi'] ) ) {
				return $query_vars['vi'];
			}
		}

		if ( empty( $segments[0] ) ) {
			return false;
		}

		// For shortened URLs like <http://youtu.be/XOY3ZUO6P0k>, the slug is the first path segment.
		if ( 'youtu.be' === $parsed_url['host'] ) {
			return $segments[0];
		}

		// For non-shortened URLs, the video ID is in the second path segment. For example:
		// * https://www.youtube.com/watch/XOY3ZUO6P0k
		// * https://www.youtube.com/embed/XOY3ZUO6P0k
		// Other top-level segments indicate non-video URLs. There are examples of URLs having segments including
		// 'v', 'vi', and 'e' but these do not work anymore. In any case, they are added here for completeness.
		if ( ! empty( $segments[1] ) && in_array( $segments[0], [ 'embed', 'watch', 'v', 'vi', 'e' ], true ) ) {

			/**
			 * Ignore live streaming channel URLs. For example:
			 * * https://www.youtube.com/embed/live_stream?channel=UCkaNo2FUEWips2z4BkOHl6Q
			 */
			if ( 'embed' === $segments[0] && 'live_stream' === $segments[1] && isset( $query_vars['channel'] ) ) {
				return false;
			}

			return $segments[1];
		}

		return false;
	}

	/**
	 * Get the start time of the YouTube video in seconds.
	 *
	 * @param string $url YouTube URL.
	 *
	 * @return int Start time in seconds.
	 */
	private function get_start_time_from_url( $url ) {

		$start_time = 0;
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['query'] ) ) {
			$query_vars = [];
			wp_parse_str( $parsed_url['query'], $query_vars );

			if ( ! empty( $query_vars['start'] ) && 0 < (int) $query_vars['start'] ) {
				return (int) $query_vars['start'];
			}
		}

		if ( ! empty( $parsed_url['fragment'] ) ) {
			$regex = '/^t=(?:(?<minutes>\d+)m)?(?:(?<seconds>\d+)s?)?$/';

			preg_match( $regex, $parsed_url['fragment'], $matches );

			if ( is_array( $matches ) ) {
				$matches    = wp_parse_args(
					$matches,
					[
						'minutes' => 0,
						'seconds' => 0,
					]
				);
				$start_time = ( (int) $matches['seconds'] + ( (int) $matches['minutes'] * 60 ) );
			}
		}

		return $start_time;
	}

	/**
	 * Override the output of YouTube videos.
	 *
	 * This overrides the value in wp_video_shortcode().
	 * The pattern matching is copied from WP_Widget_Media_Video::render().
	 *
	 * @param string $html Empty variable to be replaced with shortcode markup.
	 * @param array  $attr The shortcode attributes.
	 *
	 * @return string|null $markup The markup to output.
	 */
	public function video_override( $html, $attr ) {

		if ( ! isset( $attr[ Attribute::SRC ] ) ) {
			return $html;
		}

		$src      = $attr[ Attribute::SRC ];
		$video_id = $this->get_video_id_from_url( $src );

		if ( ! $video_id ) {
			return $html;
		}

		// Construct a tag so that any width/height attributes will be passed along.
		if ( ! $html ) {
			$html = AMP_HTML_Utils::build_tag( Tag::IFRAME, $attr );
		}

		return $this->render( $html, $src, $video_id );
	}
}
