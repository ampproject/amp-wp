<?php
/**
 * Class AMP_Twitter_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Twitter_Embed_Handler
 *
 *  Much of this class is borrowed from Jetpack embeds
 */
class AMP_Twitter_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * URL pattern for a Tweet URL.
	 *
	 * @since 0.2
	 * @var string
	 */
	const URL_PATTERN = '#https?:\/\/twitter\.com(?:\/\#\!\/|\/)(?P<username>[a-zA-Z0-9_]{1,20})\/status(?:es)?\/(?P<tweet>\d+)#i';

	/**
	 * URL pattern for a Twitter timeline.
	 *
	 * @since 1.0
	 * @var string
	 */
	const URL_PATTERN_TIMELINE = '#https?:\/\/twitter\.com(?:\/\#\!\/|\/)(?P<username>[a-zA-Z0-9_]{1,20})(?:$|\/(?P<type>likes|lists)(\/(?P<id>[a-zA-Z0-9_-]+))?)#i';

	/**
	 * Tag.
	 *
	 * @var string embed HTML blockquote tag to identify and replace with AMP version.
	 */
	protected $sanitize_tag = 'blockquote';

	/**
	 * Tag.
	 *
	 * @var string AMP amp-facebook tag
	 */
	private $amp_tag = 'amp-twitter';

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
		wp_embed_register_handler( 'amp-twitter-timeline', self::URL_PATTERN_TIMELINE, [ $this, 'oembed_timeline' ], -1 );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ] );
		wp_embed_unregister_handler( 'amp-twitter-timeline', -1 );
	}

	/**
	 * Filter oEmbed HTML for Twitter to prepare it for AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( false === strpos( $parsed_url['host'], 'twitter.com' ) ) {
			return $cache;
		}

		if ( ! preg_match( '#^https?://twitter.com/.+/status/(\d+)#', $url, $matches ) ) {
			return $cache;
		}
		$tweet_id = $matches[1];

		$cache = preg_replace(
			'#(<blockquote[^>]+?twitter-tweet[^>]+)(>[^\r]+)<script.*$#',
			sprintf(
				'<amp-twitter width="%d" height="%d" layout="responsive" data-tweetid="%s">$1 placeholder $2</amp-twitter>',
				esc_attr( $this->DEFAULT_WIDTH ),
				esc_attr( $this->DEFAULT_HEIGHT ),
				esc_attr( $tweet_id )
			),
			$cache
		);
		return $cache;
	}

	/**
	 * Gets AMP-compliant markup for the Twitter shortcode.
	 *
	 * @deprecated 1.5.0 Moved to jetpack in Jetpack_Tweet::jetpack_tweet_shortcode().
	 *
	 * @param array $attr The Twitter attributes.
	 * @return string Twitter shortcode markup.
	 */
	public function shortcode( $attr ) {
		$attr = wp_parse_args(
			$attr,
			[
				'tweet' => false,
			]
		);

		if ( empty( $attr['tweet'] ) && ! empty( $attr[0] ) ) {
			$attr['tweet'] = $attr[0];
		}

		$id = false;
		if ( is_numeric( $attr['tweet'] ) ) {
			$id = $attr['tweet'];
		} else {
			preg_match( self::URL_PATTERN, $attr['tweet'], $matches );
			if ( isset( $matches['tweet'] ) && is_numeric( $matches['tweet'] ) ) {
				$id = $matches['tweet'];
			}

			if ( empty( $id ) ) {
				return '';
			}
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			$this->amp_tag,
			[
				'data-tweetid' => $id,
				'layout'       => 'responsive',
				'width'        => $this->args['width'],
				'height'       => $this->args['height'],
			]
		);
	}

	/**
	 * Render oEmbed.
	 *
	 * @deprecated Since 1.1 as now the sanitize_raw_embeds() is used exclusively, allowing the original oEmbed response to be rapped by amp-twitter.
	 * @see \WP_Embed::shortcode()
	 *
	 * @param array $matches URL pattern matches.
	 * @return string Rendered oEmbed.
	 */
	public function oembed( $matches ) {
		_deprecated_function( __METHOD__, '1.1' );
		$id = false;

		if ( isset( $matches['tweet'] ) && is_numeric( $matches['tweet'] ) ) {
			$id = $matches['tweet'];
		}

		if ( ! $id ) {
			return '';
		}

		return $this->shortcode( [ 'tweet' => $id ] );
	}

	/**
	 * Render oEmbed for a timeline.
	 *
	 * @since 1.0
	 *
	 * @param array $matches URL pattern matches.
	 * @return string Rendered oEmbed.
	 */
	public function oembed_timeline( $matches ) {
		if ( ! isset( $matches['username'] ) ) {
			return '';
		}

		$attributes = [
			'data-timeline-source-type' => 'profile',
			'data-timeline-screen-name' => $matches['username'],
		];

		if ( isset( $matches['type'] ) ) {
			switch ( $matches['type'] ) {
				case 'likes':
					$attributes['data-timeline-source-type'] = 'likes';
					break;
				case 'lists':
					if ( ! isset( $matches['id'] ) ) {
						return '';
					}
					$attributes['data-timeline-source-type']       = 'list';
					$attributes['data-timeline-slug']              = $matches['id'];
					$attributes['data-timeline-owner-screen-name'] = $attributes['data-timeline-screen-name'];
					unset( $attributes['data-timeline-screen-name'] );
					break;
				default:
					return '';
			}
		}

		$attributes['layout'] = 'responsive';
		$attributes['width']  = $this->args['width'];
		$attributes['height'] = $this->args['height'];

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag( $this->amp_tag, $attributes );
	}

	/**
	 * Sanitized <blockquote class="twitter-tweet"> tags to <amp-twitter>.
	 *
	 * @param DOMDocument $dom DOM.
	 */
	public function sanitize_raw_embeds( $dom ) {
		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
		 */
		$nodes     = $dom->getElementsByTagName( $this->sanitize_tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( $this->is_tweet_raw_embed( $node ) ) {
				$this->create_amp_twitter_and_replace_node( $dom, $node );
			}
		}
	}

	/**
	 * Checks whether it's a twitter blockquote or not.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return bool Whether node is for raw embed.
	 */
	private function is_tweet_raw_embed( $node ) {
		// Skip processing blockquotes that have already been passed through while being wrapped with <amp-twitter>.
		if ( $node->parentNode && 'amp-twitter' === $node->parentNode->nodeName ) {
			return false;
		}

		$class_attr = $node->getAttribute( 'class' );

		return null !== $class_attr && false !== strpos( $class_attr, 'twitter-tweet' );
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param DOMDocument $dom The HTML Document.
	 * @param DOMElement  $node The DOMNode to adjust and replace.
	 */
	private function create_amp_twitter_and_replace_node( $dom, $node ) {
		$tweet_id = $this->get_tweet_id( $node );
		if ( ! $tweet_id ) {
			return;
		}

		$attributes = [
			'width'        => $this->DEFAULT_WIDTH,
			'height'       => $this->DEFAULT_HEIGHT,
			'layout'       => 'responsive',
			'data-tweetid' => $tweet_id,
		];

		if ( $node->hasAttributes() ) {
			foreach ( $node->attributes as $attr ) {
				$attributes[ $attr->nodeName ] = $attr->nodeValue;
			}
		}

		$new_node = AMP_DOM_Utils::create_node(
			$dom,
			$this->amp_tag,
			$attributes
		);

		$placeholder = $node->cloneNode( true );
		$placeholder->setAttribute( 'placeholder', '' );

		$new_node->appendChild( $placeholder );

		$this->sanitize_embed_script( $node );

		$node->parentNode->replaceChild( $new_node, $node );

		$this->did_convert_elements = true;
	}

	/**
	 * Extracts Tweet id.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return string Tweet ID.
	 */
	private function get_tweet_id( $node ) {
		/**
		 * DOMNode
		 *
		 * @var DOMNodeList $anchors
		 */
		$anchors = $node->getElementsByTagName( 'a' );

		/**
		 * Anchor.
		 *
		 * @type DOMElement $anchor
		 */
		foreach ( $anchors as $anchor ) {
			$found = preg_match( self::URL_PATTERN, $anchor->getAttribute( 'href' ), $matches );
			if ( $found ) {
				return $matches['tweet'];
			}
		}

		return null;
	}

	/**
	 * Removes Twitter's embed <script> tag.
	 *
	 * @param DOMElement $node The DOMNode to whose sibling is the instagram script.
	 */
	private function sanitize_embed_script( $node ) {
		$next_element_sibling = $node->nextSibling;
		while ( $next_element_sibling && ! ( $next_element_sibling instanceof DOMElement ) ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}

		$script_src = 'platform.twitter.com/widgets.js';

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( $next_element_sibling instanceof DOMElement && 'p' === $next_element_sibling->nodeName ) {
			$children = $next_element_sibling->getElementsByTagName( '*' );
			if ( 1 === $children->length && 'script' === $children->item( 0 )->nodeName && false !== strpos( $children->item( 0 )->getAttribute( 'src' ), $script_src ) ) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
				return;
			}
		}

		// Handle case where script is immediately following.
		$is_embed_script = (
			$next_element_sibling
			&&
			'script' === strtolower( $next_element_sibling->nodeName )
			&&
			false !== strpos( $next_element_sibling->getAttribute( 'src' ), $script_src )
		);
		if ( $is_embed_script ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
