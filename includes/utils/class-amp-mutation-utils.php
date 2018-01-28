<?php
/**
 * Class AMP_Mutation_Utils
 *
 * @package AMP
 */

/**
 * Class AMP_Mutation_Utils
 *
 * @since 0.7
 */
class AMP_Mutation_Utils {

	/**
	 * The argument if an attribute was removed.
	 *
	 * @var array.
	 */
	const ATTRIBUTE_REMOVED = 'removed_attr';

	/**
	 * The argument if a node was removed.
	 *
	 * @var array.
	 */
	const NODE_REMOVED = 'removed';

	/**
	 * The attributes that the sanitizer removed.
	 *
	 * @var array.
	 */
	public static $removed_attributes;

	/**
	 * The nodes that the sanitizer removed.
	 *
	 * @var array.
	 */
	public static $removed_nodes;

	/**
	 * Tracks when a sanitizer removes an attribute or node.
	 *
	 * @param DOMNode|DOMElement $node The node in which there was a removal.
	 * @param string             $removal_type The removal: 'removed_attr' for an attribute, or 'removed' for a node or element.
	 * @param string             $attr_name The name of the attribute removed (optional).
	 * @return void.
	 */
	public static function track_removed( $node, $removal_type, $attr_name = null ) {
		if ( ( self::ATTRIBUTE_REMOVED === $removal_type ) && isset( $node->nodeName, $attr_name ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			self::$removed_attributes[] = array(
				$node->nodeName => $attr_name, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			);
		} elseif ( self::NODE_REMOVED === $removal_type ) {
			self::$removed_nodes[] = $node;
		}
	}

	/**
	 * Gets whether a node was removed in a sanitizer.
	 *
	 * @return boolean.
	 */
	public static function was_node_removed() {
		return ! empty( self::$removed_nodes );
	}

	/**
	 * Processes markup, to determine AMP validity.
	 *
	 * Passes $markup through the AMP sanitizers.
	 * Also passes a 'mutation_callback' to keep track of stripped attributes and nodes.
	 *
	 * @param string $markup The markup to process.
	 * @return void.
	 */
	public static function process_markup( $markup ) {
		$args = array(
			'content_max_width' => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
			'mutation_callback' => 'AMP_Mutation_Utils::track_removed',
		);
		AMP_Content_Sanitizer::sanitize( $markup, amp_get_content_sanitizers(), $args );
	}

}
