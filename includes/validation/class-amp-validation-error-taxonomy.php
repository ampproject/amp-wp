<?php
/**
 * Class AMP_Validation_Error_Taxonomy
 *
 * @package AMP
 */

/**
 * Class AMP_Validation_Error_Taxonomy
 *
 * @since 1.0
 */
class AMP_Validation_Error_Taxonomy {

	/**
	 * The slug of the taxonomy to store AMP errors.
	 *
	 * @var string
	 */
	const TAXONOMY_SLUG = 'amp_validation_error';

	/**
	 * Term group for validation_error terms have not yet been acknowledged.
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_NEW_STATUS = 0;

	/**
	 * Term group for validation_error terms that the accepts and thus can be sanitized and does not disable AMP.
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_ACCEPTED_STATUS = 1;

	/**
	 * Term group for validation_error terms that the user flags as being blockers to enabling AMP.
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_REJECTED_STATUS = 2;

	/**
	 * Action name for ignoring a validation error.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_ACCEPT_ACTION = 'amp_validation_error_accept';

	/**
	 * Action name for rejecting a validation error.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_REJECT_ACTION = 'amp_validation_error_reject';

	/**
	 * Query var used when filtering by validation error status or passing updates.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_STATUS_QUERY_VAR = 'amp_validation_error_status';

	/**
	 * Query var used when filtering for the validation error type.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_TYPE_QUERY_VAR = 'amp_validation_error_type';

	/**
	 * Query var used for ordering list by node name.
	 *
	 * @var string
	 */
	const VALIDATION_DETAILS_NODE_NAME_QUERY_VAR = 'amp_validation_node_name';

	/**
	 * Query var used for ordering list by error code.
	 *
	 * @var string
	 */
	const VALIDATION_DETAILS_ERROR_CODE_QUERY_VAR = 'amp_validation_code';

	/**
	 * The <option> value to not filter at all, like for 'All Statuses'.
	 *
	 * This is also used in WP_List_Table, like for the 'Bulk Actions' option.
	 * When this is present, this ensures that this isn't filtered.
	 *
	 * @var int
	 */
	const NO_FILTER_VALUE = -1;

	/**
	 * The ID for the link to the Errors by URL.
	 *
	 * @var string
	 */
	const ID_LINK_ERRORS_BY_URL = 'link-errors-url';

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
	 * The 'type' of error for invalid HTML elements, like <frame>.
	 *
	 * These usually have the 'code' of 'invalid_element'.
	 * Except for 'invalid_element' errors for a <script>, which have the JS_ERROR_TYPE.
	 * This allows filtering by type in the taxonomy page, like displaying only HTML element errors, or only CSS errors.
	 *
	 * @var string
	 */
	const HTML_ELEMENT_ERROR_TYPE = 'html_element_error';

	/**
	 * The 'type' of error for invalid HTML attributes.
	 *
	 * These usually have the 'code' of 'invalid_attribute'.
	 * Banned attributes include i-amp-*.
	 * But on* attributes, like onclick, have the JS_ERROR_TYPE.
	 *
	 * @var string
	 */
	const HTML_ATTRIBUTE_ERROR_TYPE = 'html_attribute_error';

	/**
	 * The 'type' of error that applies to the error 'code' of 'invalid_element' when the node is a <script>.
	 * This applies both when enqueuing a script, and when a <script> is echoed directly.
	 *
	 * @var string
	 */
	const JS_ERROR_TYPE = 'js_error';

	/**
	 * The 'type' of all CSS errors, no matter what the 'code'.
	 *
	 * @var string
	 */
	const CSS_ERROR_TYPE = 'css_error';

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
	 * The key in the response for the sources that have invalid output.
	 *
	 * @var string
	 */
	const SOURCES_INVALID_OUTPUT = 'sources_with_invalid_output';

	/**
	 * The key for removed sources.
	 *
	 * @var string
	 */
	const REMOVED_SOURCES = 'removed_sources';

	/**
	 * Whether the terms_clauses filter should apply to a term query for validation errors to limit to a given status.
	 *
	 * This is set to false when calling wp_count_terms() for the admin menu and for the views.
	 *
	 * @see AMP_Validation_Manager::get_validation_error_count()
	 * @var bool
	 */
	protected static $should_filter_terms_clauses_for_error_validation_status;

	/**
	 * Registers the taxonomy to store the validation errors.
	 *
	 * @return void
	 */
	public static function register() {

		register_taxonomy( self::TAXONOMY_SLUG, AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, array(
			'labels'             => array(
				'name'                  => _x( 'AMP Validation Error Index', 'taxonomy general name', 'amp' ),
				'singular_name'         => _x( 'AMP Validation Error', 'taxonomy singular name', 'amp' ),
				'search_items'          => __( 'Search AMP Validation Errors', 'amp' ),
				'all_items'             => __( 'All AMP Validation Errors', 'amp' ),
				'edit_item'             => __( 'Edit AMP Validation Error', 'amp' ),
				'update_item'           => __( 'Update AMP Validation Error', 'amp' ),
				'menu_name'             => __( 'Error Index', 'amp' ),
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
			'show_in_menu'       => ( self::should_show_in_menu() || AMP_Invalid_URL_Post_Type::should_show_in_menu() ),
			'meta_box_cb'        => false, // See print_validation_errors_meta_box().
			'capabilities'       => array(
				'assign_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				// Note that delete_terms is needed so the checkbox (cb) table column will work.
			),
		) );

		if ( is_admin() ) {
			self::add_admin_hooks();
		}

		self::accept_validation_errors( AMP_Core_Theme_Sanitizer::get_acceptable_errors( get_template() ) );
	}

	/**
	 * Determine whether the admin menu item should be included.
	 *
	 * @return bool Whether to show in menu.
	 */
	public static function should_show_in_menu() {
		global $pagenow;
		if ( current_theme_supports( 'amp' ) ) {
			return true;
		}
		return ( 'edit-tags.php' === $pagenow && ( isset( $_GET['taxonomy'] ) && self::TAXONOMY_SLUG === $_GET['taxonomy'] ) ); // WPCS: CSRF OK.
	}

	/**
	 * Prepare a validation error for lookup or insertion as taxonomy term.
	 *
	 * @param array $error Validation error.
	 * @return array Term fields.
	 */
	public static function prepare_validation_error_taxonomy_term( $error ) {
		unset( $error['sources'] );
		ksort( $error );
		$description = wp_json_encode( $error );
		$term_slug   = md5( $description );
		return array(
			'slug'        => $term_slug,
			'name'        => $term_slug,
			'description' => $description,
		);
	}

	/**
	 * Determine whether a validation error should be sanitized.
	 *
	 * @param array $error Validation error.
	 *
	 * @return bool Whether error should be sanitized.
	 */
	public static function is_validation_error_sanitized( $error ) {
		$sanitization = self::get_validation_error_sanitization( $error );
		return self::VALIDATION_ERROR_ACCEPTED_STATUS === $sanitization['status'];
	}

	/**
	 * Get the validation error sanitization.
	 *
	 * @param array $error Validation error.
	 *
	 * @return array {
	 *     Validation error sanitization.
	 *
	 *     @type int          $status      Validation status (0=VALIDATION_ERROR_NEW_STATUS, 1=VALIDATION_ERROR_ACCEPTED_STATUS, 2=VALIDATION_ERROR_REJECTED_STATUS).
	 *     @type int          $term_status The initial validation status prior to being overridden by previewing, option, or filter.
	 *     @type false|string $forced      If and how the status is overridden from its initial term status.
	 * }
	 */
	public static function get_validation_error_sanitization( $error ) {
		$term_data = self::prepare_validation_error_taxonomy_term( $error );
		$term      = get_term_by( 'slug', $term_data['slug'], self::TAXONOMY_SLUG );
		$statuses  = array(
			self::VALIDATION_ERROR_NEW_STATUS,
			self::VALIDATION_ERROR_ACCEPTED_STATUS,
			self::VALIDATION_ERROR_REJECTED_STATUS,
		);
		if ( ! empty( $term ) && in_array( $term->term_group, $statuses, true ) ) {
			$term_status = $term->term_group;
		} else {
			$term_status = self::VALIDATION_ERROR_NEW_STATUS;
		}

		$forced = false;
		$status = $term_status;

		// See note in AMP_Validation_Manager::add_validation_error_sourcing() for why amp_validation_error_sanitized filter isn't used.
		if ( isset( AMP_Validation_Manager::$validation_error_status_overrides[ $term_data['slug'] ] ) ) {
			$status = AMP_Validation_Manager::$validation_error_status_overrides[ $term_data['slug'] ];
			$forced = 'with_preview';
		}

		$is_forced = (
			amp_is_canonical()
			||
			AMP_Options_Manager::get_option( 'force_sanitization' )
		);
		if ( $is_forced ) {
			$forced = 'with_option';
			$status = self::VALIDATION_ERROR_ACCEPTED_STATUS;
		}

		/**
		 * Filters whether the validation error should be sanitized.
		 *
		 * Returning true this indicates that the validation error is acceptable
		 * and should not be considered a blocker to render AMP. Returning null
		 * means that the default status should be used.
		 *
		 * Note that the $node is not passed here to ensure that the filter can be
		 * applied on validation errors that have been stored. Likewise, the $sources
		 * are also omitted because these are only available during an explicit
		 * validation request and so they are not suitable for plugins to vary
		 * sanitization by.
		 *
		 * @since 1.0
		 *
		 * @param null|bool $sanitized Whether sanitized; this is initially null, and changing it to bool causes the validation error to be forced.
		 * @param array $error Validation error being sanitized.
		 */
		$sanitized = apply_filters( 'amp_validation_error_sanitized', null, $error );

		if ( null !== $sanitized ) {
			$forced = 'with_filter';
			$status = $sanitized ? self::VALIDATION_ERROR_ACCEPTED_STATUS : self::VALIDATION_ERROR_REJECTED_STATUS;
		}

		return compact( 'status', 'forced', 'term_status' );
	}

	/**
	 * Automatically (forcibly) accept validation errors that arise.
	 *
	 * @since 1.0
	 * @see AMP_Core_Theme_Sanitizer::get_acceptable_errors()
	 *
	 * @param array|true $acceptable_errors Acceptable validation errors, where keys are codes and values are either `true` or sparse array to check as subset. If just true, then all validation errors are accepted.
	 */
	public static function accept_validation_errors( $acceptable_errors ) {
		if ( empty( $acceptable_errors ) ) {
			return;
		}
		add_filter( 'amp_validation_error_sanitized', function( $sanitized, $error ) use ( $acceptable_errors ) {
			if ( true === $acceptable_errors ) {
				return true;
			}

			if ( isset( $acceptable_errors[ $error['code'] ] ) ) {
				if ( true === $acceptable_errors[ $error['code'] ] ) {
					return true;
				}
				foreach ( $acceptable_errors[ $error['code'] ] as $acceptable_error_props ) {
					if ( AMP_Validation_Error_Taxonomy::is_array_subset( $error, $acceptable_error_props ) ) {
						return true;
					}
				}
			}
			return $sanitized;
		}, 10, 2 );
	}

	/**
	 * Check if one array is a sparse subset of another array.
	 *
	 * @param array $superset Superset array.
	 * @param array $subset   Subset array.
	 *
	 * @return bool Whether subset is contained in superset.
	 */
	public static function is_array_subset( $superset, $subset ) {
		foreach ( $subset as $key => $subset_value ) {
			if ( ! isset( $superset[ $key ] ) || gettype( $subset_value ) !== gettype( $superset[ $key ] ) ) {
				return false;
			}
			if ( is_array( $subset_value ) ) {
				if ( ! self::is_array_subset( $superset[ $key ], $subset_value ) ) {
					return false;
				}
			} elseif ( $superset[ $key ] !== $subset_value ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the count of validation error terms, optionally restricted by term group (e.g. accepted or rejected).
	 *
	 * @param array $args  {
	 *    Args passed into wp_count_terms().
	 *
	 *     @type int|null $group        Term group.
	 * }
	 * @return int Term count.
	 */
	public static function get_validation_error_count( $args = array() ) {
		$args = array_merge(
			array(
				'group' => null,
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
	 * Add support for querying posts by amp_validation_error_status and by error type.
	 *
	 * Add recognition of amp_validation_error_status query var for amp_invalid_url post queries.
	 * Also, conditionally filter for error type, like js_error or css_error.
	 *
	 * @see WP_Tax_Query::get_sql_for_clause()
	 *
	 * @param string   $where SQL WHERE clause.
	 * @param WP_Query $query Query.
	 * @return string Modified WHERE clause.
	 */
	public static function filter_posts_where_for_validation_error_status( $where, WP_Query $query ) {
		global $wpdb;

		// If the post type is not correct, return the $where clause unchanged.
		if ( ! in_array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, (array) $query->get( 'post_type' ), true ) ) {
			return $where;
		}

		$error_status = sanitize_key( $query->get( self::VALIDATION_ERROR_STATUS_QUERY_VAR ) );
		$error_type   = sanitize_key( $query->get( self::VALIDATION_ERROR_TYPE_QUERY_VAR ) );

		/*
		 * Selecting the 'All Statuses' <option> sends a value of '-1' to indicate that this should not filter.
		 * So ensure that the value is_numeric(), and is not '-1'.
		 */
		$is_error_status_present = is_numeric( $error_status ) && self::NO_FILTER_VALUE !== intval( $error_status );
		$is_error_type_present   = in_array( $error_type, self::get_error_types(), true );

		// If neither the error status nor the type is present, there is no need to filter the $where clause.
		if ( ! $is_error_status_present && ! $is_error_type_present ) {
			return $where;
		}

		$sql_select = $wpdb->prepare(
			"
				SELECT 1
				FROM $wpdb->term_relationships
				INNER JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
				INNER JOIN $wpdb->terms ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
				WHERE
					$wpdb->term_taxonomy.taxonomy = %s
					AND
					$wpdb->term_relationships.object_id = $wpdb->posts.ID
			",
			self::TAXONOMY_SLUG
		);

		if ( $is_error_status_present ) {
			$sql_select .= $wpdb->prepare(
				" AND $wpdb->terms.term_group = %s ",
				$error_status
			);
		}

		if ( $is_error_type_present ) {
			$sql_select .= $wpdb->prepare(
				" AND $wpdb->term_taxonomy.description LIKE %s ",
				'%"type":"' . $wpdb->esc_like( $error_type ) . '"%'
			);
		}

		$sql_select .= ' LIMIT 1 ';

		$where .= " AND ( $sql_select ) ";

		return $where;
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
				$source = array_shift( $validation_error['sources'] );

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
			$results
		);

		return $results;
	}

	/**
	 * Add admin hooks.
	 */
	public static function add_admin_hooks() {
		add_filter( 'redirect_term_location', array( __CLASS__, 'add_term_filter_query_var' ), 10, 2 );
		add_action( 'load-edit-tags.php', array( __CLASS__, 'add_group_terms_clauses_filter' ) );
		add_action( 'load-edit-tags.php', array( __CLASS__, 'add_error_type_clauses_filter' ) );
		add_action( 'load-edit-tags.php', array( __CLASS__, 'add_order_clauses_from_description_json' ) );
		add_action( sprintf( 'after-%s-table', self::TAXONOMY_SLUG ), array( __CLASS__, 'render_taxonomy_filters' ) );
		add_action( sprintf( 'after-%s-table', self::TAXONOMY_SLUG ), array( __CLASS__, 'render_link_to_errors_by_url' ) );
		add_action( 'load-edit-tags.php', function() {
			add_filter( 'user_has_cap', array( __CLASS__, 'filter_user_has_cap_for_hiding_term_list_table_checkbox' ), 10, 3 );
		} );
		add_filter( 'terms_clauses', array( __CLASS__, 'filter_terms_clauses_for_description_search' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'add_admin_notices' ) );
		add_filter( 'tag_row_actions', array( __CLASS__, 'filter_tag_row_actions' ), 10, 2 );
		if ( get_taxonomy( self::TAXONOMY_SLUG )->show_in_menu ) {
			add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_validation_error_item' ) );
		}
		add_filter( 'manage_' . self::TAXONOMY_SLUG . '_custom_column', array( __CLASS__, 'filter_manage_custom_columns' ), 10, 3 );
		add_filter( 'posts_where', array( __CLASS__, 'filter_posts_where_for_validation_error_status' ), 10, 2 );
		add_filter( 'handle_bulk_actions-edit-' . self::TAXONOMY_SLUG, array( __CLASS__, 'handle_validation_error_update' ), 10, 3 );
		add_action( 'load-edit-tags.php', array( __CLASS__, 'handle_inline_edit_request' ) );

		// Prevent query vars from persisting after redirect.
		add_filter( 'removable_query_args', function( $query_vars ) {
			$query_vars[] = 'amp_actioned';
			$query_vars[] = 'amp_actioned_count';
			$query_vars[] = 'amp_validation_errors_not_deleted';
			return $query_vars;
		} );

		// Add recognition of amp_validation_error_status and type query vars (which will only apply in admin since post type is not publicly_queryable).
		add_filter( 'query_vars', function( $query_vars ) {
			$query_vars[] = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR;
			$query_vars[] = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR;
			return $query_vars;
		} );

		// Always exclude taxonomy terms when they have empty counts.
		add_filter( 'get_terms_args', function( $args, $taxonomies ) {
			if ( array( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) === $taxonomies ) {
				$args['hide_empty'] = true;
			}
			return $args;
		}, 10, 2 );

		// Default ordering terms by ID descending so that new terms appear at the top.
		add_filter( 'get_terms_defaults', function( $args, $taxonomies ) {
			if ( array( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) === $taxonomies ) {
				$args['orderby'] = 'term_id';
				$args['order']   = 'DESC';
			}
			return $args;
		}, 10, 2 );

		// Add bulk actions.
		add_filter( 'bulk_actions-edit-' . self::TAXONOMY_SLUG, function( $bulk_actions ) {
			unset( $bulk_actions['delete'] );
			$bulk_actions[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION ] = __( 'Accept', 'amp' );
			$bulk_actions[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECT_ACTION ] = __( 'Reject', 'amp' );
			return $bulk_actions;
		} );

		// Override the columns displayed for the validation error terms.
		add_filter( 'manage_edit-' . self::TAXONOMY_SLUG . '_columns', function( $old_columns ) {
			return array(
				'cb'               => $old_columns['cb'],
				'error'            => __( 'Error', 'amp' ),
				'status'           => __( 'Status', 'amp' ),
				'details'          => __( 'Details', 'amp' ),
				'error_type'       => __( 'Type', 'amp' ),
				'created_date_gmt' => __( 'Last Seen', 'amp' ),
				'posts'            => __( 'Found URLs', 'amp' ),
			);
		} );

		// Let the created date column sort by term ID.
		add_filter( 'manage_edit-' . self::TAXONOMY_SLUG . '_sortable_columns', function( $sortable_columns ) {
			$sortable_columns['created_date_gmt'] = 'term_id';
			$sortable_columns['error_type']       = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR;
			$sortable_columns['details']          = AMP_Validation_Error_Taxonomy::VALIDATION_DETAILS_NODE_NAME_QUERY_VAR;
			$sortable_columns['error']            = AMP_Validation_Error_Taxonomy::VALIDATION_DETAILS_ERROR_CODE_QUERY_VAR;
			return $sortable_columns;
		} );

		// Hide empty term addition form.
		add_action( 'admin_enqueue_scripts', function() {
			if ( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG === get_current_screen()->taxonomy ) {
				wp_enqueue_style(
					'amp-validation-error-taxonomy',
					amp_get_asset_url( 'css/amp-validation-error-taxonomy.css' ),
					array( 'common' ),
					AMP__VERSION
				);

				wp_enqueue_script(
					'amp-validation-error-detail-toggle',
					amp_get_asset_url( 'js/amp-validation-error-detail-toggle-compiled.js' ),
					array( 'wp-dom-ready' ),
					AMP__VERSION,
					true
				);

				wp_localize_script(
					'amp-validation-error-detail-toggle',
					'ampValidationI18n',
					array( 'btnAriaLabel' => esc_attr__( 'Toggle all details', 'amp' ) )
				);
			}
		} );

		// Make sure parent menu item is expanded when visiting the taxonomy term page.
		add_filter( 'parent_file', function( $parent_file ) {
			if ( get_current_screen()->taxonomy === AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) {
				$parent_file = AMP_Options_Manager::OPTION_NAME;
			}
			return $parent_file;
		}, 10, 2 );

		// Replace the primary column to be error instead of the removed name column..
		add_filter( 'list_table_primary_column', function( $primary_column ) {
			if ( get_current_screen() && AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG === get_current_screen()->taxonomy ) {
				$primary_column = 'error';
			}
			return $primary_column;
		} );
	}

	/**
	 * Filter the term redirect URL, to possibly add query vars to filter by term status or type.
	 *
	 * On clicking the 'Filter' button on the 'AMP Validation Errors' taxonomy page,
	 * edit-tags.php processes the POST request that this submits.
	 * Then, it redirects to a URL to display the page again.
	 * This filter callback looks for a value for VALIDATION_ERROR_TYPE_QUERY_VAR in the $_POST request.
	 * That means that the user filtered by error type, like 'js_error'.
	 * It then passes that value to the redirect URL as a query var,
	 * So that the taxonomy page will be filtered for that error type.
	 *
	 * @see AMP_Validation_Error_Taxonomy::add_error_type_clauses_filter() for the filtering of the 'where' clause, based on the query vars.
	 * @param string      $url The $url to redirect to.
	 * @param WP_Taxonomy $tax The WP_Taxonomy object.
	 * @return string The filtered URL.
	 */
	public static function add_term_filter_query_var( $url, $tax ) {
		if (
			self::TAXONOMY_SLUG !== $tax->name
			||
			! isset( $_POST['post_type'] ) // WPCS: CSRF OK.
			||
			AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG !== $_POST['post_type'] // WPCS: CSRF OK.
		) {
			return $url;
		}

		// If the error type query var is valid, pass it along in the redirect $url.
		if (
			isset( $_POST[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) // WPCS: CSRF OK.
			&&
			in_array(
				$_POST[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ], // WPCS: CSRF OK.
				array_merge( self::get_error_types(), array( strval( self::NO_FILTER_VALUE ) ) ),
				true
			)
		) {
			$url = add_query_arg(
				self::VALIDATION_ERROR_TYPE_QUERY_VAR,
				sanitize_key( wp_unslash( $_POST[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) ), // WPCS: CSRF OK.
				$url
			);
		}

		// If the error status query var is valid, pass it along in the redirect $url.
		if (
			isset( $_POST[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) // WPCS: CSRF OK.
			&&
			in_array(
				intval( $_POST[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ), // WPCS: CSRF OK.
				array( self::VALIDATION_ERROR_NEW_STATUS, self::VALIDATION_ERROR_ACCEPTED_STATUS, self::VALIDATION_ERROR_REJECTED_STATUS, self::NO_FILTER_VALUE ),
				true
			)
		) {
			$url = add_query_arg(
				self::VALIDATION_ERROR_STATUS_QUERY_VAR,
				intval( $_POST[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ), // WPCS: CSRF OK.
				$url
			);
		}

		return $url;
	}

	/**
	 * Filter amp_validation_error term query by term group when requested.
	 */
	public static function add_group_terms_clauses_filter() {
		if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || ! isset( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
			return;
		}
		self::$should_filter_terms_clauses_for_error_validation_status = true;
		$group = intval( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ); // WPCS: CSRF ok.
		if ( ! in_array( $group, array( self::VALIDATION_ERROR_NEW_STATUS, self::VALIDATION_ERROR_ACCEPTED_STATUS, self::VALIDATION_ERROR_REJECTED_STATUS ), true ) ) {
			return;
		}
		add_filter( 'terms_clauses', function( $clauses, $taxonomies ) use ( $group ) {
			global $wpdb;
			if ( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG === $taxonomies[0] && AMP_Validation_Error_Taxonomy::$should_filter_terms_clauses_for_error_validation_status ) {
				$clauses['where'] .= $wpdb->prepare( ' AND t.term_group = %d', $group );
			}
			return $clauses;
		}, 10, 2 );
	}

	/**
	 * Adds filter for amp_validation_error term query by type, like in the 'AMP Validation Errors' taxonomy page.
	 * Allows viewing only a certain type at a time, like only JS errors.
	 */
	public static function add_error_type_clauses_filter() {
		if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || ! isset( $_GET[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
			return;
		}

		$type = sanitize_key( wp_unslash( $_GET[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) ); // WPCS: CSRF ok.
		if ( ! in_array( $type, self::get_error_types(), true ) ) {
			return;
		}

		add_filter( 'terms_clauses', function( $clauses, $taxonomies ) use ( $type ) {
			global $wpdb;
			if ( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG === $taxonomies[0] ) {
				$clauses['where'] .= $wpdb->prepare( ' AND tt.description LIKE %s', '%"type":"' . $wpdb->esc_like( $type ) . '"%' );
			}
			return $clauses;
		}, 10, 2 );
	}

	/**
	 * If ordering the list by a field in the description JSON, locate the best spot in the JSON string by which to sort
	 * alphabetically.
	 */
	public static function add_order_clauses_from_description_json() {
		if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy ) {
			return;
		}

		$sortable_column_vars = array(
			self::VALIDATION_ERROR_TYPE_QUERY_VAR,
			self::VALIDATION_DETAILS_NODE_NAME_QUERY_VAR,
			self::VALIDATION_DETAILS_ERROR_CODE_QUERY_VAR,
		);

		if ( ! isset( $_GET['orderby'] ) || ! in_array( $_GET['orderby'], $sortable_column_vars, true ) ) { // WPCS: CSRF ok.
			return;
		}

		add_filter( 'terms_clauses', function( $clauses ) {
			global $wpdb;

			if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) { // WPCS: CSRF ok.
				$clauses['order'] = 'DESC';
			} else {
				$clauses['order'] = 'ASC';
			}

			switch ( $_GET['orderby'] ) { // WPCS: CSRF ok.
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR:
					$clauses['orderby'] = $wpdb->prepare(
						'ORDER BY SUBSTR(tt.description, LOCATE(%s, tt.description, LOCATE(%s, tt.description)))',
						'"type":"',
						'}' // Start substr search after the first closing bracket to skip the "type" nested in the element_attributes object.
					);
					break;

				case AMP_Validation_Error_Taxonomy::VALIDATION_DETAILS_NODE_NAME_QUERY_VAR:
					$clauses['orderby'] = $wpdb->prepare(
						'ORDER BY SUBSTR(tt.description, LOCATE(%s, tt.description))',
						'"node_name":"'
					);
					break;

				case AMP_Validation_Error_Taxonomy::VALIDATION_DETAILS_ERROR_CODE_QUERY_VAR:
					$clauses['orderby'] = $wpdb->prepare(
						'ORDER BY SUBSTR(tt.description, LOCATE(%s, tt.description))',
						'"code":"'
					);
					break;
			}

			return $clauses;
		}, 10, 2 );
	}

	/**
	 * Outputs the taxonomy filter UI for this taxonomy type.
	 *
	 * Similar to what appears on /wp-admin/edit.php for posts and pages,
	 * this outputs <select> elements to choose the error status and type,
	 * and a 'Filter' submit button that filters for them.
	 *
	 * @param string $taxonomy_name The name of the taxonomy.
	 */
	public static function render_taxonomy_filters( $taxonomy_name ) {
		if ( self::TAXONOMY_SLUG !== $taxonomy_name ) {
			return;
		}

		$div_id = 'amp-tax-filter';
		?>
		<div id="<?php echo esc_attr( $div_id ); ?>" class="alignleft actions">
			<?php
			self::render_error_status_filter();
			self::render_error_type_filter();
			?>
			<input name="filter_action" type="submit" id="doaction" class="button action" value="<?php esc_html_e( 'Apply Filter', 'amp' ); ?>">
		</div>

		<script>
			( function ( $ ) {
				$( function() {
					// Move the filter UI after the 'Bulk Actions' <select>, as it looks like there's no way to do this with only an action.
					$( '#<?php echo $div_id; // WPCS: XSS OK. ?>' ).insertAfter( $( '.tablenav.top .bulkactions' ) );

					// Move the link to 'View errors by URL' to after the heading, as it also looks like there's no action for this.
					$( '#<?php echo self::ID_LINK_ERRORS_BY_URL; // WPCS: XSS OK. ?>' ).insertAfter( $( '.wp-heading-inline' ) );
				} );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * On the 'Error Index' taxonomy page, renders a link to the 'Errors by URL' page.
	 *
	 * @param string $taxonomy_name The name of the taxonomy.
	 */
	public static function render_link_to_errors_by_url( $taxonomy_name ) {
		if ( self::TAXONOMY_SLUG !== $taxonomy_name ) {
			return;
		}

		printf(
			'<a href="%s" class="page-title-action" id="%s" style="margin-left: 1rem;">%s</a>',
			esc_attr( add_query_arg(
				'post_type',
				AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
				admin_url( 'edit.php' )
			) ),
			esc_attr( self::ID_LINK_ERRORS_BY_URL ),
			esc_html__( 'View errors by URL', 'amp' )
		);
	}

	/**
	 * Renders the error status filter <select> element.
	 *
	 * There is a difference how the errors are counted, depending on which screen this is on.
	 * For example: Accepted Errors (10).
	 * This status filter <select> element is rendered on the validation error post page (Errors by URL),
	 * and the validation error taxonomy page (Error Index).
	 * On the taxonomy page, this simply needs to count the number of terms with a given type.
	 * On the post page, this needs to count the number of posts that have at least one error of a given type.
	 */
	public static function render_error_status_filter() {
		global $wp_query;
		$screen_base = get_current_screen()->base;

		if ( 'edit-tags' === $screen_base ) {
			$total_term_count    = self::get_validation_error_count();
			$rejected_term_count = self::get_validation_error_count( array( 'group' => self::VALIDATION_ERROR_REJECTED_STATUS ) );
			$accepted_term_count = self::get_validation_error_count( array( 'group' => self::VALIDATION_ERROR_ACCEPTED_STATUS ) );
			$new_term_count      = $total_term_count - $rejected_term_count - $accepted_term_count;

		} elseif ( 'edit' === $screen_base ) {
			$args = array(
				'post_type'              => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			$error_type = sanitize_key( $wp_query->get( self::VALIDATION_ERROR_TYPE_QUERY_VAR ) );
			if ( $error_type && in_array( $error_type, self::get_error_types(), true ) ) {
				$args[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] = $error_type;
			}

			$with_new_query = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_NEW_STATUS )
			) );
			$new_term_count = $with_new_query->found_posts;

			$with_rejected_query = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_REJECTED_STATUS )
			) );
			$rejected_term_count = $with_rejected_query->found_posts;

			$with_accepted_query = new WP_Query( array_merge(
				$args,
				array( self::VALIDATION_ERROR_STATUS_QUERY_VAR => self::VALIDATION_ERROR_ACCEPTED_STATUS )
			) );
			$accepted_term_count = $with_accepted_query->found_posts;
		} else {
			return;
		}

		$error_status_filter_value = isset( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ? intval( $_GET[ self::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) : ''; // WPCS: CSRF OK.

		?>
		<label for="<?php echo esc_attr( self::VALIDATION_ERROR_STATUS_QUERY_VAR ); ?>" class="screen-reader-text"><?php esc_html_e( 'Filter by error status', 'amp' ); ?></label>
		<select name="<?php echo esc_attr( self::VALIDATION_ERROR_STATUS_QUERY_VAR ); ?>" id="<?php echo esc_attr( self::VALIDATION_ERROR_STATUS_QUERY_VAR ); ?>">
			<option value="<?php echo esc_attr( self::NO_FILTER_VALUE ); ?>"><?php esc_html_e( 'All Statuses', 'amp' ); ?></option>
			<?php
			if ( 'edit' === $screen_base ) {
				$new_term_text = sprintf(
					/* translators: %s: the new term count. */
					_nx(
						'With New Error <span class="count">(%s)</span>',
						'With New Errors <span class="count">(%s)</span>',
						$new_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $new_term_count )
				);
			} else {
				$new_term_text = sprintf(
					/* translators: %s: the new term count. */
					_nx(
						'New Error <span class="count">(%s)</span>',
						'New Errors <span class="count">(%s)</span>',
						$new_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $new_term_count )
				);
			}
			?>
			<option value="<?php echo esc_attr( self::VALIDATION_ERROR_NEW_STATUS ); ?>" <?php selected( $error_status_filter_value, self::VALIDATION_ERROR_NEW_STATUS ); ?>><?php echo wp_kses_post( $new_term_text ); ?></option>
			<?php
			if ( 'edit' === $screen_base ) {
				$accepted_term_text = sprintf(
					/* translators: %s: the accepted term count. */
					_nx(
						'With Accepted Error <span class="count">(%s)</span>',
						'With Accepted Errors <span class="count">(%s)</span>',
						$accepted_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $accepted_term_count )
				);
			} else {
				$accepted_term_text = sprintf(
					/* translators: %s: the accepted term count. */
					_nx(
						'Accepted Error <span class="count">(%s)</span>',
						'Accepted Errors <span class="count">(%s)</span>',
						$accepted_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $accepted_term_count )
				);
			}
			?>
			<option value="<?php echo esc_attr( self::VALIDATION_ERROR_ACCEPTED_STATUS ); ?>" <?php selected( $error_status_filter_value, self::VALIDATION_ERROR_ACCEPTED_STATUS ); ?>><?php echo wp_kses_post( $accepted_term_text ); ?></option>
			<?php
			if ( 'edit' === $screen_base ) {
				$rejected_term_text = sprintf(
					/* translators: %s: the rejected term count. */
					_nx(
						'With Rejected Error <span class="count">(%s)</span>',
						'With Rejected Errors <span class="count">(%s)</span>',
						$rejected_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $rejected_term_count )
				);
			} else {
				$rejected_term_text = sprintf(
					/* translators: %s: the rejected term count. */
					_nx(
						'Rejected Error <span class="count">(%s)</span>',
						'Rejected Errors <span class="count">(%s)</span>',
						$rejected_term_count,
						'terms',
						'amp'
					),
					number_format_i18n( $rejected_term_count )
				);
			}
			?>
			<option value="<?php echo esc_attr( self::VALIDATION_ERROR_REJECTED_STATUS ); ?>" <?php selected( $error_status_filter_value, self::VALIDATION_ERROR_REJECTED_STATUS ); ?>><?php echo wp_kses_post( $rejected_term_text ); ?></option>
		</select>
		<?php
	}

	/**
	 * Gets all of the possible error types.
	 *
	 * @return array Error types.
	 */
	public static function get_error_types() {
		return array( self::HTML_ELEMENT_ERROR_TYPE, self::HTML_ATTRIBUTE_ERROR_TYPE, self::JS_ERROR_TYPE, self::CSS_ERROR_TYPE );
	}

	/**
	 * Renders the filter for error type.
	 *
	 * This type filter <select> element is rendered on the validation error post page (Errors by URL),
	 * and the validation error taxonomy page (Error Index).
	 */
	public static function render_error_type_filter() {
		$error_type_filter_value = isset( $_GET[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) ? sanitize_key( wp_unslash( $_GET[ self::VALIDATION_ERROR_TYPE_QUERY_VAR ] ) ) : ''; // WPCS: CSRF OK.

		/*
		 * On the 'Errors by URL' page, the <option> text should be different.
		 * For example, it should be 'With JS Errors' instead of 'JS Errors'.
		 */
		$screen_base = get_current_screen()->base;
		?>
		<label for="<?php echo esc_attr( self::VALIDATION_ERROR_TYPE_QUERY_VAR ); ?>" class="screen-reader-text"><?php esc_html_e( 'Filter by error type', 'amp' ); ?></label>
		<select name="<?php echo esc_attr( self::VALIDATION_ERROR_TYPE_QUERY_VAR ); ?>" id="<?php echo esc_attr( self::VALIDATION_ERROR_TYPE_QUERY_VAR ); ?>">
			<option value="<?php echo esc_attr( self::NO_FILTER_VALUE ); ?>">
				<?php esc_html_e( 'All Error Types', 'amp' ); ?>
			</option>
			<option value="<?php echo esc_attr( self::HTML_ELEMENT_ERROR_TYPE ); ?>" <?php selected( $error_type_filter_value, self::HTML_ELEMENT_ERROR_TYPE ); ?>>
				<?php if ( 'edit' === $screen_base ) : ?>
					<?php esc_html_e( 'With HTML (Element) Errors', 'amp' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'HTML (Element) Errors', 'amp' ); ?>
				<?php endif; ?>
			</option>
			<option value="<?php echo esc_attr( self::HTML_ATTRIBUTE_ERROR_TYPE ); ?>" <?php selected( $error_type_filter_value, self::HTML_ATTRIBUTE_ERROR_TYPE ); ?>>
				<?php if ( 'edit' === $screen_base ) : ?>
					<?php esc_html_e( 'With HTML (Attribute) Errors', 'amp' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'HTML (Attribute) Errors', 'amp' ); ?>
				<?php endif; ?>
			</option>
			<option value="<?php echo esc_attr( self::JS_ERROR_TYPE ); ?>" <?php selected( $error_type_filter_value, self::JS_ERROR_TYPE ); ?>>
				<?php if ( 'edit' === $screen_base ) : ?>
					<?php esc_html_e( 'With JS Errors', 'amp' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'JS Errors', 'amp' ); ?>
				<?php endif; ?>
			</option>
			<option value="<?php echo esc_attr( self::CSS_ERROR_TYPE ); ?>" <?php selected( $error_type_filter_value, self::CSS_ERROR_TYPE ); ?>>
				<?php if ( 'edit' === $screen_base ) : ?>
					<?php esc_html_e( 'With CSS Errors', 'amp' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'CSS Errors', 'amp' ); ?>
				<?php endif; ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Prevent user from being able to delete validation errors in order to disable the checkbox on the post list table.
	 *
	 * Yes, this is not ideal.
	 *
	 * @param array $allcaps All caps.
	 * @param array $caps    Requested caps.
	 * @param array $args    Cap args.
	 * @return array All caps.
	 */
	public static function filter_user_has_cap_for_hiding_term_list_table_checkbox( $allcaps, $caps, $args ) {
		unset( $caps );
		if ( isset( $args[0] ) && 'delete_term' === $args[0] ) {
			$term  = get_term( $args[2] );
			$error = json_decode( $term->description, true );
			if ( ! is_array( $error ) ) {
				return $allcaps;
			}
		}
		return $allcaps;
	}

	/**
	 * Include searching taxonomy term descriptions and sources term meta.
	 *
	 * @param array $clauses    Clauses.
	 * @param array $taxonomies Taxonomies.
	 * @param array $args       Args.
	 * @return array Clauses.
	 */
	public static function filter_terms_clauses_for_description_search( $clauses, $taxonomies, $args ) {
		global $wpdb;
		if ( ! empty( $args['search'] ) && in_array( self::TAXONOMY_SLUG, $taxonomies, true ) ) {
			$clauses['where'] = preg_replace(
				'#(?<=\()(?=\(t\.name LIKE \')#',
				$wpdb->prepare( '(tt.description LIKE %s) OR ', '%' . $wpdb->esc_like( $args['search'] ) . '%' ),
				$clauses['where']
			);
		}
		return $clauses;
	}

	/**
	 * Show notices for changes to amp_validation_error terms.
	 */
	public static function add_admin_notices() {
		if ( ! ( self::TAXONOMY_SLUG === get_current_screen()->taxonomy || AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG === get_current_screen()->post_type ) || empty( $_GET['amp_actioned'] ) || empty( $_GET['amp_actioned_count'] ) ) { // WPCS: CSRF ok.
			return;
		}
		$actioned = sanitize_key( $_GET['amp_actioned'] ); // WPCS: CSRF ok.
		$count    = intval( $_GET['amp_actioned_count'] ); // WPCS: CSRF ok.
		$message  = null;
		if ( self::VALIDATION_ERROR_ACCEPT_ACTION === $actioned ) {
			$message = sprintf(
				/* translators: %s is number of errors accepted */
				_n(
					'Accepted %s error. It will no longer block related URLs from being served as AMP.',
					'Accepted %s errors. They will no longer block related URLs from being served as AMP.',
					number_format_i18n( $count ),
					'amp'
				),
				$count
			);
		} elseif ( self::VALIDATION_ERROR_REJECT_ACTION === $actioned ) {
			$message = sprintf(
				/* translators: %s is number of errors rejected */
				_n(
					'Rejected %s error. It will continue to block related URLs from being served as AMP.',
					'Rejected %s errors. They will continue to block related URLs from being served as AMP.',
					number_format_i18n( $count ),
					'amp'
				),
				$count
			);
		}

		if ( $message ) {
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $message ) );
		}
	}

	/**
	 * Add row actions.
	 *
	 * @param array   $actions Actions.
	 * @param WP_Term $tag     Tag.
	 * @return array Actions.
	 */
	public static function filter_tag_row_actions( $actions, WP_Term $tag ) {
		if ( self::TAXONOMY_SLUG === $tag->taxonomy ) {
			$term_id = $tag->term_id;
			$term    = get_term( $tag->term_id ); // We don't want filter=display given by $tag.

			/*
			 * Hide deletion link since a validation error should only be removed once
			 * it no longer has an occurrence on the site. When an invalid URL is re-checked
			 * and it no longer has this validation error, then the count will be decremented.
			 * When a validation error term no longer has a count, then it is hidden from the
			 * list table. A cron job could periodically delete terms that have no counts.
			 */
			unset( $actions['delete'] );

			$actions['details'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( add_query_arg( array(
					self::TAXONOMY_SLUG => $term->name,
					'post_type'         => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
				), 'edit.php' ) ),
				esc_html__( 'Details', 'amp' )
			);

			$sanitization = self::get_validation_error_sanitization( json_decode( $term->description, true ) );
			if ( self::VALIDATION_ERROR_REJECTED_STATUS !== $sanitization['term_status'] ) {
				$actions[ self::VALIDATION_ERROR_REJECT_ACTION ] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url(
						add_query_arg( array_merge( array( 'action' => self::VALIDATION_ERROR_REJECT_ACTION ), compact( 'term_id' ) ) ),
						self::VALIDATION_ERROR_REJECT_ACTION
					),
					esc_attr__( 'Rejecting an error acknowledges that it should block a URL from being served as AMP.', 'amp' ),
					esc_html__( 'Reject', 'amp' )
				);
			}
			if ( self::VALIDATION_ERROR_ACCEPTED_STATUS !== $sanitization['term_status'] ) {
				$actions[ self::VALIDATION_ERROR_ACCEPT_ACTION ] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url(
						add_query_arg( array_merge( array( 'action' => self::VALIDATION_ERROR_ACCEPT_ACTION ), compact( 'term_id' ) ) ),
						self::VALIDATION_ERROR_ACCEPT_ACTION
					),
					esc_attr__( 'Accepting an error means it will get sanitized and not block a URL from being served as AMP.', 'amp' ),
					esc_html__( 'Accept', 'amp' )
				);
			}
		}
		return $actions;
	}

	/**
	 * Show AMP validation errors under AMP admin menu.
	 */
	public static function add_admin_menu_validation_error_item() {
		$menu_item_label = esc_html__( 'Error Index', 'amp' );
		$new_error_count = self::get_validation_error_count( array(
			'group' => self::VALIDATION_ERROR_NEW_STATUS,
		) );
		if ( $new_error_count ) {
			$menu_item_label .= ' <span class="awaiting-mod"><span class="pending-count">' . esc_html( number_format_i18n( $new_error_count ) ) . '</span></span>';
		}

		$taxonomy_caps = (object) get_taxonomy( self::TAXONOMY_SLUG )->cap; // Yes, cap is an object not an array.
		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			$menu_item_label,
			$menu_item_label,
			$taxonomy_caps->manage_terms,
			// The following esc_attr() is sadly needed due to <https://github.com/WordPress/wordpress-develop/blob/4.9.5/src/wp-admin/menu-header.php#L201>.
			esc_attr( 'edit-tags.php?taxonomy=' . self::TAXONOMY_SLUG . '&post_type=' . AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG )
		);
	}

	/**
	 * Provides a reader-friendly string for a term's error type.
	 *
	 * @param string $error_type The error type from the term's validation error JSON.
	 * @return string Reader-friendly string.
	 */
	public static function get_reader_friendly_error_type_text( $error_type ) {
		switch ( $error_type ) {
			case 'js_error':
				return esc_html__( 'JavaScript', 'amp' );

			case 'html_element_error':
				return esc_html__( 'HTML (Element)', 'amp' );

			case 'html_attribute_error':
				return esc_html__( 'HTML (Attribute)', 'amp' );

			case 'css_error':
				return esc_html__( 'CSS', 'amp' );

			default:
				return $error_type;
		}

	}

	/**
	 * Supply the content for the custom columns.
	 *
	 * @param string $content     Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id     Term ID.
	 * @return string Content.
	 */
	public static function filter_manage_custom_columns( $content, $column_name, $term_id ) {
		$term = get_term( $term_id );

		$validation_error = json_decode( $term->description, true );
		if ( ! isset( $validation_error['code'] ) ) {
			$validation_error['code'] = 'unknown';
		}

		switch ( $column_name ) {
			case 'error':
				$content .= '<p>';
				$content .= sprintf(
					'<a href="%s"><code>%s</code></a>',
					admin_url( add_query_arg( array(
						self::TAXONOMY_SLUG => $term->name,
						'post_type'         => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
					), 'edit.php' ) ),
					esc_html( $validation_error['code'] )
				);
				if ( self::INVALID_ELEMENT_CODE === $validation_error['code'] ) {
					$content .= sprintf( ': <code>&lt;%s&gt;</code>', esc_html( $validation_error['node_name'] ) );
				} elseif ( self::INVALID_ATTRIBUTE_CODE === $validation_error['code'] ) {
					$content .= sprintf( ': <code>[%s]</code>', esc_html( $validation_error['node_name'] ) );
				} elseif ( 'illegal_css_at_rule' === $validation_error['code'] ) {
					$content .= sprintf( ': <code>@%s</code>', esc_html( $validation_error['at_rule'] ) );
				}
				$content .= '</p>';

				if ( isset( $validation_error['message'] ) ) {
					$content .= sprintf( '<p>%s</p>', esc_html( $validation_error['message'] ) );
				}
				break;
			case 'status':
				$sanitization = self::get_validation_error_sanitization( $validation_error );
				if ( self::VALIDATION_ERROR_ACCEPTED_STATUS === $sanitization['term_status'] ) {
					if ( $sanitization['forced'] && $sanitization['term_status'] !== $sanitization['status'] ) {
						$class = 'sanitized';
					} else {
						$class = 'accepted';
					}
					$text = __( 'Accepted', 'amp' );
				} elseif ( self::VALIDATION_ERROR_REJECTED_STATUS === $sanitization['term_status'] ) {
					if ( $sanitization['forced'] && $sanitization['term_status'] !== $sanitization['status'] ) {
						$class = 'sanitized';
					} else {
						$class = 'rejected';
					}
					$text = __( 'Rejected', 'amp' );
				} else {
					$class = 'new';
					$text  = __( 'New', 'amp' );
				}
				$content .= sprintf( '<span class="status-text %s">%s</span>', esc_attr( $class ), esc_html( $text ) );
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
				} else {
					$time_ago = sprintf(
						'<abbr title="%s">%s</abbr>',
						esc_attr( $created_datetime->format( __( 'Y/m/d g:i:s a', 'default' ) ) ),
						/* translators: %s is relative time */
						esc_html( sprintf( __( '%s ago', 'default' ), human_time_diff( $created_datetime->getTimestamp() ) ) )
					);
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
				if ( isset( $validation_error['parent_name'] ) ) {
					if ( self::INVALID_ATTRIBUTE_CODE === $validation_error['code'] || self::INVALID_ELEMENT_CODE === $validation_error['code'] ) {
						$summary_label = sprintf( '<%s>', $validation_error['parent_name'] );
					} elseif ( isset( $validation_error['node_name'] ) ) {
						$summary_label = sprintf( '<%s>', $validation_error['node_name'] );
					} elseif ( isset( $validation_error['node_name'] ) ) {
						$summary_label = $validation_error['node_name'];
					} else {
						$summary_label = '&hellip;';
					}

					$summary = '<code>' . esc_html( $summary_label ) . '</code>';
					unset( $validation_error['error_type'] );
					unset( $validation_error['parent_name'] );

					$attributes         = array();
					$attributes_heading = '';
					if ( ! empty( $validation_error['node_attributes'] ) ) {
						$attributes_heading = sprintf( '<div class="details-attributes__title">%s</div>', esc_html__( 'Element attributes:', 'amp' ) );
						$attributes         = $validation_error['node_attributes'];
					} elseif ( ! empty( $validation_error['element_attributes'] ) ) {
						$attributes_heading = sprintf( '<div class="details-attributes__title">%s</div>', esc_html__( 'Other attributes:', 'amp' ) );
						$attributes         = $validation_error['element_attributes'];
					}

					if ( empty( $attributes ) ) {
						$content .= $summary;
					} else {
						$content  = '<details>';
						$content .= '<summary class="details-attributes__summary">';
						$content .= $summary;
						$content .= '</summary>';

						$content .= $attributes_heading;
						$content .= '<ul class="details-attributes__list">';

						foreach ( $attributes as $attr => $value ) {
							$content .= sprintf( '<li><span class="details-attributes__attr">%s</span>', esc_html( $attr ) );

							if ( isset( $value ) ) {
								$content .= sprintf( ': <span class="details-attributes__value">%s</span>', esc_html( $value ) );
							}

							$content .= '</li>';
						}

						$content .= '</ul>';
						$content .= '</details>';
					}
				}

				break;
			case 'error_type':
				if ( isset( $validation_error['type'] ) ) {
					$content = self::get_reader_friendly_error_type_text( $validation_error['type'] );
				}
				break;
		}
		return $content;
	}

	/**
	 * Handle inline edit links.
	 */
	public static function handle_inline_edit_request() {
		if ( self::TAXONOMY_SLUG !== get_current_screen()->taxonomy || ! isset( $_GET['action'] ) || ! isset( $_GET['_wpnonce'] ) || ! isset( $_GET['term_id'] ) ) { // WPCS: CSRF ok.
			return;
		}
		$action = sanitize_key( $_GET['action'] ); // WPCS: CSRF ok.
		check_admin_referer( $action );
		$taxonomy_caps = (object) get_taxonomy( self::TAXONOMY_SLUG )->cap; // Yes, cap is an object not an array.
		if ( ! current_user_can( $taxonomy_caps->manage_terms ) ) {
			return;
		}

		$referer  = wp_get_referer();
		$term_id  = intval( $_GET['term_id'] ); // WPCS: CSRF ok.
		$redirect = self::handle_validation_error_update( $referer, $action, array( $term_id ) );

		if ( $redirect !== $referer ) {
			wp_safe_redirect( $redirect );
			exit;
		}
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
		if ( self::VALIDATION_ERROR_ACCEPT_ACTION === $action ) {
			$term_group = self::VALIDATION_ERROR_ACCEPTED_STATUS;
		} elseif ( self::VALIDATION_ERROR_REJECT_ACTION === $action ) {
			$term_group = self::VALIDATION_ERROR_REJECTED_STATUS;
		}

		if ( $term_group ) {
			$has_pre_term_description_filter = has_filter( 'pre_term_description', 'wp_filter_kses' );
			if ( false !== $has_pre_term_description_filter ) {
				remove_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
			}
			foreach ( $term_ids as $term_id ) {
				wp_update_term( $term_id, self::TAXONOMY_SLUG, compact( 'term_group' ) );
			}
			if ( false !== $has_pre_term_description_filter ) {
				add_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
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
}
