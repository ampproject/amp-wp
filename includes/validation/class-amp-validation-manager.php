<?php
/**
 * Class AMP_Validation_Manager
 *
 * @package AMP
 */

use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Icon;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PluginRegistry;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Services;
use AmpProject\Attribute;
use AmpProject\Tag;
use AmpProject\Dom\Document;

/**
 * Class AMP_Validation_Manager
 *
 * @since 0.7
 * @internal
 */
class AMP_Validation_Manager {

	/**
	 * Query var that triggers validation.
	 *
	 * @var string
	 */
	const VALIDATE_QUERY_VAR = 'amp_validate';

	/**
	 * Meta capability for validation.
	 *
	 * Note that this is mapped to 'manage_options' by default via `AMP_Validation_Manager::map_meta_cap()`. Using a
	 * meta capability allows a site to customize which users get access to perform validation.
	 *
	 * @see AMP_Validation_Manager::map_meta_cap()
	 * @var string
	 */
	const VALIDATE_CAPABILITY = 'amp_validate';

	/**
	 * Action name for previewing the status change for invalid markup.
	 *
	 * @var string
	 */
	const MARKUP_STATUS_PREVIEW_ACTION = 'amp_markup_status_preview';

	/**
	 * Query var for passing status preview/update for validation error.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_TERM_STATUS_QUERY_VAR = 'amp_validation_error_term_status';

	/**
	 * Query var for cache-busting.
	 *
	 * @var string
	 */
	const CACHE_BUST_QUERY_VAR = 'amp_cache_bust';

	/**
	 * Transient key to store validation errors when activating a plugin.
	 *
	 * @var string
	 */
	const PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY = 'amp_plugin_activation_validation_errors';

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
	 *     @type array  $error     Error code.
	 *     @type bool   $sanitized Whether sanitized.
	 *     @type string $slug      Hash of the error.
	 * }
	 */
	public static $validation_results = [];

	/**
	 * Sources that enqueue (or register) each script.
	 *
	 * @var array
	 */
	public static $enqueued_script_sources = [];

	/**
	 * Sources for script extras that are attached to each dependency.
	 *
	 * The keys are the values of the extras being added; the values are an array of the source(s) that caused the extra
	 * to be added.
	 *
	 * @since 1.5
	 * @var array[]
	 */
	public static $extra_script_sources = [];

	/**
	 * Sources that enqueue (or register) each style.
	 *
	 * @var array
	 */
	public static $enqueued_style_sources = [];

	/**
	 * Sources for style extras that are attached to each dependency.
	 *
	 * The keys are the style handles, and the values are mappings of the inline CSS to the array of sources.
	 *
	 * @since 1.5
	 * @var array[]
	 */
	public static $extra_style_sources = [];

	/**
	 * Post IDs for posts that have been updated which need to be re-validated.
	 *
	 * Keys are post IDs and values are whether the post has been re-validated.
	 *
	 * @var bool[]
	 */
	public static $posts_pending_frontend_validation = [];

	/**
	 * Current sources gathered for a given hook currently being run.
	 *
	 * @see AMP_Validation_Manager::wrap_hook_callbacks()
	 * @see AMP_Validation_Manager::decorate_filter_source()
	 * @var array[]
	 */
	protected static $current_hook_source_stack = [];

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
	public static $hook_source_stack = [];

	/**
	 * Whether a validate request is being performed.
	 *
	 * When responding to a request to validate a URL, instead of an HTML document being returned, a JSON document is
	 * returned with any errors that were encountered during validation.
	 *
	 * @see AMP_Validation_Manager::get_validate_response_data()
	 *
	 * @var bool
	 */
	public static $is_validate_request = false;

	/**
	 * Overrides for validation errors.
	 *
	 * @var array
	 */
	public static $validation_error_status_overrides = [];

	/**
	 * Whether the admin bar item was added for AMP.
	 *
	 * @var bool
	 */
	protected static $amp_admin_bar_item_added = false;

	/**
	 * Get dev tools user access service.
	 *
	 * @return UserAccess
	 */
	private static function get_dev_tools_user_access() {
		$service = Services::get( 'dev_tools.user_access' );
		return $service;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'map_meta_cap', [ __CLASS__, 'map_meta_cap' ], 100, 2 );
		AMP_Validated_URL_Post_Type::register();
		AMP_Validation_Error_Taxonomy::register();

		add_action( 'save_post', [ __CLASS__, 'handle_save_post_prompting_validation' ] );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_validation' ] );
		add_action( 'edit_form_top', [ __CLASS__, 'print_edit_form_validation_status' ], 10, 2 );
		add_action( 'rest_api_init', [ __CLASS__, 'add_rest_api_fields' ] );

		// Add actions for checking theme support is present to determine plugin compatibility and show validation links in the admin bar.
		// Actions and filters involved in validation.
		add_action(
			'activate_plugin',
			static function() {
				if ( ! has_action( 'shutdown', [ __CLASS__, 'validate_after_plugin_activation' ] ) && self::get_dev_tools_user_access()->is_user_enabled() ) {
					add_action( 'shutdown', [ __CLASS__, 'validate_after_plugin_activation' ] ); // Shutdown so all plugins will have been activated.
				}
			}
		);

		add_action( 'all_admin_notices', [ __CLASS__, 'print_plugin_notice' ] );
		add_action( 'admin_bar_menu', [ __CLASS__, 'add_admin_bar_menu_items' ], 101 );
		add_action( 'wp', [ __CLASS__, 'override_validation_error_statuses' ] );
	}

	/**
	 * Determine if a post supports AMP validation.
	 *
	 * @since 1.2
	 *
	 * @param WP_Post|int $post Post.
	 * @return bool Whether post supports AMP validation.
	 */
	public static function post_supports_validation( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		return (
			// Skip if the post type is not viewable on the frontend, since we need a permalink to validate.
			in_array( $post->post_type, AMP_Post_Type_Support::get_eligible_post_types(), true )
			&&
			! wp_is_post_autosave( $post )
			&&
			! wp_is_post_revision( $post )
			&&
			'auto-draft' !== $post->post_status
			&&
			'trash' !== $post->post_status
			&&
			amp_is_post_supported( $post )
		);
	}

	/**
	 * Determine whether AMP theme support is forced via the amp_validate query param.
	 *
	 * @since 1.0
	 *
	 * @return bool Whether theme support forced.
	 */
	public static function is_theme_support_forced() {
		return self::$is_validate_request;
	}

	/**
	 * Return whether sanitization is initially accepted (by default) for newly encountered validation errors.
	 *
	 * To reject all new validation errors by default, a filter can be used like so:
	 *
	 *     add_filter( 'amp_validation_error_default_sanitized', '__return_false' );
	 *
	 * Whether or not a validation error is then actually sanitized is the ultimately determined by the
	 * `amp_validation_error_sanitized` filter.
	 *
	 * @since 1.0
	 * @see AMP_Validation_Error_Taxonomy::is_validation_error_sanitized()
	 * @see AMP_Validation_Error_Taxonomy::get_validation_error_sanitization()
	 *
	 * @param array $error Optional. Validation error. Will query the general status if no error provided.
	 * @return bool Whether sanitization is forcibly accepted.
	 */
	public static function is_sanitization_auto_accepted( $error = null ) {

		if ( $error && amp_is_canonical() ) {
			// Excessive CSS on AMP-first sites must not be removed by default since removing CSS can severely break a site.
			$accepted = AMP_Style_Sanitizer::STYLESHEET_TOO_LONG !== $error['code'];
		} else {
			$accepted = true;
		}

		/**
		 * Filters whether sanitization is accepted for a newly-encountered validation error .
		 *
		 * This only applies to validation errors that have not been encountered before. To override the sanitization
		 * status of existing validation errors, use the `amp_validation_error_sanitized` filter.
		 *
		 * @since 1.4
		 * @see AMP_Validation_Error_Taxonomy::get_validation_error_sanitization()
		 *
		 * @param bool       $accepted Default accepted.
		 * @param array|null $error    Validation error. May be null when asking if accepting sanitization is enabled by default.
		 */
		return apply_filters( 'amp_validation_error_default_sanitized', $accepted, $error );
	}

	/**
	 * Add menu items to admin bar for AMP.
	 *
	 * When on a non-AMP response (transitional mode), then the admin bar item should include:
	 * - Icon: LINK SYMBOL when AMP not known to be invalid and sanitization is not forced, or CROSS MARK when AMP is known to be valid.
	 * - Parent admin item and first submenu item: link to AMP version.
	 * - Second submenu item: link to validate the URL.
	 *
	 * When on transitional AMP response:
	 * - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors which are being forcibly sanitized.
	 *         Otherwise, if there are unsanitized validation errors then a redirect to the non-AMP version will be done.
	 * - Parent admin item and first submenu item: link to non-AMP version.
	 * - Second submenu item: link to validate the URL.
	 *
	 * When on AMP-first response:
	 * - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors.
	 * - Parent admin and first submenu item: link to validate the URL.
	 *
	 * @see AMP_Validation_Manager::finalize_validation() Where the emoji is updated.
	 * @see amp_add_admin_bar_view_link() Where an admin bar item may have been added already for Reader/Transitional modes.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
	 */
	public static function add_admin_bar_menu_items( $wp_admin_bar ) {
		if ( is_admin() || ! self::get_dev_tools_user_access()->is_user_enabled() || ! amp_is_available() ) {
			self::$amp_admin_bar_item_added = false;
			return;
		}

		$is_amp_request = amp_is_request();

		$current_url = amp_get_current_url();
		$non_amp_url = amp_remove_endpoint( $current_url );
		$non_amp_url = add_query_arg(
			QueryVar::NOAMP,
			amp_is_canonical() ? QueryVar::NOAMP_AVAILABLE : QueryVar::NOAMP_MOBILE,
			$non_amp_url
		);

		$amp_url = remove_query_arg(
			array_merge( wp_removable_query_args(), [ QueryVar::NOAMP ] ),
			$current_url
		);
		if ( ! amp_is_canonical() ) {
			$amp_url = add_query_arg( amp_get_slug(), '', $amp_url );
		}

		$validate_url = AMP_Validated_URL_Post_Type::get_recheck_url( AMP_Validated_URL_Post_Type::get_invalid_url_post( $amp_url ) ?: $amp_url );

		// Construct the parent admin bar item.
		if ( $is_amp_request ) {
			$icon = Icon::valid(); // This will get overridden in AMP_Validation_Manager::finalize_validation() if there are unaccepted errors.
			$href = $validate_url;
		} else {
			$icon = Icon::link();
			$href = $amp_url;
		}

		$icon_html = $icon->to_html(
			[
				'id'    => 'amp-admin-bar-item-status-icon',
				'class' => 'ab-icon',
			]
		);

		$parent = [
			'id'    => 'amp',
			'title' => sprintf(
				'%s %s',
				$icon_html,
				esc_html__( 'AMP', 'amp' )
			),
			'href'  => esc_url( $href ),
		];

		// Construct admin bar item for validation.
		$validate_item = [
			'parent' => 'amp',
			'id'     => 'amp-validity',
			'title'  => esc_html__( 'Validate URL', 'amp' ),
			'href'   => esc_url( $validate_url ),
		];

		// Construct admin bar item to link to AMP version or non-AMP version.
		$wp_admin_bar->remove_node( 'amp-view' ); // Remove so we can re-add in the right position.
		$link_item = [
			'parent' => 'amp',
			'id'     => 'amp-view',
			'href'   => esc_url( $is_amp_request ? $non_amp_url : $amp_url ),
		];
		if ( amp_is_canonical() ) {
			$link_item['title'] = esc_html__( 'View with AMP disabled', 'amp' );
		} else {
			$link_item['title'] = esc_html( $is_amp_request ? __( 'View non-AMP version', 'amp' ) : __( 'View AMP version', 'amp' ) );
		}

		// Add top-level menu item. Note that this will correctly merge/amend any existing AMP nav menu item added in amp_add_admin_bar_view_link().
		$wp_admin_bar->add_node( $parent );

		if ( $is_amp_request ) {
			$wp_admin_bar->add_node( $validate_item );
			$wp_admin_bar->add_node( $link_item );
		} else {
			$wp_admin_bar->add_node( $link_item );
			$wp_admin_bar->add_node( $validate_item );
		}

		if (
			AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
			&&
			AMP_Theme_Support::is_paired_available()
			&&
			amp_is_dev_mode()
		) {
			// Construct admin bar item to link to paired browsing experience.
			$paired_browsing_item = [
				'parent' => 'amp',
				'id'     => 'amp-paired-browsing',
				'title'  => esc_html__( 'Paired Browsing', 'amp' ),
				'href'   => AMP_Theme_Support::get_paired_browsing_url(),
			];

			$wp_admin_bar->add_node( $paired_browsing_item );
		}

		// Add settings link to admin bar.
		if ( current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->add_node(
				[
					'parent' => 'amp',
					'id'     => 'amp-settings',
					'title'  => esc_html__( 'Settings', 'amp' ),
					'href'   => esc_url( admin_url( add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, 'admin.php' ) ) ),
				]
			);
		}

		self::$amp_admin_bar_item_added = true;
	}

	/**
	 * Override validation error statuses (when requested).
	 *
	 * When a query var is present along with the required nonce, override the status of the status of the invalid markup
	 * as requested.
	 *
	 * @since 1.5.0
	 */
	public static function override_validation_error_statuses() {
		$override_validation_error_statuses = (
			isset( $_REQUEST['preview'] )
			&&
			! empty( $_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&&
			is_array( $_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
		if ( ! $override_validation_error_statuses ) {
			return;
		}
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), self::MARKUP_STATUS_PREVIEW_ACTION ) ) {
			wp_die(
				esc_html__( 'Preview link expired. Please try again.', 'amp' ),
				esc_html__( 'Error', 'amp' ),
				[ 'response' => 401 ]
			);
		}

		/*
		 * This can't just easily add an amp_validation_error_sanitized filter because the the filter_sanitizer_args() method
		 * currently needs to obtain the list of overrides to create a parsed_cache_variant.
		 */
		foreach ( $_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] as $slug => $data ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $data[ self::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] ) ) {
				continue;
			}

			$slug   = sanitize_key( $slug );
			$status = (int) $data[ self::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ];
			self::$validation_error_status_overrides[ $slug ] = $status;
			ksort( self::$validation_error_status_overrides );
		}
	}

	/**
	 * Initialize a validate request.
	 *
	 * This function is called as early as possible, at the plugins_loaded action, to see if the current request is to
	 * validate the response. If the validate query arg is absent, then this does nothing. If the query arg is present,
	 * but the value is not a valid auth key, then wp_send_json() is invoked to short-circuit with a failure. Otherwise,
	 * the static $is_validate_request variable is set to true.
	 *
	 * @since 1.5
	 */
	public static function init_validate_request() {
		$should_validate_response = self::should_validate_response();

		if ( true === $should_validate_response ) {
			self::add_validation_error_sourcing();
			self::$is_validate_request = true;

			if ( '1' === (string) ini_get( 'display_errors' ) ) {
				// Suppress the display of fatal errors that may arise during validation so that they will not be counted
				// as actual validation errors.
				ini_set( 'display_errors', 0 ); // phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted
			}
		} else {
			self::$is_validate_request = false;

			// Short-circuit validation requests that are unauthorized.
			if ( $should_validate_response instanceof WP_Error ) {
				wp_send_json(
					[
						'code'    => $should_validate_response->get_error_code(),
						'message' => $should_validate_response->get_error_message(),
					],
					401
				);
			}
		}
	}

	/**
	 * Add hooks for doing determining sources for validation errors during preprocessing/sanitizing.
	 */
	public static function add_validation_error_sourcing() {
		add_action( 'wp', [ __CLASS__, 'wrap_widget_callbacks' ] );

		add_action( 'all', [ __CLASS__, 'wrap_hook_callbacks' ] );
		$wrapped_filters = [ 'the_content', 'the_excerpt' ];
		foreach ( $wrapped_filters as $wrapped_filter ) {
			add_filter( $wrapped_filter, [ __CLASS__, 'decorate_filter_source' ], PHP_INT_MAX );
		}

		add_filter( 'do_shortcode_tag', [ __CLASS__, 'decorate_shortcode_source' ], PHP_INT_MAX, 2 );
		add_filter( 'embed_oembed_html', [ __CLASS__, 'decorate_embed_source' ], PHP_INT_MAX, 3 );
		add_filter( 'the_content', [ __CLASS__, 'add_block_source_comments' ], 8 ); // The do_blocks() function runs at priority 9.
	}

	/**
	 * Handle save_post action to queue re-validation of the post on the frontend.
	 *
	 * This is intended to only apply to post edits made in the classic editor.
	 *
	 * @see AMP_Validation_Manager::get_amp_validity_rest_field() The method responsible for validation post changes via Gutenberg.
	 * @see AMP_Validation_Manager::validate_queued_posts_on_frontend()
	 *
	 * @param int $post_id Post ID.
	 */
	public static function handle_save_post_prompting_validation( $post_id ) {
		global $pagenow;

		if ( ! self::get_dev_tools_user_access()->is_user_enabled() ) {
			return;
		}

		$post = get_post( $post_id );

		$is_classic_editor_post_save = (
			isset( $_SERVER['REQUEST_METHOD'] )
			&&
			'POST' === $_SERVER['REQUEST_METHOD']
			&&
			'post.php' === $pagenow
			&&
			isset( $_POST['post_ID'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			&&
			(int) $_POST['post_ID'] === (int) $post_id // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		$should_validate_post = (
			$is_classic_editor_post_save
			&&
			self::post_supports_validation( $post )
			&&
			! isset( self::$posts_pending_frontend_validation[ $post_id ] )
		);
		if ( $should_validate_post ) {
			self::$posts_pending_frontend_validation[ $post_id ] = true;

			// The reason for shutdown is to ensure that all postmeta changes have been saved, including whether AMP is enabled.
			if ( ! has_action( 'shutdown', [ __CLASS__, 'validate_queued_posts_on_frontend' ] ) ) {
				add_action( 'shutdown', [ __CLASS__, 'validate_queued_posts_on_frontend' ] );
			}
		}
	}

	/**
	 * Validate the posts pending frontend validation.
	 *
	 * @see AMP_Validation_Manager::handle_save_post_prompting_validation()
	 *
	 * @return array Mapping of post ID to the result of validating or storing the validation result.
	 */
	public static function validate_queued_posts_on_frontend() {
		$posts = array_filter(
			array_map( 'get_post', array_keys( array_filter( self::$posts_pending_frontend_validation ) ) ),
			function( $post ) {
				return self::post_supports_validation( $post );
			}
		);

		$validation_posts = [];

		/*
		 * It is unlikely that there will be more than one post in the array.
		 * For the bulk recheck action, see AMP_Validated_URL_Post_Type::handle_bulk_action().
		 */
		foreach ( $posts as $post ) {
			$url = amp_get_permalink( $post->ID );
			if ( ! $url ) {
				$validation_posts[ $post->ID ] = new WP_Error( 'no_amp_permalink' );
				continue;
			}

			// Prevent re-validating.
			self::$posts_pending_frontend_validation[ $post->ID ] = false;

			$invalid_url_post_id = (int) get_post_meta( $post->ID, '_amp_validated_url_post_id', true );

			$validity = self::validate_url_and_store( $url, $invalid_url_post_id );

			// Remember the amp_validated_url post so that when the slug changes the old amp_validated_url post can be updated.
			if ( ! is_wp_error( $validity ) && $invalid_url_post_id !== $validity['post_id'] ) {
				update_post_meta( $post->ID, '_amp_validated_url_post_id', $validity['post_id'] );
			}

			$validation_posts[ $post->ID ] = $validity instanceof WP_Error ? $validity : $validity['post_id'];
		}

		return $validation_posts;
	}

	/**
	 * Adds fields to the REST API responses, in order to display validation errors.
	 *
	 * @return void
	 */
	public static function add_rest_api_fields() {
		register_rest_field(
			AMP_Post_Type_Support::get_post_types_for_rest_api(),
			self::VALIDITY_REST_FIELD_NAME,
			[
				'get_callback' => [ __CLASS__, 'get_amp_validity_rest_field' ],
				'schema'       => [
					'description' => __( 'AMP validity status', 'amp' ),
					'type'        => 'object',
				],
			]
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
		if ( ! current_user_can( 'edit_post', $post_data['id'] ) || ! self::get_dev_tools_user_access()->is_user_enabled() || ! self::post_supports_validation( $post_data['id'] ) ) {
			return null;
		}
		$post = get_post( $post_data['id'] );

		$validation_status_post = null;
		if ( in_array( $request->get_method(), [ 'PUT', 'POST' ], true ) ) {
			if ( ! isset( self::$posts_pending_frontend_validation[ $post->ID ] ) ) {
				self::$posts_pending_frontend_validation[ $post->ID ] = true;
			}
			$results = self::validate_queued_posts_on_frontend();
			if ( isset( $results[ $post->ID ] ) && is_int( $results[ $post->ID ] ) ) {
				$validation_status_post = get_post( $results[ $post->ID ] );
			}
		}

		if ( empty( $validation_status_post ) ) {
			$validation_status_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( amp_get_permalink( $post->ID ) );
		}

		$field = [
			'results'     => [],
			'review_link' => null,
		];

		if ( $validation_status_post ) {
			$field['review_link'] = get_edit_post_link( $validation_status_post->ID, 'raw' );
			foreach ( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $validation_status_post ) as $result ) {
				$field['results'][] = [
					'sanitized'   => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $result['status'],
					'title'       => AMP_Validation_Error_Taxonomy::get_error_title_from_code( $result['data'] ),
					'error'       => $result['data'],
					'status'      => $result['status'],
					'term_status' => $result['term_status'],
					'forced'      => $result['forced'],
				];
			}
		}

		return $field;
	}

	/**
	 * Map the amp_validate meta capability to the primitive manage_options capability.
	 *
	 * Using a meta capability allows a site to customize which users get access to perform validation.
	 *
	 * @param string[] $caps Array of the user's capabilities.
	 * @param string   $cap  Capability name.
	 * @return string[] Filtered primitive capabilities.
	 */
	public static function map_meta_cap( $caps, $cap ) {
		if ( self::VALIDATE_CAPABILITY === $cap ) {
			// Note that $caps most likely only contains a single item anyway, but only swapping out the one meta
			// capability with the primitive capability allows a site to add additional required capabilities.
			$position = array_search( $cap, $caps, true );
			if ( false !== $position ) {
				$caps[ $position ] = 'manage_options';
			}
		}
		return $caps;
	}

	/**
	 * Whether the user has the required capability to validate.
	 *
	 * Checks for permissions before validating.
	 *
	 * @param int|WP_User|null $user User to check for the capability. If null, the current user is used.
	 * @return boolean $has_cap Whether the current user has the capability.
	 */
	public static function has_cap( $user = null ) {
		if ( null === $user ) {
			$user = wp_get_current_user();
		}
		return user_can( $user, self::VALIDATE_CAPABILITY );
	}

	/**
	 * Add validation error.
	 *
	 * @param array $error Error info, especially code.
	 * @param array $data Additional data, including the node.
	 *
	 * @return bool Whether the validation error should result in sanitization.
	 */
	public static function add_validation_error( array $error, array $data = [] ) {
		$node    = null;
		$sources = null;

		if ( isset( $data['node'] ) && $data['node'] instanceof DOMNode ) {
			$node = $data['node'];
		}

		if ( self::$is_validate_request ) {
			if ( ! empty( $error['sources'] ) ) {
				$sources = $error['sources'];
			} elseif ( $node ) {
				$sources = self::locate_sources( $node );
			}
		}
		unset( $error['sources'] );

		if ( ! isset( $error['code'] ) ) {
			$error['code'] = 'unknown';
		}

		/**
		 * Filters the validation error array.
		 *
		 * This allows plugins to add amend additional properties which can help with
		 * more accurately identifying a validation error beyond the name of the parent
		 * node and the element's attributes. The $sources are also omitted because
		 * these are only available during an explicit validation request and so they
		 * are not suitable for plugins to vary sanitization by. If looking to force a
		 * validation error to be ignored, use the 'amp_validation_error_sanitized'
		 * filter instead of attempting to return an empty value with this filter (as
		 * that is not supported).
		 *
		 * @since 1.0
		 *
		 * @param array $error Validation error to be printed.
		 * @param array $context   {
		 *     Context data for validation error sanitization.
		 *
		 *     @type DOMNode $node Node for which the validation error is being reported. May be null.
		 * }
		 */
		$error = apply_filters( 'amp_validation_error', $error, compact( 'node' ) );

		$sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $error );
		$sanitized    = (
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS === $sanitization['status']
			||
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $sanitization['status']
		);

		/*
		 * Ignore validation errors which are forcibly sanitized by filter. This includes errors accepted via
		 * AMP_Validation_Error_Taxonomy::accept_validation_errors(), such as the acceptable_errors in core themes.
		 * This was introduced in <https://github.com/ampproject/amp-wp/pull/1413> to prevent forcibly-sanitized
		 * validation errors from being reported, to avoid noise and wasted storage. It was inadvertently
		 * reverted in de7b04b but then restored as part of <https://github.com/ampproject/amp-wp/pull/1413>.
		 */
		if ( $sanitized && 'with_filter' === $sanitization['forced'] ) {
			return true;
		}

		// Add sources back into the $error for referencing later. @todo It may be cleaner to store sources separately to avoid having to re-remove later during storage.
		$error = array_merge( $error, compact( 'sources' ) );

		self::$validation_results[] = compact( 'error', 'sanitized' );
		return $sanitized;
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
		self::$validation_results      = [];
		self::$enqueued_style_sources  = [];
		self::$enqueued_script_sources = [];
		self::$extra_script_sources    = [];
		self::$extra_style_sources     = [];
	}

	/**
	 * Checks the AMP validity of the post content.
	 *
	 * If it's not valid AMP, it displays an error message above the 'Classic' editor.
	 *
	 * This is essentially a PHP implementation of ampBlockValidation.handleValidationErrorsStateChange() in JS.
	 *
	 * @param WP_Post $post The updated post.
	 * @return void
	 */
	public static function print_edit_form_validation_status( $post ) {
		if ( ! self::post_supports_validation( $post ) || ! self::get_dev_tools_user_access()->is_user_enabled() ) {
			return;
		}

		$invalid_url_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( get_permalink( $post->ID ) );
		if ( ! $invalid_url_post ) {
			return;
		}

		// Show all validation errors which have not been explicitly acknowledged as accepted.
		$validation_errors  = [];
		$has_rejected_error = false;
		foreach ( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $invalid_url_post ) as $error ) {
			$needs_moderation = (
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS === $error['status'] || // @todo Show differently since moderated?
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS === $error['status'] ||
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS === $error['status']
			);
			if ( $needs_moderation ) {
				$validation_errors[] = $error['data'];
			}

			if (
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS === $error['status']
				||
				AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS === $error['status']
			) {
				$has_rejected_error = true;
			}
		}

		// No validation errors so abort.
		if ( empty( $validation_errors ) ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p>';
		esc_html_e( 'There is content which fails AMP validation.', 'amp' );
		echo ' ';

		// Auto-acceptance is enabled by default but can be overridden by the the `amp_validation_error_default_sanitized` filter.
		if ( ! $has_rejected_error ) {
			esc_html_e( 'The invalid markup has been automatically removed.', 'amp' );
		} else {
			/*
			 * Even if invalid markup is removed by default, if there are non-accepted errors in non-Standard mode, it will redirect to a non-AMP page.
			 * For example, the errors could have been stored as 'New Kept' when auto-accept was false, and now auto-accept is true.
			 * In that case, this will block serving AMP.
			 * This could also apply if this is in 'Standard' mode and the user has rejected a validation error.
			 */
			esc_html_e( 'In order for AMP to be served you will have to remove the invalid markup or allow the plugin to remove it.', 'amp' );
		}

		echo sprintf(
			' <a href="%s" target="_blank">%s</a>',
			esc_url( get_edit_post_link( $invalid_url_post ) ),
			esc_html__( 'Review issues', 'amp' )
		);
		echo '</p>';

		$results      = AMP_Validation_Error_Taxonomy::summarize_validation_errors( array_unique( $validation_errors, SORT_REGULAR ) );
		$removed_sets = [];
		if ( ! empty( $results[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] ) && is_array( $results[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] ) ) {
			$removed_sets[] = [
				'label' => __( 'Invalid elements:', 'amp' ),
				'names' => array_map( 'sanitize_key', $results[ AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS ] ),
			];
		}
		if ( ! empty( $results[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] ) && is_array( $results[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] ) ) {
			$removed_sets[] = [
				'label' => __( 'Invalid attributes:', 'amp' ),
				'names' => array_map( 'sanitize_key', $results[ AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES ] ),
			];
		}
		// @todo There are other kinds of errors other than REMOVED_ELEMENTS and REMOVED_ATTRIBUTES.
		foreach ( $removed_sets as $removed_set ) {
			printf( '<p>%s ', esc_html( $removed_set['label'] ) );
			$items = [];
			foreach ( $removed_set['names'] as $name => $count ) {
				if ( 1 === (int) $count ) {
					$items[] = sprintf( '<code>%s</code>', esc_html( $name ) );
				} else {
					$items[] = sprintf( '<code>%s</code> (%d)', esc_html( $name ), $count );
				}
			}
			echo implode( ', ', $items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
	 * Recursively determine if a given dependency depends on another.
	 *
	 * @since 1.3
	 *
	 * @param WP_Dependencies $dependencies      Dependencies.
	 * @param string          $current_handle    Current handle.
	 * @param string          $dependency_handle Dependency handle.
	 * @return bool Whether the current handle is a dependency of the dependency handle.
	 */
	protected static function has_dependency( WP_Dependencies $dependencies, $current_handle, $dependency_handle ) {
		if ( $current_handle === $dependency_handle ) {
			return true;
		}
		if ( ! isset( $dependencies->registered[ $current_handle ] ) ) {
			return false;
		}
		foreach ( $dependencies->registered[ $current_handle ]->deps as $handle ) {
			if ( self::has_dependency( $dependencies, $handle, $dependency_handle ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Determine if a script element matches a given script handle.
	 *
	 * @param DOMElement $element       Element.
	 * @param string     $script_handle Script handle.
	 * @return bool
	 */
	protected static function is_matching_script( DOMElement $element, $script_handle ) {
		if ( ! isset( wp_scripts()->registered[ $script_handle ] ) ) {
			return false;
		}
		$script_dependency = wp_scripts()->registered[ $script_handle ];
		if ( empty( $script_dependency->src ) ) {
			return false;
		}

		// Script src attribute is haystack because includes protocol and may include query args (like ver).
		return false !== strpos(
			$element->getAttribute( 'src' ),
			preg_replace( '#^https?:(?=//)#', '', $script_dependency->src )
		);
	}

	/**
	 * Walk back tree to find the open sources.
	 *
	 * @todo This method and others for sourcing could be moved to a separate class.
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
		$dom      = Document::fromNode( $node );
		$comments = $dom->xpath->query( 'preceding::comment()[ starts-with( ., "amp-source-stack" ) or starts-with( ., "/amp-source-stack" ) ]', $node );
		$sources  = [];
		$matches  = [];

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

		$is_enqueued_link = (
			$node instanceof DOMElement
			&&
			'link' === $node->nodeName
			&&
			preg_match( '/(?P<handle>.+)-css$/', (string) $node->getAttribute( 'id' ), $matches )
			&&
			wp_styles()->query( $matches['handle'] )
		);
		if ( $is_enqueued_link ) {
			// Directly enqueued stylesheet.
			if ( isset( self::$enqueued_style_sources[ $matches['handle'] ] ) ) {
				$sources = array_merge(
					self::$enqueued_style_sources[ $matches['handle'] ],
					$sources
				);
			}

			// Stylesheet added as a dependency.
			foreach ( wp_styles()->done as $style_handle ) {
				if ( $matches['handle'] !== $style_handle ) {
					continue;
				}
				foreach ( self::$enqueued_style_sources as $enqueued_style_sources_handle => $enqueued_style_sources ) {
					if (
						$enqueued_style_sources_handle !== $style_handle
						&&
						wp_styles()->query( $enqueued_style_sources_handle, 'done' )
						&&
						self::has_dependency( wp_styles(), $enqueued_style_sources_handle, $style_handle )
					) {
						$sources = array_merge(
							array_map(
								static function ( $enqueued_style_source ) use ( $style_handle ) {
									$enqueued_style_source['dependency_handle'] = $style_handle;
									return $enqueued_style_source;
								},
								$enqueued_style_sources
							),
							$sources
						);
					}
				}
			}
		}

		$is_inline_style = (
			$node instanceof DOMElement
			&&
			'style' === $node->nodeName
			&&
			$node->firstChild instanceof DOMText
			&&
			$node->hasAttribute( 'id' )
			&&
			preg_match( '/^(?P<handle>.+)-inline-css$/', $node->getAttribute( 'id' ), $matches )
			&&
			wp_styles()->query( $matches['handle'] )
			&&
			isset( self::$extra_style_sources[ $matches['handle'] ] )
		);
		if ( $is_inline_style ) {
			$text = $node->textContent;
			foreach ( self::$extra_style_sources[ $matches['handle'] ] as $css => $extra_sources ) {
				if ( false !== strpos( $text, $css ) ) {
					$sources = array_merge(
						$sources,
						$extra_sources
					);
				}
			}
		}

		if ( $node instanceof DOMElement && 'script' === $node->nodeName ) {
			$enqueued_script_handles = array_intersect( wp_scripts()->done, array_keys( self::$enqueued_script_sources ) );

			if ( $node->hasAttribute( 'src' ) ) {

				// External scripts, directly enqueued.
				foreach ( $enqueued_script_handles as $enqueued_script_handle ) {
					if ( ! self::is_matching_script( $node, $enqueued_script_handle ) ) {
						continue;
					}
					$sources = array_merge(
						self::$enqueued_script_sources[ $enqueued_script_handle ],
						$sources
					);
					break;
				}

				// External scripts, added as a dependency.
				foreach ( wp_scripts()->done as $script_handle ) {
					if ( ! self::is_matching_script( $node, $script_handle ) ) {
						continue;
					}
					foreach ( self::$enqueued_script_sources as $enqueued_script_sources_handle => $enqueued_script_sources ) {
						if (
							$enqueued_script_sources_handle !== $script_handle
							&&
							wp_scripts()->query( $enqueued_script_sources_handle, 'done' )
							&&
							self::has_dependency( wp_scripts(), $enqueued_script_sources_handle, $script_handle )
						) {
							$sources = array_merge(
								array_map(
									static function ( $enqueued_script_source ) use ( $script_handle ) {
										$enqueued_script_source['dependency_handle'] = $script_handle;
										return $enqueued_script_source;
									},
									$enqueued_script_sources
								),
								$sources
							);
						}
					}
				}
			} elseif ( $node->firstChild instanceof DOMText ) {
				$text = $node->textContent;

				// Identify the inline script sources.
				foreach ( self::$extra_script_sources as $extra_data => $extra_sources ) {
					if ( false !== strpos( $text, $extra_data ) ) {
						$sources = array_merge(
							$sources,
							$extra_sources
						);
					}
				}
			}
		}

		$sources = array_unique( $sources, SORT_REGULAR );

		return $sources;
	}

	/**
	 * Add block source comments.
	 *
	 * @param string $content Content prior to blocks being processed.
	 * @return string Content with source comments added.
	 */
	public static function add_block_source_comments( $content ) {
		self::$block_content_index = 0;

		$start_block_pattern = implode(
			'',
			[
				'#<!--\s+',
				'(?P<closing>/)?',
				'wp:(?P<name>\S+)',
				'(?:\s+(?P<attributes>\{.*?\}))?',
				'\s+(?P<self_closing>\/)?',
				'-->#s',
			]
		);

		return preg_replace_callback(
			$start_block_pattern,
			[ __CLASS__, 'handle_block_source_comment_replacement' ],
			$content
		);
	}

	/**
	 * Handle block source comment replacement.
	 *
	 * @see \AMP_Validation_Manager::add_block_source_comments()
	 *
	 * @param array $matches Matches.
	 *
	 * @return string Replaced.
	 */
	protected static function handle_block_source_comment_replacement( $matches ) {
		$replaced = $matches[0];

		// Obtain source information for block.
		$source = [
			'block_name' => $matches['name'],
			'post_id'    => get_the_ID(),
		];

		if ( empty( $matches['closing'] ) ) {
			$source['block_content_index'] = self::$block_content_index;
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
			$callback_reflection = Services::get( 'dev_tools.callback_reflection' );
			$callback_source     = $callback_reflection->get_source( $block_type->render_callback );
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
	 * @return void
	 * @global array $wp_registered_widgets
	 */
	public static function wrap_widget_callbacks() {
		global $wp_registered_widgets;
		$callback_reflection = Services::get( 'dev_tools.callback_reflection' );
		foreach ( $wp_registered_widgets as $widget_id => &$registered_widget ) {
			$source = $callback_reflection->get_source( $registered_widget['callback'] );
			if ( ! $source ) {
				continue;
			}
			$source['widget_id'] = $widget_id;
			unset( $source['reflection'] ); // Omit from stored source.

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

		$callback_reflection = Services::get( 'dev_tools.callback_reflection' );

		self::$current_hook_source_stack[ $hook ] = [];
		foreach ( $wp_filter[ $hook ]->callbacks as $priority => &$callbacks ) {
			foreach ( $callbacks as &$callback ) {
				$source = $callback_reflection->get_source( $callback['function'] );
				if ( ! $source ) {
					continue;
				}

				// Skip considering ourselves.
				if ( 'AMP_Validation_Manager::add_block_source_comments' === $source['function'] ) {
					continue;
				}

				/**
				 * Reflection.
				 *
				 * @var ReflectionFunction|ReflectionMethod $reflection
				 */
				$reflection = $source['reflection'];
				unset( $source['reflection'] ); // Omit from stored source.

				// Add hook to stack for decorate_filter_source to read from.
				self::$current_hook_source_stack[ $hook ][] = $source;

				/*
				 * Wrapped callbacks cause PHP warnings when the wrapped function has arguments passed by reference.
				 * We have a special case to support functions that have the first argument passed by reference, namely
				 * wp_default_scripts() and wp_default_styles(). But other configurations are bypassed.
				 */
				$passed_by_ref = self::has_parameters_passed_by_reference( $reflection );
				if ( $passed_by_ref > 1 ) {
					continue;
				}

				$source['hook']     = $hook;
				$source['priority'] = $priority;
				$original_function  = $callback['function'];
				$wrapped_callback   = self::wrapped_callback(
					array_merge(
						$callback,
						compact( 'priority', 'source' )
					)
				);

				if ( 1 === $passed_by_ref ) {
					$callback['function'] = static function( &$first, ...$other_args ) use ( &$callback, $wrapped_callback, $original_function ) {
						$callback['function'] = $original_function; // Restore original.
						return $wrapped_callback->invoke_with_first_ref_arg( $first, ...$other_args );
					};
				} else {
					$callback['function'] = static function( ...$args ) use ( &$callback, $wrapped_callback, $original_function ) {
						$callback['function'] = $original_function; // Restore original.
						return $wrapped_callback( ...$args );
					};
				}
			}
		}
	}

	/**
	 * Determine whether the given reflection method/function has params passed by reference.
	 *
	 * @since 0.7
	 * @param ReflectionFunction|ReflectionMethod $reflection Reflection.
	 * @return int Whether there are parameters passed by reference, where 0 means none were passed, 1 means the first was passed, and 2 means some other configuration.
	 */
	protected static function has_parameters_passed_by_reference( $reflection ) {
		$status = 0;
		foreach ( $reflection->getParameters() as $i => $parameter ) {
			if ( $parameter->isPassedByReference() ) {
				if ( 0 === $i ) {
					$status = 1;
				} else {
					$status = 2;
					break;
				}
			}
		}
		return $status;
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

		$callback_reflection = Services::get( 'dev_tools.callback_reflection' );

		$source = $callback_reflection->get_source( $shortcode_tags[ $tag ] );
		if ( empty( $source ) ) {
			return $output;
		}
		$source['shortcode'] = $tag;

		$output = implode(
			'',
			[
				self::get_source_comment( $source, true ),
				$output,
				self::get_source_comment( $source, false ),
			]
		);
		return $output;
	}

	/**
	 * Filters the output created by embeds.
	 *
	 * @since 1.0
	 *
	 * @param string $output Embed output.
	 * @param string $url    URL.
	 * @param array  $attr   Attributes.
	 * @return string Output.
	 */
	public static function decorate_embed_source( $output, $url, $attr ) {
		$source = [
			'embed' => $url,
			'attr'  => $attr,
		];
		return implode(
			'',
			[
				self::get_source_comment( $source, true ),
				trim( $output ),
				self::get_source_comment( $source, false ),
			]
		);
	}

	/**
	 * Wraps output of a filter to add source stack comments.
	 *
	 * @todo Duplicate with AMP_Validation_Manager::wrap_buffer_with_source_comments()?
	 * @param string $value Value.
	 * @return string Value wrapped in source comments.
	 */
	public static function decorate_filter_source( $value ) {

		// Abort if the output is not a string and it doesn't contain any HTML tags.
		if ( ! is_string( $value ) || ! preg_match( '/<.+?>/s', $value ) ) {
			return $value;
		}

		$post   = get_post();
		$source = [
			'hook'   => current_filter(),
			'filter' => true,
		];
		if ( $post ) {
			$source['post_id']   = $post->ID;
			$source['post_type'] = $post->post_type;
		}
		if ( isset( self::$current_hook_source_stack[ current_filter() ] ) ) {
			$sources = self::$current_hook_source_stack[ current_filter() ];
			array_pop( $sources ); // Remove self.
			$source['sources'] = $sources;
		}
		return implode(
			'',
			[
				self::get_source_comment( $source, true ),
				$value,
				self::get_source_comment( $source, false ),
			]
		);
	}

	/**
	 * Gets the plugin or theme of the callback, if one exists.
	 *
	 * @deprecated 2.0.2 Use \AmpProject\AmpWP\DevTools\CallbackReflection::get_source().
	 * @codeCoverageIgnore
	 *
	 * @param string|array|callable $callback The callback for which to get the plugin.
	 * @return array|null {
	 *     The source data.
	 *
	 *     @type string $type     Source type (core, plugin, mu-plugin, or theme).
	 *     @type string $name     Source name.
	 *     @type string $file     Relative file path based on the type.
	 *     @type string $function Normalized function name.
	 *     @type ReflectionMethod|ReflectionFunction $reflection Reflection.
	 * }
	 */
	public static function get_source( $callback ) {
		_deprecated_function(
			__METHOD__,
			'2.0.2',
			'\AmpProject\AmpWP\DevTools\CallbackReflection::get_source'
		);
		return Services::get( 'dev_tools.callback_reflection' )
			->get_source( $callback );
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
		$called_functions = [];
		if ( defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ) {
			$arg = DEBUG_BACKTRACE_IGNORE_ARGS; // phpcs:ignore PHPCompatibility.Constants.NewConstants.debug_backtrace_ignore_argsFound
		} else {
			$arg = false;
		}
		$backtrace = debug_backtrace( $arg ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Only way to find out if we are in a buffering display handler.
		foreach ( $backtrace as $call_stack ) {
			if ( '{closure}' === $call_stack['function'] ) {
				$called_functions[] = 'Closure::__invoke';
			} elseif ( isset( $call_stack['class'] ) ) {
				$called_functions[] = sprintf( '%s::%s', $call_stack['class'], $call_stack['function'] );
			} else {
				$called_functions[] = $call_stack['function'];
			}
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
	 * @return AMP_Validation_Callback_Wrapper $wrapped_callback The callback, wrapped in comments.
	 */
	public static function wrapped_callback( $callback ) {
		return new AMP_Validation_Callback_Wrapper( $callback );
	}

	/**
	 * Wrap output buffer with source comments.
	 *
	 * A key reason for why this is a method and not a closure is so that
	 * the can_output_buffer method will be able to identify it by name.
	 *
	 * @since 0.7
	 * @todo Is duplicate of \AMP_Validation_Manager::decorate_filter_source()?
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
			$output = implode(
				'',
				[
					self::get_source_comment( $source, true ),
					$output,
					self::get_source_comment( $source, false ),
				]
			);
		}
		return $output;
	}

	/**
	 * Get nonce for performing amp_validate request.
	 *
	 * The returned nonce is irrespective of the authenticated user.
	 *
	 * @return string Nonce.
	 */
	public static function get_amp_validate_nonce() {
		return wp_hash( self::VALIDATE_QUERY_VAR . wp_nonce_tick(), 'nonce' );
	}

	/**
	 * Whether the request is to validate URL for validation errors.
	 *
	 * All AMP responses get validated, but when the amp_validate query parameter is present, then the source information
	 * for each validation error is captured and the validation results are returned as JSON instead of the AMP HTML page.
	 *
	 * @return bool|WP_Error Whether to validate. False is returned if it is not a validate request. WP_Error returned
	 *                       if unauthenticated, unauthorized, and/or invalid nonce supplied. True returned if
	 *                       validate response should be served.
	 */
	public static function should_validate_response() {
		if ( ! isset( $_GET[ self::VALIDATE_QUERY_VAR ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$validate_key = wp_unslash( $_GET[ self::VALIDATE_QUERY_VAR ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! hash_equals( self::get_amp_validate_nonce(), $validate_key ) ) {
			return new WP_Error(
				'http_request_failed',
				__( 'Nonce authentication failed.', 'amp' )
			);
		}

		return true;
	}

	/**
	 * Get response data for a validate request.
	 *
	 * @see AMP_Content_Sanitizer::sanitize_document()
	 *
	 * @param array $sanitization_results {
	 *     Results of sanitizing a document, as returned by AMP_Content_Sanitizer::sanitize_document().
	 *
	 *     @type array                $scripts     Scripts.
	 *     @type array                $stylesheets Stylesheets.
	 *     @type AMP_Base_Sanitizer[] $sanitizers  Sanitizers.
	 * }
	 * @return array Validate response data.
	 */
	public static function get_validate_response_data( $sanitization_results ) {
		$data = [
			'results'        => self::$validation_results,
			'queried_object' => null,
			'url'            => amp_get_current_url(),
		];

		$queried_object = get_queried_object();
		if ( $queried_object ) {
			$data['queried_object'] = [];
			$queried_object_id      = get_queried_object_id();
			if ( $queried_object_id ) {
				$data['queried_object']['id'] = $queried_object_id;
			}
			if ( $queried_object instanceof WP_Post ) {
				$data['queried_object']['type'] = 'post';
			} elseif ( $queried_object instanceof WP_Term ) {
				$data['queried_object']['type'] = 'term';
			} elseif ( $queried_object instanceof WP_User ) {
				$data['queried_object']['type'] = 'user';
			} elseif ( $queried_object instanceof WP_Post_Type ) {
				$data['queried_object']['type'] = 'post_type';
			}
		}

		/**
		 * Sanitizers
		 *
		 * @var AMP_Base_Sanitizer[] $sanitizers
		 */
		$sanitizers = $sanitization_results['sanitizers'];
		foreach ( $sanitizers as $class_name => $sanitizer ) {
			$sanitizer_data = $sanitizer->get_validate_response_data();

			$conflicting_keys = array_intersect( array_keys( $sanitizer_data ), array_keys( $data ) );
			if ( ! empty( $conflicting_keys ) ) {
				_doing_it_wrong(
					esc_html( "$class_name::get_validate_response_data" ),
					esc_html( 'Method is returning array with conflicting keys: ' . implode( ', ', $conflicting_keys ) ),
					'1.5'
				);
			} else {
				$data = array_merge( $data, $sanitizer_data );
			}
		}

		return $data;
	}

	/**
	 * Remove source stack comments which appear inside of script and style tags.
	 *
	 * HTML comments that appear inside of script and style elements get parsed as text content. AMP does not allow
	 * such HTML comments to appear inside of CDATA, resulting in validation errors to be emitted when validating a
	 * page that happens to have source stack comments output when generating JSON data (e.g. All in One SEO).
	 * Additionally, when source stack comments are output inside of style elements the result can either be CSS
	 * parse errors or incorrect stylesheet sizes being reported due to the presence of the source stack comments.
	 * So to prevent these issues from occurring, the source stack comments need to be removed from the document prior
	 * to sanitizing.
	 *
	 * @since 1.5
	 *
	 * @param Document $dom Document.
	 */
	public static function remove_illegal_source_stack_comments( Document $dom ) {
		/**
		 * Script element.
		 *
		 * @var DOMText $text
		 */
		foreach ( $dom->xpath->query( '//text()[ contains( ., "<!--amp-source-stack" ) ][ parent::script or parent::style ]' ) as $text ) {
			$text->nodeValue = preg_replace( '#<!--/?amp-source-stack.*?-->#s', '', $text->nodeValue );
		}
	}

	/**
	 * Finalize validation.
	 *
	 * @see AMP_Validation_Manager::add_admin_bar_menu_items()
	 *
	 * @param Document $dom Document.
	 * @return bool Whether the document should be displayed to the user.
	 */
	public static function finalize_validation( Document $dom ) {
		$total_count      = 0;
		$kept_count       = 0;
		$unreviewed_count = 0;
		foreach ( self::$validation_results as $validation_result ) {
			$sanitization = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $validation_result['error'] );
			if ( ! ( (int) $sanitization['status'] & AMP_Validation_Error_Taxonomy::ACCEPTED_VALIDATION_ERROR_BIT_MASK ) ) {
				$kept_count++;
			}
			if ( ! ( (int) $sanitization['status'] & AMP_Validation_Error_Taxonomy::ACKNOWLEDGED_VALIDATION_ERROR_BIT_MASK ) ) {
				$unreviewed_count++;
			}
			$total_count++;
		}

		/*
		 * Override AMP status in admin bar set in \AMP_Validation_Manager::add_admin_bar_menu_items()
		 * when there are validation errors which have not been explicitly accepted.
		 */
		if ( is_admin_bar_showing() && self::$amp_admin_bar_item_added && $total_count > 0 ) {
			self::update_admin_bar_item( $dom, $total_count, $kept_count, $unreviewed_count );
		}

		// If no invalid markup is kept, then the page should definitely be displayed to the user.
		if ( 0 === $kept_count ) {
			return true;
		}

		// When overrides are present, go ahead and display to the user.
		if ( ! empty( self::$validation_error_status_overrides ) ) {
			return true;
		}

		/*
		 * In AMP-first, strip html@amp attribute to prevent GSC from complaining about a validation error
		 * already surfaced inside of WordPress. This is intended to not serve dirty AMP, but rather a
		 * non-AMP document (intentionally not valid AMP) that contains the AMP runtime and AMP components.
		 *
		 * Otherwise, if in Paired AMP then redirect to the non-AMP version if the current user isn't an user who
		 * can manage validation error statuses (access developer tools) and change the AMP options for the template
		 * mode. Such users should be able to see kept invalid markup on the AMP page even though it is invalid.
		 */
		if ( amp_is_canonical() ) {
			$dom->documentElement->removeAttribute( Attribute::AMP );
			$dom->documentElement->removeAttribute( Attribute::AMP_EMOJI );
			$dom->documentElement->removeAttribute( Attribute::AMP_EMOJI_ALT );

			/*
			 * Make sure that document.write() is disabled to prevent dynamically-added content (such as added
			 * via amp-live-list) from wiping out the page by introducing any scripts that call this function.
			 */
			$script = $dom->createElement( Tag::SCRIPT );
			$script->appendChild( $dom->createTextNode( 'document.addEventListener( "DOMContentLoaded", function() { document.write = function( text ) { throw new Error( "[AMP-WP] Prevented document.write() call with: "  + text ); }; } );' ) );
			$dom->head->appendChild( $script );
			return true;
		}

		// Otherwise, since it is in a paired mode, only allow showing the dirty AMP page if the user is authorized.
		// If not, normally the result is redirection to the non-AMP version.
		return self::has_cap() || is_customize_preview();
	}

	/**
	 * Override AMP status in admin bar set in \AMP_Validation_Manager::add_admin_bar_menu_items()
	 * when there are validation errors which have not been explicitly accepted.
	 *
	 * @param Document $dom              Document.
	 * @param int      $total_count      Total count of validation errors (more than 0).
	 * @param int      $kept_count       Count of validation errors with invalid markup kept.
	 * @param int      $unreviewed_count Count of unreviewed validation errors.
	 */
	private static function update_admin_bar_item( Document $dom, $total_count, $kept_count, $unreviewed_count ) {
		$parent_menu_item = $dom->getElementById( 'wp-admin-bar-amp' );
		if ( ! $parent_menu_item instanceof DOMElement ) {
			return;
		}

		$parent_menu_link = $dom->xpath->query( './a[ @href ]', $parent_menu_item )->item( 0 );
		$admin_bar_icon   = $dom->xpath->query( './span[ @id = "amp-admin-bar-item-status-icon" ]', $parent_menu_link )->item( 0 );
		$validate_link    = $dom->xpath->query( './/li[ @id = "wp-admin-bar-amp-validity" ]/a[ @href ]', $parent_menu_item )->item( 0 );
		if ( ! $parent_menu_link instanceof DOMElement || ! $admin_bar_icon instanceof DOMElement || ! $validate_link instanceof DOMElement ) {
			return;
		}

		/*
		 * When in Paired AMP, non-administrators accessing the AMP version will get redirected to the non-AMP version
		 * if there are is kept invalid markup. In Paired AMP, the AMP plugin never intends to advertise the availability
		 * of dirty AMP pages. However, in AMP-first (Standard mode), there is no non-AMP version to redirect to, so
		 * kept invalid markup does not cause redirection but rather the `amp` attribute is removed from the AMP page
		 * to serve an intentionally invalid AMP page with the AMP runtime loaded which is exempted from AMP validation
		 * (and excluded from being indexed as an AMP page). So this is why the first conditional will only show the
		 * error icon for kept markup when _not_ AMP-first. This will only be displayed to administrators who are directly
		 * accessing the AMP version. Otherwise, if there is no kept invalid markup _or_ it is AMP-first, then the AMP
		 * admin bar item will be updated to show if there are any unreviewed validation errors (regardless of whether
		 * they are kept or removed).
		 */
		if ( $kept_count > 0 && ! amp_is_canonical() ) {
			$admin_bar_icon->setAttribute( 'class', 'ab-icon amp-icon ' . Icon::INVALID );
		} elseif ( $unreviewed_count > 0 || $kept_count > 0 ) {
			$admin_bar_icon->setAttribute( 'class', 'ab-icon amp-icon ' . Icon::WARNING );
		}

		// Update the text of the link to reflect the status of the validation error(s).
		$items = [];
		if ( $unreviewed_count > 0 ) {
			if ( $unreviewed_count === $total_count ) {
				/* translators: text is describing validation issue(s) */
				$items[] = _n(
					'unreviewed',
					'all unreviewed',
					$unreviewed_count,
					'amp'
				);
			} else {
				$items[] = sprintf(
					/* translators: %s the total count of unreviewed validation errors */
					_n(
						'%s unreviewed',
						'%s unreviewed',
						$unreviewed_count,
						'amp'
					),
					number_format_i18n( $unreviewed_count )
				);
			}
		}
		if ( $kept_count > 0 ) {
			if ( $kept_count === $total_count ) {
				/* translators: text is describing validation issue(s) */
				$items[] = _n(
					'kept',
					'all kept',
					$kept_count,
					'amp'
				);
			} else {
				$items[] = sprintf(
					/* translators: %s the total count of unreviewed validation errors */
					_n(
						'%s kept',
						'%s kept',
						$kept_count,
						'amp'
					),
					number_format_i18n( $kept_count )
				);
			}
		}
		if ( empty( $items ) ) {
			/* translators: text is describing validation issue(s) */
			$items[] = _n(
				'reviewed',
				'all reviewed',
				$total_count,
				'amp'
			);
		}

		$text = sprintf(
			/* translators: %s is total count of validation errors */
			_n(
				'%s issue:',
				'%s issues:',
				$total_count,
				'amp'
			),
			number_format_i18n( $total_count )
		);
		$text .= ' ' . implode( ', ', $items );

		$validate_link->appendChild( $dom->createTextNode( ' ' ) );
		$small = $dom->createElement( 'small' );
		$small->setAttribute( 'style', 'font-size: smaller' );
		$small->appendChild( $dom->createTextNode( sprintf( '(%s)', $text ) ) );
		$validate_link->appendChild( $small );
	}

	/**
	 * Adds the validation callback if front-end validation is needed.
	 *
	 * @param array $sanitizers The AMP sanitizers.
	 * @return array $sanitizers The filtered AMP sanitizers.
	 */
	public static function filter_sanitizer_args( $sanitizers ) {
		foreach ( $sanitizers as &$args ) {
			$args['validation_error_callback'] = __CLASS__ . '::add_validation_error';
		}

		if ( isset( $sanitizers['AMP_Style_Sanitizer'] ) ) {
			$sanitizers['AMP_Style_Sanitizer']['should_locate_sources'] = self::$is_validate_request;

			$css_validation_errors = [];
			foreach ( self::$validation_error_status_overrides as $slug => $status ) {
				$term = AMP_Validation_Error_Taxonomy::get_term( $slug );
				if ( ! $term ) {
					continue;
				}
				$validation_error = json_decode( $term->description, true );

				$is_css_validation_error = (
					is_array( $validation_error )
					&&
					isset( $validation_error['code'] )
					&&
					in_array( $validation_error['code'], AMP_Style_Sanitizer::get_css_parser_validation_error_codes(), true )
				);
				if ( $is_css_validation_error ) {
					$css_validation_errors[ $slug ] = $status;
				}
			}
			if ( ! empty( $css_validation_errors ) ) {
				$sanitizers['AMP_Style_Sanitizer']['parsed_cache_variant'] = md5( wp_json_encode( $css_validation_errors ) );
			}
		}

		return $sanitizers;
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
		$validity = self::validate_url_and_store( $url );
		if ( is_wp_error( $validity ) ) {
			return $validity;
		}
		$validation_errors = wp_list_pluck( $validity['results'], 'error' );
		if ( is_array( $validity ) && count( $validation_errors ) > 0 ) { // @todo This should only warn when there are unaccepted validation errors.
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
	 * @param string $url The URL to validate. This need not include the amp query var.
	 * @return WP_Error|array {
	 *     Response.
	 *
	 *     @type array  $results          Validation results, where each nested array contains an error key and sanitized key.
	 *     @type string $url              Final URL that was checked or redirected to.
	 *     @type array  $queried_object   Queried object, including keys for 'type' and 'id'.
	 *     @type array  $stylesheets      Stylesheet data.
	 *     @type string $php_fatal_error  PHP fatal error which occurred during validation.
	 * }
	 */
	public static function validate_url( $url ) {

		$added_query_vars = [
			self::VALIDATE_QUERY_VAR   => self::get_amp_validate_nonce(),
			self::CACHE_BUST_QUERY_VAR => wp_rand(),
		];
		$validation_url   = add_query_arg( $added_query_vars, $url );

		$r = null;

		/** This filter is documented in wp-includes/class-http.php */
		$allowed_redirects = apply_filters( 'http_request_redirection_count', 5 );
		for ( $redirect_count = 0; $redirect_count < $allowed_redirects; $redirect_count++ ) {
			$r = wp_remote_get(
				$validation_url,
				[
					'cookies'     => wp_unslash( $_COOKIE ), // Pass along cookies so private pages and drafts can be accessed.
					'timeout'     => 15, // Increase from default of 5 to give extra time for the plugin to identify the sources for any given validation errors.
					/** This filter is documented in wp-includes/class-wp-http-streams.php */
					'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
					'redirection' => 0, // Because we're in a loop for redirection.
					'headers'     => [
						'Cache-Control' => 'no-cache',
					],
				]
			);

			// If the response is not a redirect, then break since $r is all we need.
			$response_code   = wp_remote_retrieve_response_code( $r );
			$location_header = wp_remote_retrieve_header( $r, 'Location' );
			$is_redirect     = (
				$response_code
				&&
				$response_code > 300 && $response_code < 400
				&&
				$location_header
			);
			if ( ! $is_redirect ) {
				break;
			}

			// Ensure absolute URL.
			if ( '/' === substr( $location_header, 0, 1 ) ) {
				$location_header = preg_replace( '#(^https?://[^/]+)/.*#', '$1', home_url( '/' ) ) . $location_header;
			}

			// Block redirecting to a different host.
			$location_header = wp_validate_redirect( $location_header );
			if ( ! $location_header ) {
				break;
			}

			$validation_url = add_query_arg( $added_query_vars, $location_header );
		}

		if ( is_wp_error( $r ) ) {
			return $r;
		}

		$response = trim( wp_remote_retrieve_body( $r ) );
		if ( wp_remote_retrieve_response_code( $r ) >= 400 ) {
			$data = json_decode( $response, true );
			return new WP_Error(
				is_array( $data ) && isset( $data['code'] ) ? $data['code'] : wp_remote_retrieve_response_code( $r ),
				is_array( $data ) && isset( $data['message'] ) ? $data['message'] : wp_remote_retrieve_response_message( $r )
			);
		}

		if ( wp_remote_retrieve_response_code( $r ) >= 300 ) {
			return new WP_Error(
				'http_request_failed',
				__( 'Too many redirects.', 'amp' )
			);
		}

		$url = remove_query_arg(
			array_keys( $added_query_vars ),
			$validation_url
		);

		// Strip byte order mark (BOM).
		while ( "\xEF\xBB\xBF" === substr( $response, 0, 3 ) ) {
			$response = substr( $response, 3 );
		}

		// Strip any leading whitespace.
		$response = ltrim( $response );

		// Strip HTML comments that may have been injected at the end of the response (e.g. by a caching plugin).
		$response = preg_replace( '/<!--.*?-->\s*$/s', '', $response );

		if ( '' === $response ) {
			return new WP_Error( 'white_screen_of_death' );
		}
		if ( '{' !== substr( $response, 0, 1 ) ) {
			return new WP_Error( 'response_not_json' );
		}
		$validation = json_decode( $response, true );
		if ( json_last_error() || ! isset( $validation['results'] ) || ! is_array( $validation['results'] ) ) {
			return new WP_Error( 'malformed_json_validation_errors' );
		}

		return array_merge(
			$validation,
			compact( 'url' )
		);
	}

	/**
	 * Validate URL and store result.
	 *
	 * @param string      $url  URL to validate.
	 * @param int|WP_Post $post The amp_validated_url post to update. Optional. If empty, then post is looked up by URL.
	 * @return WP_Error|array {
	 *     Error on failure, or array on success.
	 *
	 *     @type int    $post_id          ID for the amp_validated_url post.
	 *     @type array  $results          Validation results, where each nested array contains an error key and sanitized key.
	 *     @type string $url              Final URL that was checked or redirected to.
	 *     @type array  $queried_object   Queried object, including keys for 'type' and 'id'.
	 *     @type array  $stylesheets      Stylesheet data.
	 *     @type string $php_fatal_error  PHP fatal error which occurred during validation.
	 * }
	 */
	public static function validate_url_and_store( $url, $post = null ) {
		$validity = self::validate_url( $url );
		if ( $validity instanceof WP_Error ) {
			return $validity;
		}

		$args = wp_array_slice_assoc( $validity, [ 'queried_object', 'stylesheets', 'php_fatal_error' ] );
		if ( $post ) {
			$args['invalid_url_post'] = $post;
		}

		$result = AMP_Validated_URL_Post_Type::store_validation_errors(
			wp_list_pluck( $validity['results'], 'error' ),
			$validity['url'],
			$args
		);
		if ( $result instanceof WP_Error ) {
			return $result;
		}
		$validity['post_id'] = $result;
		return $validity;
	}

	/**
	 * Serialize validation error messages.
	 *
	 * In order to safely pass validation error messages through redirects with query parameters, they must be serialized
	 * with a HMAC for security. The messages contain markup so the HMAC prevents tampering.
	 *
	 * @since 1.4.2
	 * @see AMP_Validation_Manager::unserialize_validation_error_messages()
	 *
	 * @param string[] $messages Messages.
	 * @return string Serialized.
	 */
	public static function serialize_validation_error_messages( $messages ) {
		$encoded_messages = base64_encode( wp_json_encode( array_unique( $messages ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return wp_hash( $encoded_messages ) . ':' . $encoded_messages;
	}

	/**
	 * Unserialize validation error messages.
	 *
	 * @since 1.4.2
	 * @see AMP_Validation_Manager::serialize_validation_error_messages()
	 *
	 * @param string $serialized Serialized messages.
	 * @return string[]|null
	 */
	public static function unserialize_validation_error_messages( $serialized ) {
		$parts = explode( ':', $serialized, 2 );
		if ( count( $parts ) !== 2 || wp_hash( $parts[1] ) !== $parts[0] ) {
			return null;
		}
		return json_decode( base64_decode( $parts[1] ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Get error message for a validate URL failure.
	 *
	 * @param string $error_code    Error code.
	 * @param string $error_message Error message, typically technical such as from HTTP status text or cURL error message.
	 * @return string Error message with HTML markup which has had its translated strings passed through wp_kses().
	 */
	public static function get_validate_url_error_message( $error_code, $error_message = '' ) {
		$check_error_log = sprintf(
			/* translators: %1$s is link to Debugging in WordPress, %2$s is WP_DEBUG_LOG */
			__( 'Please check your server PHP error logs; to do this you may need to <a href="%1$s" target="_blank">enable</a> %2$s.', 'amp' ),
			esc_url( 'https://wordpress.org/support/article/debugging-in-wordpress/' ),
			'<code>WP_DEBUG_LOG</code>'
		);

		if ( $error_message ) {
			$error_message = rtrim( $error_message, '.' ) . '.';
		}

		$support_forum_message = sprintf(
			/* translators: %1$s: Link to support forum. %2$s: Link to new topic form in support forum. */
			__( 'If you are stuck, please search the <a href="%1$s">support forum</a> for possible related topics, or otherwise start a <a href="%2$s">new support topic</a> including the error message, the URL to your site, and your active theme/plugins.', 'amp' ),
			esc_url( 'https://wordpress.org/support/plugin/amp/' ),
			esc_url( 'https://wordpress.org/support/plugin/amp/#new-topic-0' )
		);

		$site_health_message = '';
		if ( version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
			$site_health_message .= sprintf(
				/* translators: %s is link to Site Health */
				__( 'Please check your <a href="%s">Site Health</a> to verify it can perform loopback requests.', 'amp' ),
				esc_url( admin_url( 'site-health.php' ) )
			);
			$support_forum_message .= ' ' . sprintf(
				/* translators: %s is the URL to Site Health Info. */
				__( 'Please include your <a href="%s">Site Health Info</a>.', 'amp' ),
				esc_url( admin_url( 'site-health.php?tab=debug' ) )
			);
		}

		$implode_non_empty_strings_with_spaces_and_sanitize = static function ( $strings ) {
			return wp_kses(
				implode( ' ', array_filter( $strings ) ),
				[
					'a'    => array_fill_keys( [ 'href', 'target' ], true ),
					'code' => [],
				]
			);
		};

		switch ( $error_code ) {
			case 'http_request_failed':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'Failed to fetch URL to validate.', 'amp' ),
						esc_html( $error_message ),
						$site_health_message,
						$support_forum_message,
					]
				);
			case 'white_screen_of_death':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'Unable to validate URL. A white screen of death was encountered which is likely due to a PHP fatal error.', 'amp' ),
						esc_html( $error_message ),
						$check_error_log,
						$support_forum_message,
					]
				);
			case '404':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'The fetched URL was not found. It may have been deleted. If so, you can trash this.', 'amp' ),
						esc_html( $error_message ),
						$support_forum_message,
					]
				);
			case '500':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'An internal server error occurred when fetching the URL for validation.', 'amp' ),
						esc_html( $error_message ),
						$check_error_log,
						$support_forum_message,
					]
				);
			case 'fatal_error_during_validation':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'A PHP fatal error occurred while validating the URL. This may indicate either a bug in theme/plugin code or it may be due to an issue in the AMP plugin itself.', 'amp' ),
						defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY
							? esc_html__( 'The error details appear below.', 'amp' )
							/* translators: %s is WP_DEBUG_DISPLAY */
							: $check_error_log . ' ' . wp_kses_post( sprintf( __( 'Alternatively, you may enable %s to show the error details below.', 'amp' ), '<code>WP_DEBUG_DISPLAY</code>' ) ),
						$support_forum_message,
					]
				);
			case 'response_not_json':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'URL validation failed due to the AMP validation request not returning JSON data. This may be due to a PHP fatal error occurring.', 'amp' ),
						esc_html( $error_message ),
						$check_error_log,
						$support_forum_message,
					]
				);
			case 'malformed_json_validation_errors':
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						esc_html__( 'URL validation failed due to unexpected JSON in AMP validation response.', 'amp' ),
						esc_html( $error_message ),
						$support_forum_message,
					]
				);
			default:
				return $implode_non_empty_strings_with_spaces_and_sanitize(
					[
						/* translators: %s is error code */
						esc_html( sprintf( __( 'URL validation failed. Error code: %s.', 'amp' ), $error_code ) ),
						esc_html( $error_message ),
						$support_forum_message,
					]
				);
		}
	}

	/**
	 * On activating a plugin, display a notice if a plugin causes an AMP validation error.
	 *
	 * @return void
	 */
	public static function print_plugin_notice() {
		global $pagenow;
		if ( ( 'plugins.php' === $pagenow ) && ( ! empty( $_GET['activate'] ) || ! empty( $_GET['activate-multi'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$validation_errors = get_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY );
			if ( empty( $validation_errors ) || ! is_array( $validation_errors ) ) {
				return;
			}
			delete_transient( self::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY );
			$errors          = AMP_Validation_Error_Taxonomy::summarize_validation_errors( $validation_errors );
			$invalid_plugins = isset( $errors[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ]['plugin'] ) ? array_unique( $errors[ AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT ]['plugin'] ) : null;
			if ( isset( $invalid_plugins ) ) {
				$reported_plugins = [];
				$plugin_registry  = Services::get( 'plugin_registry' );
				foreach ( $invalid_plugins as $plugin_slug ) {
					$plugin_data        = $plugin_registry->get_plugin_from_slug( $plugin_slug );
					$plugin_name        = is_array( $plugin_data ) ? $plugin_data['data']['Name'] : $plugin_slug;
					$reported_plugins[] = $plugin_name;
				}

				$more_details_link = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							'post_type',
							AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
							admin_url( 'edit.php' )
						)
					),
					__( 'More details', 'amp' )
				);

				printf(
					'<div class="notice notice-warning is-dismissible"><p>%s %s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>',
					esc_html(
						sprintf(
							/* translators: %s is comma-separated list of one or more plugins */
							_n(
								'Warning: The following plugin may be incompatible with AMP: %s.',
								'Warning: The following plugins may be incompatible with AMP: %s.',
								count( $invalid_plugins ),
								'amp'
							),
							implode( ', ', $reported_plugins ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						)
					),
					$more_details_link, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html__( 'Dismiss this notice.', 'amp' )
				);
			}
		}
	}

	/**
	 * Enqueues the block validation script.
	 *
	 * @return void
	 */
	public static function enqueue_block_validation() {
		/*
		 * The AMP_Validation_Manager::post_supports_validation() method is not being used here because
		 * a post's status for validation checking can change during the life of the editor, such as when
		 * the user toggles AMP back on after having turned it off, and then gets the validation
		 * warnings appearing due to the amp-block-validation having been enqueued already.
		 */
		if ( ! self::get_dev_tools_user_access()->is_user_enabled() ) {
			return;
		}

		$slug = 'amp-block-validation';

		$asset_file   = AMP__DIR__ . '/assets/js/' . $slug . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			$slug,
			amp_get_asset_url( "js/{$slug}.js" ),
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			$slug,
			amp_get_asset_url( "css/{$slug}.css" ),
			false,
			AMP__VERSION
		);

		wp_styles()->add_data( $slug, 'rtl', 'replace' );

		$data = [
			'isSanitizationAutoAccepted' => self::is_sanitization_auto_accepted(),
		];

		wp_localize_script(
			$slug,
			'ampBlockValidation',
			$data
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $slug, 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			$translations = wp_json_encode( $locale_data );

			wp_add_inline_script(
				$slug,
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'after'
			);
		}
	}
}
