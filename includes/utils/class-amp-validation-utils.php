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
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_invalid_url';

	/**
	 * The slug of the taxonomy to store AMP errors.
	 *
	 * @var string
	 */
	const TAXONOMY_SLUG = 'amp_validation_error';

	/**
	 * Query var used when filtering by validation error status.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_STATUS_QUERY_VAR = 'amp_validation_error_status';

	/**
	 * Term group for validation_error terms have not yet been acknowledged.
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_NEW_STATUS = 0;

	/**
	 * Term group for validation_error terms that the user acknowledges as being ignored (and thus not disabling AMP).
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_IGNORED_STATUS = 1;

	/**
	 * Action name for ignoring a validation error.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_IGNORE_ACTION = 'amp_validation_error_ignore';

	/**
	 * Action name for acknowledging a validation error.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_ACKNOWLEDGE_ACTION = 'amp_validation_error_acknowledge';

	/**
	 * Term group for validation_error terms that the user acknowledges (as being blockers to enabling AMP).
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_ACKNOWLEDGED_STATUS = 2;

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
	 * The name of the REST API field with the AMP validation results.
	 *
	 * @var string
	 */
	const VALIDITY_REST_FIELD_NAME = 'amp_validity';

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
	 * Keys are post IDs and values are whether the post has been re-validated.
	 *
	 * @var bool[]
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
	 * Index for where block appears in a post's content.
	 *
	 * @var int
	 */
	protected static $block_content_index = 0;

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
	 * Whether the terms_clauses filter should apply to a term query for validation errors to limit to a given status.
	 *
	 * This is set to false when calling wp_count_terms() for the admin menu and for the views.
	 *
	 * @see AMP_Validation_Utils::get_validation_error_count()
	 * @var bool
	 */
	protected static $should_filter_terms_clauses_for_error_validation_status;

	/**
	 * Whether in debug mode.
	 *
	 * This means that sanitization will not be applied for validation errors.
	 *
	 * @var bool
	 */
	public static $debug = false;

	/**
	 * Add the actions.
	 *
	 * @param array $args {
	 *     Args.
	 *
	 *     @type bool $debug Whether validation should be done in debug mode, where validation errors are not sanitized and source comments are not removed.
	 * }
	 * @return void
	 */
	public static function init( $args = array() ) {
		$args = array_merge(
			array(
				'debug' => false,
			),
			$args
		);

		self::$debug = $args['debug'];

		if ( current_theme_supports( 'amp' ) ) {
			add_action( 'init', array( __CLASS__, 'register_post_type' ) );
			add_filter( 'dashboard_glance_items', array( __CLASS__, 'filter_dashboard_glance_items' ) );
			add_action( 'rightnow_end', array( __CLASS__, 'print_dashboard_glance_styles' ) );
			add_action( 'save_post', array( __CLASS__, 'handle_save_post_prompting_validation' ), 10, 2 );
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_validation' ) );
			add_action( 'rest_api_init', array( __CLASS__, 'add_rest_api_fields' ) );
		}

		add_action( 'edit_form_top', array( __CLASS__, 'print_edit_form_validation_status' ), 10, 2 );
		add_action( 'all_admin_notices', array( __CLASS__, 'plugin_notice' ) );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( __CLASS__, 'add_post_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'output_custom_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'add_bulk_action' ), 10, 2 );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'handle_bulk_action' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'remaining_error_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'persistent_object_caching_notice' ) );
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
	 *
	 * @todo This probably needs to be updated to show the number of amp_invalid_url posts which have validation errors in the new group.
	 */
	public static function add_admin_menu_validation_status_count() {
		global $submenu;
		if ( ! isset( $submenu[ AMP_Options_Manager::OPTION_NAME ] ) ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'                             => self::POST_TYPE_SLUG,
			self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_NEW_STATUS,
			'update_post_meta_cache'                => false,
			'update_post_term_cache'                => false,
		) );

		if ( 0 === $query->found_posts ) {
			return;
		}
		foreach ( $submenu[ AMP_Options_Manager::OPTION_NAME ] as &$submenu_item ) {
			if ( 'edit.php?post_type=' . self::POST_TYPE_SLUG === $submenu_item[2] ) {
				$submenu_item[0] .= ' <span class="awaiting-mod"><span class="pending-count">' . esc_html( number_format_i18n( $query->found_posts ) ) . '</span></span>';
				break;
			}
		}
	}

	/**
	 * Add recognition of amp_validation_error_status query var for amp_invalid_url post queries.
	 *
	 * @see WP_Tax_Query::get_sql_for_clause()
	 *
	 * @param string   $where SQL WHERE clause.
	 * @param WP_Query $query Query.
	 * @return string Modified WHERE clause.
	 */
	public static function filter_posts_where_for_validation_error_status( $where, WP_Query $query ) {
		global $wpdb;
		if (
			in_array( self::POST_TYPE_SLUG, (array) $query->get( 'post_type' ), true )
			&&
			is_numeric( $query->get( self::VALIDATION_ERROR_STATUS_QUERY_VAR ) )
		) {
			$where .= $wpdb->prepare(
				" AND (
					SELECT 1
					FROM $wpdb->term_relationships
					INNER JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
					INNER JOIN $wpdb->terms ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
					WHERE
						$wpdb->term_taxonomy.taxonomy = %s
						AND
						$wpdb->term_relationships.object_id = $wpdb->posts.ID
						AND
						$wpdb->terms.term_group = %d
					LIMIT 1
				)",
				self::TAXONOMY_SLUG,
				$query->get( self::VALIDATION_ERROR_STATUS_QUERY_VAR )
			);
		}
		return $where;
	}

	/**
	 * Filter At a Glance items add AMP Validation Errors.
	 *
	 * @param array $items At a glance items.
	 * @return array Items.
	 */
	public static function filter_dashboard_glance_items( $items ) {

		$query = new WP_Query( array(
			'post_type'                             => self::POST_TYPE_SLUG,
			self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_NEW_STATUS,
			'update_post_meta_cache'                => false,
			'update_post_term_cache'                => false,
		) );

		if ( 0 !== $query->found_posts ) {
			$items[] = sprintf(
				'<a class="amp-validation-errors" href="%s">%s</a>',
				esc_url( admin_url(
					add_query_arg(
						array(
							'post_type' => self::POST_TYPE_SLUG,
							self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_NEW_STATUS,
						),
						'edit.php'
					)
				) ),
				esc_html( sprintf(
					/* translators: %s is the validation error count */
					_n(
						'%s URL w/ new AMP errors',
						'%s URLs w/ new AMP errors',
						$query->found_posts,
						'amp'
					),
					$query->found_posts
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

		$do_blocks_priority  = has_filter( 'the_content', 'do_blocks' );
		$is_gutenberg_active = (
			false !== $do_blocks_priority
			&&
			class_exists( 'WP_Block_Type_Registry' )
		);
		if ( $is_gutenberg_active ) {
			add_filter( 'the_content', array( __CLASS__, 'add_block_source_comments' ), $do_blocks_priority - 1 );
		}

		add_filter( 'amp_content_sanitizers', array( __CLASS__, 'add_validation_callback' ), 1000 );
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
			&&
			! isset( self::$posts_pending_frontend_validation[ $post_id ] )
		);
		if ( $should_validate_post ) {
			self::$posts_pending_frontend_validation[ $post_id ] = true;

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
	 *
	 * @return array Mapping of post ID to the result of validating or storing the validation result.
	 */
	public static function validate_queued_posts_on_frontend() {
		$posts = array_filter(
			array_map( 'get_post', array_keys( array_filter( self::$posts_pending_frontend_validation ) ) ),
			function( $post ) {
				return $post && post_supports_amp( $post ) && 'trash' !== $post->post_status;
			}
		);

		$validation_posts = array();

		// @todo Only validate the first and then queue the rest in WP Cron?
		foreach ( $posts as $post ) {
			$url = amp_get_permalink( $post->ID );
			if ( ! $url ) {
				$validation_posts[ $post->ID ] = new WP_Error( 'no_amp_permalink' );
				continue;
			}

			// Prevent re-validating.
			self::$posts_pending_frontend_validation[ $post->ID ] = false;

			$validation_errors = self::validate_url( $url );
			if ( is_wp_error( $validation_errors ) ) {
				$validation_posts[ $post->ID ] = $validation_errors;
			} else {
				$validation_posts[ $post->ID ] = self::store_validation_errors( $validation_errors, $url );
			}
		}

		return $validation_posts;
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
	 * @return bool Whether the validation error should result in sanitization.
	 */
	public static function add_validation_error( array $data ) {
		$node = null;

		if ( isset( $data['node'] ) && $data['node'] instanceof DOMNode ) {
			$node = $data['node'];
			unset( $data['node'] );
			$data['node_name'] = $node->nodeName;
			if ( ! isset( $data['sources'] ) ) {
				$data['sources'] = self::locate_sources( $node );
			}
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

		return ! self::$debug;
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

		$amp_url           = null;
		$invalid_url_post  = null;
		$validation_errors = array();
		if ( is_post_type_viewable( $post->post_type ) ) {
			$amp_url = amp_get_permalink( $post->ID );
		}

		// Incorporate frontend validation status if there is a known URL for the post.
		$invalid_url_post = self::get_invalid_url_post( $amp_url );
		if ( $invalid_url_post ) {
			$validation_errors = wp_list_pluck(
				self::get_invalid_url_validation_errors( $invalid_url_post, array( 'ignore_ignored' => true ) ),
				'data'
			);
		} elseif ( post_type_supports( $post->post_type, 'editor' ) ) {

			// Validate post content outside frontend context.
			self::process_markup( $post->post_content );
			$validation_errors = self::$validation_errors;
			self::reset_validation_results();
		}
		if ( empty( $validation_errors ) ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p>';
		esc_html_e( 'There is content which fails AMP validation. Non-ignored validation errors prevent AMP from being served.', 'amp' );
		if ( $invalid_url_post || $amp_url ) {
			if ( $invalid_url_post ) {
				echo sprintf(
					' <a href="%s" target="_blank">%s</a>',
					esc_url( get_edit_post_link( $invalid_url_post ) ),
					esc_html__( 'Review issues', 'amp' )
				);
			}
			if ( $amp_url ) {
				if ( $invalid_url_post ) {
					echo ' | ';
				}
				echo sprintf(
					' <a href="%s" aria-label="%s" target="_blank">%s</a>',
					esc_url( self::get_debug_url( $amp_url ) ),
					esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
					esc_html__( 'Debug', 'amp' )
				);
			}
		}
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
	 * Add block source comments.
	 *
	 * @param string $content Content prior to blocks being processed.
	 * @return string Content with source comments added.
	 */
	public static function add_block_source_comments( $content ) {
		self::$block_content_index = 0;

		$start_block_pattern = implode( '', array(
			'#<!--\s+',
			'(?P<closing>/)?',
			'wp:(?P<name>\S+)',
			'(?:\s+(?P<attributes>\{.*?\}))?',
			'\s+(?P<self_closing>\/)?',
			'-->#s',
		) );

		return preg_replace_callback(
			$start_block_pattern,
			array( __CLASS__, 'handle_block_source_comment_replacement' ),
			$content
		);
	}

	/**
	 * Handle block source comment replacement.
	 *
	 * @see \AMP_Validation_Utils::add_block_source_comments()
	 * @param array $matches Matches.
	 * @return string Replaced.
	 */
	protected static function handle_block_source_comment_replacement( $matches ) {
		$replaced = $matches[0];

		// Obtain source information for block.
		$source = array(
			'block_name' => $matches['name'],
			'post_id'    => get_the_ID(), // @todo This is causing duplicate validation errors to occur when only variance is post_id.
		);

		if ( empty( $matches['closing'] ) ) {
			$source['block_content_index'] = self::$block_content_index; // @todo This is causing duplicate validation errors to occur when only variance is post_id.
			self::$block_content_index++;
		}

		// Make implicit core namespace explicit.
		$is_implicit_core_namespace = ( false === strpos( $source['block_name'], '/' ) );
		$source['block_name']       = $is_implicit_core_namespace ? 'core/' . $source['block_name'] : $source['block_name'];

		if ( ! empty( $matches['attributes'] ) ) {
			$source['block_attrs'] = json_decode( $matches['attributes'] );
		}
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $source['block_name'] );
		if ( $block_type && $block_type->is_dynamic() ) {
			$callback_source = self::get_source( $block_type->render_callback );
			if ( $callback_source ) {
				$source = array_merge(
					$source,
					$callback_source
				);
			}
		}

		if ( ! empty( $matches['closing'] ) ) {
			$replaced .= self::get_source_comment( $source, false );
		} else {
			$replaced = self::get_source_comment( $source, true ) . $replaced;
			if ( ! empty( $matches['self_closing'] ) ) {
				unset( $source['block_content_index'] );
				$replaced .= self::get_source_comment( $source, false );
			}
		}
		return $replaced;
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
			$source['post_id']   = $post->ID; // @todo This is causing duplicate validation errors to occur when only variance is post_id.
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
							'code'    => self::ENQUEUED_SCRIPT_CODE,
							'handle'  => $handle,
							'sources' => array(
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
				'remove_source_comments'           => ! self::$debug,
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
		foreach ( $sanitizers as $sanitizer => &$args ) {

			if ( isset( $args['validation_error_callback'] ) ) {
				$original_validation_error_callback = $args['validation_error_callback'];
				$args['validation_error_callback']  = function( $validation_error ) use ( $original_validation_error_callback ) {
					AMP_Validation_Utils::add_validation_error( $validation_error );
					$result = call_user_func( $original_validation_error_callback, $validation_error );
					if ( self::$debug ) {
						return false;
					}
					return $result;
				};
			} else {
				$args['validation_error_callback'] = __CLASS__ . '::add_validation_error';
			}
		}

		// @todo Pass this into all sanitizers?
		if ( isset( $sanitizers['AMP_Style_Sanitizer'] ) ) {
			$sanitizers['AMP_Style_Sanitizer']['locate_sources'] = true;
		}

		return $sanitizers;
	}

	/**
	 * Registers the post type to store the validation errors.
	 *
	 * @return void
	 */
	public static function register_post_type() {
		$post_type = register_post_type(
			self::POST_TYPE_SLUG,
			array(
				'labels'       => array(
					'name'               => _x( 'Invalid AMP Pages (URLs)', 'post type general name', 'amp' ),
					'menu_name'          => __( 'Invalid Pages', 'amp' ),
					'singular_name'      => __( 'Invalid AMP Page (URL)', 'amp' ),
					'not_found'          => __( 'No invalid AMP pages found', 'amp' ),
					'not_found_in_trash' => __( 'No invalid AMP pages in trash', 'amp' ),
					'search_items'       => __( 'Search invalid AMP pages', 'amp' ),
					'edit_item'          => __( 'Invalid AMP Page', 'amp' ),
				),
				'supports'     => false,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => AMP_Options_Manager::OPTION_NAME,
				// @todo Show in rest.
			)
		);

		// Hide the add new post link.
		$post_type->cap->create_posts = 'do_not_allow';

		// Show URL at the top of the edit form in place of the title (since title support is not present).
		add_action( 'edit_form_top', function( $post ) {
			if ( self::POST_TYPE_SLUG !== $post->post_type ) {
				return;
			}
			?>
			<h2 class="amp-invalid-url">
				<a href="<?php echo esc_url( $post->post_title ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h2>
			<?php
		} );

		// Strip host name from AMP invalid URL being printed.
		add_action( 'load-edit.php', function() {
			add_filter( 'the_title', function( $title, $post ) {
				if ( self::POST_TYPE_SLUG === get_post_type( $post ) ) {
					$title = preg_replace( '#^(\w+:)?//[^/]+#', '', $title );
				}
				return $title;
			}, 10, 2 );
		} );

		register_taxonomy( self::TAXONOMY_SLUG, self::POST_TYPE_SLUG, array(
			'labels'             => array(
				'name'                  => _x( 'AMP Validation Errors', 'taxonomy general name', 'amp' ),
				'singular_name'         => _x( 'AMP Validation Error', 'taxonomy singular name', 'amp' ),
				'search_items'          => __( 'Search AMP Validation Errors', 'amp' ),
				'all_items'             => __( 'All AMP Validation Errors', 'amp' ),
				'edit_item'             => __( 'Edit AMP Validation Error', 'amp' ),
				'update_item'           => __( 'Update AMP Validation Error', 'amp' ),
				'menu_name'             => __( 'Validation Errors', 'amp' ),
				'back_to_items'         => __( 'Back to AMP Validation Errors', 'amp' ),
				'popular_items'         => __( 'Frequent Validation Errors', 'amp' ),
				'view_item'             => __( 'View Validation Error', 'amp' ),
				'add_new_item'          => __( 'Add New Validation Error', 'amp' ), // Makes no sense.
				'new_item_name'         => __( 'New Validation Error Hash', 'amp' ), // Makes no sense.
				'not_found'             => __( 'No validation errors found.', 'amp' ),
				'no_terms'              => __( 'Validation Error', 'amp' ),
				'items_list_navigation' => __( 'Validation errors navigation', 'amp' ),
				'items_list'            => __( 'Validation errors list', 'amp' ),
				/* translators: Tab heading when selecting from the most used terms */
				'most_used'             => __( 'Most Used Validation Errors', 'amp' ),
			),
			'public'             => false,
			'show_ui'            => true, // @todo False because we need a custom UI.
			'show_tagcloud'      => false,
			'show_in_quick_edit' => false,
			'hierarchical'       => false, // Or true? Code could be the parent term?
			'show_in_menu'       => true,
			'meta_box_cb'        => false, // See print_validation_errors_meta_box().
			'capabilities'       => array(
				'assign_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				// Note that delete_terms is needed so the checkbox (cb) table column will work.
			),
		) );

		// Add support for querying posts by amp_validation_error_status.
		add_filter( 'posts_where', array( __CLASS__, 'filter_posts_where_for_validation_error_status' ), 10, 2 );

		// Hide irrelevant "published" label in the invalid URL post list.
		add_filter( 'post_date_column_status', function( $status, $post ) {
			if ( self::POST_TYPE_SLUG === get_post_type( $post ) ) {
				$status = '';
			}
			return $status;
		}, 10, 2 );

		// Add recognition of amp_validation_error_status query var (which will only apply in admin since post type is not publicly_queryable).
		add_filter( 'query_vars', function( $query_vars ) {
			$query_vars[] = self::VALIDATION_ERROR_STATUS_QUERY_VAR;
			return $query_vars;
		} );

		// Include searching taxonomy term descriptions and sources term meta.
		add_filter( 'terms_clauses', function( $clauses, $taxonomies, $args ) {
			global $wpdb;
			if ( ! empty( $args['search'] ) && in_array( self::TAXONOMY_SLUG, $taxonomies, true ) ) {
				$clauses['join'] .= " LEFT JOIN $wpdb->termmeta AS termmeta_sources ON termmeta_sources.term_id = t.term_id AND termmeta_sources.meta_key = 'sources'";

				$conditions = array(
					$wpdb->prepare( '(tt.description LIKE %s)', '%' . $wpdb->esc_like( $args['search'] ) . '%' ),
					$wpdb->prepare( '(termmeta_sources.meta_value LIKE %s)', '%' . $wpdb->esc_like( $args['search'] ) . '%' ),
				);

				$clauses['where'] = preg_replace(
					'#(?<=\()(?=\(t\.name LIKE \')#',
					implode( 'OR', $conditions ) . ' OR ',
					$clauses['where']
				);
			}
			return $clauses;
		}, 10, 3 );

		// Hide empty term addition form.
		add_action( 'admin_enqueue_scripts', function() {
			if ( self::TAXONOMY_SLUG === get_current_screen()->taxonomy ) {
				wp_add_inline_style( 'common', '
					#col-left { display: none; }
					#col-right { float:none; width: auto; }

					/* Improve column widths */
					td.column-details pre, td.column-sources pre { overflow:auto; }
					th.column-created_date_gmt { width:15%; }
					th.column-status { width:10%; }
				' );
			}
		} );

		// Show AMP validation errors under AMP admin menu.
		add_action( 'admin_menu', function() {
			$menu_item_label = esc_html__( 'Validation Errors', 'amp' );
			$new_error_count = self::get_validation_error_count( array(
				'group'        => self::VALIDATION_ERROR_NEW_STATUS,
				'ignore_empty' => true,
			) );
			if ( $new_error_count ) {
				$menu_item_label .= ' <span class="awaiting-mod"><span class="pending-count">' . esc_html( number_format_i18n( $new_error_count ) ) . '</span></span>';
			}

			add_submenu_page(
				AMP_Options_Manager::OPTION_NAME,
				esc_html__( 'Validation Errors', 'amp' ),
				$menu_item_label,
				get_taxonomy( self::TAXONOMY_SLUG )->cap->manage_terms, // Yes, cap is an object not an array.
				// The following esc_attr() is sadly needed due to <https://github.com/WordPress/wordpress-develop/blob/4.9.5/src/wp-admin/menu-header.php#L201>.
				esc_attr( 'edit-tags.php?taxonomy=' . self::TAXONOMY_SLUG . '&post_type=' . self::POST_TYPE_SLUG )
			);
		} );

		// Make sure parent menu item is expanded when visiting the taxonomy term page.
		add_filter( 'parent_file', function( $parent_file ) {
			if ( get_current_screen()->taxonomy === self::TAXONOMY_SLUG ) {
				$parent_file = AMP_Options_Manager::OPTION_NAME;
			}
			return $parent_file;
		}, 10, 2 );

		// Replace the primary column to be error instead of the removed name column..
		add_filter( 'list_table_primary_column', function( $primary_column ) {
			if ( self::TAXONOMY_SLUG === get_current_screen()->taxonomy ) {
				$primary_column = 'error';
			}
			return $primary_column;
		} );

		// Add views for filtering validation errors by status.
		add_filter( 'views_edit-' . self::POST_TYPE_SLUG, function( $views ) {
			unset( $views['publish'] );

			$args = array(
				'post_type'              => self::POST_TYPE_SLUG,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			$with_new_query          = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_NEW_STATUS )
			) );
			$with_acknowledged_query = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS )
			) );
			$with_ignored_query      = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_IGNORED_STATUS )
			) );

			$current_url = remove_query_arg(
				array_merge(
					wp_removable_query_args(),
					array( 's' ) // For some reason behavior of posts list table is to not persist the search query.
				),
				wp_unslash( $_SERVER['REQUEST_URI'] )
			);

			$current_status = null;
			if ( isset( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
				$value = intval( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ); // WPCS: CSRF ok.
				if ( in_array( $value, array( self::VALIDATION_ERROR_NEW_STATUS, self::VALIDATION_ERROR_IGNORED_STATUS, self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ), true ) ) {
					$current_status = $value;
				}
			}

			$views['new'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_NEW_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_NEW_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the post count */
					_nx(
						'With New Errors <span class="count">(%s)</span>',
						'With New Errors <span class="count">(%s)</span>',
						$with_new_query->found_posts,
						'posts',
						'amp'
					),
					number_format_i18n( $with_new_query->found_posts )
				)
			);

			$views['acknowledged'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the post count */
					_nx(
						'With Acknowledged Errors <span class="count">(%s)</span>',
						'With Acknowledged Errors <span class="count">(%s)</span>',
						$with_acknowledged_query->found_posts,
						'posts',
						'amp'
					),
					number_format_i18n( $with_acknowledged_query->found_posts )
				)
			);

			$views['ignored'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_IGNORED_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_IGNORED_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the post count */
					_nx(
						'With Ignored Errors <span class="count">(%s)</span>',
						'With Ignored Errors <span class="count">(%s)</span>',
						$with_ignored_query->found_posts,
						'posts',
						'amp'
					),
					number_format_i18n( $with_ignored_query->found_posts )
				)
			);

			return $views;
		} );

		// Add views for filtering validation errors by status.
		add_filter( 'views_edit-' . self::TAXONOMY_SLUG, function( $views ) {
			$total_term_count        = self::get_validation_error_count();
			$acknowledged_term_count = self::get_validation_error_count( array( 'group' => self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ) );
			$ignored_term_count      = self::get_validation_error_count( array( 'group' => self::VALIDATION_ERROR_IGNORED_STATUS ) );
			$new_term_count          = $total_term_count - $acknowledged_term_count - $ignored_term_count;

			$current_url = remove_query_arg(
				array_merge(
					wp_removable_query_args(),
					array( 's' ) // For some reason behavior of posts list table is to not persist the search query.
				),
				wp_unslash( $_SERVER['REQUEST_URI'] )
			);

			$current_status = null;
			if ( isset( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
				$value = intval( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ); // WPCS: CSRF ok.
				if ( in_array( $value, array( self::VALIDATION_ERROR_NEW_STATUS, self::VALIDATION_ERROR_IGNORED_STATUS, self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ), true ) ) {
					$current_status = $value;
				}
			}

			$views['all'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url( remove_query_arg( self::VALIDATION_ERROR_STATUS_QUERY_VAR, $current_url ) ),
				null === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the term count */
					_nx(
						'All <span class="count">(%s)</span>',
						'All <span class="count">(%s)</span>',
						$total_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $total_term_count )
				)
			);

			$views['new'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_NEW_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_NEW_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the term count */
					_nx(
						'New <span class="count">(%s)</span>',
						'New <span class="count">(%s)</span>',
						$new_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $new_term_count )
				)
			);

			$views['acknowledged'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the term count */
					_nx(
						'Acknowledged <span class="count">(%s)</span>',
						'Acknowledged <span class="count">(%s)</span>',
						$acknowledged_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $acknowledged_term_count )
				)
			);

			$views['ignored'] = sprintf(
				'<a href="%s" class="%s">%s</a>',
				esc_url(
					add_query_arg(
						self::VALIDATION_ERROR_STATUS_QUERY_VAR,
						self::VALIDATION_ERROR_IGNORED_STATUS,
						$current_url
					)
				),
				self::VALIDATION_ERROR_IGNORED_STATUS === $current_status ? 'current' : '',
				sprintf(
					/* translators: %s is the term count */
					_nx(
						'Ignored <span class="count">(%s)</span>',
						'Ignored <span class="count">(%s)</span>',
						$ignored_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $ignored_term_count )
				)
			);
			return $views;
		} );

		// Override the columns displayed for the validation error terms.
		add_filter( 'manage_edit-' . self::TAXONOMY_SLUG . '_columns', function( $old_columns ) {
			return array(
				'cb'               => $old_columns['cb'],
				'error'            => __( 'Error', 'amp' ),
				'created_date_gmt' => __( 'Created Date', 'amp' ),
				'status'           => __( 'Status', 'amp' ),
				'details'          => __( 'Details', 'amp' ),
				'sources'          => __( 'Sources', 'amp' ),
				'posts'            => __( 'URLs', 'amp' ),
			);
		} );

		// Let the created date column sort by term ID.
		add_filter( 'manage_edit-' . self::TAXONOMY_SLUG . '_sortable_columns', function( $sortable_columns ) {
			$sortable_columns['created_date_gmt'] = 'term_id';
			return $sortable_columns;
		} );

		// Supply the content for the custom columns.
		add_filter( 'manage_' . self::TAXONOMY_SLUG . '_custom_column', function( $content, $column_name, $term_id ) {
			$term = get_term( $term_id );

			$validation_error = json_decode( $term->description, true );
			if ( ! isset( $validation_error['code'] ) ) {
				$validation_error['code'] = 'unknown';
			}

			switch ( $column_name ) {
				case 'error':
					$content .= '<p>';
					$content .= sprintf( '<code>%s</code>', esc_html( $validation_error['code'] ) );
					if ( 'invalid_element' === $validation_error['code'] || 'invalid_attribute' === $validation_error['code'] ) {
						$content .= sprintf( ': <code>%s</code>', esc_html( $validation_error['node_name'] ) );
					}
					$content .= '</p>';

					if ( isset( $validation_error['message'] ) ) {
						$content .= sprintf( '<p>%s</p>', esc_html( $validation_error['message'] ) );
					}
					break;
				case 'status':
					if ( self::VALIDATION_ERROR_IGNORED_STATUS === $term->term_group ) {
						$content = esc_html__( 'Ignored', 'amp' );
					} elseif ( self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS === $term->term_group ) {
						$content = esc_html__( 'Acknowledged', 'amp' );
					} else {
						$content = esc_html__( 'New', 'amp' );
					}
					break;
				case 'created_date_gmt':
					$created_datetime = null;
					$created_date_gmt = get_term_meta( $term_id, 'created_date_gmt', true );
					if ( $created_date_gmt ) {
						try {
							$created_datetime = new DateTime( $created_date_gmt, new DateTimeZone( 'UTC' ) );
							$timezone_string  = get_option( 'timezone_string' );
							if ( ! $timezone_string && get_option( 'gmt_offset' ) ) {
								$timezone_string = timezone_name_from_abbr( '', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS, false );
							}
							if ( $timezone_string ) {
								$created_datetime->setTimezone( new DateTimeZone( get_option( 'timezone_string' ) ) );
							}
						} catch ( Exception $e ) {
							unset( $e );
						}
					}
					if ( ! $created_datetime ) {
						$time_ago = __( 'n/a', 'amp' );
					} elseif ( time() - $created_datetime->getTimestamp() < DAY_IN_SECONDS ) {
						/* translators: %s is the relative time */
						$time_ago = sprintf(
							'<abbr title="%s">%s</abbr>',
							esc_attr( $created_datetime->format( __( 'Y/m/d g:i:s a', 'default' ) ) ),
							/* translators: %s is relative time */
							esc_html( sprintf( __( '%s ago', 'default' ), human_time_diff( $created_datetime->getTimestamp() ) ) )
						);
					} else {
						$time_ago = mysql2date( __( 'Y/m/d g:i:s a', 'default' ), $created_date_gmt );
					}

					if ( $created_datetime ) {
						$time_ago = sprintf(
							'<time datetime="%s">%s</time>',
							$created_datetime->format( 'c' ),
							$time_ago
						);
					}
					$content .= $time_ago;

					break;
				case 'details':
					unset( $validation_error['code'] );
					unset( $validation_error['message'] );
					$content = sprintf( '<pre>%s</pre>', esc_html( wp_json_encode( $validation_error, 128 /* JSON_PRETTY_PRINT */ | 64 /* JSON_UNESCAPED_SLASHES */ ) ) );
					break;
				case 'sources':
					$sources = get_term_meta( $term_id, 'sources', true );
					if ( $sources ) {
						$sources = json_decode( $sources, true );
					}
					if ( ! is_array( $sources ) ) {
						$content .= sprintf( '<em>%s</em>', __( 'n/a', 'amp' ) );
					} else {
						$content = sprintf(
							'<details><summary>%s</summary><pre>%s</pre></details>',
							number_format_i18n( count( $sources ) ),
							esc_html( wp_json_encode( $sources, 128 /* JSON_PRETTY_PRINT */ | 64 /* JSON_UNESCAPED_SLASHES */ ) )
						);
					}
					break;
			}
			return $content;
		}, 10, 3 );

		// Hacikly remove amp_validation_error terms before they get bulk deleted (as workaround for WP_Terms_List_Table::column_cb()).
		add_action( 'load-edit-tags.php', function() {
			$is_delete_tags_request = isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] && ! empty( $_REQUEST['delete_tags'] ); // WPCS: CSRF ok.
			if ( ! $is_delete_tags_request ) {
				return;
			}
			$requested_delete_tags = array_map( 'intval', (array) $_REQUEST['delete_tags'] ); // WPCS: CSRF ok.
			$actual_delete_tags    = array();
			$blocked_delete_tags   = array();
			foreach ( $requested_delete_tags as $requested_delete_tag ) {
				$term = get_term( $requested_delete_tag );
				if ( $term && self::TAXONOMY_SLUG === $term->taxonomy && 0 !== $term->count ) {
					$blocked_delete_tags[] = $requested_delete_tag;
				} else {
					$actual_delete_tags[] = $requested_delete_tag;
				}
			}

			// Prevent deleting terms that shouldn't be deleted.
			$_POST['delete_tags']    = $actual_delete_tags;
			$_REQUEST['delete_tags'] = $actual_delete_tags;

			// Show admin notice when terms were blocked from being deleted.
			if ( ! empty( $blocked_delete_tags ) ) {
				add_filter( 'redirect_term_location', function( $url ) use ( $blocked_delete_tags ) {
					return add_query_arg( 'amp_validation_errors_not_deleted', count( $blocked_delete_tags ), $url );
				} );
			}

			// Remove success message if no terms were actually deleted.
			if ( empty( $actual_delete_tags ) ) {
				add_filter( 'redirect_term_location', function( $url ) {
					return remove_query_arg( 'message', $url );
				} );
			}
		} );

		// Show admin notice when validation error terms were skipped from being deleted due to still having associated URLs (workaround for WP_Terms_List_Table::column_cb()).
		add_action( 'admin_notices', function() {
			if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || empty( $_REQUEST['amp_validation_errors_not_deleted'] ) ) {
				return;
			}
			$count = intval( $_REQUEST['amp_validation_errors_not_deleted'] ); // WPCS: CSRF ok.
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %s is number of validation errors */
						_n(
							'%s validation error was not deleted because it still has occurrence on the site.',
							'%s validation errors were not deleted because they still have occurrences on the site.',
							$count,
							'amp'
						),
						number_format_i18n( $count )
					)
				)
			);
		} );

		// Prevent user from being able to delete validation errors when they still have associated invalid URLs.
		add_filter( 'user_has_cap', function( $allcaps, $caps, $args ) {
			if ( isset( $args[0] ) && 'delete_term' === $args[0] && 0 !== get_term( $args[2] )->count ) {
				/*
				 * However, only apply this if not on the edit terms screen for validation errors, since
				 * WP_Terms_List_Table::column_cb() unfortunately has a hard-coded delete_term capability check, so
				 * without that check passing then the checkbox is not shown.
				 */
				if ( self::TAXONOMY_SLUG === get_current_screen()->taxonomy && empty( $_REQUEST['action'] ) ) {
					return $allcaps;
				}

				$allcaps = array_merge(
					$allcaps,
					array_fill_keys( $caps, false )
				);
			}
			return $allcaps;
		}, 10, 3 );

		// Add row actions.
		add_filter( 'tag_row_actions', function( $actions, WP_Term $tag ) {
			if ( self::TAXONOMY_SLUG === $tag->taxonomy ) {
				$term_id = $tag->term_id;

				/*
				 * Hide deletion link when there are remaining invalid URLs associated with them.
				 * Note that this would normally be handled via the user_has_cap filter above,
				 * but this has to be here due to a problem with WP_Terms_List_Table::column_cb()
				 * which requires a workaround.
				 */
				if ( 0 !== $tag->count ) {
					unset( $actions['delete'] );
				}

				if ( self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS !== $tag->term_group ) {
					$actions[ self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION ] = sprintf(
						'<a href="%s" aria-label="%s">%s</a>',
						wp_nonce_url(
							add_query_arg( array_merge( array( 'action' => self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION ), compact( 'term_id' ) ) ),
							self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION
						),
						esc_attr__( 'Acknowledging an error marks it as read. AMP validation errors prevent a URL from being served as AMP.', 'amp' ),
						esc_html__( 'Acknowledge', 'amp' )
					);
				}
				if ( self::VALIDATION_ERROR_IGNORED_STATUS !== $tag->term_group ) {
					$actions[ self::VALIDATION_ERROR_IGNORE_ACTION ] = sprintf(
						'<a href="%s" aria-label="%s">%s</a>',
						wp_nonce_url(
							add_query_arg( array_merge( array( 'action' => self::VALIDATION_ERROR_IGNORE_ACTION ), compact( 'term_id' ) ) ),
							self::VALIDATION_ERROR_IGNORE_ACTION
						),
						esc_attr__( 'Ignoring an error prevents it from blocking a URL from being served as AMP.', 'amp' ),
						esc_html__( 'Ignore', 'amp' )
					);
				}
			}
			return $actions;
		}, 10, 2 );

		// Filter amp_validation_error term query by term group when requested.
		add_filter( 'get_terms_defaults', function( $args, $taxonomies ) {
			if ( array( self::TAXONOMY_SLUG ) === $taxonomies ) {
				$args['orderby'] = 'term_id';
				$args['order']   = 'DESC';
			}
			return $args;
		}, 10, 2 );

		// Filter amp_validation_error term query by term group when requested.
		add_action( 'load-edit-tags.php', function() {
			if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || ! isset( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
				return;
			}
			self::$should_filter_terms_clauses_for_error_validation_status = true;
			$group = intval( $_GET[ AMP_Validation_Utils::VALIDATION_ERROR_STATUS_QUERY_VAR ] ); // WPCS: CSRF ok.
			if ( ! in_array( $group, array( self::VALIDATION_ERROR_NEW_STATUS, self::VALIDATION_ERROR_IGNORED_STATUS, self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ), true ) ) {
				return;
			}
			add_filter( 'terms_clauses', function( $clauses, $taxonomies ) use ( $group ) {
				global $wpdb;
				if ( self::TAXONOMY_SLUG === $taxonomies[0] && self::$should_filter_terms_clauses_for_error_validation_status ) {
					$clauses['where'] .= $wpdb->prepare( ' AND t.term_group = %d', $group );
				}
				return $clauses;
			}, 10, 2 );
		} );

		// Handle inline edit links.
		add_action( 'load-edit-tags.php', function() {
			if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || ! isset( $_GET['action'] ) || ! isset( $_GET['_wpnonce'] ) || ! isset( $_GET['term_id'] ) ) { // WPCS: CSRF ok.
				return;
			}
			$action = sanitize_key( $_GET['action'] ); // WPCS: CSRF ok.
			check_admin_referer( $action );
			$tax = get_taxonomy( self::TAXONOMY_SLUG );
			if ( ! current_user_can( $tax->cap->manage_terms ) ) { // Yes it is an object.
				return;
			}

			$referer  = wp_get_referer();
			$term_id  = intval( $_GET['term_id'] ); // WPCS: CSRF ok.
			$redirect = self::handle_validation_error_update( $referer, $action, array( $term_id ) );

			if ( $redirect !== $referer ) {
				wp_safe_redirect( $redirect );
				exit;
			}
		} );

		// Add bulk actions.
		add_filter( 'bulk_actions-edit-' . self::TAXONOMY_SLUG, function( $bulk_actions ) {
			$bulk_actions[ self::VALIDATION_ERROR_IGNORE_ACTION ]      = __( 'Ignore', 'amp' );
			$bulk_actions[ self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION ] = __( 'Acknowledge', 'amp' );
			return $bulk_actions;
		} );

		// Handle bulk actions.
		add_filter( 'handle_bulk_actions-edit-' . self::TAXONOMY_SLUG, array( __CLASS__, 'handle_validation_error_update' ), 10, 3 );

		// Prevent query vars from persisting after redirect.
		add_filter( 'removable_query_args', function( $query_vars ) {
			$query_vars[] = 'amp_actioned';
			$query_vars[] = 'amp_actioned_count';
			$query_vars[] = 'amp_validation_errors_not_deleted';
			$query_vars[] = 'amp_remaining_errors';
			$query_vars[] = 'amp_urls_tested';
			return $query_vars;
		} );

		// Show notices for changes to amp_validation_error terms.
		add_action( 'admin_notices', function() {
			if ( ! ( self::TAXONOMY_SLUG === get_current_screen()->taxonomy || self::POST_TYPE_SLUG === get_current_screen()->post_type ) || empty( $_GET['amp_actioned'] ) || empty( $_GET['amp_actioned_count'] ) ) { // WPCS: CSRF ok.
				return;
			}
			$actioned = sanitize_key( $_GET['amp_actioned'] ); // WPCS: CSRF ok.
			$count    = intval( $_GET['amp_actioned_count'] ); // WPCS: CSRF ok.
			$message  = null;
			if ( self::VALIDATION_ERROR_IGNORE_ACTION === $actioned ) {
				$message = sprintf(
					/* translators: %s is number of errors ignored */
					_n(
						'Ignored %s error. It will no longer block related URLs from being served as AMP.',
						'Ignored %s errors. They will no longer block related URLs from being served as AMP.',
						number_format_i18n( $count ),
						'amp'
					),
					$count
				);
			} elseif ( self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION === $actioned ) {
				$message = sprintf(
					/* translators: %s is number of errors acknowledged */
					_n(
						'Acknowledged %s error. It will continue to block related URLs from being served as AMP.',
						'Acknowledged %s errors. They will continue to block related URLs from being served as AMP.',
						number_format_i18n( $count ),
						'amp'
					),
					$count
				);
			}

			if ( $message ) {
				printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $message ) );
			}
		} );
	}

	/**
	 * Get the count of validation error terms, optionally restricted by term group (e.g. ignored or acknowledged).
	 *
	 * @param array $args  {
	 *    Args passed into wp_count_terms().
	 *
	 *     @type int|null $group        Term group.
	 *     @type bool     $ignore_empty Ignore terms that are no longer associated with any URLs. Default false.
	 * }
	 * @return int Term count.
	 */
	public static function get_validation_error_count( $args = array() ) {
		$args = array_merge(
			array(
				'group'        => null,
				'ignore_empty' => false,
			),
			$args
		);

		$filter = function( $clauses ) use ( $args ) {
			global $wpdb;
			$clauses['where'] .= $wpdb->prepare( ' AND t.term_group = %d', $args['group'] );
			return $clauses;
		};
		if ( isset( $args['group'] ) ) {
			add_filter( 'terms_clauses', $filter );
		}
		self::$should_filter_terms_clauses_for_error_validation_status = false;
		$term_count = wp_count_terms( self::TAXONOMY_SLUG, $args );
		self::$should_filter_terms_clauses_for_error_validation_status = true;
		if ( isset( $args['group'] ) ) {
			remove_filter( 'terms_clauses', $filter );
		}
		return $term_count;
	}

	/**
	 * Handle bulk and inline edits to amp_validation_error terms.
	 *
	 * @param string $redirect_to Redirect to.
	 * @param string $action      Action.
	 * @param int[]  $term_ids    Term IDs.
	 *
	 * @return string Redirect.
	 */
	public static function handle_validation_error_update( $redirect_to, $action, $term_ids ) {
		$term_group = null;
		if ( self::VALIDATION_ERROR_IGNORE_ACTION === $action ) {
			$term_group = self::VALIDATION_ERROR_IGNORED_STATUS;
		} elseif ( self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION === $action ) {
			$term_group = self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS;
		}

		if ( $term_group ) {
			foreach ( $term_ids as $term_id ) {
				wp_update_term( $term_id, self::TAXONOMY_SLUG, compact( 'term_group' ) );
			}
			$redirect_to = add_query_arg(
				array(
					'amp_actioned'       => $action,
					'amp_actioned_count' => count( $term_ids ),
				),
				$redirect_to
			);
		}

		return $redirect_to;
	}

	/**
	 * Stores the validation errors.
	 *
	 * If there are no validation errors provided, then any existing amp_invalid_url post is deleted.
	 *
	 * @param array  $validation_errors Validation errors.
	 * @param string $url               URL on which the validation errors occurred.
	 * @return int|WP_Error $post_id The post ID of the custom post type used, null if post was deleted due to no validation errors, or WP_Error on failure.
	 * @global WP $wp
	 */
	public static function store_validation_errors( $validation_errors, $url ) {
		$post_slug = md5( $url );
		$post      = get_page_by_path( $post_slug, OBJECT, self::POST_TYPE_SLUG );
		if ( ! $post ) {
			$post = get_page_by_path( $post_slug . '__trashed', OBJECT, self::POST_TYPE_SLUG );
		}

		// Since there are no validation errors and there is an existing $existing_post_id, just delete the post.
		if ( empty( $validation_errors ) ) {
			if ( $post ) {
				wp_delete_post( $post->ID, true );
			}
			return null;
		}

		// Keep track of the original order of the validation errors, and when there are duplicates of a given error.
		$ordered_validation_error_hashes = array();

		$terms = array();
		foreach ( $validation_errors as $data ) {
			/*
			 * Exclude sources from data since not available unless sources are being obtained,
			 * and thus not able to be matched when hashed.
			 */
			$sources = null;
			if ( isset( $data['sources'] ) ) {
				$sources = $data['sources'];
				unset( $data['sources'] );
			}
			ksort( $data );
			$description = wp_json_encode( $data );
			$term_slug   = md5( $description );

			if ( ! isset( $terms[ $term_slug ] ) ) {

				// Not using WP_Term_Query since more likely individual terms are cached and wp_insert_term() will itself look at this cache anyway.
				$term = get_term_by( 'slug', $term_slug, self::TAXONOMY_SLUG );
				if ( ! ( $term instanceof WP_Term ) ) {
					$r = wp_insert_term( $term_slug, self::TAXONOMY_SLUG, wp_slash( compact( 'description' ) ) );
					if ( is_wp_error( $r ) ) {
						continue;
					}
					$term_id = $r['term_id'];
					update_term_meta( $term_id, 'created_date_gmt', current_time( 'mysql', true ) );
					update_term_meta( $term_id, 'sources', wp_slash( wp_json_encode( $sources ) ) );
					$term = get_term( $term_id );
				}
				$terms[ $term_slug ] = $term;
			}

			$ordered_validation_error_hashes[] = $term_slug;
		}

		// Create a new invalid AMP URL post, or update the existing one.
		$r = wp_insert_post(
			wp_slash( array(
				'ID'           => $post ? $post->ID : null,
				'post_type'    => self::POST_TYPE_SLUG,
				'post_title'   => $url,
				'post_name'    => $post_slug,
				'post_content' => implode( "\n", $ordered_validation_error_hashes ),
				'post_status'  => 'publish', // @todo Use draft when doing a post preview?
			) ),
			true
		);
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$post_id = $r;
		wp_set_object_terms( $post_id, wp_list_pluck( $terms, 'term_id' ), self::TAXONOMY_SLUG );
		return $post_id;
	}

	/**
	 * Gets the existing custom post that stores errors for the $url, if it exists.
	 *
	 * @param string $url The URL of the post.
	 * @return WP_Post|null The post of the existing custom post, or null.
	 */
	public static function get_invalid_url_post( $url ) {
		return get_page_by_path( md5( $url ), OBJECT, self::POST_TYPE_SLUG );
	}

	/**
	 * Gets validation errors for a given invalid URL post.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 * @param array       $args {
	 *     Args.
	 *
	 *     @type bool $ignore_ignored Exclude validation errors that are ignored. Default false.
	 * }
	 * @return array List of errors.
	 */
	public static function get_invalid_url_validation_errors( $post, $args = array() ) {
		$args   = array_merge(
			array(
				'ignore_ignored' => false,
			),
			$args
		);
		$post   = get_post( $post );
		$errors = array();
		foreach ( array_filter( explode( "\n", $post->post_content ) ) as $term_slug ) {
			if ( ! preg_match( '/^[0-9a-f]{32}$/', $term_slug ) ) {
				continue;
			}
			$term = get_term_by( 'slug', $term_slug, self::TAXONOMY_SLUG );
			if ( ! $term ) {
				continue;
			}
			if ( $args['ignore_ignored'] && self::VALIDATION_ERROR_IGNORED_STATUS === $term->term_group ) {
				continue;
			}
			$errors[] = array(
				'term' => $term,
				'data' => json_decode( $term->description, true ),
			);
		}
		return $errors;
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
			'cookies'   => wp_unslash( $_COOKIE ), // @todo Passing-along the credentials of the currently-authenticated user prevents this from working in cron.
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
				'error_status'               => esc_html__( 'Error Status', 'amp' ),
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

		// @todo Move into helper function.
		$counts = array_fill_keys(
			array(
				self::VALIDATION_ERROR_NEW_STATUS,
				self::VALIDATION_ERROR_IGNORED_STATUS,
				self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS,
			),
			0
		);

		// @todo Move into helper function.
		$validation_errors = self::get_invalid_url_validation_errors( $post_id );
		foreach ( wp_list_pluck( $validation_errors, 'term' ) as $term ) {
			if ( isset( $counts[ $term->term_group ] ) ) {
				$counts[ $term->term_group ]++;
			}
		}

		$error_summary = self::summarize_validation_errors( wp_list_pluck( $validation_errors, 'data' ) );

		switch ( $column_name ) {
			case 'error_status':
				$displayed_counts = array();
				if ( $counts[ self::VALIDATION_ERROR_NEW_STATUS ] ) {
					$displayed_counts[] = esc_html( sprintf(
						/* translators: %s is count */
						__( 'New: %s', 'amp' ),
						number_format_i18n( $counts[ self::VALIDATION_ERROR_NEW_STATUS ] )
					) );
				}
				if ( $counts[ self::VALIDATION_ERROR_IGNORED_STATUS ] ) {
					$displayed_counts[] = esc_html( sprintf(
						/* translators: %s is count */
						__( 'Ignored: %s', 'amp' ),
						number_format_i18n( $counts[ self::VALIDATION_ERROR_IGNORED_STATUS ] )
					) );
				}
				if ( $counts[ self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ] ) {
					$displayed_counts[] = esc_html( sprintf(
						/* translators: %s is count */
						__( 'Acknowledged: %s', 'amp' ),
						number_format_i18n( $counts[ self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ] )
					) );
				}
				echo implode( '<br>', $displayed_counts ); // WPCS: xss ok.
				break;
			case self::REMOVED_ELEMENTS:
				if ( ! empty( $error_summary[ self::REMOVED_ELEMENTS ] ) ) {
					self::output_removed_set( $error_summary[ self::REMOVED_ELEMENTS ] );
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case self::REMOVED_ATTRIBUTES:
				if ( ! empty( $error_summary[ self::REMOVED_ATTRIBUTES ] ) ) {
					self::output_removed_set( $error_summary[ self::REMOVED_ATTRIBUTES ] );
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case self::SOURCES_INVALID_OUTPUT:
				if ( isset( $error_summary[ self::SOURCES_INVALID_OUTPUT ] ) ) {
					$sources = array();
					foreach ( $error_summary[ self::SOURCES_INVALID_OUTPUT ] as $type => $names ) {
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
		$url = $post->post_title;

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
			$post = get_post( $item );
			if ( empty( $post ) ) {
				continue;
			}
			$url = $post->post_title;
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
		$post = get_post( $post_id );
		$url  = $post->post_title;
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
		printf( __( 'Last checked: <b>%s</b>', 'amp' ), esc_html( date_i18n( $date_format, strtotime( $post->post_date ) ) ) ); // WPCS: XSS ok.
		echo '</span></div>';

		// @todo Move into helper function.
		$counts = array_fill_keys(
			array(
				self::VALIDATION_ERROR_NEW_STATUS,
				self::VALIDATION_ERROR_IGNORED_STATUS,
				self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS,
			),
			0
		);

		// @todo Move into helper function.
		$validation_errors = self::get_invalid_url_validation_errors( $post );
		foreach ( wp_list_pluck( $validation_errors, 'term' ) as $term ) {
			if ( isset( $counts[ $term->term_group ] ) ) {
				$counts[ $term->term_group ]++;
			}
		}

		// @todo De-duplicate with other place where logic is run.
		$displayed_counts = array();
		if ( $counts[ self::VALIDATION_ERROR_NEW_STATUS ] ) {
			$displayed_counts[] = esc_html( sprintf(
				/* translators: %s is count */
				__( 'New: %s', 'amp' ),
				number_format_i18n( $counts[ self::VALIDATION_ERROR_NEW_STATUS ] )
			) );
		}
		if ( $counts[ self::VALIDATION_ERROR_IGNORED_STATUS ] ) {
			$displayed_counts[] = esc_html( sprintf(
				/* translators: %s is count */
				__( 'Ignored: %s', 'amp' ),
				number_format_i18n( $counts[ self::VALIDATION_ERROR_IGNORED_STATUS ] )
			) );
		}
		if ( $counts[ self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ] ) {
			$displayed_counts[] = esc_html( sprintf(
				/* translators: %s is count */
				__( 'Acknowledged: %s', 'amp' ),
				number_format_i18n( $counts[ self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS ] )
			) );
		}

		echo '<div class="misc-pub-section">';
		echo implode( '<br>', $displayed_counts ); // WPCS: xss ok.
		echo '</div>';

		printf( '<div class="misc-pub-section"><a class="submitdelete deletion" href="%s">%s</a></div>', esc_url( get_delete_post_link( $post->ID ) ), esc_html__( 'Move to Trash', 'default' ) );
		$url = $post->post_title;

		echo '<div class="misc-pub-section">';
		printf( '<a href="%s">%s</a> | ', esc_url( $url ), esc_html__( 'View', 'amp' ) );
		echo self::get_recheck_link( $post, $redirect_url ); // WPCS: XSS ok.
		printf(
			' | <a href="%s" aria-label="%s">%s</a>',
			esc_url( self::get_debug_url( $url ) ),
			esc_attr__( 'Validate URL on frontend but without invalid elements/attributes removed', 'amp' ),
			esc_html__( 'Debug', 'amp' )
		); // WPCS: XSS ok.
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
		$validation_errors = self::get_invalid_url_validation_errors( $post );
		?>
		<style>
			.amp-validation-errors .detailed,
			.amp-validation-errors .actions {
				margin-left: 30px;
			}
			.amp-validation-errors pre {
				overflow: auto;
			}
		</style>
		<div class="amp-validation-errors">
			<ul>
				<?php foreach ( $validation_errors as $error ) : ?>
					<?php
					$collasped_details = array();
					$term              = $error['term'];
					$term_id           = $term->term_id;
					$edit_terms_url    = admin_url( 'edit-tags.php?taxonomy=' . self::TAXONOMY_SLUG );
					?>
					<li>
						<details <?php echo ( self::VALIDATION_ERROR_NEW_STATUS === $term->term_group ) ? 'open' : ''; ?>>
							<summary>
								<?php if ( self::VALIDATION_ERROR_NEW_STATUS === $term->term_group ) : ?>
									<?php esc_html_e( '[New]', 'amp' ); ?>
								<?php elseif ( self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS === $term->term_group ) : ?>
									<?php esc_html_e( '[Acknowledged]', 'amp' ); ?>
								<?php elseif ( self::VALIDATION_ERROR_IGNORED_STATUS === $term->term_group ) : ?>
									<?php esc_html_e( '[Ignored]', 'amp' ); ?>
								<?php endif; ?>
								<code><?php echo esc_html( $error['data']['code'] ); ?></code>
							</summary>
							<p class="actions">
								<?php
								$actions = array();
								if ( self::VALIDATION_ERROR_ACKNOWLEDGED_STATUS !== $term->term_group ) {
									$actions[ self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION ] = sprintf(
										'<a href="%s" aria-label="%s">%s</a>',
										wp_nonce_url(
											add_query_arg(
												array_merge( array( 'action' => self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION ), compact( 'term_id' ) ),
												$edit_terms_url
											),
											self::VALIDATION_ERROR_ACKNOWLEDGE_ACTION
										),
										esc_attr__( 'Acknowledging an error marks it as read. AMP validation errors prevent a URL from being served as AMP.', 'amp' ),
										esc_html__( 'Acknowledge', 'amp' )
									);
								}
								if ( self::VALIDATION_ERROR_IGNORED_STATUS !== $term->term_group ) {
									$actions[ self::VALIDATION_ERROR_IGNORE_ACTION ] = sprintf(
										'<a href="%s" aria-label="%s">%s</a>',
										wp_nonce_url(
											add_query_arg(
												array_merge( array( 'action' => self::VALIDATION_ERROR_IGNORE_ACTION ), compact( 'term_id' ) ),
												$edit_terms_url
											),
											self::VALIDATION_ERROR_IGNORE_ACTION
										),
										esc_attr__( 'Ignoring an error prevents it from blocking a URL from being served as AMP.', 'amp' ),
										esc_html__( 'Ignore', 'amp' )
									);
								}
								echo implode( ' | ', $actions ); // WPCS: xss ok.
								?>
							</p>
							<ul class="detailed">
							<?php if ( self::INVALID_ELEMENT_CODE === $error['data']['code'] ) : ?>
								<li>
									<details open>
										<summary><?php esc_html_e( 'Removed:', 'amp' ); ?></summary>
										<code class="detailed">
											<?php
											if ( isset( $error['data']['parent_name'] ) ) {
												echo esc_html( sprintf( '<%s …>', $error['data']['parent_name'] ) );
											}
											?>
											<mark>
												<?php
												echo esc_html( sprintf( '<%s', $error['data']['node_name'] ) );
												if ( isset( $error['data']['node_attributes'] ) ) {
													foreach ( $error['data']['node_attributes'] as $key => $value ) {
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
							<?php elseif ( self::INVALID_ATTRIBUTE_CODE === $error['data']['code'] ) : ?>
								<li>
									<details open>
										<summary><?php esc_html_e( 'Removed:', 'amp' ); ?></summary>
										<code class="detailed">
											<?php
											if ( isset( $error['data']['parent_name'] ) ) {
												echo esc_html( sprintf( '<%s', $error['data']['parent_name'] ) );
											}
											foreach ( $error['data']['element_attributes'] as $key => $value ) {
												if ( $key === $error['data']['node_name'] ) {
													echo '<mark>';
												}
												printf( ' %s="%s"', esc_html( $key ), esc_html( $value ) );
												if ( $key === $error['data']['node_name'] ) {
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
								<?php unset( $error['data']['code'] ); ?>
								<?php foreach ( $error['data'] as $key => $value ) : ?>
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
				self::VALIDATE_QUERY_VAR => '',
				self::DEBUG_QUERY_VAR    => '',
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

	/**
	 * Enqueues the block validation script.
	 *
	 * @return void
	 */
	public static function enqueue_block_validation() {
		$slug = 'amp-block-validation';

		wp_enqueue_script(
			$slug,
			amp_get_asset_url( "js/{$slug}.js" ),
			array( 'underscore' ),
			AMP__VERSION,
			true
		);

		$data = wp_json_encode( array(
			'i18n'                 => gutenberg_get_jed_locale_data( 'amp' ), // @todo POT file.
			'ampValidityRestField' => self::VALIDITY_REST_FIELD_NAME,
		) );
		wp_add_inline_script( $slug, sprintf( 'ampBlockValidation.boot( %s );', $data ) );
	}

	/**
	 * Adds fields to the REST API responses, in order to display validation errors.
	 *
	 * @return void
	 */
	public static function add_rest_api_fields() {
		if ( amp_is_canonical() ) {
			$object_types = get_post_types_by_support( 'editor' );
		} else {
			$object_types = array_intersect(
				get_post_types_by_support( 'amp' ),
				get_post_types( array(
					'show_in_rest' => true,
				) )
			);
		}

		register_rest_field(
			$object_types,
			self::VALIDITY_REST_FIELD_NAME,
			array(
				'get_callback' => array( __CLASS__, 'get_amp_validity_rest_field' ),
				'schema'       => array(
					'description' => __( 'AMP validity status', 'amp' ),
					'type'        => 'object',
				),
			)
		);
	}

	/**
	 * Adds a field to the REST API responses to display the validation status.
	 *
	 * First, get existing errors for the post.
	 * If there are none, validate the post and return any errors.
	 *
	 * @param array           $post_data  Data for the post.
	 * @param string          $field_name The name of the field to add.
	 * @param WP_REST_Request $request    The name of the field to add.
	 * @return array|null $validation_data Validation data if it's available, or null.
	 */
	public static function get_amp_validity_rest_field( $post_data, $field_name, $request ) {
		unset( $field_name );
		if ( ! current_user_can( 'edit_post', $post_data['id'] ) ) {
			return null;
		}
		$post = get_post( $post_data['id'] );

		$validation_status_post = null;
		if ( in_array( $request->get_method(), array( 'PUT', 'POST' ), true ) ) {
			if ( ! isset( self::$posts_pending_frontend_validation[ $post->ID ] ) ) {
				self::$posts_pending_frontend_validation[ $post->ID ] = true;
			}
			$results = self::validate_queued_posts_on_frontend();
			if ( isset( $results[ $post->ID ] ) && is_int( $results[ $post->ID ] ) ) {
				$validation_status_post = get_post( $results[ $post->ID ] );
			}
		}

		if ( empty( $validation_status_post ) ) {
			// @todo Consider process_markup() if not post type is not viewable and if post type supports editor.
			$validation_status_post = self::get_invalid_url_post( amp_get_permalink( $post->ID ) );
		}

		$field = array(
			'errors'      => array(),
			'review_link' => null,
			'debug_link'  => self::get_debug_url( amp_get_permalink( $post_data['id'] ) ),
		);

		if ( $validation_status_post ) {
			$field = array_merge(
				$field,
				array(
					'review_link' => get_edit_post_link( $validation_status_post->ID, 'raw' ),
					'errors'      => wp_list_pluck(
						self::get_invalid_url_validation_errors( $validation_status_post, array( 'ignore_ignored' => true ) ),
						'data'
					),
				)
			);
		}

		return $field;
	}

	/**
	 * Outputs an admin notice if persistent object cache is not present.
	 *
	 * @return void
	 */
	public static function persistent_object_caching_notice() {
		if ( ! wp_using_ext_object_cache() && 'toplevel_page_amp-options' === get_current_screen()->id ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'The AMP plugin performs at its best when persistent object cache is enabled.', 'amp' ),
				esc_url( 'https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching' ),
				esc_html__( 'More details', 'amp' )
			);
		}
	}

}

