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
	const VALIDATE_ACTION = 'amp_validate';

	/**
	 * The action to bulk recheck URLs for AMP validity.
	 *
	 * @var string
	 */
	const BULK_VALIDATE_ACTION = 'amp_bulk_validate';

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
					'not_found_in_trash' => __( 'No forgotten invalid AMP pages', 'amp' ),
					'search_items'       => __( 'Search invalid AMP pages', 'amp' ),
					'edit_item'          => __( 'Invalid AMP Page (URL)', 'amp' ),
				),
				'supports'     => false,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => ( self::should_show_in_menu() || AMP_Validation_Error_Taxonomy::should_show_in_menu() ) ? AMP_Options_Manager::OPTION_NAME : false,
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
	 * Determine whether the admin menu item should be included.
	 *
	 * @return bool Whether to show in menu.
	 */
	public static function should_show_in_menu() {
		global $pagenow;
		if ( current_theme_supports( 'amp' ) ) {
			return true;
		}
		return ( 'edit.php' === $pagenow && ( isset( $_GET['post_type'] ) && self::POST_TYPE_SLUG === $_GET['post_type'] ) ); // WPCS: CSRF OK.
	}

	/**
	 * Add admin hooks.
	 */
	public static function add_admin_hooks() {
		add_filter( 'dashboard_glance_items', array( __CLASS__, 'filter_dashboard_glance_items' ) );
		add_action( 'rightnow_end', array( __CLASS__, 'print_dashboard_glance_styles' ) );

		// Edit post screen hooks.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_edit_post_screen_scripts' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'edit_form_top', array( __CLASS__, 'print_url_as_title' ) );

		// Post list screen hooks.
		add_filter( 'the_title', array( __CLASS__, 'filter_the_title_in_post_list_table' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'render_post_filters' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( __CLASS__, 'add_post_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'output_custom_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'filter_bulk_actions' ), 10, 2 );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'handle_bulk_action' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'print_admin_notice' ) );
		add_action( 'admin_action_' . self::VALIDATE_ACTION, array( __CLASS__, 'handle_validate_request' ) );
		add_action( 'post_action_' . self::UPDATE_POST_TERM_STATUS_ACTION, array( __CLASS__, 'handle_validation_error_status_update' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_new_invalid_url_count' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 10, 2 );
		add_filter( sprintf( 'views_edit-%s', self::POST_TYPE_SLUG ), array( __CLASS__, 'filter_table_views' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'filter_bulk_post_updated_messages' ), 10, 2 );

		// Hide irrelevant "published" label in the invalid URL post list.
		add_filter( 'post_date_column_status', function( $status, $post ) {
			if ( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG === get_post_type( $post ) ) {
				$status = '';
			}
			return $status;
		}, 10, 2 );

		// Prevent query vars from persisting after redirect.
		add_filter( 'removable_query_args', function( $query_vars ) {
			$query_vars[] = 'amp_actioned';
			$query_vars[] = 'amp_taxonomy_terms_updated';
			$query_vars[] = AMP_Invalid_URL_Post_Type::REMAINING_ERRORS;
			$query_vars[] = 'amp_urls_tested';
			$query_vars[] = 'amp_validate_error';
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
	 * @param string|int|WP_Post $url Either the URL string or a post (ID or WP_Post) of amp_invalid_url type.
	 * @param array              $args {
	 *     Args.
	 *
	 *     @type bool $ignore_accepted Exclude validation errors that are accepted. Default false.
	 * }
	 * @return array List of errors, with keys for term, data, status, and (sanitization) forced.
	 */
	public static function get_invalid_url_validation_errors( $url, $args = array() ) {
		$args = array_merge(
			array(
				'ignore_accepted' => false,
			),
			$args
		);

		// Look up post by URL or ensure the amp_invalid_url object.
		if ( is_string( $url ) ) {
			$post = self::get_invalid_url_post( $url );
		} else {
			$post = get_post( $url );
		}
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return array();
		}

		// Skip when parse error.
		$stored_validation_errors = json_decode( $post->post_content, true );
		if ( ! is_array( $stored_validation_errors ) ) {
			return array();
		}

		$errors = array();
		foreach ( $stored_validation_errors as $stored_validation_error ) {
			if ( ! isset( $stored_validation_error['term_slug'] ) ) {
				continue;
			}

			$term = get_term_by( 'slug', $stored_validation_error['term_slug'], AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
			if ( ! $term ) {
				continue;
			}

			$sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $stored_validation_error['data'] );
			if ( $args['ignore_accepted'] && AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS === $sanitization['status'] ) {
				continue;
			}

			$errors[] = array_merge(
				array(
					'term' => $term,
					'data' => $stored_validation_error['data'],
				),
				$sanitization
			);
		}
		return $errors;
	}

	/**
	 * Display summary of the validation error counts for a given post.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 */
	public static function display_invalid_url_validation_error_counts_summary( $post ) {
		$counts = array_fill_keys(
			array( 'new', 'accepted', 'rejected' ),
			0
		);

		$validation_errors = self::get_invalid_url_validation_errors( $post );
		foreach ( $validation_errors as $error ) {
			switch ( $error['term']->term_group ) {
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS:
					$counts['new']++;
					break;
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS:
					$counts['accepted']++;
					break;
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS:
					$counts['rejected']++;
					break;
			}
		}

		$result = array();
		if ( $counts['new'] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x2753; New: %s', 'amp' ),
				number_format_i18n( $counts['new'] )
			) );
		}
		if ( $counts['accepted'] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x2705; Accepted: %s', 'amp' ),
				number_format_i18n( $counts['accepted'] )
			) );
		}
		if ( $counts['rejected'] ) {
			$result[] = esc_html( sprintf(
				/* translators: %s is count */
				__( '&#x274C; Rejected: %s', 'amp' ),
				number_format_i18n( $counts['rejected'] )
			) );
		}
		echo implode( '<br>', $result ); // WPCS: xss ok.
	}

	/**
	 * Gets the existing custom post that stores errors for the $url, if it exists.
	 *
	 * @param string $url The (in)valid URL.
	 * @return WP_Post|null The post of the existing custom post, or null.
	 */
	public static function get_invalid_url_post( $url ) {
		$url = remove_query_arg( amp_get_slug(), $url );
		return get_page_by_path( md5( $url ), OBJECT, self::POST_TYPE_SLUG );
	}

	/**
	 * Get the URL from a given amp_invalid_url post.
	 *
	 * The URL will be returned with the amp query var added to it if the site is not canonical. The post_title
	 * is always stored using the canonical AMP-less URL.
	 *
	 * @param int|WP_post $post Post.
	 * @return string|null The URL stored for the post or null if post does not exist or it is not the right type.
	 */
	public static function get_url_from_post( $post ) {
		$post = get_post( $post );
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return null;
		}
		$url = $post->post_title;
		if ( ! amp_is_canonical() ) {
			$url = add_query_arg( amp_get_slug(), '', $url );
		}
		return $url;
	}

	/**
	 * Stores the validation errors.
	 *
	 * If there are no validation errors provided, then any existing amp_invalid_url post is deleted.
	 *
	 * @param array  $validation_errors Validation errors.
	 * @param string $url               URL on which the validation errors occurred. Will be normalized to non-AMP version.
	 * @param array  $args {
	 *     Args.
	 *
	 *     @type int|WP_Post $invalid_url_post Post to update. Optional. If empty, then post is looked up by URL.
	 *     @type array       $queried_object   Queried object, including keys for type and id. May be empty.
	 * }
	 * @return int|WP_Error $post_id The post ID of the custom post type used, or WP_Error on failure.
	 * @global WP $wp
	 */
	public static function store_validation_errors( $validation_errors, $url, $args = array() ) {
		$url  = remove_query_arg( amp_get_slug(), $url ); // Only ever store the canonical version.
		$slug = md5( $url );
		if ( isset( $args['invalid_url_post'] ) ) {
			$post = get_post( $args['invalid_url_post'] );
		} else {
			$post = get_page_by_path( $slug, OBJECT, self::POST_TYPE_SLUG );
			if ( ! $post ) {
				$post = get_page_by_path( $slug . '__trashed', OBJECT, self::POST_TYPE_SLUG );
			}
		}

		/*
		 * The details for individual validation errors is stored in the amp_validation_error taxonomy terms.
		 * The post content just contains the slugs for these terms and the sources for the given instance of
		 * the validation error.
		 */
		$stored_validation_errors = array();

		// Prevent Kses from corrupting JSON in description.
		$has_pre_term_description_filter = has_filter( 'pre_term_description', 'wp_filter_kses' );
		if ( false !== $has_pre_term_description_filter ) {
			remove_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
		}

		$terms = array();
		foreach ( $validation_errors as $data ) {
			$term_data = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $data );
			$term_slug = $term_data['slug'];

			if ( ! isset( $terms[ $term_slug ] ) ) {

				// Not using WP_Term_Query since more likely individual terms are cached and wp_insert_term() will itself look at this cache anyway.
				$term = get_term_by( 'slug', $term_slug, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
				if ( ! ( $term instanceof WP_Term ) ) {
					$r = wp_insert_term( $term_slug, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, wp_slash( $term_data ) );
					if ( is_wp_error( $r ) ) {
						continue;
					}
					$term_id = $r['term_id'];
					update_term_meta( $term_id, 'created_date_gmt', current_time( 'mysql', true ) );

					/*
					 * When sanitization is forced by filter, make sure the term is created with the filtered status.
					 * For some reason, the wp_insert_term() function doesn't work with the term_group being passed in.
					 */
					$sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $data );
					if ( 'with_filter' === $sanitization['forced'] ) {
						wp_update_term( $term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, array(
							'term_group' => $sanitization['status'],
						) );
						$term_data['term_group'] = $sanitization['status'];
					}

					$term = get_term( $term_id );
				}
				$terms[ $term_slug ] = $term;
			}

			$stored_validation_errors[] = compact( 'term_slug', 'data' );
		}

		// Finish preventing Kses from corrupting JSON in description.
		if ( false !== $has_pre_term_description_filter ) {
			add_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
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
				AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG === $post_data['post_type']
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
				'post_name'    => $slug,
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

		update_post_meta( $post_id, '_amp_validated_environment', self::get_validated_environment() );
		if ( isset( $args['queried_object'] ) ) {
			update_post_meta( $post_id, '_amp_queried_object', $args['queried_object'] );
		}

		return $post_id;
	}

	/**
	 * Get the environment properties which will likely effect whether validation results are stale.
	 *
	 * @return array Environment.
	 */
	public static function get_validated_environment() {
		return array(
			'theme'   => get_stylesheet(),
			'plugins' => get_option( 'active_plugins', array() ),
			'options' => array(
				'accept_tree_shaking' => ( AMP_Options_Manager::get_option( 'accept_tree_shaking' ) || AMP_Options_Manager::get_option( 'force_sanitization' ) ),
			),
		);
	}

	/**
	 * Get the differences between the current themes, plugins, and relevant options when amp_invalid_url post was last updated and now.
	 *
	 * @param int|WP_Post $post Post of amp_invalid_url type.
	 * @return array {
	 *     Staleness of the validation results. An empty array if the results are fresh.
	 *
	 *     @type string $theme   The theme that was active but is no longer. Absent if theme is the same.
	 *     @type array  $plugins Plugins that used to be active but are no longer, or which are active now but weren't. Absent if the plugins were the same.
	 *     @type array  $options Options that are now different. Absent if the options were the same.
	 * }
	 */
	public static function get_post_staleness( $post ) {
		$post = get_post( $post );
		if ( empty( $post ) || self::POST_TYPE_SLUG !== $post->post_type ) {
			return array();
		}

		$old_validated_environment = get_post_meta( $post->ID, '_amp_validated_environment', true );
		$new_validated_environment = self::get_validated_environment();

		$staleness = array();
		if ( isset( $old_validated_environment['theme'] ) && $new_validated_environment['theme'] !== $old_validated_environment['theme'] ) {
			$staleness['theme'] = $old_validated_environment['theme'];
		}

		if ( isset( $old_validated_environment['plugins'] ) ) {
			$new_active_plugins = array_diff( $new_validated_environment['plugins'], $old_validated_environment['plugins'] );
			if ( ! empty( $new_active_plugins ) ) {
				$staleness['plugins']['new'] = array_values( $new_active_plugins );
			}
			$old_active_plugins = array_diff( $old_validated_environment['plugins'], $new_validated_environment['plugins'] );
			if ( ! empty( $old_active_plugins ) ) {
				$staleness['plugins']['old'] = array_values( $old_active_plugins );
			}
		}

		if ( isset( $old_validated_environment['options'] ) ) {
			$differing_options = array_diff_assoc( $new_validated_environment['options'], $old_validated_environment['options'] );
			if ( $differing_options ) {
				$staleness['options'] = $differing_options;
			}
		}

		return $staleness;
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
				$staleness = self::get_post_staleness( $post_id );
				if ( ! empty( $staleness ) ) {
					echo '<strong><em>' . esc_html__( 'Stale results', 'amp' ) . '</em></strong><br>';
				}
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

		$url = self::get_url_from_post( $post );
		if ( $url ) {
			$actions['view'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, '', $url ) ),
				esc_html__( 'View', 'amp' )
			);
		}

		$actions[ self::VALIDATE_ACTION ] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( self::get_recheck_url( $post ) ),
			esc_html__( 'Recheck', 'amp' )
		);
		if ( self::get_post_staleness( $post ) ) {
			$actions[ self::VALIDATE_ACTION ] = sprintf( '<em>%s</em>', $actions[ self::VALIDATE_ACTION ] );
		}

		return $actions;
	}

	/**
	 * Adds a 'Recheck' bulk action to the edit.php page and modifies the 'Move to Trash' text.
	 *
	 * @param array $actions The bulk actions in the edit.php page.
	 * @return array $actions The filtered bulk actions.
	 */
	public static function filter_bulk_actions( $actions ) {
		if ( isset( $actions['trash'] ) ) {
			$actions['trash'] = esc_html__( 'Forget', 'amp' );
		}

		if ( isset( $actions['delete'] ) ) {
			$actions['delete'] = esc_html__( 'Forget permanently', 'amp' );
		}

		unset( $actions['edit'] );
		$actions[ self::BULK_VALIDATE_ACTION ] = esc_html__( 'Recheck', 'amp' );
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
		if ( self::BULK_VALIDATE_ACTION !== $action ) {
			return $redirect;
		}
		$remaining_invalid_urls = array();

		$errors = array();

		foreach ( $items as $item ) {
			$post = get_post( $item );
			if ( empty( $post ) ) {
				continue;
			}

			$url = self::get_url_from_post( $post );
			if ( empty( $url ) ) {
				continue;
			}

			$validity = AMP_Validation_Manager::validate_url( $url );
			if ( is_wp_error( $validity ) ) {
				$errors[] = $validity->get_error_code();
				continue;
			}

			$validation_errors = wp_list_pluck( $validity['results'], 'error' );
			self::store_validation_errors(
				$validation_errors,
				$validity['url'],
				wp_array_slice_assoc( $validity, array( 'queried_object' ) )
			);
			$unaccepted_error_count = count( array_filter(
				$validation_errors,
				function( $error ) {
					return ! AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error );
				}
			) );
			if ( $unaccepted_error_count > 0 ) {
				$remaining_invalid_urls[] = $validity['url'];
			}
		}

		// Get the URLs that still have errors after rechecking.
		$args = array(
			self::URLS_TESTED => count( $items ),
		);
		if ( ! empty( $errors ) ) {
			$args['amp_validate_error'] = $errors;
		} else {
			$args[ self::REMAINING_ERRORS ] = count( $remaining_invalid_urls );
		}

		$redirect = remove_query_arg( wp_removable_query_args(), $redirect );
		return add_query_arg( $args, $redirect );
	}

	/**
	 * Outputs an admin notice after rechecking URL(s) on the custom post page.
	 *
	 * @return void
	 */
	public static function print_admin_notice() {
		if ( ! get_current_screen() || self::POST_TYPE_SLUG !== get_current_screen()->post_type ) { // WPCS: CSRF ok.
			return;
		}

		if ( isset( $_GET['amp_validate_error'] ) ) { // WPCS: CSRF OK.
			$error_codes = array_unique( array_map( 'sanitize_key', (array) $_GET['amp_validate_error'] ) ); // WPCS: CSRF OK.
			foreach ( $error_codes as $error_code ) {
				switch ( $error_code ) {
					case 'http_request_failed':
						$message = __( 'Failed to fetch URL(s) to validate. This may be due to a request timeout.', 'amp' );
						break;
					case '404':
						$message = __( 'The fetched URL(s) was not found. It may have been deleted. If so, you can trash this.', 'amp' );
						break;
					case '500':
						$message = __( 'An internal server error occurred when fetching the URL.', 'amp' );
						break;
					default:
						/* translators: %s is error code */
						$message = sprintf( __( 'Unable to validate the URL(s); error code is %s.', 'amp' ), $error_code ); // Note that $error_code has been sanitized with sanitize_key(); will be escaped below as well.
				}
				printf(
					'<div class="notice is-dismissible error"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
					esc_html( $message ),
					esc_html__( 'Dismiss this notice.', 'amp' )
				);
			}
		}

		if ( isset( $_GET[ self::REMAINING_ERRORS ] ) ) {
			$count_urls_tested = isset( $_GET[ self::URLS_TESTED ] ) ? intval( $_GET[ self::URLS_TESTED ] ) : 1; // WPCS: CSRF ok.
			$errors_remain     = ! empty( $_GET[ self::REMAINING_ERRORS ] ); // WPCS: CSRF ok.
			if ( $errors_remain ) {
				$message = _n( 'The rechecked URL still has unaccepted validation errors.', 'The rechecked URLs still have unaccepted validation errors.', $count_urls_tested, 'amp' );
				$class   = 'notice-warning';
			} else {
				$message = _n( 'The rechecked URL is free of unaccepted validation errors.', 'The rechecked URLs are free of unaccepted validation errors.', $count_urls_tested, 'amp' );
				$class   = 'updated';
			}

			printf(
				'<div class="notice is-dismissible %s"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
				esc_attr( $class ),
				esc_html( $message ),
				esc_html__( 'Dismiss this notice.', 'amp' )
			);
		}

		$count = isset( $_GET['amp_taxonomy_terms_updated'] ) ? intval( $_GET['amp_taxonomy_terms_updated'] ) : 0; // WPCS: CSRF ok.
		if ( $count > 0 ) {
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

		if ( 'post' !== get_current_screen()->base ) {
			// Display admin notice according to the AMP mode.
			if ( amp_is_canonical() ) {
				$template_mode = 'native';
			} elseif ( current_theme_supports( 'amp' ) ) {
				$template_mode = 'paired';
			} else {
				$template_mode = 'classic';
			}
			$auto_sanitization = AMP_Options_Manager::get_option( 'force_sanitization' );

			if ( 'native' === $template_mode ) {
				$message = __( 'The site is using native AMP mode, the validation errors found are already automatically handled.', 'amp' );
			} elseif ( 'paired' === $template_mode && $auto_sanitization ) {
				$message = __( 'The site is using paired AMP mode with auto-sanitization turned on, the validation errors found are already automatically handled.', 'amp' );
			} elseif ( 'paired' === $template_mode ) {
				$message = sprintf(
					/* translators: %s is a link to the AMP settings screen */
					__( 'The site is using paired AMP mode without auto-sanitization, the validation errors found require action and influence which pages are shown in AMP. For automatically handling the errors turn on auto-sanitization from <a href="%s">Validation Handling settings</a>.', 'amp' ),
					esc_url( admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME ) )
				);
			} else {
				$message = __( 'The site is using classic AMP mode, your theme templates are not used and the errors below are irrelevant.', 'amp' );
			}

			$class = 'info';
			printf(
				/* translators: 1. Notice classname; 2. Message text; 3. Screenreader text; */
				'<div class="notice notice-%s"><p>%s</p></div>',
				esc_attr( $class ),
				wp_kses_post( $message )
			);
		}
	}

	/**
	 * Handles clicking 'recheck' on the inline post actions and in the admin bar on the frontend.
	 *
	 * @throws Exception But it is caught. This is here for a PHPCS bug.
	 */
	public static function handle_validate_request() {
		check_admin_referer( self::NONCE_ACTION );
		if ( ! AMP_Validation_Manager::has_cap() ) {
			wp_die( esc_html__( 'You do not have permissions to validate an AMP URL. Did you get logged out?', 'amp' ) );
		}

		$post = null;
		$url  = null;

		try {
			if ( isset( $_GET['post'] ) ) {
				$post = intval( $_GET['post'] );
				if ( $post <= 0 ) {
					throw new Exception( 'unknown_post' );
				}
				$post = get_post( $post );
				if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
					throw new Exception( 'invalid_post' );
				}
				$url = self::get_url_from_post( $post );
			} elseif ( isset( $_GET['url'] ) ) {
				$url = wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['url'] ) ), null );
				if ( ! $url ) {
					throw new Exception( 'illegal_url' );
				}
			}

			if ( ! $url ) {
				throw new Exception( 'missing_url' );
			}

			$validity = AMP_Validation_Manager::validate_url( $url );
			if ( is_wp_error( $validity ) ) {
				throw new Exception( esc_html( $validity->get_error_code() ) );
			}

			$errors = wp_list_pluck( $validity['results'], 'error' );
			$stored = self::store_validation_errors(
				$errors,
				$validity['url'],
				array_merge(
					array(
						'invalid_url_post' => $post,
					),
					wp_array_slice_assoc( $validity, array( 'queried_object' ) )
				)
			);
			if ( is_wp_error( $stored ) ) {
				throw new Exception( esc_html( $stored->get_error_code() ) );
			}
			$redirect = get_edit_post_link( $stored, 'raw' );

			$error_count = count( array_filter(
				$errors,
				function ( $error ) {
					return ! AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error );
				}
			) );

			$args[ self::URLS_TESTED ]      = '1';
			$args[ self::REMAINING_ERRORS ] = $error_count;
		} catch ( Exception $e ) {
			$args['amp_validate_error'] = $e->getMessage();
			$args[ self::URLS_TESTED ]  = '0';

			if ( $post && self::POST_TYPE_SLUG === $post->post_type ) {
				$redirect = get_edit_post_link( $post->ID, 'raw' );
			} else {
				$redirect = admin_url(
					add_query_arg(
						array( 'post_type' => self::POST_TYPE_SLUG ),
						'edit.php'
					)
				);
			}
		}

		wp_safe_redirect( add_query_arg( $args, $redirect ) );
		exit();
	}

	/**
	 * Re-check invalid URL post for whether it has blocking validation errors.
	 *
	 * @param int|WP_Post $post Post.
	 * @return array|WP_Error List of blocking validation results, or a WP_Error in the case of failure.
	 */
	public static function recheck_post( $post ) {
		if ( ! $post ) {
			return new WP_Error( 'missing_post' );
		}
		$post = get_post( $post );
		if ( ! $post ) {
			return new WP_Error( 'missing_post' );
		}
		$url = self::get_url_from_post( $post );
		if ( ! $url ) {
			return new WP_Error( 'missing_url' );
		}

		$validity = AMP_Validation_Manager::validate_url( $url );
		if ( is_wp_error( $validity ) ) {
			return $validity;
		}

		$validation_errors  = wp_list_pluck( $validity['results'], 'error' );
		$validation_results = array();
		self::store_validation_errors(
			$validation_errors,
			$validity['url'],
			array_merge(
				array(
					'invalid_url_post' => $post,
				),
				wp_array_slice_assoc( $validity, array( 'queried_object' ) )
			)
		);
		foreach ( $validation_errors  as $error ) {
			$sanitized = AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error ); // @todo Consider re-using $validity['results'][x]['sanitized'], unless auto-sanitize is causing problem.

			$validation_results[] = compact( 'error', 'sanitized' );
		}
		return $validation_results;
	}

	/**
	 * Handle validation error status update.
	 *
	 * @see AMP_Validation_Error_Taxonomy::handle_validation_error_update()
	 * @todo This is duplicated with logic in AMP_Validation_Error_Taxonomy. All of the term updating needs to be refactored to make use of the REST API.
	 */
	public static function handle_validation_error_status_update() {
		check_admin_referer( self::UPDATE_POST_TERM_STATUS_ACTION, self::UPDATE_POST_TERM_STATUS_ACTION . '_nonce' );
		if ( ! AMP_Validation_Manager::has_cap() ) {
			wp_die( esc_html__( 'You do not have permissions to validate an AMP URL. Did you get logged out?', 'amp' ) );
		}

		if ( empty( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) || ! is_array( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) ) {
			return;
		}
		$post = get_post();
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
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

		/*
		 * Re-check the post after the validation status change. This is particularly important for validation errors like
		 * 'removed_unused_css_rules' since whether it is accepted will determine whether other validation errors are triggered
		 * such as in this case 'excessive_css'.
		 */
		if ( $updated_count > 0 ) {
			$validation_results = self::recheck_post( $post->ID );
			// @todo For WP_Error case, see <https://github.com/Automattic/amp-wp/issues/1166>.
			if ( ! is_wp_error( $validation_results ) ) {
				$args[ self::REMAINING_ERRORS ] = count( array_filter(
					$validation_results,
					function( $result ) {
						return ! $result['sanitized'];
					}
				) );
			}
		}

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = get_edit_post_link( $post->ID );
		}

		$redirect = remove_query_arg( wp_removable_query_args(), $redirect );
		wp_safe_redirect( add_query_arg( $args, $redirect ) );
		exit();
	}

	/**
	 * Enqueue scripts for the edit post screen.
	 */
	public static function enqueue_edit_post_screen_scripts() {
		$current_screen = get_current_screen();
		if ( 'post' !== $current_screen->base || self::POST_TYPE_SLUG !== $current_screen->post_type ) {
			return;
		}

		// Eliminate autosave since it is only relevant for the content editor.
		wp_dequeue_script( 'autosave' );

		$handle = 'amp-invalid-url-post-edit-screen';
		wp_enqueue_script( $handle, amp_get_asset_url( "js/$handle.js" ), array(), AMP__VERSION, true );
		$data = array(
			'l10n' => array(
				'unsaved_changes' => __( 'You have unsaved changes. Are you sure you want to leave?', 'amp' ),
			),
		);
		wp_add_inline_script(
			$handle,
			sprintf( 'document.addEventListener( "DOMContentLoaded", function() { ampInvalidUrlPostEditScreen.boot( %s ); } );', wp_json_encode( $data ) ),
			'after'
		);
	}

	/**
	 * Adds the meta boxes to the CPT post.php page.
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		remove_meta_box( 'submitdiv', self::POST_TYPE_SLUG, 'side' );
		remove_meta_box( 'slugdiv', self::POST_TYPE_SLUG, 'normal' );
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
		$is_sanitization_forcibly_accepted_by_filter = AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( array(
			'code' => 'does_not_exist',
		) );

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
						<a class="button button-secondary" href="<?php echo esc_url( self::get_recheck_url( $post ) ); ?>">
							<?php esc_html_e( 'Recheck', 'amp' ); ?>
						</a>
					</div>
					<?php if ( ! ( AMP_Validation_Manager::is_sanitization_forcibly_accepted() || $is_sanitization_forcibly_accepted_by_filter ) ) : ?>
						<div id="preview-action">
							<button type="button" name="action" class="preview button" id="preview_validation_errors"><?php esc_html_e( 'Preview Changes', 'default' ); ?></button>
						</div>
					<?php endif; ?>
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
						<?php
						$staleness = self::get_post_staleness( $post );
						if ( ! empty( $staleness ) ) {
							echo '<div class="notice notice-info notice-alt inline"><p>';
							echo '<b>';
							esc_html_e( 'Stale results', 'amp' );
							echo '</b>';
							echo '<br>';
							if ( ! empty( $staleness['theme'] ) && ! empty( $staleness['plugins'] ) ) {
								esc_html_e( 'Different theme and plugins were active when these results were obtained.', 'amp' );
								echo ' ';
							} elseif ( ! empty( $staleness['theme'] ) ) {
								esc_html_e( 'A different theme was active when these results were obtained.', 'amp' );
								echo ' ';
							} elseif ( ! empty( $staleness['plugins'] ) ) {
								esc_html_e( 'Different plugins were active when these results were obtained.', 'amp' );
								echo ' ';
							}
							if ( ! empty( $staleness['options'] ) ) {
								esc_html_e( 'Options have changed.', 'amp' );
								echo ' ';
							}
							esc_html_e( 'Please recheck.', 'amp' );
							echo '</p></div>';
						}
						?>
						<?php self::display_invalid_url_validation_error_counts_summary( $post ); ?>
					</div>

					<div class="misc-pub-section">
						<?php
						$view_label     = __( 'View URL', 'amp' );
						$queried_object = get_post_meta( $post->ID, '_amp_queried_object', true );
						if ( isset( $queried_object['id'] ) && isset( $queried_object['type'] ) ) {
							$after = ' | ';
							if ( 'post' === $queried_object['type'] && get_post( $queried_object['id'] ) && post_type_exists( get_post( $queried_object['id'] )->post_type ) ) {
								$post_type_object = get_post_type_object( get_post( $queried_object['id'] )->post_type );
								edit_post_link( $post_type_object->labels->edit_item, '', $after, $queried_object['id'] );
								$view_label = $post_type_object->labels->view_item;
							} elseif ( 'term' === $queried_object['type'] && get_term( $queried_object['id'] ) && taxonomy_exists( get_term( $queried_object['id'] )->taxonomy ) ) {
								$taxonomy_object = get_taxonomy( get_term( $queried_object['id'] )->taxonomy );
								edit_term_link( $taxonomy_object->labels->edit_item, '', $after, get_term( $queried_object['id'] ) );
								$view_label = $taxonomy_object->labels->view_item;
							} elseif ( 'user' === $queried_object['type'] ) {
								$link = get_edit_user_link( $queried_object['id'] );
								if ( $link ) {
									printf( '<a href="%s">%s</a>%s', esc_url( $link ), esc_html__( 'Edit User', 'amp' ), esc_html( $after ) );
								}
								$view_label = __( 'View User', 'amp' );
							}
						}
						printf( '<a href="%s">%s</a>', esc_url( self::get_url_from_post( $post ) ), esc_html( $view_label ) );
						?>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">
						<?php esc_html_e( 'Forget', 'amp' ); ?>
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

		if ( empty( $validation_errors ) ) {
			?>
			<div class="notice notice-success notice-alt inline">
				<p><?php esc_html_e( 'There are no AMP validation errors on this URL.', 'amp' ); ?></p>
			</div>
			<?php
			return;
		}

		$url = self::get_url_from_post( $post );

		$has_unaccepted_errors = 0 !== count( array_filter( $validation_errors, function( $validation_error ) {
			return AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS !== $validation_error['term_status'];
		} ) );

		$is_sanitization_forcibly_accepted_by_filter = AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( array(
			'code' => 'does_not_exist',
		) );

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

		<?php if ( $has_unaccepted_errors ) : ?>
			<?php if ( $is_sanitization_forcibly_accepted_by_filter || AMP_Validation_Manager::is_sanitization_forcibly_accepted() ) : ?>
				<div class="notice notice-warning notice-alt inline">
					<p>
						<?php esc_html_e( 'This URL will be served served as valid AMP but some of the markup will be stripped from the response since it is not valid.', 'amp' ); ?>
						<?php if ( amp_is_canonical() ) : ?>
							<?php esc_html_e( 'Accepting sanitization is required for native AMP mode.', 'amp' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'You have elected in the AMP settings for all sanitization to be accepted.', 'amp' ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-error notice-alt inline">
					<p><?php esc_html_e( 'This URL cannot be served as AMP because it has validation errors which are either new or rejected as being blockers.', 'amp' ); ?></p>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="notice notice-success notice-alt inline">
				<p><?php esc_html_e( 'This URL can be served as AMP because all validation errors have been accepted as not being blockers.', 'amp' ); ?></p>
			</div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'An accepted validation error is one that will not block a URL from being served as AMP; the validation error will be sanitized, normally resulting in the offending markup being stripped from the response to ensure AMP validity. A validation error that is accepted here will also be accepted for any other URL it occurs on.', 'amp' ); ?>
		</p>

		<script>
		jQuery( function( $ ) {
			var validateUrl, postId;
			validateUrl = <?php echo wp_json_encode( add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, AMP_Validation_Manager::get_amp_validate_nonce(), $url ) ); ?>;
			postId = <?php echo wp_json_encode( $post->ID ); ?>;
			$( '#preview_validation_errors' ).on( 'click', function() {
				var params = {}, validatePreviewUrl = validateUrl;
				$( '.amp-validation-error-status' ).each( function() {
					if ( this.value && ! this.options[ this.selectedIndex ].defaultSelected ) {
						params[ this.name ] = this.value;
					}
				} );
				validatePreviewUrl += '&' + $.param( params );
				validatePreviewUrl += '#development=1';
				window.open( validatePreviewUrl, 'amp-validation-error-term-status-preview-' + String( postId ) );
			} );
		} );
		</script>

		<?php
		$has_forced_sanitized = false;
		foreach ( $validation_errors as $validation_error ) {
			if ( $validation_error['forced'] ) {
				$has_forced_sanitized = true;
				break;
			}
		}
		?>

		<?php if ( $has_forced_sanitized || ( $has_unaccepted_errors && amp_is_canonical() ) ) : ?>
			<div class="notice notice-info notice-alt inline">
				<p>&#x1F6A9; <?php esc_html_e( 'Flagged validation error statuses will not be applied to your site since they are automatically handled by the theme or the plugin\'s settings. You can use the flag to mark issues that you need to follow up on.', 'amp' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="amp-validation-errors">
			<ul>
				<?php foreach ( $validation_errors as $error ) : ?>
					<?php
					$collapsed_details = array();
					$term              = $error['term'];
					$select_name       = sprintf( '%s[%s]', AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR, $term->slug );
					?>
					<li>
						<details <?php echo ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $error['term']->term_group ) ? 'open' : ''; ?>>
							<summary>
								<label for="<?php echo esc_attr( $select_name ); ?>" class="screen-reader-text">
									<?php esc_html_e( 'Status:', 'amp' ); ?>
								</label>
								<select class="amp-validation-error-status" id="<?php echo esc_attr( $select_name ); ?>" name="<?php echo esc_attr( $select_name ); ?>">
									<?php if ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS === $error['term']->term_group ) : ?>
										<option value="">
											&#x2753;
											<?php esc_html_e( 'New', 'amp' ); ?>
										</option>
									<?php endif; ?>
									<option value="<?php echo esc_attr( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS ); ?>" <?php selected( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS, $error['term']->term_group ); ?>>
										<?php if ( $error['forced'] && AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS === $error['status'] ) : ?>
											&#x1F6A9;
										<?php else : ?>
											&#x2705;
										<?php endif; ?>
										<?php esc_html_e( 'Accepted', 'amp' ); ?>
									</option>
									<option style="text-decoration: line-through" value="<?php echo esc_attr( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS ); ?>" <?php selected( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS, $error['term']->term_group ); ?>>
										<?php if ( amp_is_canonical() || ( $error['forced'] && AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS === $error['status'] ) ) : ?>
											&#x1F6A9;
										<?php else : ?>
											&#x274C;
										<?php endif; ?>
										<?php esc_html_e( 'Rejected', 'amp' ); ?>
									</option>
								</select>
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
										/* translators: 1: the URL to the invalid URL page, 2: number of invalid URLs. */
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

		$url = self::get_url_from_post( $post );
		if ( ! $url ) {
			return;
		}

		?>
		<h2 class="amp-invalid-url">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $url ); ?></a>
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
		if ( function_exists( 'get_current_screen' ) && get_current_screen() && get_current_screen()->base === 'edit' && get_current_screen()->post_type === self::POST_TYPE_SLUG && self::POST_TYPE_SLUG === get_post_type( $post ) ) {
			$title = preg_replace( '#^(\w+:)?//[^/]+#', '', $title );
		}
		return $title;
	}

	/**
	 * Renders the filters on the invalid URL post type edit.php page.
	 *
	 * @param string $post_type The slug of the post type.
	 * @param string $which     The location for the markup, either 'top' or 'bottom'.
	 */
	public static function render_post_filters( $post_type, $which ) {
		if ( self::POST_TYPE_SLUG === $post_type && 'top' === $which ) {
			AMP_Validation_Error_Taxonomy::render_error_status_filter();
			AMP_Validation_Error_Taxonomy::render_error_type_filter();
		}
	}

	/**
	 * Gets the URL to recheck the post for AMP validity.
	 *
	 * Appends a query var to $redirect_url.
	 * On clicking the link, it checks if errors still exist for $post.
	 *
	 * @param  string|WP_Post $url_or_post   The post storing the validation error or the URL to check.
	 * @return string The URL to recheck the post.
	 */
	public static function get_recheck_url( $url_or_post ) {
		$args = array(
			'action' => self::VALIDATE_ACTION,
		);
		if ( is_string( $url_or_post ) ) {
			$args['url'] = $url_or_post;
		} elseif ( $url_or_post instanceof WP_Post && self::POST_TYPE_SLUG === $url_or_post->post_type ) {
			$args['post'] = $url_or_post->ID;
		}

		return wp_nonce_url(
			add_query_arg( $args, admin_url() ),
			self::NONCE_ACTION
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

	/**
	 * Filters post row actions.
	 *
	 * @param array    $actions Row action links.
	 * @param \WP_Post $post Current WP post.
	 * @return array Filtered action links.
	 */
	public static function filter_post_row_actions( $actions, $post ) {
		// Replace 'Trash' text with 'Forget'.
		if ( isset( $actions['trash'] ) ) {
			$actions['trash'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				get_delete_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Forget &#8220;%s&#8221;', 'amp' ), $post->post_title ) ),
				esc_html__( 'Forget', 'amp' )
			);
		}

		if ( isset( $actions['delete'] ) ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				get_delete_post_link( $post->ID, '', true ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Forget &#8220;%s&#8221; permanently', 'amp' ), $post->post_title ) ),
				esc_html__( 'Forget Permanently', 'amp' )
			);
		}

		return $actions;
	}

	/**
	 * Filters table views for the post type.
	 *
	 * @param array $views Array of table view links keyed by status slug.
	 * @return array Filtered views.
	 */
	public static function filter_table_views( $views ) {
		// Replace 'Trash' text with 'Forgotten'.
		if ( isset( $views['trash'] ) ) {
			$status = get_post_status_object( 'trash' );

			$views['trash'] = str_replace( $status->label, esc_html__( 'Forgotten', 'amp' ), $views['trash'] );
		}

		return $views;
	}


	/**
	 * Filters messages displayed after bulk updates.
	 *
	 * @param array $messages    Bulk message text.
	 * @param array $bulk_counts Post numbers for the current message.
	 * @return array Filtered messages.
	 */
	public static function filter_bulk_post_updated_messages( $messages, $bulk_counts ) {
		if ( get_current_screen()->id === sprintf( 'edit-%s', self::POST_TYPE_SLUG ) ) {
			$messages['post'] = array_merge(
				$messages['post'],
				array(
					/* translators: %s is the number of posts permanently forgotten */
					'deleted'   => _n(
						'%s invalid AMP page permanently forgotten.',
						'%s invalid AMP post permanently forgotten.',
						$bulk_counts['deleted'],
						'amp'
					),
					/* translators: %s is the number of posts forgotten */
					'trashed'   => _n(
						'%s invalid AMP page forgotten.',
						'%s invalid AMP pages forgotten.',
						$bulk_counts['trashed'],
						'amp'
					),
					/* translators: %s is the number of posts restored from trash. */
					'untrashed' => _n(
						'%s invalid AMP page unforgotten.',
						'%s invalid AMP pages unforgotten.',
						$bulk_counts['untrashed'],
						'amp'
					),
				)
			);
		}

		return $messages;
	}

}
