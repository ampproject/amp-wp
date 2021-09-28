<?php
/**
 * Class AMP_Base_Embed_Handler
 *
 * Used by some children.
 *
 * @package  AMP
 */

use AmpProject\Tag;

/**
 * Class AMP_Base_Embed_Handler
 *
 * @since 0.2
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
	 * Registers embed.
	 */
	abstract public function register_embed();

	/**
	 * Unregisters embed.
	 */
	abstract public function unregister_embed();

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
	 * Get mapping of AMP component names to AMP script URLs.
	 *
	 * This is normally no longer needed because the validating
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
					static function ( $attr_name ) {
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
	 * @since 2.0.6
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
	 * Replace an element's parent with itself if the parent is a <p> tag which has no attributes and has no other children.
	 *
	 * This usually happens while `wpautop()` processes the element.
	 *
	 * @since 2.0.6
	 * @see AMP_Tag_And_Attribute_Sanitizer::remove_node()
	 *
	 * @param DOMElement $node Node.
	 */
	protected function unwrap_p_element( DOMElement $node ) {
		$parent_node = $node->parentNode;
		if (
			$parent_node instanceof DOMElement
			&&
			'p' === $parent_node->tagName
			&&
			false === $parent_node->hasAttributes()
			&&
			1 === count( $this->get_child_elements( $parent_node ) )
		) {
			$parent_node->parentNode->replaceChild( $node, $parent_node );
		}
	}

	/**
	 * Removes the node's nearest `<script>` sibling with a `src` attribute containing the base `src` URL provided.
	 *
	 * @since 2.1
	 *
	 * @param DOMElement $node           The DOMNode to whose sibling is the script to be removed.
	 * @param callable   $match_callback Callback which is passed the script element to determine if it is a match.
	 */
	protected function maybe_remove_script_sibling( DOMElement $node, callable $match_callback ) {
		$next_element_sibling = $node->nextSibling;
		while ( $next_element_sibling && ! $next_element_sibling instanceof DOMElement ) {
			$next_element_sibling = $next_element_sibling->nextSibling;
		}
		if ( ! $next_element_sibling instanceof DOMElement ) {
			return;
		}

		// Handle case where script is immediately following.
		if ( Tag::SCRIPT === $next_element_sibling->tagName && $match_callback( $next_element_sibling ) ) {
			$next_element_sibling->parentNode->removeChild( $next_element_sibling );
			return;
		}

		// Handle case where script is wrapped in paragraph by wpautop.
		if ( 'p' === $next_element_sibling->tagName ) {
			/** @var DOMElement[] $children_elements */
			$children_elements = array_values(
				array_filter(
					iterator_to_array( $next_element_sibling->childNodes ),
					static function ( DOMNode $child ) {
						return $child instanceof DOMElement;
					}
				)
			);

			if (
				1 === count( $children_elements )
				&&
				Tag::SCRIPT === $children_elements[0]->tagName
				&&
				$match_callback( $children_elements[0] )
			) {
				$next_element_sibling->parentNode->removeChild( $next_element_sibling );
			}
		}
	}
}
