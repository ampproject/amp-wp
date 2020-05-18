<?php
/**
 * Class AMP_Base_Embed_Handler
 *
 * Used by some children.
 *
 * @package  AMP
 */

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
	 * Replace the node's parent with itself if the parent is a <p> tag, has no attributes and has no other children.
	 * This usually happens while `wpautop()` processes the element.
	 *
	 * @param DOMElement $node Node.
	 */
	protected function maybe_unwrap_p_element( DOMElement $node ) {
		$parent_element = AMP_DOM_Utils::get_parent_element( $node );

		if ( $parent_element && 'p' === $parent_element->nodeName && false === $parent_element->hasAttributes() ) {
			$child_element_count = array_sum(
				array_map(
					static function ( DOMNode $child ) {
						return $child instanceof DOMElement ? 1 : 0;
					},
					iterator_to_array( $node->childNodes )
				)
			);
			if ( 1 === $child_element_count ) {
				$parent_element->parentNode->replaceChild( $node, $parent_element );
			}
		}
	}
}
