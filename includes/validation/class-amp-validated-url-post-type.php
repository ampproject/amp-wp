<?php
/**
 * Class AMP_Validated_URL_Post_Type
 *
 * @package AMP
 */

/**
 * Class AMP_Validated_URL_Post_Type
 *
 * @since 1.0
 */
class AMP_Validated_URL_Post_Type {

	/**
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_validated_url';

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
	 * The handle for the post edit screen script.
	 *
	 * @var string
	 */
	const EDIT_POST_SCRIPT_HANDLE = 'amp-validated-url-post-edit-screen';

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
	 * The total number of errors associated with a URL, regardless of the maximum that can display.
	 *
	 * @var int
	 */
	public static $total_errors_for_url;

	/**
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'amp_plugin_update', array( __CLASS__, 'handle_plugin_update' ) );

		$post_type = register_post_type(
			self::POST_TYPE_SLUG,
			array(
				'labels'       => array(
					'name'               => _x( 'AMP Validated URLs', 'post type general name', 'amp' ),
					'menu_name'          => __( 'Validated URLs', 'amp' ),
					'singular_name'      => __( 'Validated URL', 'amp' ),
					'not_found'          => __( 'No validated URLs found', 'amp' ),
					'not_found_in_trash' => __( 'No forgotten validated URLs', 'amp' ),
					'search_items'       => __( 'Search validated URLs', 'amp' ),
					'edit_item'          => '', // Overwritten in JS, so this prevents the page header from appearing and changing.
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
	 * Handle update to plugin.
	 *
	 * @param string $old_version Old version.
	 */
	public static function handle_plugin_update( $old_version ) {

		// Update the old post type slug from amp_validated_url to amp_validated_url.
		if ( '1.0-' === substr( $old_version, 0, 4 ) || version_compare( $old_version, '1.0', '<' ) ) {
			global $wpdb;
			$post_ids = get_posts(
				array(
					'post_type'      => 'amp_invalid_url',
					'fields'         => 'ids',
					'posts_per_page' => -1,
				)
			);
			foreach ( $post_ids as $post_id ) {
				$wpdb->update(
					$wpdb->posts,
					array( 'post_type' => self::POST_TYPE_SLUG ),
					array( 'ID' => $post_id )
				);
				clean_post_cache( $post_id );
			}
		}
	}

	/**
	 * Determine whether the admin menu item should be included.
	 *
	 * @return bool Whether to show in menu.
	 */
	public static function should_show_in_menu() {
		global $pagenow;
		if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			return true;
		}
		return ( 'edit.php' === $pagenow && ( isset( $_GET['post_type'] ) && self::POST_TYPE_SLUG === $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Add admin hooks.
	 */
	public static function add_admin_hooks() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_post_list_screen_scripts' ) );

		add_filter( 'dashboard_glance_items', array( __CLASS__, 'filter_dashboard_glance_items' ) );
		add_action( 'rightnow_end', array( __CLASS__, 'print_dashboard_glance_styles' ) );

		// Edit post screen hooks.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_edit_post_screen_scripts' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'edit_form_after_title', array( __CLASS__, 'render_single_url_list_table' ) );
		add_filter( 'edit_' . AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG . '_per_page', array( __CLASS__, 'get_terms_per_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'add_taxonomy' ) );
		add_action( 'edit_form_top', array( __CLASS__, 'print_url_as_title' ) );

		// Post list screen hooks.
		add_filter(
			'view_mode_post_types',
			function( $post_types ) {
				return array_diff( $post_types, array( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG ) );
			}
		);
		add_action(
			'load-edit.php',
			function() {
				if ( 'edit-' . AMP_Validated_URL_Post_Type::POST_TYPE_SLUG !== get_current_screen()->id ) {
					return;
				}
				add_action(
					'admin_head-edit.php',
					function() {
						global $mode;
						$mode = 'list'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					}
				);
			}
		);
		add_action( 'admin_notices', array( __CLASS__, 'render_link_to_error_index_screen' ) );
		add_filter( 'the_title', array( __CLASS__, 'filter_the_title_in_post_list_table' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'render_post_filters' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_posts_columns', array( __CLASS__, 'add_post_columns' ) );
		add_filter( 'manage_' . self::POST_TYPE_SLUG . '_columns', array( __CLASS__, 'add_single_post_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'output_custom_column' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'filter_bulk_actions' ), 10, 2 );
		add_filter( 'bulk_actions-' . self::POST_TYPE_SLUG, '__return_false' );
		add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array( __CLASS__, 'handle_bulk_action' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'print_admin_notice' ) );
		add_action( 'admin_action_' . self::VALIDATE_ACTION, array( __CLASS__, 'handle_validate_request' ) );
		add_action( 'post_action_' . self::UPDATE_POST_TERM_STATUS_ACTION, array( __CLASS__, 'handle_validation_error_status_update' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu_new_invalid_url_count' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 10, 2 );
		add_filter( sprintf( 'views_edit-%s', self::POST_TYPE_SLUG ), array( __CLASS__, 'filter_table_views' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'filter_bulk_post_updated_messages' ), 10, 2 );

		// Hide irrelevant "published" label in the AMP Validated URLs post list.
		add_filter(
			'post_date_column_status',
			function ( $status, $post ) {
				if ( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG === get_post_type( $post ) ) {
					$status = '';
				}

				return $status;
			},
			10,
			2
		);

		// Prevent query vars from persisting after redirect.
		add_filter(
			'removable_query_args',
			function ( $query_vars ) {
				$query_vars[] = 'amp_actioned';
				$query_vars[] = 'amp_taxonomy_terms_updated';
				$query_vars[] = AMP_Validated_URL_Post_Type::REMAINING_ERRORS;
				$query_vars[] = 'amp_urls_tested';
				$query_vars[] = 'amp_validate_error';

				return $query_vars;
			}
		);
	}
	/**
	 * Enqueue style.
	 */
	public static function enqueue_post_list_screen_scripts() {
		$screen = get_current_screen();

		if ( 'edit-' . self::POST_TYPE_SLUG === $screen->id && self::POST_TYPE_SLUG === $screen->post_type ) {
			wp_enqueue_script(
				'amp-validated-urls-index',
				amp_get_asset_url( 'js/amp-validated-urls-index.js' ),
				array(),
				AMP__VERSION,
				true
			);
			wp_add_inline_script(
				'amp-validated-urls-index',
				sprintf( 'document.addEventListener( "DOMContentLoaded", function() { ampValidatedUrlsIndex.boot(); } );' ),
				'after'
			);
		}

		// Enqueue this on both the 'AMP Validated URLs' page and the single URL page.
		if ( 'edit-' . self::POST_TYPE_SLUG === $screen->id || self::POST_TYPE_SLUG === $screen->id ) {
			wp_enqueue_style(
				'amp-admin-tables',
				amp_get_asset_url( 'css/admin-tables.css' ),
				false,
				AMP__VERSION
			);
		}

		if ( 'edit-' . self::POST_TYPE_SLUG !== $screen->id ) {
			return;
		}

		wp_enqueue_style(
			'amp-validation-error-taxonomy',
			amp_get_asset_url( 'css/amp-validation-error-taxonomy.css' ),
			array( 'common', 'amp-validation-tooltips' ),
			AMP__VERSION
		);

		wp_enqueue_script(
			'amp-validation-detail-toggle',
			amp_get_asset_url( 'js/amp-validation-detail-toggle-compiled.js' ),
			array( 'wp-dom-ready', 'amp-validation-tooltips' ),
			AMP__VERSION,
			true
		);
		wp_localize_script(
			'amp-validation-detail-toggle',
			'ampValidationI18n',
			array( 'btnAriaLabel' => esc_attr__( 'Toggle all', 'amp' ) )
		);
	}

	/**
	 * On the 'AMP Validated URLs' screen, renders a link to the 'Error Index' page.
	 *
	 * @see AMP_Validation_Error_Taxonomy::render_link_to_invalid_urls_screen()
	 */
	public static function render_link_to_error_index_screen() {
		if ( ! ( get_current_screen() && 'edit' === get_current_screen()->base && self::POST_TYPE_SLUG === get_current_screen()->post_type ) ) {
			return;
		}

		$taxonomy_object = get_taxonomy( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		if ( ! current_user_can( $taxonomy_object->cap->manage_terms ) ) {
			return;
		}

		$id = 'link-errors-index';

		printf(
			'<a href="%s" hidden class="page-title-action" id="%s" style="margin-left: 1rem;">%s</a>',
			esc_url( get_admin_url( null, 'edit-tags.php?taxonomy=' . AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG . '&post_type=' . self::POST_TYPE_SLUG ) ),
			esc_attr( $id ),
			esc_html__( 'View Error Index', 'amp' )
		);

		?>
		<script>
			jQuery( function( $ ) {
				// Move the link to after the heading, as it also looks like there's no action for this.
				$( <?php echo wp_json_encode( '#' . $id ); ?> ).removeAttr( 'hidden' ).insertAfter( $( '.wp-heading-inline' ) );
			} );
		</script>
		<?php
	}

	/**
	 * Add count of how many validation error posts there are to the admin menu.
	 */
	public static function add_admin_menu_new_invalid_url_count() {
		global $submenu;
		if ( ! isset( $submenu[ AMP_Options_Manager::OPTION_NAME ] ) ) {
			return;
		}

		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE_SLUG,
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => array(
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
				),
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

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
	 * Gets validation errors for a given validated URL post.
	 *
	 * @param string|int|WP_Post $url Either the URL string or a post (ID or WP_Post) of amp_validated_url type.
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

		// Look up post by URL or ensure the amp_validated_url object.
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

			$term = AMP_Validation_Error_Taxonomy::get_term( $stored_validation_error['term_slug'] );
			if ( ! $term ) {
				continue;
			}

			$sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $stored_validation_error['data'] );
			if ( $args['ignore_accepted'] && ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS === $sanitization['status'] || AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $sanitization['status'] ) ) {
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
	 * @param int|WP_Post $post Post of amp_validated_url type.
	 * @param array       $args {
	 *     Arguments.
	 *
	 *     @type bool $display_enabled_status Whether to display the status of whether AMP is enabled on the URL.
	 * }
	 */
	public static function display_invalid_url_validation_error_counts_summary( $post, $args = array() ) {
		$args              = array_merge(
			array(
				'display_enabled_status' => false,
			),
			$args
		);
		$validation_errors = self::get_invalid_url_validation_errors( $post );
		$counts            = self::count_invalid_url_validation_errors( $validation_errors );

		$result = array();
		if ( $counts['new_rejected'] ) {
			$result[] = sprintf(
				/* translators: 1: status. 2: count. */
				'<span class="status-text new new-rejected">%1$s: %2$s</span>',
				esc_html__( 'New Rejected', 'amp' ),
				number_format_i18n( $counts['new_rejected'] )
			);
		}
		if ( $counts['new_accepted'] ) {
			$result[] = sprintf(
				/* translators: 1: status. 2: count. */
				'<span class="status-text new new-accepted">%1$s: %2$s</span>',
				esc_html__( 'New Accepted', 'amp' ),
				number_format_i18n( $counts['new_accepted'] )
			);
		}
		if ( $counts['ack_accepted'] ) {
			$result[] = sprintf(
				/* translators: 1. Title, 2. %s is count */
				'<span class="status-text accepted">%1$s: %2$s</span>',
				esc_html__( 'Accepted', 'amp' ),
				number_format_i18n( $counts['ack_accepted'] )
			);
		}
		if ( $counts['ack_rejected'] ) {
			$result[] = sprintf(
				/* translators: %s is count */
				'<span class="status-text rejected">%1$s: %2$s</span>',
				esc_html__( 'Rejected', 'amp' ),
				number_format_i18n( $counts['ack_rejected'] )
			);
		}

		if ( $args['display_enabled_status'] ) {
			$is_amp_enabled = self::is_amp_enabled_on_post( $post );
			$class          = $is_amp_enabled ? 'sanitized' : 'new';
			?>
			<span id="amp-enabled-icon" class="status-text <?php echo esc_attr( $class ); ?>">
				<?php
				if ( $is_amp_enabled ) {
					esc_html_e( 'AMP: Enabled', 'amp' );
				} else {
					esc_html_e( 'AMP: Disabled', 'amp' );
				}
				?>
			</span>
			<?php
		}
		echo implode( '', $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Gets the existing custom post that stores errors for the $url, if it exists.
	 *
	 * @param string $url     The (in)valid URL.
	 * @param array  $options {
	 *     Options.
	 *
	 *     @type bool $normalize       Whether to normalize the URL.
	 *     @type bool $include_trashed Include trashed.
	 * }
	 * @return WP_Post|null The post of the existing custom post, or null.
	 */
	public static function get_invalid_url_post( $url, $options = array() ) {
		$default = array(
			'normalize'       => true,
			'include_trashed' => false,
		);
		$options = wp_parse_args( $options, $default );

		if ( $options['normalize'] ) {
			$url = self::normalize_url_for_storage( $url );
		}
		$slug = md5( $url );

		$post = get_page_by_path( $slug, OBJECT, self::POST_TYPE_SLUG );
		if ( $post ) {
			return $post;
		}

		if ( $options['include_trashed'] ) {
			$post = get_page_by_path( $slug . '__trashed', OBJECT, self::POST_TYPE_SLUG );
			if ( $post ) {
				return $post;
			}
		}

		return null;
	}

	/**
	 * Get the URL from a given amp_validated_url post.
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

		// Add AMP query var if in transitional mode.
		if ( ! amp_is_canonical() ) {
			$url = add_query_arg( amp_get_slug(), '', $url );
		}

		// Set URL scheme based on whether HTTPS is current.
		$url = set_url_scheme( $url, ( 'http' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ) ? 'http' : 'https' );

		return $url;
	}

	/**
	 * Normalize a URL for storage.
	 *
	 * This ensures that query vars like utm_* and the like will not cause duplicates.
	 * The AMP query param is removed to facilitate switching between native and transitional.
	 * The URL scheme is also normalized to HTTPS to help with transition from HTTP to HTTPS.
	 *
	 * @param string $url URL.
	 * @return string Normalized URL.
	 * @global WP $wp
	 */
	protected static function normalize_url_for_storage( $url ) {
		global $wp;

		// Only ever store the canonical version.
		$url = amp_remove_endpoint( $url );

		// Remove fragment identifier in the rare case it could be provided. It is irrelevant for validation.
		$url = strtok( $url, '#' );

		// Normalize query args, removing all that are not recognized or which are removable.
		$url_parts = explode( '?', $url, 2 );
		if ( 2 === count( $url_parts ) ) {
			parse_str( $url_parts[1], $args );
			foreach ( wp_removable_query_args() as $removable_query_arg ) {
				unset( $args[ $removable_query_arg ] );
			}
			$args = wp_array_slice_assoc( $args, $wp->public_query_vars );
			$url  = $url_parts[0];
			if ( ! empty( $args ) ) {
				$url = $url_parts[0] . '?' . build_query( $args );
			}
		}

		// Normalize the scheme as HTTPS.
		$url = set_url_scheme( $url, 'https' );

		return $url;
	}

	/**
	 * Stores the validation errors.
	 *
	 * If there are no validation errors provided, then any existing amp_validated_url post is deleted.
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
		$url  = self::normalize_url_for_storage( $url );
		$slug = md5( $url );
		$post = null;
		if ( ! empty( $args['invalid_url_post'] ) ) {
			$post = get_post( $args['invalid_url_post'] );
		}
		if ( ! $post ) {
			$post = self::get_invalid_url_post(
				$url,
				array(
					'include_trashed' => true,
					'normalize'       => false, // Since already normalized.
				)
			);
		}

		/*
		 * The details for individual validation errors is stored in the amp_validation_error taxonomy terms.
		 * The post content just contains the slugs for these terms and the sources for the given instance of
		 * the validation error.
		 */
		$stored_validation_errors = array();

		// Prevent Kses from corrupting JSON in description.
		$pre_term_description_filters = array(
			'wp_filter_kses'       => has_filter( 'pre_term_description', 'wp_filter_kses' ),
			'wp_targeted_link_rel' => has_filter( 'pre_term_description', 'wp_targeted_link_rel' ),
		);
		foreach ( $pre_term_description_filters as $callback => $priority ) {
			if ( false !== $priority ) {
				remove_filter( 'pre_term_description', $callback, $priority );
			}
		}

		$terms = array();
		foreach ( $validation_errors as $data ) {
			$term_data = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $data );
			$term_slug = $term_data['slug'];

			if ( ! isset( $terms[ $term_slug ] ) ) {

				// Not using WP_Term_Query since more likely individual terms are cached and wp_insert_term() will itself look at this cache anyway.
				$term = AMP_Validation_Error_Taxonomy::get_term( $term_slug );
				if ( ! ( $term instanceof WP_Term ) ) {
					/*
					 * The default term_group is 0 so that is AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS.
					 * If sanitization auto-acceptance is enabled, then the term_group will be updated below.
					 */
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
						$term_data['term_group'] = $sanitization['status'];
						wp_update_term(
							$term_id,
							AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
							array(
								'term_group' => $sanitization['status'],
							)
						);
					} elseif ( AMP_Validation_Manager::is_sanitization_auto_accepted() ) {
						$term_data['term_group'] = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS;
						wp_update_term(
							$term_id,
							AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
							array(
								'term_group' => $term_data['term_group'],
							)
						);
					}

					$term = get_term( $term_id );
				}
				$terms[ $term_slug ] = $term;
			}

			$stored_validation_errors[] = compact( 'term_slug', 'data' );
		}

		// Finish preventing Kses from corrupting JSON in description.
		foreach ( $pre_term_description_filters as $callback => $priority ) {
			if ( false !== $priority ) {
				add_filter( 'pre_term_description', $callback, $priority );
			}
		}

		$post_content = wp_json_encode( $stored_validation_errors );
		$placeholder  = 'amp_validated_url_content_placeholder' . wp_rand();

		// Guard against Kses from corrupting content by adding post_content after content_save_pre filter applies.
		$insert_post_content = function( $post_data ) use ( $placeholder, $post_content ) {
			$should_supply_post_content = (
				isset( $post_data['post_content'], $post_data['post_type'] )
				&&
				$placeholder === $post_data['post_content']
				&&
				AMP_Validated_URL_Post_Type::POST_TYPE_SLUG === $post_data['post_type']
			);
			if ( $should_supply_post_content ) {
				$post_data['post_content'] = wp_slash( $post_content );
			}
			return $post_data;
		};
		add_filter( 'wp_insert_post_data', $insert_post_content );

		// Create a new invalid AMP URL post, or update the existing one.
		$r = wp_insert_post(
			wp_slash(
				array(
					'ID'           => $post ? $post->ID : null,
					'post_type'    => self::POST_TYPE_SLUG,
					'post_title'   => $url,
					'post_name'    => $slug,
					'post_content' => $placeholder, // Content is provided via wp_insert_post_data filter above to guard against Kses-corruption.
					'post_status'  => 'publish',
				)
			),
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
				'accept_tree_shaking' => ( AMP_Options_Manager::get_option( 'accept_tree_shaking' ) || AMP_Options_Manager::get_option( 'auto_accept_sanitization' ) ),
			),
		);
	}

	/**
	 * Get the differences between the current themes, plugins, and relevant options when amp_validated_url post was last updated and now.
	 *
	 * @param int|WP_Post $post Post of amp_validated_url type.
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
				AMP_Validation_Error_Taxonomy::ERROR_STATUS => sprintf(
					'%s<span class="dashicons dashicons-editor-help tooltip-button" tabindex="0"></span><div class="tooltip" hidden data-content="%s"></div>',
					esc_html__( 'Status', 'amp' ),
					esc_attr(
						sprintf(
							'<h3>%s</h3><p>%s</p>',
							__( 'Status', 'amp' ),
							__( 'An accepted validation error is one that will not block a URL from being served as AMP; the validation error will be sanitized, normally resulting in the offending markup being stripped from the response to ensure AMP validity.', 'amp' )
						)
					)
				),
				AMP_Validation_Error_Taxonomy::FOUND_ELEMENTS_AND_ATTRIBUTES => esc_html__( 'Invalid', 'amp' ),
				AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT => esc_html__( 'Sources', 'amp' ),
			)
		);

		if ( isset( $columns['title'] ) ) {
			$columns['title'] = esc_html__( 'URL', 'amp' );
		}

		// Move date to end.
		if ( isset( $columns['date'] ) ) {
			unset( $columns['date'] );
			$columns['date'] = esc_html__( 'Last Checked', 'amp' );
		}

		if ( ! empty( $_GET[ \AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset( $columns['error_status'], $columns[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ], $columns[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] );
			$columns[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ] = esc_html__( 'Sources', 'amp' );
			$columns['date']  = esc_html__( 'Last Checked', 'amp' );
			$columns['title'] = esc_html__( 'URL', 'amp' );
		}

		return $columns;
	}

	/**
	 * Adds post columns to the /wp-admin/post.php page for amp_validated_url.
	 *
	 * @return array The filtered post columns.
	 */
	public static function add_single_post_columns() {
		return array(
			'cb'                          => '<input type="checkbox" />',
			'error'                       => __( 'Error', 'amp' ),
			'status'                      => sprintf(
				'%s<span class="dashicons dashicons-editor-help tooltip-button" tabindex="0"></span><div class="tooltip" hidden data-content="%s"></div>',
				esc_html__( 'Status', 'amp' ),
				esc_attr(
					sprintf(
						'<h3>%s</h3><p>%s</p>',
						esc_html__( 'Status', 'amp' ),
						esc_html__( 'An accepted validation error is one that will not block a URL from being served as AMP; the validation error will be sanitized, normally resulting in the offending markup being stripped from the response to ensure AMP validity.', 'amp' )
					)
				)
			),
			'details'                     => sprintf(
				'%s<span class="dashicons dashicons-editor-help tooltip-button" tabindex="0"></span><div class="tooltip" hidden data-content="%s"></div>',
				esc_html__( 'Details', 'amp' ),
				esc_attr(
					sprintf(
						'<h3>%s</h3><p>%s</p>',
						esc_html__( 'Details', 'amp' ),
						esc_html__( 'The parent element of where the error occurred.', 'amp' )
					)
				)
			),
			'sources_with_invalid_output' => __( 'Sources', 'amp' ),
			'error_type'                  => __( 'Type', 'amp' ),
		);
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
			case AMP_Validation_Error_Taxonomy::FOUND_ELEMENTS_AND_ATTRIBUTES:
				$items = array();
				if ( ! empty( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] ) ) {
					foreach ( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] as $name => $count ) {
						if ( 1 === intval( $count ) ) {
							$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
						} else {
							$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
						}
					}
				}
				if ( ! empty( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] ) ) {
					foreach ( $error_summary[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] as $name => $count ) {
						if ( 1 === intval( $count ) ) {
							$items[] = sprintf( '<code>[%s]</code>', esc_html( $name ) );
						} else {
							$items[] = sprintf( '<code>[%s]</code> (%d)', esc_html( $name ), $count );
						}
					}
				}
				if ( ! empty( $items ) ) {
					$imploded_items = implode( ',</div><div>', $items );
					echo sprintf( '<div>%s</div>', $imploded_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					esc_html_e( '--', 'amp' );
				}
				break;
			case AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT:
				self::render_sources_column( $error_summary, $post_id );
				break;
		}
	}

	/**
	 * Renders the sources column on the the single error URL page and the 'AMP Validated URLs' page.
	 *
	 * @param array $error_summary The summary of errors.
	 * @param int   $post_id       The ID of the amp_validated_url post.
	 */
	public static function render_sources_column( $error_summary, $post_id ) {
		if ( ! isset( $error_summary[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ] ) ) {
			return;
		}

		// Show nothing if there are no valudation errors.
		if ( 0 === count( array_filter( $error_summary ) ) ) {
			esc_html_e( '--', 'amp' );
			return;
		}

		$active_theme          = null;
		$validated_environment = get_post_meta( $post_id, '_amp_validated_environment', true );
		if ( isset( $validated_environment['theme'] ) ) {
			$active_theme = $validated_environment['theme'];
		}

		$sources = $error_summary[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ];
		$output  = array();
		$plugins = get_plugins();
		foreach ( wp_array_slice_assoc( $sources, array( 'plugin', 'mu-plugin' ) ) as $type => $slugs ) {
			$plugin_names = array();
			$plugin_slugs = array_unique( $slugs );
			foreach ( $plugin_slugs as $plugin_slug ) {
				if ( 'mu-plugin' === $type ) {
					$plugin_names[] = $plugin_slug;
				} else {
					$name = $plugin_slug;
					foreach ( $plugins as $plugin_file => $plugin_data ) {
						if ( strtok( $plugin_file, '/' ) === $plugin_slug ) {
							$name = $plugin_data['Name'];
							break;
						}
					}
					$plugin_names[] = $name;
				}
			}
			$count = count( $plugin_names );
			if ( 1 === $count ) {
				$output[] = sprintf( '<strong class="source"><span class="dashicons dashicons-admin-plugins"></span>%s</strong>', esc_html( $plugin_names[0] ) );
			} else {
				$output[] = '<details class="source">';
				$output[] = sprintf(
					'<summary class="details-attributes__summary"><strong><span class="dashicons dashicons-admin-plugins"></span>%s (%d)</strong></summary>',
					'mu-plugin' === $type ? esc_html__( 'Must-Use Plugins', 'amp' ) : esc_html__( 'Plugins', 'amp' ),
					$count
				);
				$output[] = '<div>';
				$output[] = implode( '<br/>', array_unique( $plugin_names ) );
				$output[] = '</div>';
				$output[] = '</details>';
			}
		}
		if ( isset( $sources['theme'] ) && empty( $sources['embed'] ) ) {
			$output[] = '<div class="source">';
			$output[] = '<span class="dashicons dashicons-admin-appearance"></span>';
			$themes   = array_unique( $sources['theme'] );
			foreach ( $themes as $theme_slug ) {
				$theme_obj = wp_get_theme( $theme_slug );
				if ( ! $theme_obj->errors() ) {
					$theme_name = $theme_obj->get( 'Name' );
				} else {
					$theme_name = $theme_slug;
				}
				$output[] = sprintf( '<strong>%s</strong>', esc_html( $theme_name ) );
			}
			$output[] = '</div>';
		}
		if ( isset( $sources['core'] ) ) {
			$core_sources = array_unique( $sources['core'] );
			$count        = count( $core_sources );
			if ( 1 === $count ) {
				$output[] = sprintf( '<strong class="source"><span class="dashicons dashicons-wordpress-alt"></span>%s</strong>', esc_html( $core_sources[0] ) );
			} else {
				$output[] = '<details class="source">';
				$output[] = sprintf( '<summary class="details-attributes__summary"><strong><span class="dashicons dashicons-wordpress-alt"></span>%s (%d)</strong></summary>', esc_html__( 'Other', 'amp' ), $count );
				$output[] = '<div>';
				$output[] = implode( '<br/>', array_unique( $sources['core'] ) );
				$output[] = '</div>';
				$output[] = '</details>';
			}
		}

		if ( empty( $output ) && ! empty( $sources['embed'] ) ) {
			$output[] = sprintf( '<strong class="source"><span class="dashicons dashicons-wordpress-alt"></span>%s</strong>', esc_html( 'Embed' ) );
		}

		if ( empty( $output ) && ! empty( $sources['hook'] ) ) {
			$output[] = sprintf( '<strong class="source"><span class="dashicons dashicons-wordpress-alt"></span>%s</strong>', esc_html( $sources['hook'] ) );
		}

		if ( empty( $sources ) && $active_theme ) {
			$theme_obj = wp_get_theme( $active_theme );
			if ( ! $theme_obj->errors() ) {
				$theme_name = $theme_obj->get( 'Name' );
			} else {
				$theme_name = $active_theme;
			}
			$output[] = '<div class="source">';
			$output[] = '<span class="dashicons dashicons-admin-appearance"></span>';
			/* translators: %s is the guessed theme as the source for the error */
			$output[] = esc_html( sprintf( __( '%s (?)', 'amp' ), $theme_name ) );
			$output[] = '</div>';
		}

		echo implode( '', $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Adds a 'Recheck' bulk action to the edit.php page and modifies the 'Move to Trash' text.
	 *
	 * Ensure only delete action is present, not trash.
	 *
	 * @param array $actions The bulk actions in the edit.php page.
	 * @return array $actions The filtered bulk actions.
	 */
	public static function filter_bulk_actions( $actions ) {
		$has_delete = ( isset( $actions['trash'] ) || isset( $actions['delete'] ) );
		unset( $actions['trash'], $actions['delete'] );
		if ( $has_delete ) {
			$actions['delete'] = esc_html__( 'Forget', 'amp' );
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
			if ( empty( $post ) || ! current_user_can( 'edit_post', $post->ID ) ) {
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
			$unaccepted_error_count = count(
				array_filter(
					$validation_errors,
					function( $error ) {
						return ! AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error );
					}
				)
			);
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
		if ( ! get_current_screen() || self::POST_TYPE_SLUG !== get_current_screen()->post_type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( isset( $_GET['amp_validate_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$error_codes = array_unique( array_map( 'sanitize_key', (array) $_GET['amp_validate_error'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			foreach ( $error_codes as $error_code ) {
				printf(
					'<div class="notice is-dismissible error"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
					esc_html( AMP_Validation_Manager::get_validate_url_error_message( $error_code ) ),
					esc_html__( 'Dismiss this notice.', 'amp' )
				);
			}
		}

		if ( isset( $_GET[ self::REMAINING_ERRORS ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count_urls_tested = isset( $_GET[ self::URLS_TESTED ] ) ? intval( $_GET[ self::URLS_TESTED ] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$errors_remain     = ! empty( $_GET[ self::REMAINING_ERRORS ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

		$count = isset( $_GET['amp_taxonomy_terms_updated'] ) ? intval( $_GET['amp_taxonomy_terms_updated'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $count > 0 ) {
			$class = 'updated';
			printf(
				'<div class="notice is-dismissible %s"><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
				esc_attr( $class ),
				esc_html(
					sprintf(
						/* translators: %s is count of validation errors updated */
						_n(
							'Updated %s validation error.',
							'Updated %s validation errors.',
							$count,
							'amp'
						),
						number_format_i18n( $count )
					)
				),
				esc_html__( 'Dismiss this notice.', 'amp' )
			);
		}

		if ( 'post' !== get_current_screen()->base ) {
			// Display admin notice according to the AMP mode.
			if ( amp_is_canonical() ) {
				$template_mode = 'native';
			} elseif ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
				$template_mode = 'paired';
			} else {
				$template_mode = 'reader';
			}
			$auto_sanitization = AMP_Options_Manager::get_option( 'auto_accept_sanitization' );

			if ( 'native' === $template_mode ) {
				$message = __( 'The site is using native AMP mode, the validation errors found are already automatically handled.', 'amp' );
			} elseif ( 'paired' === $template_mode && $auto_sanitization ) {
				$message = __( 'The site is using transitional AMP mode with auto-sanitization turned on, the validation errors found are already automatically handled.', 'amp' );
			} elseif ( 'paired' === $template_mode ) {
				$message = sprintf(
					/* translators: %s is a link to the AMP settings screen */
					__( 'The site is using transitional AMP mode without auto-sanitization, the validation errors found require action and influence which pages are shown in AMP. For automatically handling the errors turn on auto-sanitization from <a href="%s">Validation Handling settings</a>.', 'amp' ),
					esc_url( admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME ) )
				);
			} else {
				$message = __( 'The site is using AMP reader mode, your theme templates are not used and the errors below are irrelevant.', 'amp' );
			}

			$class = 'info';
			printf(
				/* translators: 1. Notice classname; 2. Message text; 3. Screenreader text; */
				'<div class="notice notice-%s"><p>%s</p></div>',
				esc_attr( $class ),
				wp_kses_post( $message )
			);
		}

		/**
		 * Adds notices to the single error page.
		 * 1. Notice with detailed error information in an expanding box.
		 * 2. Notice with accept and reject buttons.
		 */
		if ( ! empty( $_GET[ \AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ] ) && isset( $_GET['post_type'] ) && self::POST_TYPE_SLUG === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$error_id = sanitize_key( wp_unslash( $_GET[ \AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$error = AMP_Validation_Error_Taxonomy::get_term( $error_id );
			if ( ! $error ) {
				return;
			}

			// @todo Update this to use the method which will be developed in PR #1429 AMP_Validation_Error_Taxonomy::get_term_error() .
			$description      = json_decode( $error->description, true );
			$sanitization     = \AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $description );
			$status_text      = \AMP_Validation_Error_Taxonomy::get_status_text_with_icon( $sanitization );
			$error_code       = isset( $description['code'] ) ? $description['code'] : 'error';
			$error_title      = \AMP_Validation_Error_Taxonomy::get_error_title_from_code( $error_code );
			$validation_error = json_decode( $error->description, true );
			$accept_all_url   = wp_nonce_url(
				add_query_arg(
					array(
						'action'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION,
						'term_id' => $error->term_id,
					)
				),
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION
			);
			$reject_all_url   = wp_nonce_url(
				add_query_arg(
					array(
						'action'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECT_ACTION,
						'term_id' => $error->term_id,
					)
				),
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECT_ACTION
			);

			if ( ! $sanitization['forced'] ) {
				echo '<div class="notice accept-reject-error">';

				if ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS === $sanitization['term_status'] || AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $sanitization['term_status'] ) {
					if ( amp_is_canonical() ) {
						$info = __( 'Rejecting an error means that any URL on which it occurs will not be served as AMP.', 'amp' );
					} else {
						$info = __( 'Rejecting an error means that any URL on which it occurs will redirect to the non-AMP version.', 'amp' );
					}
					printf(
						'<p>%s</p><a class="button button-primary reject" href="%s">%s</a>',
						esc_html__( 'Reject this validation error for all instances.', 'amp' ) . ' ' . esc_html( $info ),
						esc_url( $reject_all_url ),
						esc_html__( 'Reject', 'amp' )
					);
				} elseif ( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS === $sanitization['term_status'] || AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS === $sanitization['term_status'] ) {
					if ( amp_is_canonical() ) {
						$info = __( 'Accepting all validation errors which occur on a URL will allow it to be served as AMP.', 'amp' );
					} else {
						$info = __( 'Accepting all validation errors which occur on a URL will allow it to be served as AMP.', 'amp' );
					}
					printf(
						'<p>%s</p><a class="button button-primary accept" href="%s">%s</a>',
						esc_html__( 'Accept this error for all instances.', 'amp' ) . ' ' . esc_html( $info ),
						esc_url( $accept_all_url ),
						esc_html__( 'Accept', 'amp' )
					);
				} else {
					if ( amp_is_canonical() ) {
						$info = __( 'Rejecting an error means that any URL on which it occurs will not be served as AMP. If all errors occurring on a URL are accepted, then it will be served as AMP.', 'amp' );
					} else {
						$info = __( 'Rejecting an error means that any URL on which it occurs will redirect to the non-AMP version. If all errors occurring on a URL are accepted, then it will not redirect.', 'amp' );
					}
					printf(
						'<p>%s</p><a class="button reject" href="%s">%s</a><a class="button button-primary accept" href="%s">%s</a>',
						esc_html__( 'Accept or Reject this error for all instances.', 'amp' ) . ' ' . esc_html( $info ),
						esc_url( $reject_all_url ),
						esc_html__( 'Reject', 'amp' ),
						esc_url( $accept_all_url ),
						esc_html__( 'Accept', 'amp' )
					);
				}
				echo '</div>';
			}

			?>
			<div class="notice error-details">
				<ul>
					<?php echo AMP_Validation_Error_Taxonomy::render_single_url_error_details( $validation_error, $error ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</ul>
			</div>
			<?php

			$heading = sprintf(
				'%s: <code>%s</code>%s',
				esc_html( $error_title ),
				esc_html( $description['node_name'] ),
				wp_kses_post( $status_text )
			);
			?>
			<script type="text/javascript">
				jQuery( function( $ ) {
					$( 'h1.wp-heading-inline' ).html( <?php echo wp_json_encode( $heading ); ?> );
				});
			</script>
			<?php
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
				if ( ! current_user_can( 'edit_post', $post->ID ) ) {
					throw new Exception( 'unauthorized' );
				}
				$url = self::get_url_from_post( $post );
			} elseif ( isset( $_GET['url'] ) ) {
				$url = wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['url'] ) ), null );
				if ( ! $url ) {
					throw new Exception( 'illegal_url' );
				}
				// Don't let non-admins create new amp_validated_url posts.
				if ( ! current_user_can( 'manage_options' ) ) {
					throw new Exception( 'unauthorized' );
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

			$error_count = count(
				array_filter(
					$errors,
					function ( $error ) {
						return ! AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $error );
					}
				)
			);

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
	 * Re-check validated URL post for whether it has blocking validation errors.
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

		if ( empty( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) || ! is_array( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) ) {
			return;
		}
		$post = get_post();
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		if ( ! AMP_Validation_Manager::has_cap() || ! current_user_can( 'edit_post', $post->ID ) ) {
			wp_die( esc_html__( 'You do not have permissions to validate an AMP URL. Did you get logged out?', 'amp' ) );
		}

		$updated_count = 0;

		$has_pre_term_description_filter = has_filter( 'pre_term_description', 'wp_filter_kses' );
		if ( false !== $has_pre_term_description_filter ) {
			remove_filter( 'pre_term_description', 'wp_filter_kses', $has_pre_term_description_filter );
		}

		foreach ( $_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] as $term_slug => $status ) {
			if ( ! is_numeric( $status ) ) {
				continue;
			}
			$term_slug = sanitize_key( $term_slug );
			$term      = AMP_Validation_Error_Taxonomy::get_term( $term_slug );
			if ( ! $term ) {
				continue;
			}
			$term_group = AMP_Validation_Error_Taxonomy::sanitize_term_status( $status );
			if ( null !== $term_group && $term_group !== $term->term_group ) {
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
			// @todo For WP_Error case, see <https://github.com/ampproject/amp-wp/issues/1166>.
			if ( ! is_wp_error( $validation_results ) ) {
				$args[ self::REMAINING_ERRORS ] = count(
					array_filter(
						$validation_results,
						function( $result ) {
							return ! $result['sanitized'];
						}
					)
				);
			}
		}

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = get_edit_post_link( $post->ID, 'raw' );
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
		wp_enqueue_script( self::EDIT_POST_SCRIPT_HANDLE, amp_get_asset_url( 'js/' . self::EDIT_POST_SCRIPT_HANDLE . '.js' ), array(), AMP__VERSION, true );
	}

	/**
	 * Enqueues scripts for the edit post screen.
	 *
	 * This is called in render_single_url_list_table() instead of enqueue_edit_post_screen_scripts(),
	 * as it depends on data from the WP_Terms_List_Table in that method.
	 * So this has to run after the 'admin_enqueue_scripts' hook.
	 */
	public static function add_edit_post_inline_script() {
		$current_screen = get_current_screen();
		if ( 'post' !== $current_screen->base || self::POST_TYPE_SLUG !== $current_screen->post_type ) {
			return;
		}

		$post = get_post();
		$data = array(
			'l10n' => array(
				'unsaved_changes' => __( 'You have unsaved changes. Are you sure you want to leave?', 'amp' ),
				'page_heading'    => self::get_single_url_page_heading(),
				'show_all'        => __( 'Show all', 'amp' ),
				'amp_enabled'     => self::is_amp_enabled_on_post( $post ),
			),
		);

		// Only the second number is interpolated by PHP, as the JS file will dynamically replace %1$s with the errors being displayed.
		$data['l10n']['showing_number_errors'] = sprintf(
			/* translators: 1: number of errors being displayed. 2: total number of errors found. */
			_n(
				'Showing %1$s of %2$s validation error',
				'Showing %1$s of %2$s validation errors',
				self::$total_errors_for_url,
				'amp'
			),
			'%1$s',
			number_format_i18n( self::$total_errors_for_url )
		);

		wp_add_inline_script(
			self::EDIT_POST_SCRIPT_HANDLE,
			sprintf( 'document.addEventListener( "DOMContentLoaded", function() { ampValidatedUrlPostEditScreen.boot( %s ); } );', wp_json_encode( $data ) ),
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
		add_meta_box(
			self::STATUS_META_BOX,
			__( 'Status', 'amp' ),
			array( __CLASS__, 'print_status_meta_box' ),
			self::POST_TYPE_SLUG,
			'side',
			'default',
			array( '__back_compat_meta_box' => true )
		);
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
				<div class="curtime misc-pub-section">
					<span id="timestamp">
					<?php
					printf(
						/* translators: %s: The date this was published */
						wp_kses_post( __( 'Last checked: <b>%s</b>', 'amp' ) ),
						/* translators: Meta box date format */
						esc_html( date_i18n( __( 'M j, Y @ H:i', 'amp' ), strtotime( $post->post_date ) ) )
					);
					?>
					</span>
				</div>
				<div id="minor-publishing-actions">
					<div id="re-check-action">
						<a class="button button-secondary" href="<?php echo esc_url( self::get_recheck_url( $post ) ); ?>">
							<?php esc_html_e( 'Recheck', 'amp' ); ?>
						</a>
					</div>
					<div id="preview-action">
						<button type="button" name="action" class="preview button" id="preview_validation_errors"><?php esc_html_e( 'Preview Changes', 'amp' ); ?></button>
					</div>
					<div class="clear"></div>
				</div>
				<div id="misc-publishing-actions">

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
						<?php self::display_invalid_url_validation_error_counts_summary( $post, array( 'display_enabled_status' => true ) ); ?>
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
					<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID, '', true ) ); ?>">
						<?php esc_html_e( 'Forget', 'amp' ); ?>
					</a>
				</div>
				<div id="publishing-action">
					<button type="submit" name="action" class="button button-primary" value="<?php echo esc_attr( self::UPDATE_POST_TERM_STATUS_ACTION ); ?>"><?php esc_html_e( 'Update', 'amp' ); ?></button>
				</div>
				<div class="clear"></div>
			</div>
		</div><!-- /submitpost -->

		<script>
		jQuery( function( $ ) {
			var validateUrl, postId;
			validateUrl = <?php echo wp_json_encode( add_query_arg( AMP_Validation_Manager::VALIDATE_QUERY_VAR, AMP_Validation_Manager::get_amp_validate_nonce(), self::get_url_from_post( $post ) ) ); ?>;
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
	}

	/**
	 * Renders the single URL list table.
	 *
	 * Mainly copied from edit-tags.php.
	 * This is output on the post.php page for amp_validated_url,
	 * where the editor normally would be.
	 * But it's really more similar to /wp-admin/edit-tags.php than a post.php page,
	 * as this outputs a WP_Terms_List_Table of amp_validation_error terms.
	 *
	 * @todo: complete this, as it may need to use more logic from edit-tags.php.
	 * @param WP_Post $post The post for the meta box.
	 * @return void
	 */
	public static function render_single_url_list_table( $post ) {
		if ( self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		$taxonomy        = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_object ) {
			wp_die( esc_html__( 'Invalid taxonomy.', 'amp' ) );
		}

		/**
		 * Set the order of the terms in the order of occurrence.
		 *
		 * Note that this function will call \AMP_Validation_Error_Taxonomy::get_term() repeatedly, and the
		 * object cache will be pre-populated with terms due to the term query in the term list table.
		 *
		 * @return WP_Term[]
		 */
		$override_terms_in_occurrence_order = function() use ( $post ) {
			return wp_list_pluck( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $post ), 'term' );
		};

		add_filter( 'get_terms', $override_terms_in_occurrence_order );

		$wp_list_table = _get_list_table( 'WP_Terms_List_Table' );
		get_current_screen()->set_screen_reader_content(
			array(
				'heading_pagination' => $taxonomy_object->labels->items_list_navigation,
				'heading_list'       => $taxonomy_object->labels->items_list,
			)
		);

		$wp_list_table->prepare_items();
		$wp_list_table->views();

		// The inline script depends on data from the list table.
		self::$total_errors_for_url = $wp_list_table->get_pagination_arg( 'total_items' );
		self::add_edit_post_inline_script();

		?>
		<form class="search-form wp-clearfix" method="get">
			<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" />
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $post->post_type ); ?>" />
			<?php $wp_list_table->search_box( esc_html__( 'Search Errors', 'amp' ), 'invalid-url-search' ); ?>
		</form>

		<div id="accept-reject-buttons" class="hidden">
			<button type="button" class="button action accept"><?php esc_html_e( 'Accept', 'amp' ); ?></button>
			<button type="button" class="button action reject"><?php esc_html_e( 'Reject', 'amp' ); ?></button>
			<div id="vertical-divider"></div>
		</div>
		<div id="url-post-filter" class="alignleft actions">
			<?php AMP_Validation_Error_Taxonomy::render_error_type_filter(); ?>
		</div>
		<?php $wp_list_table->display(); ?>

		<?php
		remove_filter( 'get_terms', $override_terms_in_occurrence_order );
	}

	/**
	 * Gets the number of amp_validation_error terms that should appear on the single amp_validated_url /wp-admin/post.php page.
	 *
	 * @param int $terms_per_page The number of terms on a page.
	 * @return int The number of terms on the page.
	 */
	public static function get_terms_per_page( $terms_per_page ) {
		global $pagenow;
		if ( 'post.php' === $pagenow ) {
			return PHP_INT_MAX;
		}
		return $terms_per_page;
	}

	/**
	 * Adds the taxonomy to the $_REQUEST, so that it is available in WP_Screen and WP_Terms_List_Table.
	 *
	 * It would be ideal to do this in render_single_url_list_table(),
	 * but set_current_screen() looks to run before that, and that needs access to the 'taxonomy'.
	 */
	public static function add_taxonomy() {
		global $pagenow;

		if ( 'post.php' !== $pagenow || ! isset( $_REQUEST['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$post_id = (int) $_REQUEST['post']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $post_id ) && self::POST_TYPE_SLUG === get_post_type( $post_id ) ) {
			$_REQUEST['taxonomy'] = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		}
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
		<h2 class="amp-validated-url">
			<a href="<?php echo esc_url( $url ); ?>">
				<?php
				printf(
					/* translators: %s is a link dashicon, %s is the front-end URL, %s is an external dashicon %s  */
					'%s url: %s %s',
					'<span class="dashicons dashicons-admin-links"></span>',
					esc_html( $url ),
					'<span class="dashicons dashicons-external"></span>'
				);
				?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Strip host name from AMP validated URL being printed.
	 *
	 * @param string $title Title.
	 * @param int    $id Post ID.
	 *
	 * @return string Title.
	 */
	public static function filter_the_title_in_post_list_table( $title, $id = null ) {
		if ( function_exists( 'get_current_screen' ) && get_current_screen() && get_current_screen()->base === 'edit' && get_current_screen()->post_type === self::POST_TYPE_SLUG && self::POST_TYPE_SLUG === get_post_type( $id ) ) {
			$title = preg_replace( '#^(\w+:)?//[^/]+#', '', $title );
		}
		return $title;
	}

	/**
	 * Renders the filters on the validated URL post type edit.php page.
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

		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE_SLUG,
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => array(
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
				),
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( 0 !== $query->found_posts ) {
			$items[] = sprintf(
				'<a class="amp-validation-errors" href="%s">%s</a>',
				esc_url(
					admin_url(
						add_query_arg(
							array(
								'post_type' => self::POST_TYPE_SLUG,
								AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR => array(
									AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS,
									AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
								),
							),
							'edit.php'
						)
					)
				),
				esc_html(
					sprintf(
						/* translators: %s is the validation error count */
						_n(
							'%s URL w/ new AMP errors',
							'%s URLs w/ new AMP errors',
							$query->found_posts,
							'amp'
						),
						number_format_i18n( $query->found_posts )
					)
				)
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
	 * Gets the heading for the single URL page at /wp-admin/post.php.
	 * This will be in the format of 'Errors for: <page title>'.
	 *
	 * @return string|null The page heading, or null.
	 */
	public static function get_single_url_page_heading() {
		global $pagenow;

		if (
			'post.php' !== $pagenow
			||
			! isset( $_GET['post'], $_GET['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			||
			self::POST_TYPE_SLUG !== get_post_type( $_GET['post'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return null;
		}

		// Mainly uses the same conditionals as print_status_meta_box().
		$post           = get_post();
		$queried_object = get_post_meta( $post->ID, '_amp_queried_object', true );
		$name           = __( 'Single URL', 'amp' ); // Default.
		if ( isset( $queried_object['type'] ) && isset( $queried_object['id'] ) ) {
			if ( 'post' === $queried_object['type'] && get_post( $queried_object['id'] ) ) {
				$name = html_entity_decode( get_the_title( $queried_object['id'], ENT_QUOTES ) );
			} elseif ( 'term' === $queried_object['type'] && get_term( $queried_object['id'] ) ) {
				$name = get_term( $queried_object['id'] )->name;
			} elseif ( 'user' === $queried_object['type'] && get_user_by( 'ID', $queried_object['id'] ) ) {
				$name = get_user_by( 'ID', $queried_object['id'] )->display_name;
			}
		}

		/* translators: %s is the name of the page with the the validation error(s) */
		return esc_html( sprintf( __( 'Errors for: %s', 'amp' ), $name ) );
	}

	/**
	 * Filters post row actions.
	 *
	 * Manages links for details, recheck, view, forget, and forget permanently.
	 *
	 * @param array    $actions Row action links.
	 * @param \WP_Post $post Current WP post.
	 * @return array Filtered action links.
	 */
	public static function filter_post_row_actions( $actions, $post ) {
		if ( ! is_object( $post ) || self::POST_TYPE_SLUG !== $post->post_type ) {
			return $actions;
		}

		// Inline edits are not relevant.
		unset( $actions['inline hide-if-no-js'] );

		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_post_link( $post ) ),
				esc_html__( 'Details', 'amp' )
			);
		}

		if ( 'trash' !== $post->post_status && current_user_can( 'edit_post', $post->ID ) ) {
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
		}

		// Replace 'Trash' with 'Forget' (which permanently deletes).
		$has_delete = ( isset( $actions['trash'] ) || isset( $actions['delete'] ) );
		unset( $actions['trash'], $actions['delete'] );
		if ( $has_delete ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				get_delete_post_link( $post->ID, '', true ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Forget &#8220;%s&#8221;', 'amp' ), self::get_url_from_post( $post ) ) ),
				esc_html__( 'Forget', 'amp' )
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
	 * Note that trashing is replaced with deletion whenever possible, so the trashed and untrashed messages will not be used in practice.
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
					/* translators: %s is the number of posts forgotten */
					'deleted'   => _n(
						'%s validated URL forgotten.',
						'%s validated URLs forgotten.',
						$bulk_counts['deleted'],
						'amp'
					),
					/* translators: %s is the number of posts forgotten */
					'trashed'   => _n(
						'%s validated URL forgotten.',
						'%s validated URLs forgotten.',
						$bulk_counts['trashed'],
						'amp'
					),
					/* translators: %s is the number of posts restored from trash. */
					'untrashed' => _n(
						'%s validated URL unforgotten.',
						'%s validated URLs unforgotten.',
						$bulk_counts['untrashed'],
						'amp'
					),
				)
			);
		}

		return $messages;
	}

	/**
	 * Is AMP Enabled on Post
	 *
	 * @param WP_Post $post Post object to check.
	 *
	 * @return bool|void
	 */
	public static function is_amp_enabled_on_post( $post ) {
		if ( empty( $post ) ) {
			return;
		}

		$validation_errors           = self::get_invalid_url_validation_errors( $post );
		$counts                      = self::count_invalid_url_validation_errors( $validation_errors );
		$are_there_unaccepted_errors = ( $counts['new_rejected'] || $counts['ack_rejected'] );
		return ! $are_there_unaccepted_errors;
	}

	/**
	 * Count URL Validation Errors
	 *
	 * @param array $validation_errors Validation errors.
	 *
	 * @return array
	 */
	protected static function count_invalid_url_validation_errors( $validation_errors ) {
		$counts = array_fill_keys(
			array( 'new_accepted', 'ack_accepted', 'new_rejected', 'ack_rejected' ),
			0
		);
		foreach ( $validation_errors as $error ) {
			switch ( $error['term']->term_group ) {
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS:
					$counts['new_rejected']++;
					break;
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS:
					$counts['new_accepted']++;
					break;
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS:
					$counts['ack_accepted']++;
					break;
				case AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS:
					$counts['ack_rejected']++;
					break;
			}
		}
		return $counts;
	}
}
