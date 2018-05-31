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
	 * Query var that triggers validation.
	 *
	 * @var string
	 */
	const VALIDATE_QUERY_VAR = 'amp_validate';

	/**
	 * Query var that enables validation debug mode, to disable removal of invalid elements/attributes.
	 *
	 * @var string
	 */
	const DEBUG_QUERY_VAR = 'amp_debug';

	/**
	 * Query var for cache-busting.
	 *
	 * @var string
	 */
	const CACHE_BUST_QUERY_VAR = 'amp_cache_bust';

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
	 * Validation code for an invalid element.
	 *
	 * @var string
	 */
	const INVALID_ELEMENT_CODE = 'invalid_element';

	/**
	 * Validation code for an invalid attribute.
	 *
	 * @var string
	 */
	const INVALID_ATTRIBUTE_CODE = 'invalid_attribute';

	/**
	 * Validation code for when script is enqueued (which is not allowed).
	 *
	 * @var string
	 */
	const ENQUEUED_SCRIPT_CODE = 'enqueued_script';

	/**
	 * The meta key for the AMP URL where the error occurred.
	 *
	 * @var string
	 */
	const AMP_URL_META = 'amp_url';

	/**
	 * The key for removed elements.
	 *
	 * @var string
	 */
	const REMOVED_ELEMENTS = 'removed_elements';

	/**
	 * The key for removed attributes.
	 *
	 * @var string
	 */
	const REMOVED_ATTRIBUTES = 'removed_attributes';

	/**
	 * The key for removed sources.
	 *
	 * @var string
	 */
	const REMOVED_SOURCES = 'removed_sources';

	/**
	 * The action to recheck URLs for AMP validity.
	 *
	 * @var string
	 */
	const RECHECK_ACTION = 'amp_recheck';

	/**
	 * The query arg for whether there are remaining errors after rechecking URLs.
	 *
	 * @var string
	 */
	const REMAINING_ERRORS = 'amp_remaining_errors';

	/**
	 * The query arg for the number of URLs tested.
	 *
	 * @var string
	 */
	const URLS_TESTED = 'amp_urls_tested';

	/**
	 * The nonce action for rechecking a URL.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'amp_recheck_';

	/**
	 * Transient key to store validation errors when activating a plugin.
	 *
	 * @var string
	 */
	const PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY = 'amp_plugin_activation_validation_errors';

	/**
	 * The name of the side meta box on the CPT post.php page.
	 *
	 * @var string
	 */
	const STATUS_META_BOX = 'amp_validation_status';

	/**
	 * The name of the side meta box on the CPT post.php page.
	 *
	 * @var string
	 */
	const VALIDATION_ERRORS_META_BOX = 'amp_validation_errors';

	/**
	 * The errors encountered when validating.
	 *
	 * @var array[][] {
	 *     @type string  $code        Error code.
	 *     @type string  $node_name   Name of removed node.
	 *     @type string  $parent_name Name of parent node.
	 * }
	 */
	public static $validation_errors = array();

	/**
	 * Sources that enqueue each script.
	 *
	 * @var array
	 */
	public static $enqueued_script_sources = array();

	/**
	 * Sources that enqueue each style.
	 *
	 * @var array
	 */
	public static $enqueued_style_sources = array();

	/**
	 * Post IDs for posts that have been updated which need to be re-validated.
	 *
	 * @var int[]
	 */
	public static $posts_pending_frontend_validation = array();

	/**
	 * Current sources gathered for a given hook currently being run.
	 *
	 * @see AMP_Validation_Utils::wrap_hook_callbacks()
	 * @see AMP_Validation_Utils::decorate_filter_source()
	 * @var array[]
	 */
	protected static $current_hook_source_stack = array();

	/**
	 * Hook source stack.
	 *
	 * This has to be public for the sake of PHP 5.3.
	 *
	 * @since 0.7
	 * @var array[]
	 */
	public static $hook_source_stack = array();

	/**
	 * Add the actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'dashboard_glance_items', array( __CLASS__, 'filter_dashboard_glance_items' ) );
		add_action( 'rightnow_end', array( __CLASS__, 'print_dashboard_glance_styles' ) );
		add_action( 'save_post', array( __CLASS__, 'handle_save_post_prompting_validation' ), 10, 2 );
		add_action( 'edit_form_top', array( __CLASS__, 'print_edit_form_validation_status' ), 10, 2 );
		add_action( 'all_admin_notices', array( __CLASS__, 'plugin_notice' ) );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( __CLASS__, 'add_post_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'output_custom_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'add_bulk_action' ), 10, 2 );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'handle_bulk_action' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'remaining_error_notice' ) );
		add_action( 'post_action_' . self::RECHECK_ACTION, array( __CLASS__, 'handle_inline_recheck' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_publish_meta_box' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_validation_status_count' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

		// Actions and filters involved in validation.
		add_action( 'activate_plugin', function() {
			if ( ! has_action( 'shutdown', array( __CLASS__, 'validate_after_plugin_activation' ) ) ) {
				add_action( 'shutdown', array( __CLASS__, 'validate_after_plugin_activation' ) ); // Shutdown so all plugins will have been activated.
			}
		} );
	}

	/**
	 * Add count of how many validation error posts there are to the admin menu.
	 */
	public static function add_admin_menu_validation_status_count() {
		global $submenu;
		if ( ! isset( $submenu[ AMP_Options_Manager::OPTION_NAME ] ) ) {
			return;
		}
		$count = wp_count_posts( self::POST_TYPE_SLUG );
		if ( empty( $count->publish ) ) {
			return;
		}
		foreach ( $submenu[ AMP_Options_Manager::OPTION_NAME ] as &$submenu_item ) {
			if ( 'edit.php?post_type=' . self::POST_TYPE_SLUG === $submenu_item[2] ) {
				$submenu_item[0] .= ' <span class="awaiting-mod"><span class="pending-count">' . esc_html( $count->publish ) . '</span></span>';
				break;
			}
		}
	}

	/**
	 * Filter At a Glance items add AMP Validation Errors.
	 *
	 * @param array $items At a glance items.
	 * @return array Items.
	 */
	public static function filter_dashboard_glance_items( $items ) {
		$counts = wp_count_posts( self::POST_TYPE_SLUG );
		if ( ! empty( $counts->publish ) ) {
			$items[] = sprintf(
				'<a class="amp-validation-errors" href="%s">%s</a>',
				esc_url( admin_url( 'edit.php?post_type=' . self::POST_TYPE_SLUG ) ),
				esc_html( sprintf(
					/* translators: %s is the validation error count */
					_n( '%s AMP Validation Error', '%s AMP Validation Errors', $counts->publish, 'amp' ),
					$counts->publish
				) )
			);
		}
		return $items;
	}

	/**
	 * Print styles for the At a Glance widget.
	 */
	public static function print_dashboard_glance_styles() {
		?>
		<style>
			#dashboard_right_now .amp-validation-errors {
				color: #a00;
			}
			#dashboard_right_now .amp-validation-errors:before {
				content: "\f534";
			}
			#dashboard_right_now .amp-validation-errors:hover {
				color: #dc3232;
				border: none;
			}
		</style>
		<?php
	}

	/**
	 * Add hooks for doing validation during preprocessing/sanitizing.
	 */
	public static function add_validation_hooks() {
		self::wrap_widget_callbacks();

		add_action( 'all', array( __CLASS__, 'wrap_hook_callbacks' ) );
		$wrapped_filters = array( 'the_content', 'the_excerpt' );
		foreach ( $wrapped_filters as $wrapped_filter ) {
			add_filter( $wrapped_filter, array( __CLASS__, 'decorate_filter_source' ), PHP_INT_MAX );
		}

		add_filter( 'do_shortcode_tag', array( __CLASS__, 'decorate_shortcode_source' ), -1, 2 );
		add_filter( 'amp_content_sanitizers', array( __CLASS__, 'add_validation_callback' ) );
	}

	/**
	 * Handle save_post action to queue re-validation of the post on the frontend.
	 *
	 * @see AMP_Validation_Utils::validate_queued_posts_on_frontend()
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function handle_save_post_prompting_validation( $post_id, $post ) {
		$should_validate_post = (
			is_post_type_viewable( $post->post_type )
			&&
			! wp_is_post_autosave( $post )
			&&
			! wp_is_post_revision( $post )
		);
		if ( $should_validate_post ) {
			self::$posts_pending_frontend_validation[] = $post_id;

			// The reason for shutdown is to ensure that all postmeta changes have been saved, including whether AMP is enabled.
			if ( ! has_action( 'shutdown', array( __CLASS__, 'validate_queued_posts_on_frontend' ) ) ) {
				add_action( 'shutdown', array( __CLASS__, 'validate_queued_posts_on_frontend' ) );
			}
		}
	}

	/**
	 * Validate the posts pending frontend validation.
	 *
	 * @see AMP_Validation_Utils::handle_save_post_prompting_validation()
	 */
	public static function validate_queued_posts_on_frontend() {
		$posts = array_filter(
			array_map( 'get_post', self::$posts_pending_frontend_validation ),
			function( $post ) {
				return $post && post_supports_amp( $post ) && 'trash' !== $post->post_status;
			}
		);

		// @todo Only validate the first and then queue the rest in WP Cron?
		foreach ( $posts as $post ) {
			$url = amp_get_permalink( $post->ID );
			if ( ! $url ) {
				continue;
			}

			$validation_errors = self::validate_url( $url );
			if ( is_wp_error( $validation_errors ) ) {
				continue;
			}

			self::store_validation_errors( $validation_errors, $url );
		}
	}

	/**
	 * Processes markup, to determine AMP validity.
	 *
	 * Passes $markup through the AMP sanitizers.
	 * Also passes a 'validation_error_callback' to keep track of stripped attributes and nodes.
	 *
	 * @param string $markup The markup to process.
	 * @return string Sanitized markup.
	 */
	public static function process_markup( $markup ) {
		AMP_Theme_Support::register_content_embed_handlers();

		/** This filter is documented in wp-includes/post-template.php */
		$markup = apply_filters( 'the_content', $markup );
		$args   = array(
			'content_max_width'         => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
			'validation_error_callback' => 'AMP_Validation_Utils::add_validation_error',
		);

		$results = AMP_Content_Sanitizer::sanitize( $markup, amp_get_content_sanitizers(), $args );
		return $results[0];
	}

	/**
	 * Whether the user has the required capability.
	 *
	 * Checks for permissions before validating.
	 *
	 * @return boolean $has_cap Whether the current user has the capability.
	 */
	public static function has_cap() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Add validation error.
	 *
	 * @param array $data {
	 *     Data.
	 *
	 *     @type string $code Error code.
	 *     @type DOMElement|DOMNode $node The removed node.
	 * }
	 */
	public static function add_validation_error( array $data ) {
		$node = null;

		if ( isset( $data['node'] ) && $data['node'] instanceof DOMNode ) {
			$node = $data['node'];
			unset( $data['node'] );
			$data['node_name'] = $node->nodeName;
			$data['sources']   = self::locate_sources( $node );
			if ( $node->parentNode ) {
				$data['parent_name'] = $node->parentNode->nodeName;
			}
		}

		if ( $node instanceof DOMElement ) {
			if ( ! isset( $data['code'] ) ) {
				$data['code'] = self::INVALID_ELEMENT_CODE;
			}
			$data['node_attributes'] = array();
			foreach ( $node->attributes as $attribute ) {
				$data['node_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
			}

			$is_enqueued_link = (
				'link' === $node->nodeName
				&&
				preg_match( '/(?P<handle>.+)-css$/', (string) $node->getAttribute( 'id' ), $matches )
				&&
				isset( self::$enqueued_style_sources[ $matches['handle'] ] )
			);
			if ( $is_enqueued_link ) {
				$data['sources'] = self::$enqueued_style_sources[ $matches['handle'] ];
			}
		} elseif ( $node instanceof DOMAttr ) {
			if ( ! isset( $data['code'] ) ) {
				$data['code'] = self::INVALID_ATTRIBUTE_CODE;
			}
			$data['element_attributes'] = array();
			if ( $node->parentNode && $node->parentNode->hasAttributes() ) {
				foreach ( $node->parentNode->attributes as $attribute ) {
					$data['element_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
				}
			}
		}

		if ( ! isset( $data['code'] ) ) {
			$data['code'] = 'unknown';
		}

		self::$validation_errors[] = $data;
	}

	/**
	 * Gets the AMP validation response.
	 *
	 * Returns the current validation errors the sanitizers found in rendering the page.
	 *
	 * @param array $validation_errors Validation errors.
	 * @return array The AMP validity of the markup.
	 */
	public static function summarize_validation_errors( $validation_errors ) {
		$results            = array();
		$removed_elements   = array();
		$removed_attributes = array();
		$invalid_sources    = array();
		foreach ( $validation_errors as $validation_error ) {
			$code = isset( $validation_error['code'] ) ? $validation_error['code'] : null;

			if ( self::INVALID_ELEMENT_CODE === $code ) {
				if ( ! isset( $removed_elements[ $validation_error['node_name'] ] ) ) {
					$removed_elements[ $validation_error['node_name'] ] = 0;
				}
				$removed_elements[ $validation_error['node_name'] ] += 1;
			} elseif ( self::INVALID_ATTRIBUTE_CODE === $code ) {
				if ( ! isset( $removed_attributes[ $validation_error['node_name'] ] ) ) {
					$removed_attributes[ $validation_error['node_name'] ] = 0;
				}
				$removed_attributes[ $validation_error['node_name'] ] += 1;
			}

			if ( ! empty( $validation_error['sources'] ) ) {
				$source = array_pop( $validation_error['sources'] );

				if ( isset( $source['type'], $source['name'] ) ) {
					$invalid_sources[ $source['type'] ][] = $source['name'];
				}
			}
		}

		$results = array_merge(
			array(
				self::SOURCES_INVALID_OUTPUT => $invalid_sources,
			),
			compact(
				'removed_elements',
				'removed_attributes'
			),
		$results );

		return $results;
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
	public static function reset_validation_results() {
		self::$validation_errors       = array();
		self::$enqueued_style_sources  = array();
		self::$enqueued_script_sources = array();
	}

	/**
	 * Checks the AMP validity of the post content.
	 *
	 * If it's not valid AMP, it displays an error message above the 'Classic' editor.
	 *
	 * @param WP_Post $post The updated post.
	 * @return void
	 */
	public static function print_edit_form_validation_status( $post ) {
		if ( ! post_supports_amp( $post ) || ! self::has_cap() ) {
			return;
		}

		// Skip if the post type is not viewable on the frontend, since we need a permalink to validate.
		if ( ! is_post_type_viewable( $post->post_type ) ) {
			return;
		}

		$url                    = amp_get_permalink( $post->ID );
		$validation_status_post = self::get_validation_status_post( $url );

		// No validation status exists yet, so there is nothing to show.
		if ( ! $validation_status_post ) {
			return;
		}

		$validation_errors = json_decode( $validation_status_post->post_content, true );

		// No validation errors so abort.
		if ( empty( $validation_errors ) || ! is_array( $validation_errors ) ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p>';
		esc_html_e( 'Warning: There is content which fails AMP validation; it will be stripped when served as AMP.', 'amp' );
		echo sprintf(
			' <a href="%s" target="_blank">%s</a>',
			esc_url( get_edit_post_link( $validation_status_post ) ),
			esc_html__( 'Details', 'amp' )
		);
		echo ' | ';
		echo sprintf(
			' <a href="%s" aria-label="%s" target="_blank">%s</a>',
			esc_url( self::get_debug_url( $url ) ),
			esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
			esc_html__( 'Debug', 'amp' )
		);
		echo '</p>';

		$results      = self::summarize_validation_errors( array_unique( $validation_errors, SORT_REGULAR ) );
		$removed_sets = array();
		if ( ! empty( $results[ self::REMOVED_ELEMENTS ] ) && is_array( $results[ self::REMOVED_ELEMENTS ] ) ) {
			$removed_sets[] = array(
				'label' => __( 'Invalid elements:', 'amp' ),
				'names' => array_map( 'sanitize_key', $results[ self::REMOVED_ELEMENTS ] ),
			);
		}
		if ( ! empty( $results[ self::REMOVED_ATTRIBUTES ] ) && is_array( $results[ self::REMOVED_ATTRIBUTES ] ) ) {
			$removed_sets[] = array(
				'label' => __( 'Invalid attributes:', 'amp' ),
				'names' => array_map( 'sanitize_key', $results[ self::REMOVED_ATTRIBUTES ] ),
			);
		}
		// @todo There are other kinds of errors other than REMOVED_ELEMENTS and REMOVED_ATTRIBUTES.
		foreach ( $removed_sets as $removed_set ) {
			printf( '<p>%s ', esc_html( $removed_set['label'] ) );
			self::output_removed_set( $removed_set['names'] );
			echo '</p>';
		}

		echo '</div>';
	}

	/**
	 * Get source start comment.
	 *
	 * @param array $source   Source data.
	 * @param bool  $is_start Whether the comment is the start or end.
	 * @return string HTML Comment.
	 */
	public static function get_source_comment( array $source, $is_start = true ) {
		unset( $source['reflection'] );
		return sprintf(
			'<!--%samp-source-stack %s-->',
			$is_start ? '' : '/',
			str_replace( '--', '', wp_json_encode( $source ) )
		);
	}

	/**
	 * Parse source comment.
	 *
	 * @param DOMComment $comment Comment.
	 * @return array|null Parsed source or null if not a source comment.
	 */
	public static function parse_source_comment( DOMComment $comment ) {
		if ( ! preg_match( '#^\s*(?P<closing>/)?amp-source-stack\s+(?P<args>{.+})\s*$#s', $comment->nodeValue, $matches ) ) {
			return null;
		}

		$source  = json_decode( $matches['args'], true );
		$closing = ! empty( $matches['closing'] );

		return compact( 'source', 'closing' );
	}

	/**
	 * Walk back tree to find the open sources.
	 *
	 * @param DOMNode $node Node to look for.
	 * @return array[][] {
	 *       The data of the removed sources (theme, plugin, or mu-plugin).
	 *
	 *       @type string $name The name of the source.
	 *       @type string $type The type of the source.
	 * }
	 */
	public static function locate_sources( DOMNode $node ) {
		$xpath    = new DOMXPath( $node->ownerDocument );
		$comments = $xpath->query( 'preceding::comment()[ starts-with( ., "amp-source-stack" ) or starts-with( ., "/amp-source-stack" ) ]', $node );
		$sources  = array();
		foreach ( $comments as $comment ) {
			$parsed_comment = self::parse_source_comment( $comment );
			if ( ! $parsed_comment ) {
				continue;
			}
			if ( $parsed_comment['closing'] ) {
				array_pop( $sources );
			} else {
				$sources[] = $parsed_comment['source'];
			}
		}
		return $sources;
	}

	/**
	 * Remove source comments.
	 *
	 * @param DOMDocument $dom Document.
	 */
	public static function remove_source_comments( $dom ) {
		$xpath    = new DOMXPath( $dom );
		$comments = array();
		foreach ( $xpath->query( '//comment()[ starts-with( ., "amp-source-stack" ) or starts-with( ., "/amp-source-stack" ) ]' ) as $comment ) {
			if ( self::parse_source_comment( $comment ) ) {
				$comments[] = $comment;
			}
		}
		foreach ( $comments as $comment ) {
			$comment->parentNode->removeChild( $comment );
		}
	}

	/**
	 * Wrap callbacks for registered widgets to keep track of queued assets and the source for anything printed for validation.
	 *
	 * @global array $wp_filter
	 * @return void
	 */
	public static function wrap_widget_callbacks() {
		global $wp_registered_widgets;
		foreach ( $wp_registered_widgets as $widget_id => &$registered_widget ) {
			$source = self::get_source( $registered_widget['callback'] );
			if ( ! $source ) {
				continue;
			}
			$source['widget_id'] = $widget_id;

			$function      = $registered_widget['callback'];
			$accepted_args = 2; // For the $instance and $args arguments.
			$callback      = compact( 'function', 'accepted_args', 'source' );

			$registered_widget['callback'] = self::wrapped_callback( $callback );
		}
	}

	/**
	 * Wrap filter/action callback functions for a given hook.
	 *
	 * Wrapped callback functions are reset to their original functions after invocation.
	 * This runs at the 'all' action. The shutdown hook is excluded.
	 *
	 * @global WP_Hook[] $wp_filter
	 * @param string $hook Hook name for action or filter.
	 * @return void
	 */
	public static function wrap_hook_callbacks( $hook ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook ] ) || 'shutdown' === $hook ) {
			return;
		}

		self::$current_hook_source_stack[ $hook ] = array();
		foreach ( $wp_filter[ $hook ]->callbacks as $priority => &$callbacks ) {
			foreach ( $callbacks as &$callback ) {
				$source = self::get_source( $callback['function'] );
				if ( ! $source ) {
					continue;
				}

				$reflection = $source['reflection'];
				unset( $source['reflection'] ); // Omit from stored source.

				// Add hook to stack for decorate_filter_source to read from.
				self::$current_hook_source_stack[ $hook ][] = $source;

				/*
				 * A current limitation with wrapping callbacks is that the wrapped function cannot have
				 * any parameters passed by reference. Without this the result is:
				 *
				 * > PHP Warning:  Parameter 1 to wp_default_styles() expected to be a reference, value given.
				 */
				if ( self::has_parameters_passed_by_reference( $reflection ) ) {
					continue;
				}

				$source['hook']    = $hook;
				$original_function = $callback['function'];
				$wrapped_callback  = self::wrapped_callback( array_merge(
					$callback,
					compact( 'priority', 'source', 'hook' )
				) );

				$callback['function'] = function() use ( &$callback, $wrapped_callback, $original_function ) {
					$callback['function'] = $original_function; // Restore original.
					return call_user_func_array( $wrapped_callback, func_get_args() );
				};
			}
		}
	}

	/**
	 * Determine whether the given reflection method/function has params passed by reference.
	 *
	 * @since 0.7
	 * @param ReflectionFunction|ReflectionMethod $reflection Reflection.
	 * @return bool Whether there are parameters passed by reference.
	 */
	protected static function has_parameters_passed_by_reference( $reflection ) {
		foreach ( $reflection->getParameters() as $parameter ) {
			if ( $parameter->isPassedByReference() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Filters the output created by a shortcode callback.
	 *
	 * @since 0.7
	 *
	 * @param string $output Shortcode output.
	 * @param string $tag    Shortcode name.
	 * @return string Output.
	 * @global array $shortcode_tags
	 */
	public static function decorate_shortcode_source( $output, $tag ) {
		global $shortcode_tags;
		if ( ! isset( $shortcode_tags[ $tag ] ) ) {
			return $output;
		}
		$source = self::get_source( $shortcode_tags[ $tag ] );
		if ( empty( $source ) ) {
			return $output;
		}
		$source['shortcode'] = $tag;

		$output = implode( '', array(
			self::get_source_comment( $source, true ),
			$output,
			self::get_source_comment( $source, false ),
		) );
		return $output;
	}

	/**
	 * Wraps output of a filter to add source stack comments.
	 *
	 * @todo Duplicate with AMP_Validation_Utils::wrap_buffer_with_source_comments()?
	 * @param string $value Value.
	 * @return string Value wrapped in source comments.
	 */
	public static function decorate_filter_source( $value ) {

		// Abort if the output is not a string and it doesn't contain any HTML tags.
		if ( ! is_string( $value ) || ! preg_match( '/<.+?>/s', $value ) ) {
			return $value;
		}

		$post   = get_post();
		$source = array(
			'hook'   => current_filter(),
			'filter' => true,
		);
		if ( $post ) {
			$source['post_id']   = $post->ID;
			$source['post_type'] = $post->post_type;
		}
		if ( isset( self::$current_hook_source_stack[ current_filter() ] ) ) {
			$sources = self::$current_hook_source_stack[ current_filter() ];
			array_pop( $sources ); // Remove self.
			$source['sources'] = $sources;
		}
		return implode( '', array(
			self::get_source_comment( $source, true ),
			$value,
			self::get_source_comment( $source, false ),
		) );
	}

	/**
	 * Gets the plugin or theme of the callback, if one exists.
	 *
	 * @param string|array $callback The callback for which to get the plugin.
	 * @return array|null {
	 *     The source data.
	 *
	 *     @type string $type Source type (core, plugin, mu-plugin, or theme).
	 *     @type string $name Source name.
	 *     @type string $function Normalized function name.
	 *     @type ReflectionMethod|ReflectionFunction $reflection
	 * }
	 */
	public static function get_source( $callback ) {
		$reflection = null;
		$class_name = null; // Because ReflectionMethod::getDeclaringClass() can return a parent class.
		try {
			if ( is_string( $callback ) && is_callable( $callback ) ) {
				// The $callback is a function or static method.
				$exploded_callback = explode( '::', $callback, 2 );
				if ( 2 === count( $exploded_callback ) ) {
					$class_name = $exploded_callback[0];
					$reflection = new ReflectionMethod( $exploded_callback[0], $exploded_callback[1] );
				} else {
					$reflection = new ReflectionFunction( $callback );
				}
			} elseif ( is_array( $callback ) && isset( $callback[0], $callback[1] ) && method_exists( $callback[0], $callback[1] ) ) {
				// The $callback is a method.
				if ( is_string( $callback[0] ) ) {
					$class_name = $callback[0];
				} elseif ( is_object( $callback[0] ) ) {
					$class_name = get_class( $callback[0] );
				}
				$reflection = new ReflectionMethod( $callback[0], $callback[1] );
			} elseif ( is_object( $callback ) && ( 'Closure' === get_class( $callback ) ) ) {
				$reflection = new ReflectionFunction( $callback );
			}
		} catch ( Exception $e ) {
			return null;
		}

		if ( ! $reflection ) {
			return null;
		}

		$source = compact( 'reflection' );

		$file = $reflection->getFileName();
		if ( $file ) {
			$file         = wp_normalize_path( $file );
			$slug_pattern = '([^/]+)';
			if ( preg_match( ':' . preg_quote( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ), ':' ) . $slug_pattern . ':s', $file, $matches ) ) {
				$source['type'] = 'plugin';
				$source['name'] = $matches[1];
			} elseif ( preg_match( ':' . preg_quote( trailingslashit( wp_normalize_path( get_theme_root() ) ), ':' ) . $slug_pattern . ':s', $file, $matches ) ) {
				$source['type'] = 'theme';
				$source['name'] = $matches[1];
			} elseif ( preg_match( ':' . preg_quote( trailingslashit( wp_normalize_path( WPMU_PLUGIN_DIR ) ), ':' ) . $slug_pattern . ':s', $file, $matches ) ) {
				$source['type'] = 'mu-plugin';
				$source['name'] = $matches[1];
			} elseif ( preg_match( ':' . preg_quote( trailingslashit( wp_normalize_path( ABSPATH ) ), ':' ) . '(wp-admin|wp-includes)/:s', $file, $matches ) ) {
				$source['type'] = 'core';
				$source['name'] = $matches[1];
			}
		}

		if ( $class_name ) {
			$source['function'] = $class_name . '::' . $reflection->getName();
		} else {
			$source['function'] = $reflection->getName();
		}

		return $source;
	}

	/**
	 * Check whether or not output buffering is currently possible.
	 *
	 * This is to guard against a fatal error: "ob_start(): Cannot use output buffering in output buffering display handlers".
	 *
	 * @return bool Whether output buffering is allowed.
	 */
	public static function can_output_buffer() {

		// Output buffering for validation can only be done while overall output buffering is being done for the response.
		if ( ! AMP_Theme_Support::is_output_buffering() ) {
			return false;
		}

		// Abort when in shutdown since output has finished, when we're likely in the overall output buffering display handler.
		if ( did_action( 'shutdown' ) ) {
			return false;
		}

		// Check if any functions in call stack are output buffering display handlers.
		$called_functions = array();
		if ( defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ) {
			$arg = DEBUG_BACKTRACE_IGNORE_ARGS; // phpcs:ignore PHPCompatibility.PHP.NewConstants.debug_backtrace_ignore_argsFound
		} else {
			$arg = false;
		}
		$backtrace = debug_backtrace( $arg ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Only way to find out if we are in a buffering display handler.
		foreach ( $backtrace as $call_stack ) {
			$called_functions[] = '{closure}' === $call_stack['function'] ? 'Closure::__invoke' : $call_stack['function'];
		}
		return 0 === count( array_intersect( ob_list_handlers(), $called_functions ) );
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
	 *     @type array    $source
	 * }
	 * @return closure $wrapped_callback The callback, wrapped in comments.
	 */
	public static function wrapped_callback( $callback ) {
		return function() use ( $callback ) {
			global $wp_styles, $wp_scripts;

			$function      = $callback['function'];
			$accepted_args = $callback['accepted_args'];
			$args          = func_get_args();

			$before_styles_enqueued = array();
			if ( isset( $wp_styles ) && isset( $wp_styles->queue ) ) {
				$before_styles_enqueued = $wp_styles->queue;
			}
			$before_scripts_enqueued = array();
			if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
				$before_scripts_enqueued = $wp_scripts->queue;
			}

			// Wrap the markup output of (action) hooks in source comments.
			AMP_Validation_Utils::$hook_source_stack[] = $callback['source'];
			$has_buffer_started                        = false;
			if ( AMP_Validation_Utils::can_output_buffer() ) {
				$has_buffer_started = ob_start( array( __CLASS__, 'wrap_buffer_with_source_comments' ) );
			}
			$result = call_user_func_array( $function, array_slice( $args, 0, intval( $accepted_args ) ) );
			if ( $has_buffer_started ) {
				ob_end_flush();
			}
			array_pop( AMP_Validation_Utils::$hook_source_stack );

			// Keep track of which source enqueued the styles.
			if ( isset( $wp_styles ) && isset( $wp_styles->queue ) ) {
				foreach ( array_diff( $wp_styles->queue, $before_styles_enqueued ) as $handle ) {
					AMP_Validation_Utils::$enqueued_style_sources[ $handle ][] = $callback['source'];
				}
			}

			// Keep track of which source enqueued the scripts, and immediately report validity .
			if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
				foreach ( array_diff( $wp_scripts->queue, $before_scripts_enqueued ) as $handle ) {
					AMP_Validation_Utils::$enqueued_script_sources[ $handle ][] = $callback['source'];

					// Flag all scripts not loaded from the AMP CDN as validation errors.
					if ( isset( $wp_scripts->registered[ $handle ] ) && 0 !== strpos( $wp_scripts->registered[ $handle ]->src, 'https://cdn.ampproject.org/' ) ) {
						self::add_validation_error( array(
							'code'       => self::ENQUEUED_SCRIPT_CODE,
							'handle'     => $handle,
							'dependency' => $wp_scripts->registered[ $handle ],
							'sources'    => array(
								$callback['source'],
							),
						) );
					}
				}
			}

			return $result;
		};
	}

	/**
	 * Wrap output buffer with source comments.
	 *
	 * A key reason for why this is a method and not a closure is so that
	 * the can_output_buffer method will be able to identify it by name.
	 *
	 * @since 0.7
	 * @todo Is duplicate of \AMP_Validation_Utils::decorate_filter_source()?
	 *
	 * @param string $output Output buffer.
	 * @return string Output buffer conditionally wrapped with source comments.
	 */
	public static function wrap_buffer_with_source_comments( $output ) {
		if ( empty( self::$hook_source_stack ) ) {
			return $output;
		}

		$source = self::$hook_source_stack[ count( self::$hook_source_stack ) - 1 ];

		// Wrap output that contains HTML tags (as opposed to actions that trigger in HTML attributes).
		if ( ! empty( $output ) && preg_match( '/<.+?>/s', $output ) ) {
			$output = implode( '', array(
				self::get_source_comment( $source, true ),
				$output,
				self::get_source_comment( $source, false ),
			) );
		}
		return $output;
	}

	/**
	 * Output a removed set, each wrapped in <code></code>.
	 *
	 * @param array[][] $set {
	 *     The removed elements to output.
	 *
	 *     @type string $name  The name of the source.
	 *     @type string $count The number that were invalid.
	 * }
	 * @return void
	 */
	protected static function output_removed_set( $set ) {
		$items = array();
		foreach ( $set as $name => $count ) {
			if ( 1 === intval( $count ) ) {
				$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
			} else {
				$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
			}
		}
		echo implode( ', ', $items ); // WPCS: XSS OK.
	}

	/**
	 * Whether to validate the front end response.
	 *
	 * Either the user has the capability and the query var is present.
	 *
	 * @return boolean Whether to validate.
	 */
	public static function should_validate_response() {
		return self::has_cap() && isset( $_GET[ self::VALIDATE_QUERY_VAR ] ); // WPCS: CSRF ok.
	}

	/**
	 * Finalize validation.
	 *
	 * @param DOMDocument $dom Document.
	 * @param array       $args {
	 *     Args.
	 *
	 *     @type bool $remove_source_comments           Whether source comments should be removed. Defaults to true.
	 *     @type bool $append_validation_status_comment Whether the validation errors should be appended as an HTML comment. Defaults to true.
	 * }
	 */
	public static function finalize_validation( DOMDocument $dom, $args = array() ) {
		$args = array_merge(
			array(
				'remove_source_comments'           => true,
				'append_validation_status_comment' => true,
			),
			$args
		);

		if ( $args['remove_source_comments'] ) {
			self::remove_source_comments( $dom );
		}

		if ( $args['append_validation_status_comment'] ) {
			$encoded = wp_json_encode( self::$validation_errors, 128 /* JSON_PRETTY_PRINT */ );
			$encoded = str_replace( '--', '\u002d\u002d', $encoded ); // Prevent "--" in strings from breaking out of HTML comments.
			$comment = $dom->createComment( 'AMP_VALIDATION_ERRORS:' . $encoded . "\n" );
			$dom->documentElement->appendChild( $comment );
		}
	}

	/**
	 * Adds the validation callback if front-end validation is needed.
	 *
	 * @param array $sanitizers The AMP sanitizers.
	 * @return array $sanitizers The filtered AMP sanitizers.
	 */
	public static function add_validation_callback( $sanitizers ) {
		foreach ( $sanitizers as $sanitizer => $args ) {
			$sanitizers[ $sanitizer ] = array_merge(
				$args,
				array(
					'validation_error_callback' => __CLASS__ . '::add_validation_error',
				)
			);
		}
		return $sanitizers;
	}

	/**
	 * Registers the post type to store the validation errors.
	 *
	 * @return void.
	 */
	public static function register_post_type() {
		$post_type = register_post_type(
			self::POST_TYPE_SLUG,
			array(
				'labels'       => array(
					'name'               => _x( 'Validation Status', 'post type general name', 'amp' ),
					'singular_name'      => __( 'validation error', 'amp' ),
					'not_found'          => __( 'No validation errors found', 'amp' ),
					'not_found_in_trash' => __( 'No validation errors found in trash', 'amp' ),
					'search_items'       => __( 'Search statuses', 'amp' ),
					'edit_item'          => __( 'Validation Status', 'amp' ),
				),
				'supports'     => false,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => AMP_Options_Manager::OPTION_NAME,
			)
		);

		// Hide the add new post link.
		$post_type->cap->create_posts = 'do_not_allow';
	}

	/**
	 * Stores the validation errors.
	 *
	 * After the preprocessors run, this gets the validation response if the query var is present.
	 * It then stores the response in a custom post type.
	 * If there's already an error post for the URL, but there's no error anymore, it deletes it.
	 *
	 * @param array  $validation_errors Validation errors.
	 * @param string $url               URL on which the validation errors occurred.
	 * @return int|null $post_id The post ID of the custom post type used, or null.
	 * @global WP $wp
	 */
	public static function store_validation_errors( $validation_errors, $url ) {
		$post_for_this_url = self::get_validation_status_post( $url );

		// Since there are no validation errors and there is an existing $existing_post_id, just delete the post.
		if ( empty( $validation_errors ) ) {
			if ( $post_for_this_url ) {
				wp_delete_post( $post_for_this_url->ID, true );
			}
			return null;
		}

		$encoded_errors = wp_json_encode( $validation_errors );
		$post_name      = md5( $encoded_errors );

		// If the post name is unchanged then the errors are the same and there is nothing to do.
		if ( $post_for_this_url && $post_for_this_url->post_name === $post_name ) {
			return $post_for_this_url->ID;
		}

		// If there already exists a post for the given validation errors, just amend the $url to the existing post.
		$post_for_other_url = get_page_by_path( $post_name, OBJECT, self::POST_TYPE_SLUG );
		if ( ! $post_for_other_url ) {
			$post_for_other_url = get_page_by_path( $post_name . '__trashed', OBJECT, self::POST_TYPE_SLUG );
		}
		if ( $post_for_other_url ) {
			if ( 'trash' === $post_for_other_url->post_status ) {
				wp_untrash_post( $post_for_other_url->ID );
			}
			if ( ! in_array( $url, get_post_meta( $post_for_other_url->ID, self::AMP_URL_META, false ), true ) ) {
				add_post_meta( $post_for_other_url->ID, self::AMP_URL_META, wp_slash( $url ), false );
			}
			return $post_for_other_url->ID;
		}

		// Otherwise, create a new validation status post, or update the existing one.
		$post_id = wp_insert_post( wp_slash( array(
			'ID'           => $post_for_this_url ? $post_for_this_url->ID : null,
			'post_type'    => self::POST_TYPE_SLUG,
			'post_title'   => $url,
			'post_name'    => $post_name,
			'post_content' => $encoded_errors,
			'post_status'  => 'publish',
		) ) );
		if ( ! $post_id ) {
			return null;
		}
		if ( ! in_array( $url, get_post_meta( $post_id, self::AMP_URL_META, false ), true ) ) {
			add_post_meta( $post_id, self::AMP_URL_META, wp_slash( $url ), false );
		}
		return $post_id;
	}

	/**
	 * Gets the existing custom post that stores errors for the $url, if it exists.
	 *
	 * @param string $url The URL of the post.
	 * @return WP_Post|null The post of the existing custom post, or null.
	 */
	public static function get_validation_status_post( $url ) {
		if ( ! post_type_exists( self::POST_TYPE_SLUG ) ) {
			return null;
		}
		$query = new WP_Query( array(
			'post_type'      => self::POST_TYPE_SLUG,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => self::AMP_URL_META,
					'value' => $url,
				),
			),
		) );
		return array_shift( $query->posts );
	}

	/**
	 * Validates the latest published post.
	 *
	 * @return array|WP_Error The validation errors, or WP_Error.
	 */
	public static function validate_after_plugin_activation() {
		$url = amp_admin_get_preview_permalink();
		if ( ! $url ) {
			return new WP_Error( 'no_published_post_url_available' );
		}
		$validation_errors = self::validate_url( $url );
		if ( is_array( $validation_errors ) && count( $validation_errors ) > 0 ) {
			self::store_validation_errors( $validation_errors, $url );
			set_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY, $validation_errors, 60 );
		} else {
			delete_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY );
		}
		return $validation_errors;
	}

	/**
	 * Validates a given URL.
	 *
	 * The validation errors will be stored in the validation status custom post type,
	 * as well as in a transient.
	 *
	 * @param string $url The URL to validate.
	 * @return array|WP_Error The validation errors, or WP_Error on error.
	 */
	public static function validate_url( $url ) {
		$validation_url = add_query_arg(
			array(
				self::VALIDATE_QUERY_VAR   => 1,
				self::CACHE_BUST_QUERY_VAR => wp_rand(),
			),
			$url
		);

		$r = wp_remote_get( $validation_url, array(
			'cookies'   => wp_unslash( $_COOKIE ),
			'sslverify' => false,
			'headers'   => array(
				'Cache-Control' => 'no-cache',
			),
		) );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		if ( wp_remote_retrieve_response_code( $r ) >= 400 ) {
			return new WP_Error(
				wp_remote_retrieve_response_code( $r ),
				wp_remote_retrieve_response_message( $r )
			);
		}
		$response = wp_remote_retrieve_body( $r );
		if ( ! preg_match( '#</body>.*?<!--\s*AMP_VALIDATION_ERRORS\s*:\s*(\[.*?\])\s*-->#s', $response, $matches ) ) {
			return new WP_Error( 'response_comment_absent' );
		}
		$validation_errors = json_decode( $matches[1], true );
		if ( ! is_array( $validation_errors ) ) {
			return new WP_Error( 'malformed_json_validation_errors' );
		}

		return $validation_errors;
	}

	/**
	 * On activating a plugin, display a notice if a plugin causes an AMP validation error.
	 *
	 * @return void
	 */
	public static function plugin_notice() {
		global $pagenow;
		if ( ( 'plugins.php' === $pagenow ) && ( ! empty( $_GET['activate'] ) || ! empty( $_GET['activate-multi'] ) ) ) { // WPCS: CSRF ok.
			$validation_errors = get_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY );
			if ( empty( $validation_errors ) || ! is_array( $validation_errors ) ) {
				return;
			}
			delete_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY );
			$errors          = self::summarize_validation_errors( $validation_errors );
			$invalid_plugins = isset( $errors[ self::SOURCES_INVALID_OUTPUT ]['plugin'] ) ? array_unique( $errors[ self::SOURCES_INVALID_OUTPUT ]['plugin'] ) : null;
			if ( isset( $invalid_plugins ) ) {
				$reported_plugins = array();
				foreach ( $invalid_plugins as $plugin ) {
					$reported_plugins[] = sprintf( '<code>%s</code>', esc_html( $plugin ) );
				}

				$more_details_link = sprintf(
					'<a href="%s">%s</a>',
					esc_url( add_query_arg(
						'post_type',
						self::POST_TYPE_SLUG,
						admin_url( 'edit.php' )
					) ),
					__( 'More details', 'amp' )
				);
				printf(
					'<div class="notice notice-warning is-dismissible"><p>%s %s %s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
					esc_html( _n( 'Warning: The following plugin may be incompatible with AMP:', 'Warning: The following plugins may be incompatible with AMP: ', count( $invalid_plugins ), 'amp' ) ),
					implode( ', ', $reported_plugins ),
					$more_details_link,
					esc_html__( 'Dismiss this notice.', 'amp' )
				); // WPCS: XSS ok.
			}
		}
	}

	/**
	 * Adds post columns to the UI for the validation errors.
	 *
	 * @param array $columns The post columns.
	 * @return array $columns The new post columns.
	 */
	public static function add_post_columns( $columns ) {
		$columns = array_merge(
			$columns,
			array(
				'url_count'                  => esc_html__( 'Count', 'amp' ),
				self::REMOVED_ELEMENTS       => esc_html__( 'Removed Elements', 'amp' ),
				self::REMOVED_ATTRIBUTES     => esc_html__( 'Removed Attributes', 'amp' ),
				self::SOURCES_INVALID_OUTPUT => esc_html__( 'Incompatible Sources', 'amp' ),
			)
		);

		// Move date to end.
		if ( isset( $columns['date'] ) ) {
			$date = $columns['date'];
			unset( $columns['date'] );
			$columns['date'] = $date;
		}

		return $columns;
	}

	/**
	 * Outputs custom columns in the /wp-admin UI for the AMP validation errors.
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_id     The ID of the post for the column.
	 * @return void
	 */
	public static function output_custom_column( $column_name, $post_id ) {
		$post = get_post( $post_id );
		if ( self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}
		$validation_errors = json_decode( $post->post_content, true );
		if ( ! is_array( $validation_errors ) ) {
			return;
		}
		$errors = self::summarize_validation_errors( $validation_errors );
		$urls   = get_post_meta( $post_id, self::AMP_URL_META, false );

		switch ( $column_name ) {
			case 'url_count':
				echo count( $urls );
				break;
			case self::REMOVED_ELEMENTS:
				if ( ! empty( $errors[ self::REMOVED_ELEMENTS ] ) ) {
					self::output_removed_set( $errors[ self::REMOVED_ELEMENTS ] );
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case self::REMOVED_ATTRIBUTES:
				if ( ! empty( $errors[ self::REMOVED_ATTRIBUTES ] ) ) {
					self::output_removed_set( $errors[ self::REMOVED_ATTRIBUTES ] );
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case self::SOURCES_INVALID_OUTPUT:
				if ( isset( $errors[ self::SOURCES_INVALID_OUTPUT ] ) ) {
					$sources = array();
					foreach ( $errors[ self::SOURCES_INVALID_OUTPUT ] as $type => $names ) {
						foreach ( array_unique( $names ) as $name ) {
							$sources[] = sprintf( '%s: <code>%s</code>', esc_html( $type ), esc_html( $name ) );
						}
					}
					echo implode( ', ', $sources ); // WPCS: XSS ok.
				}
				break;
		}
	}

	/**
	 * Adds a 'Recheck' link to the edit.php row actions.
	 *
	 * The logic to add the new action is mainly copied from WP_Posts_List_Table::handle_row_actions().
	 *
	 * @param array   $actions The actions in the edit.php page.
	 * @param WP_Post $post    The post for the actions.
	 * @return array $actions The filtered actions.
	 */
	public static function filter_row_actions( $actions, $post ) {
		if ( self::POST_TYPE_SLUG !== $post->post_type ) {
			return $actions;
		}

		$actions['edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_edit_post_link( $post ) ),
			esc_html__( 'Details', 'amp' )
		);
		unset( $actions['inline hide-if-no-js'] );
		$url = get_post_meta( $post->ID, self::AMP_URL_META, true );

		if ( ! empty( $url ) ) {
			$actions[ self::RECHECK_ACTION ]  = self::get_recheck_link( $post, get_edit_post_link( $post->ID, 'raw' ), $url );
			$actions[ self::DEBUG_QUERY_VAR ] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( self::get_debug_url( $url ) ),
				esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
				esc_html__( 'Debug', 'amp' )
			);
		}

		return $actions;
	}

	/**
	 * Adds a 'Recheck' bulk action to the edit.php page.
	 *
	 * @param array $actions The bulk actions in the edit.php page.
	 * @return array $actions The filtered bulk actions.
	 */
	public static function add_bulk_action( $actions ) {
		unset( $actions['edit'] );
		$actions[ self::RECHECK_ACTION ] = esc_html__( 'Recheck', 'amp' );
		return $actions;
	}

	/**
	 * Handles the 'Recheck' bulk action on the edit.php page.
	 *
	 * @param string $redirect The URL of the redirect.
	 * @param string $action   The action.
	 * @param array  $items    The items on which to take the action.
	 * @return string $redirect The filtered URL of the redirect.
	 */
	public static function handle_bulk_action( $redirect, $action, $items ) {
		if ( self::RECHECK_ACTION !== $action ) {
			return $redirect;
		}
		$remaining_invalid_urls = array();
		foreach ( $items as $item ) {
			$url = get_post_meta( $item, self::AMP_URL_META, true );
			if ( empty( $url ) ) {
				continue;
			}

			$validation_errors = self::validate_url( $url );
			if ( ! is_array( $validation_errors ) ) {
				continue;
			}

			self::store_validation_errors( $validation_errors, $url );
			if ( ! empty( $validation_errors ) ) {
				$remaining_invalid_urls[] = $url;
			}
		}

		// Get the URLs that still have errors after rechecking.
		$args = array(
			self::URLS_TESTED      => count( $items ),
			self::REMAINING_ERRORS => empty( $remaining_invalid_urls ) ? '0' : '1',
		);

		return add_query_arg( $args, $redirect );
	}

	/**
	 * Outputs an admin notice after rechecking URL(s) on the custom post page.
	 *
	 * @return void
	 */
	public static function remaining_error_notice() {
		if ( ! isset( $_GET[ self::REMAINING_ERRORS ] ) || self::POST_TYPE_SLUG !== get_current_screen()->post_type ) { // WPCS: CSRF ok.
			return;
		}

		$count_urls_tested = isset( $_GET[ self::URLS_TESTED ] ) ? intval( $_GET[ self::URLS_TESTED ] ) : 1; // WPCS: CSRF ok.
		$errors_remain     = ! empty( $_GET[ self::REMAINING_ERRORS ] ); // WPCS: CSRF ok.
		if ( $errors_remain ) {
			$class   = 'notice-warning';
			$message = _n( 'The rechecked URL still has validation errors.', 'The rechecked URLs still have validation errors.', $count_urls_tested, 'amp' );
		} else {
			$message = _n( 'The rechecked URL has no validation errors.', 'The rechecked URLs have no validation errors.', $count_urls_tested, 'amp' );
			$class   = 'updated';
		}

		printf(
			'<div class="notice is-dismissible %s"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
			esc_attr( $class ),
			esc_html( $message ),
			esc_html__( 'Dismiss this notice.', 'amp' )
		);
	}

	/**
	 * Handles clicking 'recheck' on the inline post actions.
	 *
	 * @param int $post_id The post ID of the recheck.
	 * @return void
	 */
	public static function handle_inline_recheck( $post_id ) {
		check_admin_referer( self::NONCE_ACTION . $post_id );
		$url = get_post_meta( $post_id, self::AMP_URL_META, true );
		if ( isset( $_GET['recheck_url'] ) ) {
			$url = wp_validate_redirect( wp_unslash( $_GET['recheck_url'] ) );
		}
		$validation_errors = self::validate_url( $url );
		$remaining_errors  = true;
		if ( is_array( $validation_errors ) ) {
			self::store_validation_errors( $validation_errors, $url );
			$remaining_errors = ! empty( $validation_errors );
		}

		$redirect = wp_get_referer();
		if ( ! $redirect || empty( $validation_errors ) ) {
			// If there are no remaining errors and the post was deleted, redirect to edit.php instead of post.php.
			$redirect = add_query_arg(
				'post_type',
				self::POST_TYPE_SLUG,
				admin_url( 'edit.php' )
			);
		}
		$args = array(
			self::URLS_TESTED      => '1',
			self::REMAINING_ERRORS => $remaining_errors ? '1' : '0',
		);
		wp_safe_redirect( add_query_arg( $args, $redirect ) );
		exit();
	}

	/**
	 * Removes the 'Publish' meta box from the CPT post.php page.
	 *
	 * @return void
	 */
	public static function remove_publish_meta_box() {
		remove_meta_box( 'submitdiv', self::POST_TYPE_SLUG, 'side' );
	}

	/**
	 * Adds the meta boxes to the CPT post.php page.
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		add_meta_box( self::VALIDATION_ERRORS_META_BOX, __( 'Validation Errors', 'amp' ), array( __CLASS__, 'print_validation_errors_meta_box' ), self::POST_TYPE_SLUG, 'normal' );
		add_meta_box( self::STATUS_META_BOX, __( 'Status', 'amp' ), array( __CLASS__, 'print_status_meta_box' ), self::POST_TYPE_SLUG, 'side' );
	}

	/**
	 * Outputs the markup of the side meta box in the CPT post.php page.
	 *
	 * This is partially copied from meta-boxes.php.
	 * Adds 'Published on,' and links to move to trash and recheck.
	 *
	 * @param WP_Post $post The post for which to output the box.
	 * @return void
	 */
	public static function print_status_meta_box( $post ) {
		$redirect_url = add_query_arg(
			'post',
			$post->ID,
			admin_url( 'post.php' )
		);

		echo '<div id="submitpost" class="submitbox">';
		/* translators: Meta box date format */
		$date_format = __( 'M j, Y @ H:i', 'default' );
		echo '<div class="curtime misc-pub-section"><span id="timestamp">';
		/* translators: %s: The date this was published */
		printf( __( 'Published on: <b>%s</b>', 'amp' ), esc_html( date_i18n( $date_format, strtotime( $post->post_date ) ) ) ); // WPCS: XSS ok.
		echo '</span></div>';
		printf( '<div class="misc-pub-section"><a class="submitdelete deletion" href="%s">%s</a></div>', esc_url( get_delete_post_link( $post->ID ) ), esc_html__( 'Move to Trash', 'default' ) );

		echo '<div class="misc-pub-section">';
		echo self::get_recheck_link( $post, $redirect_url ); // WPCS: XSS ok.
		$url = get_post_meta( $post->ID, self::AMP_URL_META, true );
		if ( $url ) {
			printf(
				' | <a href="%s" aria-label="%s">%s</a>',
				esc_url( self::get_debug_url( $url ) ),
				esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
				esc_html__( 'Debug', 'amp' )
			); // WPCS: XSS ok.
		}
		echo '</div>';

		echo '</div><!-- /submitpost -->';
	}

	/**
	 * Outputs the full meta box on the CPT post.php page.
	 *
	 * This displays the errors stored in the post content.
	 * These are output as stored, but using <details> elements.
	 *
	 * @param WP_Post $post The post for which to output the box.
	 * @return void
	 */
	public static function print_validation_errors_meta_box( $post ) {
		$errors = json_decode( $post->post_content, true );
		$urls   = get_post_meta( $post->ID, self::AMP_URL_META, false );
		?>
		<style>
			.amp-validation-errors .detailed {
				margin-left: 30px;
			}
			.amp-validation-errors .amp-recheck {
				float: right;
			}
		</style>
		<div class="amp-validation-errors">
			<ul>
				<?php foreach ( $errors as $error ) : ?>
					<?php
					$collasped_details = array();
					?>
					<li>
						<details open>
							<summary><code><?php echo esc_html( $error['code'] ); ?></code></summary>
							<ul class="detailed">
							<?php if ( self::INVALID_ELEMENT_CODE === $error['code'] ) : ?>
								<li>
									<details open>
										<summary><?php esc_html_e( 'Removed:', 'amp' ); ?></summary>
										<code class="detailed">
											<?php
											if ( isset( $error['parent_name'] ) ) {
												echo esc_html( sprintf( '<%s …>', $error['parent_name'] ) );
											}
											?>
											<mark>
												<?php
												echo esc_html( sprintf( '<%s', $error['node_name'] ) );
												if ( isset( $error['node_attributes'] ) ) {
													foreach ( $error['node_attributes'] as $key => $value ) {
														printf( ' %s="%s"', esc_html( $key ), esc_html( $value ) );
													}
												}
												echo esc_html( '>…' );
												?>
											</mark>
										</code>
									</details>
									<?php
									$collasped_details[] = 'node_attributes';
									$collasped_details[] = 'node_name';
									$collasped_details[] = 'parent_name';
									?>
								</li>
							<?php elseif ( self::INVALID_ATTRIBUTE_CODE === $error['code'] ) : ?>
								<li>
									<details open>
										<summary><?php esc_html_e( 'Removed:', 'amp' ); ?></summary>
										<code class="detailed">
											<?php
											if ( isset( $error['parent_name'] ) ) {
												echo esc_html( sprintf( '<%s', $error['parent_name'] ) );
											}
											foreach ( $error['element_attributes'] as $key => $value ) {
												if ( $key === $error['node_name'] ) {
													echo '<mark>';
												}
												printf( ' %s="%s"', esc_html( $key ), esc_html( $value ) );
												if ( $key === $error['node_name'] ) {
													echo '</mark>';
												}
											}
											echo esc_html( '>' );
											?>
										</code>
									</details>
									<?php
									$collasped_details[] = 'parent_name';
									$collasped_details[] = 'element_attributes';
									$collasped_details[] = 'node_name';
									?>
								</li>
							<?php endif; ?>
								<?php unset( $error['code'] ); ?>
								<?php foreach ( $error as $key => $value ) : ?>
									<li>
										<details <?php echo ! in_array( $key, $collasped_details, true ) ? 'open' : ''; ?>>
											<summary><code><?php echo esc_html( $key ); ?></code></summary>
											<div class="detailed">
												<?php if ( is_string( $value ) ) : ?>
													<?php echo esc_html( $value ); ?>
												<?php else : ?>
													<pre><?php echo esc_html( wp_json_encode( $value, 128 /* JSON_PRETTY_PRINT */ | 64 /* JSON_UNESCAPED_SLASHES */ ) ); ?></pre>
												<?php endif; ?>
											</div>
										</details>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					</li>
				<?php endforeach; ?>
			</ul>
			<hr>
			<h3><?php esc_html_e( 'URLs', 'amp' ); ?></h3>
			<ul>
				<?php foreach ( $urls as $url ) : ?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a>
						<span class="amp-recheck">
							<?php echo self::get_recheck_link( $post, get_edit_post_link( $post->ID, 'raw' ), $url ); // WPCS: XSS ok. ?>
							|
							<?php
							printf(
								'<a href="%s" aria-label="%s">%s</a>',
								esc_url( self::get_debug_url( $url ) ),
								esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
								esc_html__( 'Debug', 'amp' )
							)
							?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Get validation debug UR:.
	 *
	 * @param string $url URL to to validate and debug.
	 * @return string Debug URL.
	 */
	public static function get_debug_url( $url ) {
		return add_query_arg(
			array(
				self::VALIDATE_QUERY_VAR => 1,
				self::DEBUG_QUERY_VAR    => 1,
			),
			$url
		) . '#development=1';
	}

	/**
	 * Gets the link to recheck the post for AMP validity.
	 *
	 * Appends a query var to $redirect_url.
	 * On clicking the link, it checks if errors still exist for $post.
	 *
	 * @param  WP_Post $post         The post storing the validation error.
	 * @param  string  $redirect_url The URL of the redirect.
	 * @param  string  $recheck_url  The URL to check. Optional.
	 * @return string $link The link to recheck the post.
	 */
	public static function get_recheck_link( $post, $redirect_url, $recheck_url = null ) {
		return sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'action'      => self::RECHECK_ACTION,
						'recheck_url' => $recheck_url,
					),
					$redirect_url
				),
				self::NONCE_ACTION . $post->ID
			),
			esc_html__( 'Recheck the URL for AMP validity', 'amp' ),
			esc_html__( 'Recheck', 'amp' )
		);
	}

}
