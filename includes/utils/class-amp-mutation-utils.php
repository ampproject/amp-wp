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
	 * Key for the markup value in the REST API endpoint.
	 *
	 * @var string.
	 */
	const MARKUP_KEY = 'markup';

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

	/**
	 * Registers the REST API endpoint for validation.
	 *
	 * @return void.
	 */
	public static function amp_rest_validation() {
		register_rest_route( 'amp-wp/v1', '/validate', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'validate_markup' ),
			'args'                => array(
				self::MARKUP_KEY => array(
					'validate_callback' => array( __CLASS__, 'validate_arg' ),
				),
			),
			'permission_callback' => array( __CLASS__, 'permission' ),
		) );
	}

	/**
	 * The permission callback for the REST request.
	 *
	 * @return boolean $has_permission Whether the current user has the permission.
	 */
	public static function permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Validate the markup passed to the REST API.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return array|WP_Error.
	 */
	public static function validate_markup( WP_REST_Request $request ) {
		$json = $request->get_json_params();
		if ( empty( $json[ self::MARKUP_KEY ] ) ) {
			return new WP_Error( 'no_markup', 'No markup passed to validator', array(
				'status' => 404,
			) );
		}

		self::process_markup( $json[ self::MARKUP_KEY ] );
		$response = array(
			'is_error' => self::was_node_removed(),
		);
		self::reset_removed();

		return $response;
	}

	/**
	 * Reset the stored removed nodes and attributes.
	 *
	 * After testing if the markup is valid,
	 * these static values will remain.
	 * So reset them in case another test is needed.
	 *
	 * @return void.
	 */
	public static function reset_removed() {
		self::$removed_nodes      = null;
		self::$removed_attributes = null;
	}

	/**
	 * Validate the argument in the REST API request.
	 *
	 * It would be ideal to simply pass 'is_string' in register_rest_route().
	 * But it always returned false.
	 *
	 * @param mixed $arg The argument to validate.
	 * @return boolean $is_valid Whether the argument is valid.
	 */
	public static function validate_arg( $arg ) {
		return is_string( $arg );
	}

}
