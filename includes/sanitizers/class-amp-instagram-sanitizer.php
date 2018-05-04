<?php
/**
 * Class AMP_Instagram_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Instagram_Sanitizer
 *
 * Converts <blockquote class="instagram-media"> tags to <amp-instagram>
 */
class AMP_Instagram_Sanitizer extends AMP_Base_Sanitizer {

	const URL_PATTERN    = '#http(s?)://(www\.)?instagr(\.am|am\.com)/p/([^/?]+)#i';
	const DEFAULT_WIDTH  = 600;
	const DEFAULT_HEIGHT = 600;

	/**
	 * Tag.
	 *
	 * @var string embed HTML blockquote tag to identify and replace with AMP version.
	 */
	public static $tag = 'blockquote';

	/**
	 * Script slug.
	 *
	 * @var string AMP HTML instagram tag to use in place of HTML's embed instagram 'blockquote' tag.
	 */
	private static $script_slug = 'amp-instagram';

	/**
	 * Script src.
	 *
	 * @var string URL to AMP Project's instagram element javascript file found at cdn.ampproject.org
	 */
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-instagram-0.1.js';

	/**
	 * Return one element array containing AMP HTML instagram tag and respective Javascript URL
	 *
	 * HTML tags and Javascript URLs found at cdn.ampproject.org
	 *
	 * @return string[] Returns AMP HTML instagram tag as array key and Javascript URL as array value,
	 *                  respectively. Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML instagram elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return array( self::$script_slug => self::$script_src );
	}

	/**
	 * Sanitize the <blockquote with data-instgrm-permalink> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.2
	 */
	public function sanitize() {

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $node
		 */
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( $node->hasAttribute( 'data-instgrm-permalink' ) ) {
				$this->create_amp_instragram_and_replace_node( $node );
			}
		}
	}

	/**
	 * Make final modifications to DOMNode
	 *
	 * @param DOMNode $node The DOMNode to adjust and replace.
	 */
	private function create_amp_instragram_and_replace_node( $node ) {
		$instagram_id = $this->get_instagram_id_from_url( $node->getAttribute( 'data-instgrm-permalink' ) );

		$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-instagram', array(
			'data-shortcode' => $instagram_id,
			'layout'         => 'responsive',
			'width'          => self::DEFAULT_WIDTH,
			'height'         => self::DEFAULT_HEIGHT,
		) );

		$this->sanitize_embed_script( $node );

		$node->parentNode->replaceChild( $new_node, $node );

		$this->did_convert_elements = true;
	}

	/**
	 * Finds istagram id
	 *
	 * @param String $url The Url to find instagram_id.
	 * @return String
	 */
	private function get_instagram_id_from_url( $url ) {
		$found = preg_match( self::URL_PATTERN, $url, $matches );

		if ( ! $found ) {
			return false;
		}

		return end( $matches );
	}

	/**
	 * Removes instagram's embed <script> tag
	 *
	 * @param DOMNode $node The DOMNode to whose sibling is the instagram script.
	 */
	private function sanitize_embed_script( $node ) {

		$next_sibling = $node->nextSibling;

		if ( null !== $next_sibling && 'script' === strtolower( $next_sibling->nodeName )
			&& false !== strpos( $next_sibling->getAttribute( 'src' ), 'instagram.com/embed.js' ) ) {
			$next_sibling->parentNode->removeChild( $next_sibling );
		}
	}
}
