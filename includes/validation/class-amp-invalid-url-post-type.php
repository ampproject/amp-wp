<?php
/**
 * Class AMP_Invalid_URL_Post_Type
 *
 * @package AMP
 */

/**
 * Class AMP_Invalid_URL_Post_Type
 *
 * @since 1.0
 */
class AMP_Invalid_URL_Post_Type {

	/**
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_invalid_url';

	/**
	 * The action to recheck URLs for AMP validity.
	 *
	 * @var string
	 */
	const RECHECK_ACTION = 'amp_recheck';

	/**
	 * Action to update the status of AMP validation errors.
	 *
	 * @var string
	 */
	const UPDATE_POST_TERM_STATUS_ACTION = 'amp_update_validation_error_status';

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
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {
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
					'edit_item'          => __( 'Invalid AMP Page (URL)', 'amp' ),
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

		if ( is_admin() ) {
			self::add_admin_hooks();
		}
	}

	/**
	 * Add admin hooks.
	 */
	public static function add_admin_hooks() {
		add_filter( 'dashboard_glance_items', array( __CLASS__, 'filter_dashboard_glance_items' ) );
		add_action( 'rightnow_end', array( __CLASS__, 'print_dashboard_glance_styles' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'edit_form_top', array( __CLASS__, 'print_url_as_title' ) );
		add_filter( 'the_title', array( __CLASS__, 'filter_the_title_in_post_list_table' ), 10, 2 );

		add_filter( 'views_edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'filter_views_edit' ) );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( __CLASS__, 'add_post_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'output_custom_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'add_bulk_action' ), 10, 2 );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'handle_bulk_action' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'print_admin_notice' ) );
		add_action( 'post_action_' . self::RECHECK_ACTION, array( __CLASS__, 'handle_inline_recheck' ) );
		add_action( 'post_action_' . self::UPDATE_POST_TERM_STATUS_ACTION, array( __CLASS__, 'handle_validation_error_status_update' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_new_invalid_url_count' ) );

		// Hide irrelevant "published" label in the invalid URL post list.
		add_filter( 'post_date_column_status', function( $status, $post ) {
			if ( self::POST_TYPE_SLUG === get_post_type( $post ) ) {
				$status = '';
			}
			return $status;
		}, 10, 2 );

		// Prevent query vars from persisting after redirect.
		add_filter( 'removable_query_args', function( $query_vars ) {
			$query_vars[] = 'amp_actioned';
			$query_vars[] = 'amp_taxonomy_terms_updated';
			$query_vars[] = self::REMAINING_ERRORS;
			$query_vars[] = 'amp_urls_tested';
			return $query_vars;
		} );
	}

	/**
	 * Add count of how many validation error posts there are to the admin menu.
	 */
	public static function add_admin_menu_new_invalid_url_count() {
		global $submenu;
		if ( ! isset( $submenu[ AMP_Options_Manager::OPTION_NAME ] ) ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'              => self::POST_TYPE_SLUG,
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
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
	 * Gets validation errors for a given invalid URL post.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 * @param array       $args {
	 *     Args.
	 *
	 *     @type bool $ignore_accepted Exclude validation errors that are accepted. Default false.
	 * }
	 * @return array List of errors.
	 */
	public static function get_invalid_url_validation_errors( $post, $args = array() ) {
		$args   = array_merge(
			array(
				'ignore_accepted' => false,
			),
			$args
		);
		$post   = get_post( $post );
		$errors = array();

		$stored_validation_errors = json_decode( $post->post_content, true );
		if ( ! is_array( $stored_validation_errors ) ) {
			return array();
		}
		foreach ( $stored_validation_errors as $stored_validation_error ) {
			if ( ! isset( $stored_validation_error['term_slug'] ) ) {
				continue;
			}
			$term = get_term_by( 'slug', $stored_validation_error['term_slug'], AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
			if ( $term && $args['ignore_accepted'] && AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS === $term->term_group ) {
				continue;
			}
			$errors[] = array(
				'term' => $term,
				'data' => $stored_validation_error['data'],
			);
		}
		return $errors;
	}

	/**
	 * Get counts for the validation errors associated with a given invalid URL.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 * @return array Term counts.
	 */
	public static function get_invalid_url_validation_error_counts( $post ) {
		$counts = array_fill_keys(
			array(
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS,
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS,
			),
			0
		);

		$validation_errors = self::get_invalid_url_validation_errors( $post );
		foreach ( wp_list_pluck( $validation_errors, 'term' ) as $term ) {
			if ( isset( $counts[ $term->term_group ] ) ) {
				$counts[ $term->term_group ]++;
			}
		}
		return $counts;
	}

	/**
	 * Display summary of the validation error counts for a given post.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 */
	public static function display_invalid_url_validation_error_counts_summary( $post ) {
		$result = array();
		$counts = self::get_invalid_url_validation_error_counts( $post );
		if ( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS ] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x2753; New: %s', 'amp' ),
				number_format_i18n( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS ] )
			) );
		}
		if ( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS ] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x2705; Accepted: %s', 'amp' ),
				number_format_i18n( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS ] )
			) );
		}
		if ( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS ] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x274C; Rejected: %s', 'amp' ),
				number_format_i18n( $counts[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS ] )
			) );
		}
		echo implode( '<br>', $result ); // WPCS: xss ok.
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

		/*
		 * The details for individual validation errors is stored in the amp_validation_error taxonomy terms.
		 * The post content just contains the slugs for these terms and the sources for the given instance of
		 * the validation error.
		 */
		$stored_validation_errors = array();

		$terms = array();
		foreach ( $validation_errors as $data ) {
			$term_data = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $data );
			$term_slug = $term_data['slug'];
			if ( ! isset( $terms[ $term_slug ] ) ) {

				// Not using WP_Term_Query since more likely individual terms are cached and wp_insert_term() will itself look at this cache anyway.
				$term = get_term_by( 'slug', $term_slug, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
				if ( ! ( $term instanceof WP_Term ) ) {
					$has_pre_term_description_filter = has_filter( 'pre_term_description', 'wp_filter_kses' );
					if ( false !== $has_pre_term_description_filter ) {
						remove_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
					}
					$r = wp_insert_term( $term_slug, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, wp_slash( $term_data ) );
					if ( false !== $has_pre_term_description_filter ) {
						add_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
					}
					if ( is_wp_error( $r ) ) {
						continue;
					}
					$term_id = $r['term_id'];
					update_term_meta( $term_id, 'created_date_gmt', current_time( 'mysql', true ) );
					$term = get_term( $term_id );
				}
				$terms[ $term_slug ] = $term;
			}

			$stored_validation_errors[] = compact( 'term_slug', 'data' );
		}

		$post_content = wp_json_encode( $stored_validation_errors );
		$placeholder  = 'amp_invalid_url_content_placeholder' . wp_rand();

		// Guard against Kses from corrupting content by adding post_content after content_save_pre filter applies.
		$insert_post_content = function( $post_data ) use ( $placeholder, $post_content ) {
			$should_supply_post_content = (
				isset( $post_data['post_content'], $post_data['post_type'] )
				&&
				$placeholder === $post_data['post_content']
				&&
				self::POST_TYPE_SLUG === $post_data['post_type']
			);
			if ( $should_supply_post_content ) {
				$post_data['post_content'] = wp_slash( $post_content );
			}
			return $post_data;
		};
		add_filter( 'wp_insert_post_data', $insert_post_content );

		// Create a new invalid AMP URL post, or update the existing one.
		$r = wp_insert_post(
			wp_slash( array(
				'ID'           => $post ? $post->ID : null,
				'post_type'    => self::POST_TYPE_SLUG,
				'post_title'   => $url,
				'post_name'    => $post_slug,
				'post_content' => $placeholder, // Content is provided via wp_insert_post_data filter above to guard against Kses-corruption.
				'post_status'  => 'publish',
			) ),
			true
		);
		remove_filter( 'wp_insert_post_data', $insert_post_content );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$post_id = $r;
		wp_set_object_terms( $post_id, wp_list_pluck( $terms, 'term_id' ), AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		return $post_id;
	}

	/**
	 * Add views for filtering validation errors by status.
	 *
	 * @param array $views Views.
	 * @return array Views
	 */
	public static function filter_views_edit( $views ) {
		unset( $views['publish'] );

		$args = array(
			'post_type'              => self::POST_TYPE_SLUG,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$with_new_query      = new WP_Query( array_merge(
			$args,
			array( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS )
		) );
		$with_rejected_query = new WP_Query( array_merge(
			$args,
			array( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS )
		) );
		$with_accepted_query = new WP_Query( array_merge(
			$args,
			array( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS )
		) );

		$current_url = remove_query_arg(
			array_merge(
				wp_removable_query_args(),
				array( 's' ) // For some reason behavior of posts list table is to not persist the search query.
			),
			wp_unslash( $_SERVER['REQUEST_URI'] )
		);

		$current_status = null;
		if ( isset( $_GET[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR ] ) ) { // WPCS: CSRF ok.
			$value = intval( $_GET[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR ] ); // WPCS: CSRF ok.
			if ( in_array( $value, array( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS, AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS, AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS ), true ) ) {
				$current_status = $value;
			}
		}

		$views['new'] = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url(
				add_query_arg(
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
					$current_url
				)
			),
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $current_status ? 'current' : '',
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

		$views['rejected'] = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url(
				add_query_arg(
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS,
					$current_url
				)
			),
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS === $current_status ? 'current' : '',
			sprintf(
				/* translators: %s is the post count */
				_nx(
					'With Rejected Errors <span class="count">(%s)</span>',
					'With Rejected Errors <span class="count">(%s)</span>',
					$with_rejected_query->found_posts,
					'posts',
					'amp'
				),
				number_format_i18n( $with_rejected_query->found_posts )
			)
		);

		$views['accepted'] = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url(
				add_query_arg(
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS,
					$current_url
				)
			),
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS === $current_status ? 'current' : '',
			sprintf(
				/* translators: %s is the post count */
				_nx(
					'With Accepted Errors <span class="count">(%s)</span>',
					'With Accepted Errors <span class="count">(%s)</span>',
					$with_accepted_query->found_posts,
					'posts',
					'amp'
				),
				number_format_i18n( $with_accepted_query->found_posts )
			)
		);

		return $views;
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
				'error_status' => esc_html__( 'Error Status', 'amp' ),
				AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS => esc_html__( 'Removed Elements', 'amp' ),
				AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES => esc_html__( 'Removed Attributes', 'amp' ),
				AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT => esc_html__( 'Incompatible Sources', 'amp' ),
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

		$validation_errors = self::get_invalid_url_validation_errors( $post_id );
		$error_summary     = AMP_Validation_Error_Taxonomy::summarize_validation_errors( wp_list_pluck( $validation_errors, 'data' ) );

		switch ( $column_name ) {
			case 'error_status':
				self::display_invalid_url_validation_error_counts_summary( $post_id );
				break;
			case AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS:
				if ( ! empty( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] ) ) {
					$items = array();
					foreach ( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] as $name => $count ) {
						if ( 1 === intval( $count ) ) {
							$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
						} else {
							$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
						}
					}
					echo implode( ', ', $items ); // WPCS: XSS OK.
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES:
				if ( ! empty( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] ) ) {
					$items = array();
					foreach ( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] as $name => $count ) {
						if ( 1 === intval( $count ) ) {
							$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
						} else {
							$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
						}
					}
					echo implode( ', ', $items ); // WPCS: XSS OK.
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT:
				if ( isset( $error_summary[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ] ) ) {
					$sources = array();
					foreach ( $error_summary[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ] as $type => $names ) {
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

		$view_url        = add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, '', $url ); // Prevent redirection to non-AMP page.
		$actions['view'] = sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), esc_html__( 'View', 'amp' ) );

		if ( ! empty( $url ) ) {
			$actions[ self::RECHECK_ACTION ] = sprintf(
				'<a href="%s">%s</a>',
				self::get_recheck_url( $post, get_edit_post_link( $post->ID, 'raw' ), $url ),
				esc_html__( 'Re-check', 'amp' )
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

			$validation_errors = AMP_Validation_Manager::validate_url( $url );
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
	public static function print_admin_notice() {
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) { // WPCS: CSRF ok.
			return;
		}

		if ( isset( $_GET[ self::REMAINING_ERRORS ] ) ) {
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

		if ( isset( $_GET['amp_taxonomy_terms_updated'] ) ) { // WPCS: CSRF ok.
			$count = intval( $_GET['amp_taxonomy_terms_updated'] );
			$class = 'updated';
			printf(
				'<div class="notice is-dismissible %s"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
				esc_attr( $class ),
				esc_html( sprintf(
					/* translators: %s is count of validation errors updated */
					_n(
						'Updated %s validation error.',
						'Updated %s validation errors.',
						$count,
						'amp'
					),
					number_format_i18n( $count )
				) ),
				esc_html__( 'Dismiss this notice.', 'amp' )
			);
		}
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
		$validation_errors = AMP_Validation_Manager::validate_url( $url );
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
	 * Handle validation error status update.
	 *
	 * @see AMP_Validation_Error_Taxonomy::handle_validation_error_update()
	 * @todo This is duplicated with logic in AMP_Validation_Error_Taxonomy. All of the term updating needs to be refactored to make use of the REST API.
	 */
	public static function handle_validation_error_status_update() {
		check_admin_referer( self::UPDATE_POST_TERM_STATUS_ACTION, self::UPDATE_POST_TERM_STATUS_ACTION . '_nonce' );

		if ( empty( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) || ! is_array( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) ) {
			return;
		}
		$updated_count = 0;

		$has_pre_term_description_filter = has_filter( 'pre_term_description', 'wp_filter_kses' );
		if ( false !== $has_pre_term_description_filter ) {
			remove_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
		}

		foreach ( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] as $term_slug => $status ) {
			$term_slug = sanitize_key( $term_slug );
			$term      = get_term_by( 'slug', $term_slug, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
			if ( ! $term ) {
				continue;
			}
			$term_group = intval( $status );
			if ( $term_group !== $term->term_group ) {
				$updated_count++;
				wp_update_term( $term->term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, compact( 'term_group' ) );
			}
		}

		if ( false !== $has_pre_term_description_filter ) {
			add_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
		}

		$args = array(
			'amp_taxonomy_terms_updated' => $updated_count,
		);
		wp_safe_redirect( add_query_arg( $args, wp_get_referer() ) );
		exit();
	}

	/**
	 * Adds the meta boxes to the CPT post.php page.
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		remove_meta_box( 'submitdiv', self::POST_TYPE_SLUG, 'side' );
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

		?>
		<style>
			#amp_validation_status .inside {
				margin: 0;
				padding: 0;
			}
			#re-check-action {
				float: left;
			}
		</style>
		<div id="submitpost" class="submitbox">
			<?php wp_nonce_field( self::UPDATE_POST_TERM_STATUS_ACTION, self::UPDATE_POST_TERM_STATUS_ACTION . '_nonce', false ); ?>
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
					<div id="re-check-action">
						<a class="button button-secondary" href="<?php echo esc_url( self::get_recheck_url( $post, $redirect_url ) ); ?>">
							<?php esc_html_e( 'Re-check', 'amp' ); ?>
						</a>
					</div>
					<div id="preview-action">
						<button type="button" name="action" class="preview button" id="preview_validation_errors"><?php esc_html_e( 'Preview Changes', 'default' ); ?></button>
					</div>
					<div class="clear"></div>
				</div>
				<div id="misc-publishing-actions">
					<div class="curtime misc-pub-section">
						<span id="timestamp">
						<?php
						printf(
							/* translators: %s: The date this was published */
							wp_kses_post( __( 'Last checked: <b>%s</b>', 'amp' ) ),
							/* translators: Meta box date format */
							esc_html( date_i18n( __( 'M j, Y @ H:i', 'default' ), strtotime( $post->post_date ) ) )
						);
						?>
						</span>
					</div>

					<div class="misc-pub-section">
						<?php self::display_invalid_url_validation_error_counts_summary( $post ); ?>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">
						<?php esc_html_e( 'Move to Trash', 'default' ); ?>
					</a>
				</div>
				<div id="publishing-action">
					<button type="submit" name="action" class="button button-primary" value="<?php echo esc_attr( self::UPDATE_POST_TERM_STATUS_ACTION ); ?>"><?php esc_html_e( 'Update', 'default' ); ?></button>
				</div>
				<div class="clear"></div>
			</div>
		</div><!-- /submitpost -->
		<?php
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

		$can_serve_amp = 0 === count( array_filter( $validation_errors, function( $validation_error ) {
			return AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS !== $validation_error['term']->term_group;
		} ) );
		?>
		<style>
			.amp-validation-errors .detailed,
			.amp-validation-errors .validation-error-other-urls {
				margin-left: 30px;
			}
			.amp-validation-errors pre {
				overflow: auto;
			}
		</style>

		<?php if ( $can_serve_amp ) : ?>
			<div class="notice notice-success notice-alt inline">
				<p><?php esc_html_e( 'This URL can be served as AMP because all validation errors have been accepted as not being blockers.', 'amp' ); ?></p>
			</div>
		<?php else : ?>
			<div class="notice notice-warning notice-alt inline">
				<p><?php esc_html_e( 'This URL cannot be served as AMP because it has validation errors which are either new or rejected as being blockers.', 'amp' ); ?></p>
			</div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'An accepted validation error is one that will not block a URL from being served as AMP; the validation error will be sanitized, normally resulting in the offending markup being stripped from the response to ensure AMP validity. A validation error that is accepted here will also be accepted for any other URL it occurs on.', 'amp' ); ?>
		</p>

		<script>
		jQuery( function( $ ) {
			var validateUrl, postId;
			validateUrl = <?php echo wp_json_encode( add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, AMP_Validation_Manager::get_amp_validate_nonce(), $post->post_title ) ); ?>;
			postId = <?php echo wp_json_encode( $post->ID ); ?>;
			$( '#preview_validation_errors' ).on( 'click', function() {
				var params = {}, validatePreviewUrl = validateUrl;
				$( '.amp-validation-error-status' ).each( function() {
					if ( this.value && ! this.options[ this.selectedIndex ].defaultSelected ) {
						params[ this.name ] = this.value;
					}
				} );
				validatePreviewUrl += '&' + $.param( params );
				window.open( validatePreviewUrl, 'amp-validation-error-term-status-preview-' + String( postId ) );
			} );
		} );
		</script>

		<div class="amp-validation-errors">
			<ul>
				<?php foreach ( $validation_errors as $error ) : ?>
					<?php
					$collapsed_details = array();
					$term              = $error['term'];
					$select_name       = sprintf( '%s[%s]', AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR, $term->slug );
					?>
					<li>
						<details <?php echo ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $term->term_group ) ? 'open' : ''; ?>>
							<summary>
								<label for="<?php echo esc_attr( $select_name ); ?>" class="screen-reader-text">
									<?php esc_html_e( 'Status:', 'amp' ); ?>
								</label>
								<select class="amp-validation-error-status" id="<?php echo esc_attr( $select_name ); ?>" name="<?php echo esc_attr( $select_name ); ?>">
									<?php if ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $term->term_group ) : ?>
										<option value=""><?php esc_html_e( 'New', 'amp' ); ?></option>
									<?php endif; ?>
									<option value="<?php echo esc_attr( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS ); ?>" <?php selected( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS, $term->term_group ); ?>><?php esc_html_e( 'Accepted', 'amp' ); ?></option>
									<option value="<?php echo esc_attr( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS ); ?>" <?php selected( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS, $term->term_group ); ?>><?php esc_html_e( 'Rejected', 'amp' ); ?></option>
								</select>
								<?php if ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $term->term_group ) : ?>
									&#x2753;
								<?php elseif ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS === $term->term_group ) : ?>
									&#x274C;
								<?php elseif ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS === $term->term_group ) : ?>
									&#x2705;
								<?php endif; ?>
								<code><?php echo esc_html( $error['data']['code'] ); ?></code>
							</summary>
							<?php if ( $term->count > 1 ) : ?>
								<p class="validation-error-other-urls">
									<?php
									$url = admin_url(
										add_query_arg(
											array(
												AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG => $term->slug,
												'post_type' => self::POST_TYPE_SLUG,
											),
											'edit.php'
										)
									);
									printf(
										/* translators: %1$s is URL to invalid URL page, and %2$s is the count */
										wp_kses_post( _n(
											'There is at least <a href="%1$s">%2$s other URL</a> which has this validation error. Accepting or rejecting the error here will also apply to the other URL.',
											'There are at least <a href="%1$s">%2$s other URLs</a> which have this validation error. Accepting or rejecting the error here will also apply to the other URLs.',
											$term->count - 1,
											'amp'
										) ),
										esc_url( $url ),
										esc_html( number_format_i18n( $term->count - 1 ) )
									);
									?>
								</p>
							<?php endif; ?>
							<ul class="detailed">
							<?php if ( AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE === $error['data']['code'] ) : ?>
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
									$collapsed_details[] = 'node_attributes';
									$collapsed_details[] = 'node_name';
									$collapsed_details[] = 'parent_name';
									?>
								</li>
							<?php elseif ( AMP_Validation_Error_Taxonomy::INVALID_ATTRIBUTE_CODE === $error['data']['code'] ) : ?>
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
									$collapsed_details[] = 'parent_name';
									$collapsed_details[] = 'element_attributes';
									$collapsed_details[] = 'node_name';
									?>
								</li>
							<?php endif; ?>
								<?php unset( $error['data']['code'] ); ?>
								<?php foreach ( $error['data'] as $key => $value ) : ?>
									<li>
										<details <?php echo ! in_array( $key, $collapsed_details, true ) ? 'open' : ''; ?>>
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
	 * Show URL at the top of the edit form in place of the title (since title support is not present).
	 *
	 * @param WP_Post $post Post.
	 */
	public static function print_url_as_title( $post ) {
		if ( self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		// Remember URL is stored in post_title. Adding query var prevents redirection to non-AMP page.
		$view_url = add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, '', $post->post_title );
		?>
		<h2 class="amp-invalid-url">
			<a href="<?php echo esc_url( $view_url ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
		</h2>
		<?php
	}

	/**
	 * Strip host name from AMP invalid URL being printed.
	 *
	 * @param string  $title Title.
	 * @param WP_Post $post  Post.
	 *
	 * @return string Title.
	 */
	public static function filter_the_title_in_post_list_table( $title, $post ) {
		if ( get_current_screen()->base === 'edit' && get_current_screen()->post_type === self::POST_TYPE_SLUG && self::POST_TYPE_SLUG === get_post_type( $post ) ) {
			$title = preg_replace( '#^(\w+:)?//[^/]+#', '', $title );
		}
		return $title;
	}

	/**
	 * Gets the URL to recheck the post for AMP validity.
	 *
	 * Appends a query var to $redirect_url.
	 * On clicking the link, it checks if errors still exist for $post.
	 *
	 * @param  WP_Post $post         The post storing the validation error.
	 * @param  string  $redirect_url The URL of the redirect.
	 * @param  string  $recheck_url  The URL to check. Optional.
	 * @return string The URL to recheck the post.
	 */
	public static function get_recheck_url( $post, $redirect_url, $recheck_url = null ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'      => self::RECHECK_ACTION,
					'recheck_url' => $recheck_url,
				),
				$redirect_url
			),
			self::NONCE_ACTION . $post->ID
		);
	}

	/**
	 * Filter At a Glance items add AMP Validation Errors.
	 *
	 * @param array $items At a glance items.
	 * @return array Items.
	 */
	public static function filter_dashboard_glance_items( $items ) {

		$query = new WP_Query( array(
			'post_type'              => self::POST_TYPE_SLUG,
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		if ( 0 !== $query->found_posts ) {
			$items[] = sprintf(
				'<a class="amp-validation-errors" href="%s">%s</a>',
				esc_url( admin_url(
					add_query_arg(
						array(
							'post_type' => self::POST_TYPE_SLUG,
							AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
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

}
