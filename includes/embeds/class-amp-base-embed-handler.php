<?php
/**
 * Class AMP_Base_Embed_Handler
 *
 * Used by some children.
 *
 * @package  AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Base_Embed_Handler
 */
abstract class AMP_Base_Embed_Handler {
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
	protected $DEFAULT_HEIGHT = 480;

	/**
	 * Default arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Whether or not conversion was completed.
	 *
	 * @var boolean
	 */
	protected $did_convert_elements = false;

	/**
	 * Default AMP tag to be used when sanitizing embeds.
	 *
	 * @var string
	 */
	protected $amp_tag = 'amp-iframe';

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	protected $base_embed_url = '';

	/**
	 * Constructor.
	 *
	 * @param array $args Height and width for embed.
	 */
	public function __construct( $args = [] ) {
		$this->args = wp_parse_args(
			$args,
			[
				'width'  => $this->DEFAULT_WIDTH,
				'height' => $this->DEFAULT_HEIGHT,
			]
		);
	}

	/**
	 * Sanitize all embeds on the page to be AMP compatible.
	 *
	 * @param Document $dom DOM.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$nodes = $this->get_raw_embed_nodes( $dom );

		foreach ( $nodes as $node ) {
			if ( ! $this->is_raw_embed( $node ) ) {
				continue;
			}
			$this->sanitize_raw_embed( $node );
		}
	}

	/**
	 * Get all raw embeds from the DOM.
	 *
	 * @param Document $dom Document.
	 * @return DOMNodeList A list of DOMElement nodes.
	 */
	protected function get_raw_embed_nodes( Document $dom ) {
		return $dom->xpath->query( sprintf( '//iframe[ starts-with( @src, "%s" ) ]', $this->base_embed_url ) );
	}

	/**
	 * Determine if the node is indeed a raw embed.
	 *
	 * @param DOMElement $node DOM element.
	 * @return bool True if it is a raw embed, false otherwise.
	 */
	protected function is_raw_embed( DOMElement $node ) {
		return $node->parentNode && $this->amp_tag !== $node->parentNode->nodeName;
	}

	/**
	 * Make embed AMP compatible.
	 *
	 * @param DOMElement $node DOM element.
	 */
	abstract protected function sanitize_raw_embed( DOMElement $node );

	/**
	 * Get mapping of AMP component names to AMP script URLs.
	 *
	 * This is normally no longer needed because the whitelist
	 * sanitizer will automatically detect the need for them via
	 * the spec.
	 *
	 * @see AMP_Tag_And_Attribute_Sanitizer::get_scripts()
	 * @return array Scripts.
	 */
	public function get_scripts() {
		return [];
	}

	/**
	 * Get regex pattern for matching HTML attributes from a given tag name.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $html            HTML source haystack.
	 * @param string   $tag_name        Tag name.
	 * @param string[] $attribute_names Attribute names.
	 * @return string[]|null Matched attributes, or null if the element was not matched at all.
	 */
	protected function match_element_attributes( $html, $tag_name, $attribute_names ) {
		$pattern = sprintf(
			'/<%s%s/',
			preg_quote( $tag_name, '/' ),
			implode(
				'',
				array_map(
					function ( $attr_name ) {
						return sprintf( '(?=[^>]*?%1$s="(?P<%1$s>[^"]+)")?', preg_quote( $attr_name, '/' ) );
					},
					$attribute_names
				)
			)
		);
		if ( ! preg_match( $pattern, $html, $matches ) ) {
			return null;
		}
		return wp_array_slice_assoc( $matches, $attribute_names );
	}

	/**
	 * Get all child elements of the specified element.
	 *
	 * @param DOMElement $node Element.
	 * @return DOMElement[] Array of child elements for specified element.
	 */
	protected function get_child_elements( DOMElement $node ) {
		return array_filter(
			iterator_to_array( $node->childNodes ),
			static function ( DOMNode $child ) {
				return $child instanceof DOMElement;
			}
		);
	}

	/**
	 * Replace the node's parent with itself if the parent is a <p> tag, has no attributes and has no other children.
	 * This usually happens while `wpautop()` processes the element.
	 *
	 * @since 1.6
	 *
	 * @param DOMElement $node Node.
	 */
	protected function unwrap_p_element( DOMElement $node ) {
		$parent_element = AMP_DOM_Utils::get_parent_element( $node );

		if ( $parent_element && 'p' === $parent_element->nodeName && false === $parent_element->hasAttributes() ) {
			$child_element_count = count( $this->get_child_elements( $parent_element ) );
			if ( 1 === $child_element_count ) {
				$parent_element->parentNode->replaceChild( $node, $parent_element );
			}
		}
	}

	/**
	 * Removes the node's nearest <script> sibling with a `src` attribute containing the base `src` URL provided.
	 *
	 * @since 1.6
	 *
	 * @param DOMElement $node         The DOMNode to whose sibling is the script to be removed.
	 * @param string     $base_src_url Script URL to match against.
	 * @param string     $content      Text content of node to match against.
	 */
	protected function remove_script_sibling( DOMElement $node, $base_src_url, $content = '' ) {
		$next_element_sibling = $node->nextSibling;

		while ( $next_element_sibling && ! ( $next_element_sibling instanceof DOMElement ) ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( $next_element_sibling instanceof DOMElement && 'p' === $next_element_sibling->nodeName ) {
			$children_elements = array_values( $this->get_child_elements( $next_element_sibling ) );

			if (
				1 === count( $children_elements ) &&
				'script' === $children_elements[0]->nodeName &&
				(
					( $base_src_url && false !== strpos( $children_elements[0]->getAttribute( 'src' ), $base_src_url ) ) ||
					( $content && false !== strpos( $children_elements[0]->textContent, $content ) )
				)
			) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
				return;
			}
		}

		// Handle case where script is immediately following.
		$is_embed_script = (
			$next_element_sibling instanceof DOMElement &&
			'script' === strtolower( $next_element_sibling->nodeName ) &&
			(
				( $base_src_url && false !== strpos( $next_element_sibling->getAttribute( 'src' ), $base_src_url ) ) ||
				( $content && false !== strpos( $next_element_sibling->textContent, $content ) )
			)
		);
		if ( $is_embed_script ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
		}
	}
}
