<?php
/**
 * Class AMP_Facebook_Embed_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Facebook_Embed_Handler
 *
 * @internal
 */
class AMP_Facebook_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * URL pattern.
	 *
	 * @var string
	 */
	const URL_PATTERN = '#https?://(www\.)?facebook\.com/.*#i';

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
	protected $DEFAULT_HEIGHT = 400;

	/**
	 * Tag.
	 *
	 * @var string embed HTML blockquote tag to identify and replace with AMP version.
	 */
	protected $sanitize_tag = 'div';

	/**
	 * Tag.
	 *
	 * @var string AMP amp-facebook tag
	 */
	private $amp_tag = 'amp-facebook';

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( $this->amp_tag, self::URL_PATTERN, [ $this, 'oembed' ], -1 );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( $this->amp_tag, -1 );
	}

	/**
	 * WordPress oEmbed rendering callback.
	 *
	 * @param array  $matches URL pattern matches.
	 * @param array  $attr    Matched attributes.
	 * @param string $url     Matched URL.
	 * @return string HTML markup for rendered embed.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( [ 'url' => $url ] );
	}

	/**
	 * Gets the rendered embed markup.
	 *
	 * @param array $args Embed rendering arguments.
	 * @return string HTML markup for rendered embed.
	 */
	public function render( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'url' => false,
			]
		);

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			$this->amp_tag,
			[
				'data-href' => $args['url'],
				'layout'    => 'responsive',
				'width'     => $this->args['width'],
				'height'    => $this->args['height'],
			],
			$this->create_overflow_button_markup()
		);
	}

	/**
	 * Sanitized <div class="fb-video" data-href=> tags to <amp-facebook>.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		// If there were any previous embeds in the DOM that were wrapped by `wpautop()`, unwrap them.
		$embed_nodes = $dom->xpath->query( "//p/{$this->amp_tag}" );
		if ( $embed_nodes->length ) {
			foreach ( $embed_nodes as $embed_node ) {
				$this->unwrap_p_element( $embed_node );
			}
		}

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

			$embed_type = $this->get_embed_type( $node );

			if ( null !== $embed_type ) {
				$this->create_amp_facebook_and_replace_node( $dom, $node, $embed_type );
			}
		}

		/*
		 * Remove the fb-root div and the Facebook Connect JS script since irrelevant.
		 * <div id="fb-root"></div>
		 * <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>
		 */
		$fb_root = $dom->getElementById( 'fb-root' );
		if ( $fb_root ) {
			$script_elements = $dom->xpath->query( '//script[ starts-with( @src, "https://connect.facebook.net" ) and contains( @src, "sdk.js" ) ]' );
			foreach ( $script_elements as $script ) {
				$parent_node = $script->parentNode;
				$parent_node->removeChild( $script );

				// Remove parent node if it is an empty <p> tag.
				if ( 'p' === $parent_node->nodeName && null === $parent_node->firstChild ) {
					$parent_node->parentNode->removeChild( $parent_node );
				}
			}

			// Remove other instances of <div id="fb-root">.
			$fb_root_elements = $dom->xpath->query( '//div[ @id = "fb-root" ]' );
			foreach ( $fb_root_elements as $fb_root ) {
				$fb_root->parentNode->removeChild( $fb_root );
			}
		}
	}

	/**
	 * Get embed type.
	 *
	 * @param DOMElement $node The DOMNode to adjust and replace.
	 * @return string|null Embed type or null if not detected.
	 */
	private function get_embed_type( DOMElement $node ) {
		$class_attr = $node->getAttribute( 'class' );
		if ( empty( $class_attr ) || ! $node->hasAttribute( 'data-href' ) ) {
			return null;
		}

		if ( false !== strpos( $class_attr, 'fb-post' ) ) {
			return 'post';
		}

		if ( false !== strpos( $class_attr, 'fb-video' ) ) {
			return 'video';
		}

		if ( false !== strpos( $class_attr, 'fb-page' ) ) {
			return 'page';
		}

		if ( false !== strpos( $class_attr, 'fb-like' ) ) {
			return 'like';
		}

		if ( false !== strpos( $class_attr, 'fb-comments' ) ) {
			return 'comments';
		}

		if ( false !== strpos( $class_attr, 'fb-comment-embed' ) ) {
			return 'comment';
		}

		return null;
	}

	/**
	 * Create amp-facebook and replace node.
	 *
	 * @param Document   $dom        The HTML Document.
	 * @param DOMElement $node       The DOMNode to adjust and replace.
	 * @param string     $embed_type Embed type.
	 */
	private function create_amp_facebook_and_replace_node( Document $dom, DOMElement $node, $embed_type ) {

		$attributes = [
			// The layout sanitizer will convert this to `layout` when being sanitized.
			// The data attribute needs to be used so that the layout sanitizer will process it.
			'data-amp-layout' => 'responsive',
			'width'           => $node->hasAttribute( 'data-width' ) ? $node->getAttribute( 'data-width' ) : $this->DEFAULT_WIDTH,
			'height'          => $node->hasAttribute( 'data-height' ) ? $node->getAttribute( 'data-height' ) : $this->DEFAULT_HEIGHT,
		];

		$node->removeAttribute( 'data-width' );
		$node->removeAttribute( 'data-height' );

		foreach ( $node->attributes as $attribute ) {
			if ( 'data-' === substr( $attribute->nodeName, 0, 5 ) ) {
				$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
			}
		}

		if ( 'page' === $embed_type ) {
			$amp_tag = 'amp-facebook-page';
		} elseif ( 'like' === $embed_type ) {
			$amp_tag = 'amp-facebook-like';
		} elseif ( 'comments' === $embed_type ) {
			$amp_tag = 'amp-facebook-comments';
		} else {
			$amp_tag = $this->amp_tag;

			$attributes['data-embed-as'] = $embed_type;
		}

		$amp_facebook_node = AMP_DOM_Utils::create_node(
			$dom,
			$amp_tag,
			$attributes
		);

		$amp_facebook_node->appendChild( $this->create_overflow_button_element( $dom ) );

		$fallback = null;
		foreach ( $node->childNodes as $child_node ) {
			if ( $child_node instanceof DOMElement && false !== strpos( $child_node->getAttribute( 'class' ), 'fb-xfbml-parse-ignore' ) ) {
				$fallback = $child_node;
				$child_node->parentNode->removeChild( $child_node );
				$fallback->setAttribute( 'fallback', '' );
				break;
			}
		}

		$node->parentNode->replaceChild( $amp_facebook_node, $node );
		if ( $fallback ) {
			$amp_facebook_node->appendChild( $fallback );
		}

		$this->did_convert_elements = true;
	}
}
