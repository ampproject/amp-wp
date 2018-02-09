<?php
/**
 * Class AMP_Validation_Utils
 *
 * @package AMP
 */

/**
 * Class AMP_Validation_Utils
 *
 * @since 0.7
 */
class AMP_Validation_Utils {

	/**
	 * Key for the markup value in the REST API endpoint.
	 *
	 * @var string.
	 */
	const MARKUP_KEY = 'markup';

	/**
	 * Key for the error value in the response.
	 *
	 * @var string.
	 */
	const ERROR_KEY = 'has_error';

	/**
	 * Key of the AMP error query var.
	 *
	 * @var string.
	 */
	const ERROR_QUERY_KEY = 'amp_error';

	/**
	 * Query arg value if there is an AMP error in the post content.
	 *
	 * @var string.
	 */
	const ERROR_QUERY_VALUE = '1';

	/**
	 * Nonce name for the editor error message.
	 *
	 * @var string.
	 */
	const ERROR_NONCE = 'amp_nonce';

	/**
	 * Nonce action for displaying the invalid AMP message.
	 *
	 * @var string.
	 */
	const ERROR_NONCE_ACTION = 'invalid_amp_message';

	/**
	 * The nodes that the sanitizer removed.
	 *
	 * @var DOMNode[]
	 */
	public static $removed_nodes = array();

	/**
	 * Add the actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'amp_rest_validation' ) );
		add_action( 'save_post', array( __CLASS__, 'validate_content' ), 10, 2 );
		add_action( 'edit_form_top', array( __CLASS__, 'display_error' ) );
	}

	/**
	 * Tracks when a sanitizer removes an node (element or attribute).
	 *
	 * @param DOMNode $node The node which was removed.
	 * @return void
	 */
	public static function track_removed( $node ) {
		self::$removed_nodes[] = $node;
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
	 * Also passes a 'remove_invalid_callback' to keep track of stripped attributes and nodes.
	 *
	 * @param string $markup The markup to process.
	 * @return void.
	 */
	public static function process_markup( $markup ) {
		$args = array(
			'content_max_width' => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
		);
		if ( self::is_authorized() ) {
			$args['remove_invalid_callback'] = 'AMP_Validation_Utils::track_removed';
		}
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
			'permission_callback' => array( __CLASS__, 'has_cap' ),
		) );
	}

	/**
	 * Whether the user has the required capability.
	 *
	 * Checks for permissions before validating.
	 * Also serves as the permission callback for REST requests.
	 *
	 * @return boolean $has_cap Whether the current user has the capability.
	 */
	public static function has_cap() {
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

		return self::get_response( $json[ self::MARKUP_KEY ] );
	}

	/**
	 * Gets the AMP validation response.
	 *
	 * If $markup isn't passed,
	 * It will return the validation errors the sanitizers found in rendering the page.
	 *
	 * @param string $markup   To validate for AMP compatibility (optional).
	 * @return array $response The AMP validity of the markup.
	 */
	public static function get_response( $markup = null ) {
		$response = array();
		if ( isset( $markup ) ) {
			self::process_markup( $markup );
			$response['processed_markup'] = $markup;
		}

		$removed_elements   = array();
		$removed_attributes = array();
		foreach ( self::$removed_nodes as $node ) {
			if ( $node instanceof DOMAttr ) {
				if ( ! isset( $removed_attributes[ $node->nodeName ] ) ) {
					$removed_attributes[ $node->nodeName ] = 1;
				} else {
					$removed_attributes[ $node->nodeName ]++;
				}
			} elseif ( $node instanceof DOMElement ) {
				if ( ! isset( $removed_elements[ $node->nodeName ] ) ) {
					$removed_elements[ $node->nodeName ] = 1;
				} else {
					$removed_elements[ $node->nodeName ]++;
				}
			}
		}

		$response = array_merge(
			array(
				self::ERROR_KEY => self::was_node_removed(),
			),
			compact(
				'removed_elements',
				'removed_attributes'
			),
		$response );
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
		self::$removed_nodes = array();
	}

	/**
	 * Validate the argument in the REST API request.
	 *
	 * It would be ideal to simply pass 'is_string' in register_rest_route().
	 * But it always returned false.
	 *
	 * @param mixed $arg      The argument to validate.
	 * @return boolean $is_valid Whether the argument is valid.
	 */
	public static function validate_arg( $arg ) {
		return is_string( $arg );
	}

	/**
	 * On updating a post, this checks the AMP validity of the content.
	 *
	 * If it's not valid AMP, it adds a query arg to the redirect URL.
	 * This will cause an error message to appear above the 'Classic' editor.
	 *
	 * @param integer $post_id The ID of the updated post.
	 * @param WP_Post $post    The updated post.
	 * @return void.
	 */
	public static function validate_content( $post_id, $post ) {
		unset( $post_id );
		if ( ! post_supports_amp( $post ) || ! self::is_authorized() ) {
			return;
		}
		/** This filter is documented in wp-includes/post-template.php */
		$filtered_content = apply_filters( 'the_content', $post->post_content, $post->ID );
		$response         = self::get_response( $filtered_content );
		if ( isset( $response[ self::ERROR_KEY ] ) && ( true === $response[ self::ERROR_KEY ] ) ) {
			add_filter( 'redirect_post_location', function( $location ) use ( $response ) {
				$location = AMP_Validation_Utils::error_message( $location );
				$location = add_query_arg(
					array(
						'removed_elements'   => array_keys( $response['removed_elements'] ),
						'removed_attributes' => array_keys( $response['removed_attributes'] ),
					),
					$location
				);
				return $location;
			} );
		}
	}

	/**
	 * Whether the current user is authorized.
	 *
	 * This checks that the user has a certain capability and the nonce is valid.
	 * It will only return true when updating the post on:
	 * wp-admin/post.php
	 * Avoids using check_admin_referer().
	 * This function might be called in different places,
	 * and it can't cause it to die() if the nonce is invalid.
	 *
	 * @return boolean $is_valid True if the nonce is valid.
	 */
	public static function is_authorized() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // WPCS: CSRF ok.
		return ( self::has_cap() && ( false !== wp_verify_nonce( $nonce, 'update-post_' . get_the_ID() ) ) );
	}

	/**
	 * Adds an error message to the URL if it's not valid AMP.
	 *
	 * When redirecting after saving a post, the content was validated for AMP compliance.
	 * If it wasn't valid AMP, this will add a query arg to the URL.
	 * And an error message will display on /wp-admin/post.php.
	 *
	 * @param string $url The URL of the redirect.
	 * @return string $url The filtered URL, including the AMP error message query var.
	 */
	public static function error_message( $url ) {
		$args = array(
			self::ERROR_QUERY_KEY => self::ERROR_QUERY_VALUE,
			self::ERROR_NONCE     => wp_create_nonce( self::ERROR_NONCE_ACTION ),
		);
		return add_query_arg( $args, $url );
	}

	/**
	 * Displays an error message on /wp-admin/post.php if the saved content is not valid AMP.
	 *
	 * Use $_GET, as get_query_var won't return the value.
	 * This displays at the top of the 'Classic' editor.
	 *
	 * @return void.
	 */
	public static function display_error() {
		if ( ! isset( $_GET[ self::ERROR_QUERY_KEY ] ) ) {
			return;
		}
		check_admin_referer( self::ERROR_NONCE_ACTION, self::ERROR_NONCE );
		$error = isset( $_GET[ self::ERROR_QUERY_KEY ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::ERROR_QUERY_KEY ] ) ) : ''; // WPCS: CSRF ok.
		if ( self::ERROR_QUERY_VALUE === $error ) {
			echo '<div class="notice notice-warning">';
			printf( '<p>%s</p>', esc_html__( 'Warning: There is content which fails AMP validation; it will be stripped when served as AMP.', 'amp' ) );
			if ( ! empty( $_GET['removed_elements'] ) && is_array( $_GET['removed_elements'] ) ) {
				printf(
					'<p>%s %s</p>',
					esc_html__( 'Invalid elements:', 'amp' ),
					'<code>' . implode( '</code>, <code>', array_map( 'esc_html', $_GET['removed_elements'] ) ) . '</code>'
				);
			}
			if ( ! empty( $_GET['removed_attributes'] ) ) {
				printf(
					'<p>%s %s</p>',
					esc_html__( 'Invalid attributes:', 'amp' ),
					'<code>' . implode( '</code>, <code>', array_map( 'esc_html', $_GET['removed_attributes'] ) ) . '</code>'
				);
			}
			echo '</div>';
		}
	}

}
