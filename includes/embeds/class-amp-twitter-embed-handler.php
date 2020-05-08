<?php
/**
 * Class AMP_Twitter_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

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
	const URL_PATTERN_MOMENT = '#https?:\/\/twitter\.com\/i\/moments\/(?P<id>\d+)#i';

	/**
	 * URL pattern for a Twitter timeline.
	 *
	 * @since 1.0
	 * @var string
	 */
	const URL_PATTERN_TIMELINE = '#https?:\/\/twitter\.com(?:\/\#\!\/|\/)(?P<username>[a-zA-Z0-9_]{1,20})(?:$|\/(?P<type>likes|lists|timelines)(\/(?P<id>[a-zA-Z0-9_-]+))?|.+?)#i';

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		// Not implemented.
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		// Not implemented.
	}

	/**
	 * Sanitized <blockquote class="twitter-tweet"> tags to <amp-twitter>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$this->sanitize_tweet_embeds( $dom );
		$this->sanitize_timeline_embeds( $dom );
		$this->sanitize_moment_embeds( $dom );
	}

	/**
	 * Sanitize tweets.
	 *
	 * @param Document $dom Document.
	 */
	private function sanitize_tweet_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//blockquote[ @class = "twitter-tweet" ]' );
		$this->sanitize_raw_embed( 'tweet', $nodes );
	}

	/**
	 * Sanitize timelines.
	 *
	 * @param Document $dom Document.
	 */
	private function sanitize_timeline_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//a[ @class = "twitter-timeline" ]' );
		$this->sanitize_raw_embed( 'timeline', $nodes );
	}

	/**
	 * Sanitize moments.
	 *
	 * @param Document $dom Document.
	 */
	private function sanitize_moment_embeds( Document $dom ) {
		$nodes = $dom->xpath->query( '//a[ @class = "twitter-moment" ]' );
		$this->sanitize_raw_embed( 'moment', $nodes );
	}

	/**
	 * Checks whether it's a twitter AMP component.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return bool Whether node is for raw embed.
	 */
	private function is_raw_embed( DOMElement $node ) {
		return $node->parentNode && 'amp-twitter' !== $node->parentNode->nodeName;
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param string      $embed_type The type of Twitter embed.
	 * @param DOMNodeList $nodes List of DOMElement nodes.
	 */
	private function sanitize_raw_embed( $embed_type, DOMNodeList $nodes ) {
		foreach ( $nodes as $node ) {
			/**
			 * Node.
			 *
			 * @var DOMElement $node
			 */

			if ( ! $this->is_raw_embed( $node ) ) {
				continue;
			}

			$attributes = [
				'width'  => $this->DEFAULT_WIDTH,
				'height' => $this->DEFAULT_HEIGHT,
				'layout' => 'responsive',
			];

			switch ( $embed_type ) {
				case 'tweet':
					$tweet_id = $this->get_tweet_id( $node );
					if ( ! $tweet_id ) {
						return;
					}

					$attributes['data-tweetid'] = $tweet_id;
					break;
				case 'timeline':
					$timeline_attrs = $this->get_timeline_attributes( $node->getAttribute( 'href' ) );
					if ( ! $timeline_attrs ) {
						return;
					}

					if (
						( ! isset( $timeline_attrs['type'] ) && isset( $timeline_attrs['username'] ) ) ||
						( isset( $timeline_attrs['type'] ) && in_array( $timeline_attrs['type'], [ 'likes', 'lists' ], true ) )
					) {
						$attributes['data-timeline-source-type'] = 'url';
						$attributes['data-timeline-url']         = $node->getAttribute( 'href' );
					}

					break;
				case 'moment':
					$moment_id = $this->get_moment_id( $node->getAttribute( 'href' ) );
					if ( ! $moment_id ) {
						return;
					}

					$attributes['data-momentid'] = $moment_id;
					break;
				default:
					return;
			}

			if ( $node->hasAttributes() ) {
				foreach ( $node->attributes as $attr ) {
					// Copy only `data-` attributes.
					if ( false !== strpos( $attr->nodeName, 'data-', 0 ) ) {
						$attributes[ $attr->nodeName ] = $attr->nodeValue;
					}
				}
			}

			$new_node = AMP_DOM_Utils::create_node(
				Document::fromNode( $node ),
				'amp-twitter',
				$attributes
			);

			/**
			 * Placeholder element to append to the new node.
			 *
			 * @var DOMElement $placeholder
			 */
			$placeholder = $node->cloneNode( true );
			$placeholder->setAttribute( 'placeholder', '' );

			$new_node->appendChild( $placeholder );

			$this->sanitize_embed_script( $node );
			// We can unwrap the <p> tag once the accompanied <script> is removed.
			$this->maybe_unwrap_p_element( $node );

			$node->parentNode->replaceChild( $new_node, $node );
		}
	}

	/**
	 * Extracts Tweet id.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return string Tweet ID.
	 */
	private function get_tweet_id( DOMElement $node ) {
		/**
		 * DOMNode
		 *
		 * @var DOMNodeList $anchors
		 */
		$anchors = $node->getElementsByTagName( 'a' );

		/**
		 * Anchor.
		 *
		 * @var DOMElement $anchor
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
	 * Parse Twitter timeline attributes from a URL.
	 *
	 * @param string $url URL.
	 * @return array Timeline attributes.
	 */
	private function get_timeline_attributes( $url ) {
		$found = preg_match( self::URL_PATTERN_TIMELINE, $url, $matches );
		if ( $found ) {
			return $matches;
		}

		return null;
	}

	/**
	 * Parse Twitter moment ID from a URL.
	 *
	 * @param string $url URL.
	 * @return array Timeline attributes.
	 */
	private function get_moment_id( $url ) {
		$found = preg_match( self::URL_PATTERN_MOMENT, $url, $matches );
		if ( $found ) {
			return $matches['id'];
		}

		return null;
	}

	/**
	 * Removes Twitter's embed <script> tag.
	 *
	 * @param DOMElement $node The DOMNode to whose sibling is the Twitter script.
	 */
	private function sanitize_embed_script( DOMElement $node ) {
		$next_element_sibling = $node->nextSibling;

		// Remove any <br> siblings.
		while ( $next_element_sibling && $next_element_sibling instanceof DOMElement && 'br' === $next_element_sibling->nodeName ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
			$next_element_sibling = $node->nextSibling;
		}

		if ( $next_element_sibling instanceof DOMElement && 'br' === $next_element_sibling->nodeName ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}

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
			$next_element_sibling instanceof DOMElement
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
