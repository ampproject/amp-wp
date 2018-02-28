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
	 * @var string
	 */
	const MARKUP_KEY = 'markup';

	/**
	 * Key for the error value in the response.
	 *
	 * @var string
	 */
	const ERROR_KEY = 'has_error';

	/**
	 * Query var that triggers validation.
	 *
	 * @var string
	 */
	const VALIDATION_QUERY_VAR = 'validate';

	/**
	 * Key of the callback to track invalid markup.
	 *
	 * @var string
	 */
	const CALLBACK_KEY = 'remove_invalid_callback';

	/**
	 * The slug of the post type to store AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_validation_error';

	/**
	 * The key in the response for the sources that have invalid output.
	 *
	 * @var string
	 */
	const SOURCES_INVALID_OUTPUT = 'sources_with_invalid_output';

	/**
	 * The meta key for the primary AMP URL where the error occurred.
	 *
	 * @var string
	 */
	const AMP_URL_META = 'amp_url';

	/**
	 * The key of the meta value for the URLs with the validation error.
	 *
	 * @var string
	 */
	const URLS_VALIDATION_ERROR = 'amp_additional_urls_validation_error';

	/**
	 * The nodes that the sanitizer removed.
	 *
	 * @var array[][]
	 */
	public static $removed_nodes = array();

	/**
	 * The sources that have had nodes removed.
	 *
	 * @var array[][] {
	 *     @type string $name The name of the source.
	 *     @type string $type The type of the source.
	 * }
	 */
	public static $sources_removed_nodes = array();

	/**
	 * Add the actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'amp_rest_validation' ) );
		add_action( 'edit_form_top', array( __CLASS__, 'validate_content' ), 10, 2 );
		add_action( 'wp', array( __CLASS__, 'callback_wrappers' ) );
		add_filter( 'amp_content_sanitizers', array( __CLASS__, 'add_validation_callback' ) );
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'activate_plugin', array( __CLASS__, 'validate_home' ) );
		add_action( 'all_admin_notices', array( __CLASS__, 'plugin_notice' ) );
	}

	/**
	 * Tracks when a sanitizer removes a node (element or attribute).
	 *
	 * @param array $removed {
	 *     The data of the removed node.
	 *
	 *     @type DOMElement|DOMNode $node   The removed node.
	 *     @type DOMElement|DOMNode $parent The parent of the removed node.
	 *     @type array[][]          $sources {
	 *           The data of the removed sources (theme, plugin, or mu-plugin).
	 *
	 *           @type string $name The name of the source.
	 *           @type string $type The type of the source.
	 *     }
	 * }
	 * @return void
	 */
	public static function track_removed( $removed ) {
		$source = ! empty( $removed['sources'] ) ? array_pop( $removed['sources'] ) : null;
		if ( isset( $source['name'], $source['type'] ) ) {
			$removed['sources'] = $source;
			$name               = $source['name'];
			$type               = $source['type'];
			if ( ! isset( self::$sources_removed_nodes[ $type ] ) ) {
				self::$sources_removed_nodes[ $type ] = array( $name );
			} elseif ( is_array( self::$sources_removed_nodes[ $type ] ) && ( ! in_array( $name, self::$sources_removed_nodes[ $type ], true ) ) ) {
				self::$sources_removed_nodes[ $type ][] = $name;
			}
		}
		self::$removed_nodes[] = $removed;
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
	 * @return void
	 */
	public static function process_markup( $markup ) {
		if ( ! self::has_cap() ) {
			return;
		}

		AMP_Theme_Support::register_content_embed_handlers();
		remove_filter( 'the_content', 'wpautop' );

		/** This filter is documented in wp-includes/post-template.php */
		$markup = apply_filters( 'the_content', $markup );
		$args   = array(
			'content_max_width' => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
			self::CALLBACK_KEY  => 'AMP_Validation_Utils::track_removed',
		);
		AMP_Content_Sanitizer::sanitize( $markup, amp_get_content_sanitizers(), $args );
	}

	/**
	 * Registers the REST API endpoint for validation.
	 *
	 * @return void
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
		global $wp;
		$response = array();
		if ( isset( $markup ) ) {
			self::process_markup( $markup );
			$response['processed_markup'] = $markup;
		} elseif ( isset( $wp ) ) {
			$response['url'] = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		}
		if ( self::should_validate_front_end() ) {
			$response[ self::SOURCES_INVALID_OUTPUT ] = self::$sources_removed_nodes;
		}

		$removed_elements   = array();
		$removed_attributes = array();
		foreach ( self::$removed_nodes as $removed ) {
			$node = $removed['node'];
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
	 * @return void
	 */
	public static function reset_removed() {
		self::$removed_nodes         = array();
		self::$sources_removed_nodes = array();
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
	 * Checks the AMP validity of the post content.
	 *
	 * If it's not valid AMP,
	 * it displays an error message above the 'Classic' editor.
	 *
	 * @param WP_Post $post The updated post.
	 * @return void
	 */
	public static function validate_content( $post ) {
		if ( ! post_supports_amp( $post ) || ! self::has_cap() ) {
			return;
		}
		AMP_Theme_Support::register_content_embed_handlers();
		/** This filter is documented in wp-includes/post-template.php */
		$filtered_content = apply_filters( 'the_content', $post->post_content, $post->ID );
		$response         = self::get_response( $filtered_content );
		if ( isset( $response[ self::ERROR_KEY ] ) && ( true === $response[ self::ERROR_KEY ] ) ) {
			self::display_error( $response );
		}
	}

	/**
	 * Wraps callbacks in comments, to indicate to the sanitizer which plugin added them.
	 *
	 * Iterates through all of the registered callbacks.
	 * If a callback is from a plugin and outputs markup,
	 * this wraps the markup in comments.
	 * Later, the sanitizer can identify which plugin any illegal markup is from.
	 *
	 * @global array $wp_filter
	 * @return void
	 */
	public static function callback_wrappers() {
		global $wp_filter;
		if ( ! self::should_validate_front_end() ) {
			return;
		}
		$pending_wrap_callbacks = array();
		foreach ( $wp_filter as $filter_tag => $wp_hook ) {
			foreach ( $wp_hook->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$source_data = self::get_source( $callback['function'] );
					if ( isset( $source_data ) ) {
						$pending_wrap_callbacks[ $filter_tag ][] = array_merge(
							$callback,
							$source_data,
							compact( 'priority' )
						);
					}
				}
			}
		}

		// Iterate over hooks to replace after iterating over all to begin with to prevent infinite loop in PHP<=5.4.
		foreach ( $pending_wrap_callbacks as $hook => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				remove_action( $hook, $callback['function'], $callback['priority'] );
				$wrapped_callback = self::wrapped_callback( $callback );
				add_action( $hook, $wrapped_callback, $callback['priority'], $callback['accepted_args'] );
			}
		}
	}

	/**
	 * Gets the plugin or theme of the callback, if one exists.
	 *
	 * @param string|array $callback The callback for which to get the plugin.
	 * @return array|null {
	 *     The source data.
	 *
	 *     @type string $type Source type.
	 *     @type string $name Source name.
	 * }
	 */
	public static function get_source( $callback ) {
		try {
			if ( is_string( $callback ) && is_callable( $callback ) ) {
				// The $callback is a function or static method.
				$exploded_callback = explode( '::', $callback );
				if ( count( $exploded_callback ) > 1 ) {
					$reflection = new ReflectionClass( $exploded_callback[0] );
				} else {
					$reflection = new ReflectionFunction( $callback );
				}
			} elseif ( is_array( $callback ) && isset( $callback[0], $callback[1] ) && method_exists( $callback[0], $callback[1] ) ) {
				// The $callback is a method.
				$reflection = new ReflectionClass( $callback[0] );
			} elseif ( is_object( $callback ) && ( 'Closure' === get_class( $callback ) ) ) {
				$reflection = new ReflectionFunction( $callback );
			}
		} catch ( Exception $e ) {
			return null;
		}

		$file = isset( $reflection ) ? $reflection->getFileName() : null;
		if ( ! isset( $file ) ) {
			return null;
		}

		$closing_pattern = '([^/]+)/:s';
		preg_match( ':' . trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $closing_pattern, $file, $plugin_matches );
		preg_match( ':' . trailingslashit( wp_normalize_path( get_theme_root() ) ) . $closing_pattern, $file, $theme_matches );
		preg_match( ':' . trailingslashit( wp_normalize_path( WPMU_PLUGIN_DIR ) ) . $closing_pattern, $file, $mu_plugin_matches );

		if ( isset( $plugin_matches[1] ) ) {
			$type = 'plugin';
			$name = $plugin_matches[1];
		} elseif ( isset( $theme_matches[1] ) ) {
			$type = 'theme';
			$name = $theme_matches[1];
		} elseif ( isset( $mu_plugin_matches[1] ) ) {
			$type = 'mu-plugin';
			$name = $mu_plugin_matches[1];
		}

		if ( isset( $type, $name ) ) {
			return compact( 'type', 'name' );
		}
		return null;
	}

	/**
	 * Wraps a callback in comments if it outputs markup.
	 *
	 * If the sanitizer removes markup,
	 * this indicates which plugin it was from.
	 * The call_user_func_array() logic is mainly copied from WP_Hook:apply_filters().
	 *
	 * @param array $callback {
	 *     The callback data.
	 *
	 *     @type callable $function
	 *     @type int      $accepted_args
	 *     @type string   $type
	 *     @type string   $source
	 * }
	 * @return closure $wrapped_callback The callback, wrapped in comments.
	 */
	public static function wrapped_callback( $callback ) {
		return function() use ( $callback ) {
			$function      = $callback['function'];
			$accepted_args = $callback['accepted_args'];
			$args          = func_get_args();

			ob_start();
			if ( 0 === $accepted_args ) {
				$result = call_user_func_array( $function, array() );
			} elseif ( $accepted_args >= func_num_args() ) {
				$result = call_user_func_array( $function, $args );
			} else {
				$result = call_user_func_array( $function, array_slice( $args, 0, intval( $accepted_args ) ) );
			}
			$output = ob_get_clean();

			if ( ! empty( $output ) ) {
				printf( '<!--%s:%s-->', esc_attr( $callback['type'] ), esc_attr( $callback['name'] ) );
				echo $output; // WPCS: XSS ok.
				printf( '<!--/%s:%s-->', esc_attr( $callback['type'] ), esc_attr( $callback['name'] ) );
			}
			return $result;
		};
	}

	/**
	 * Displays an error message on /wp-admin/post.php.
	 *
	 * Located at the top of the 'Classic' editor.
	 * States that the content is not valid AMP.
	 *
	 * @param array $response The validation response, an associative array.
	 * @return void
	 */
	public static function display_error( $response ) {
		echo '<div class="notice notice-warning">';
		printf( '<p>%s</p>', esc_html__( 'Warning: There is content which fails AMP validation; it will be stripped when served as AMP.', 'amp' ) );
		$removed_sets = array();
		if ( ! empty( $response['removed_elements'] ) && is_array( $response['removed_elements'] ) ) {
			$removed_sets[] = array(
				'label' => __( 'Invalid elements:', 'amp' ),
				'names' => array_map( 'sanitize_key', $response['removed_elements'] ),
			);
		}
		if ( ! empty( $response['removed_attributes'] ) && is_array( $response['removed_attributes'] ) ) {
			$removed_sets[] = array(
				'label' => __( 'Invalid attributes:', 'amp' ),
				'names' => array_map( 'sanitize_key', $response['removed_attributes'] ),
			);
		}
		foreach ( $removed_sets as $removed_set ) {
			printf( '<p>%s ', esc_html( $removed_set['label'] ) );
			$items = array();
			foreach ( $removed_set['names'] as $name => $count ) {
				if ( 1 === intval( $count ) ) {
					$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
				} else {
					$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
				}
			}
			echo implode( ', ', $items ); // WPCS: XSS OK.
			echo '</p>';
		}
		echo '</div>';
	}

	/**
	 * Whether to validate the front end response.
	 *
	 * @return boolean
	 */
	public static function should_validate_front_end() {
		return ( self::has_cap() && ( isset( $_GET[ self::VALIDATION_QUERY_VAR ] ) ) ); // WPCS: CSRF ok.
	}

	/**
	 * Adds the validation callback if front-end validation is needed.
	 *
	 * @param array $sanitizers The AMP sanitizers.
	 * @return array $sanitizers The filtered AMP sanitizers.
	 */
	public static function add_validation_callback( $sanitizers ) {
		if ( self::should_validate_front_end() ) {
			foreach ( $sanitizers as $sanitizer => $args ) {
				$args[ self::CALLBACK_KEY ] = __CLASS__ . '::track_removed';
				$sanitizers[ $sanitizer ]   = $args;
			}
		}
		return $sanitizers;
	}

	/**
	 * Registers the post type to store the validation errors.
	 *
	 * @return void.
	 */
	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE_SLUG,
			array(
				'labels'   => array(
					'name' => _x( 'AMP Validation Errors', 'post type general name', 'amp' ),
				),
				'supports' => false,
				'public'   => false,
			)
		);
	}

	/**
	 * Stores the validation errors.
	 *
	 * After the preprocessors run, this gets the validation response if the query var is present.
	 * It then stores the response in a custom post type.
	 * If there's already an error post for the URL, but there's no error anymore, it deletes it.
	 *
	 * @return int|null $post_id The post ID of the custom post type used, or null.
	 */
	public static function store_validation_errors() {
		if ( ! self::should_validate_front_end() ) {
			return null;
		}

		$response = self::get_response();
		$url      = isset( $response['url'] ) ? $response['url'] : null;
		unset( $response['url'] );
		$encoded_errors            = wp_json_encode( $response );
		$post_name                 = md5( $encoded_errors );
		$different_post_same_error = get_page_by_path( $post_name, OBJECT, self::POST_TYPE_SLUG );
		$post_args                 = array(
			'post_type'    => self::POST_TYPE_SLUG,
			'post_name'    => $post_name,
			'post_content' => $encoded_errors,
			'post_status'  => 'publish',
		);
		$existing_post_id          = self::existing_post( $url );
		if ( isset( $existing_post_id ) ) {
			// A post for the $url already exists.
			if ( empty( $response[ self::SOURCES_INVALID_OUTPUT ] ) ) {
				wp_delete_post( $existing_post_id, true );
				return null;
			} else {
				wp_insert_post( array_merge(
					array(
						'ID' => $existing_post_id,
					),
					$post_args
				) );
			}
			return $existing_post_id;
		} elseif ( isset( $different_post_same_error->ID ) ) {
			// The error is already stored somewhere, so append it to the post meta.
			$updated_meta = add_post_meta( $different_post_same_error->ID, self::URLS_VALIDATION_ERROR, $url, true );
			if ( ! $updated_meta ) {
				$meta   = get_post_meta( $different_post_same_error->ID, self::URLS_VALIDATION_ERROR, true );
				$meta   = is_array( $meta ) ? $meta : array( $meta );
				$meta[] = $url;
				update_post_meta( $different_post_same_error->ID, self::URLS_VALIDATION_ERROR, $meta );
			}
			return $different_post_same_error->ID;
		} elseif ( ! empty( $response[ self::SOURCES_INVALID_OUTPUT ] ) ) {
			// There are validation issues from a plugin, but no existing post for them, so create one.
			$new_post_id = wp_insert_post(
				array_merge(
					array(
						'meta_input' => array(
							self::AMP_URL_META => $url,
						),
					),
					$post_args
				)
			);
			return $new_post_id;
		}

		return null;
	}

	/**
	 * Gets the existing custom post that stores errors for the $url, if it exists.
	 *
	 * @param string $url The URL of the post.
	 * @return int|null $post_id The post ID of the existing custom post, or null.
	 */
	public static function existing_post( $url ) {
		$query = new WP_Query( array(
			'post_type'  => self::POST_TYPE_SLUG,
			'meta_query' => array(
				array(
					'key'   => self::AMP_URL_META,
					'value' => $url,
				),
			),
		) );

		if ( $query->have_posts() ) {
			$posts = $query->get_posts();
			$post  = reset( $posts );
			return $post->ID;
		}
		return null;
	}

	/**
	 * Validate the home page.
	 *
	 * @return WP_Error|array The response array, or WP_Error.
	 */
	public static function validate_home() {
		return wp_remote_get( add_query_arg(
			self::VALIDATION_QUERY_VAR,
			1,
			get_home_url()
		) );
	}

	/**
	 * On activating a plugin, display a notice if a plugin causes an AMP validation error.
	 *
	 * @return void
	 */
	public static function plugin_notice() {
		global $pagenow;
		if ( ( 'plugins.php' === $pagenow ) && isset( $_GET['activate'] ) && ( 'true' === wp_unslash( $_GET['activate'] ) ) ) { // WPCS: CSRF ok.
			$home_errors = self::existing_post( get_home_url() );
			if ( ! is_int( $home_errors ) ) {
				return;
			}
			$error_post = get_post( $home_errors );
			if ( ! isset( $error_post->post_content ) ) {
				return;
			}
			$errors          = json_decode( $error_post->post_content, true );
			$invalid_plugins = isset( $errors[ self::SOURCES_INVALID_OUTPUT ]['plugin'] ) ? $errors[ self::SOURCES_INVALID_OUTPUT ]['plugin'] : null;
			if ( isset( $invalid_plugins ) ) {
				echo '<div class="notice notice-warning"><p>';
				$reported_plugins = array();
				foreach ( $invalid_plugins as $plugin ) {
					$reported_plugins[] = sprintf( '<code>%s</code>', esc_html( $plugin ) );
				}
				esc_html_e( 'Warning: the following plugins are incompatible with AMP: ', 'amp' );
				echo implode( ', ', $reported_plugins ); // WPCS: XSS ok.
				echo '</p></div>';
			}
		}
	}

}
