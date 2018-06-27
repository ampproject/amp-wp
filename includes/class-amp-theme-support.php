<?php
/**
 * Class AMP_Theme_Support
 *
 * @package AMP
 */

/**
 * Class AMP_Theme_Support
 *
 * Callbacks for adding AMP-related things when theme support is added.
 */
class AMP_Theme_Support {

	/**
	 * Replaced with the necessary scripts depending on components used in output.
	 *
	 * @var string
	 */
	const SCRIPTS_PLACEHOLDER = '<!-- AMP:SCRIPTS_PLACEHOLDER -->';

	/**
	 * Sanitizer classes.
	 *
	 * @var array
	 */
	protected static $sanitizer_classes = array();

	/**
	 * Embed handlers.
	 *
	 * @var AMP_Base_Embed_Handler[]
	 */
	protected static $embed_handlers = array();

	/**
	 * Template types.
	 *
	 * @var array
	 */
	protected static $template_types = array(
		'paged', // Deprecated.
		'index',
		'404',
		'archive',
		'author',
		'category',
		'tag',
		'taxonomy',
		'date',
		'home',
		'front_page',
		'page',
		'search',
		'single',
		'embed',
		'singular',
		'attachment',
	);

	/**
	 * AMP-specific query vars that were purged.
	 *
	 * @since 0.7
	 * @see AMP_Theme_Support::purge_amp_query_vars()
	 * @var string[]
	 */
	public static $purged_amp_query_vars = array();

	/**
	 * Headers sent (or attempted to be sent).
	 *
	 * @since 0.7
	 * @see AMP_Theme_Support::send_header()
	 * @var array[]
	 */
	public static $headers_sent = array();

	/**
	 * Whether output buffering has started.
	 *
	 * @since 0.7
	 * @var bool
	 */
	protected static $is_output_buffering = false;

	/**
	 * Initialize.
	 */
	public static function init() {
		if ( ! current_theme_supports( 'amp' ) ) {
			return;
		}

		AMP_Validation_Utils::init();

		self::purge_amp_query_vars();
		self::handle_xhr_request();

		require_once AMP__DIR__ . '/includes/amp-post-template-actions.php';

		// Validate theme support usage.
		$support = get_theme_support( 'amp' );
		if ( WP_DEBUG && is_array( $support ) ) {
			$args = array_shift( $support );
			if ( ! is_array( $args ) ) {
				trigger_error( esc_html__( 'Expected AMP theme support arg to be array.', 'amp' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			} elseif ( count( array_diff( array_keys( $args ), array( 'template_dir', 'available_callback', 'comments_live_list' ) ) ) !== 0 ) {
				trigger_error( esc_html__( 'Expected AMP theme support to only have template_dir and/or available_callback.', 'amp' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			}
		}

		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );

		/*
		 * Note that wp action is use instead of template_redirect because some themes/plugins output
		 * the response at this action and then short-circuit with exit. So this is why the the preceding
		 * action to template_redirect--the wp action--is used instead.
		 */
		add_action( 'wp', array( __CLASS__, 'finish_init' ), PHP_INT_MAX );
	}

	/**
	 * Finish initialization once query vars are set.
	 *
	 * @since 0.7
	 */
	public static function finish_init() {
		if ( ! is_amp_endpoint() ) {
			// Add amphtml link when paired mode is available.
			if ( self::is_paired_available() ) {
				amp_add_frontend_actions(); // @todo This function is poor in how it requires a file that then does add_action().
				if ( ! has_action( 'wp_head', 'amp_frontend_add_canonical' ) ) {
					add_action( 'wp_head', 'amp_frontend_add_canonical' );
				}
			}
			return;
		}

		if ( amp_is_canonical() ) {
			self::redirect_canonical_amp();
		} else {
			self::register_paired_hooks();
		}

		self::add_hooks();
		self::$sanitizer_classes = amp_get_content_sanitizers();
		self::$embed_handlers    = self::register_content_embed_handlers();
	}

	/**
	 * Redirect to canonical URL if the AMP URL was loaded, since canonical is now AMP.
	 *
	 * @since 0.7
	 */
	public static function redirect_canonical_amp() {
		if ( false !== get_query_var( amp_get_slug(), false ) ) { // Because is_amp_endpoint() now returns true if amp_is_canonical().
			$url = preg_replace( '#^(https?://.+?)(/.*)$#', '$1', home_url( '/' ) );
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$url .= wp_unslash( $_SERVER['REQUEST_URI'] );
			}

			$url = amp_remove_endpoint( $url );

			wp_safe_redirect( $url, 302 ); // Temporary redirect because canonical may change in future.
			exit;
		}
	}

	/**
	 * Determines whether paired mode is available.
	 *
	 * When 'amp' theme support has not been added or canonical mode is enabled, then this returns false.
	 * Returns true when there is a template_dir defined in theme support, and if a defined available_callback
	 * returns true.
	 *
	 * @return bool Whether available.
	 */
	public static function is_paired_available() {
		$support = get_theme_support( 'amp' );
		if ( empty( $support ) || amp_is_canonical() ) {
			return false;
		}

		if ( is_singular() && ! post_supports_amp( get_queried_object() ) ) {
			return false;
		}

		$args = array_shift( $support );

		if ( isset( $args['available_callback'] ) && is_callable( $args['available_callback'] ) ) {
			return call_user_func( $args['available_callback'] );
		}
		return true;
	}

	/**
	 * Determine whether the user is in the Customizer preview iframe.
	 *
	 * @since 0.7
	 *
	 * @return bool Whether in Customizer preview iframe.
	 */
	public static function is_customize_preview_iframe() {
		global $wp_customize;
		return is_customize_preview() && $wp_customize->get_messenger_channel();
	}

	/**
	 * Register hooks for paired mode.
	 */
	public static function register_paired_hooks() {
		foreach ( self::$template_types as $template_type ) {
			add_filter( "{$template_type}_template_hierarchy", array( __CLASS__, 'filter_paired_template_hierarchy' ) );
		}
		add_filter( 'template_include', array( __CLASS__, 'filter_paired_template_include' ), 100 );
	}

	/**
	 * Register hooks.
	 */
	public static function add_hooks() {

		// Remove core actions which are invalid AMP.
		remove_action( 'wp_head', 'wp_post_preview_js', 1 );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		// Prevent MediaElement.js scripts/styles from being enqueued.
		add_filter( 'wp_video_shortcode_library', function() {
			return 'amp';
		} );
		add_filter( 'wp_audio_shortcode_library', function() {
			return 'amp';
		} );

		/*
		 * Add additional markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
		 * Note that the meta[name=viewport] is not added here because a theme may want to define one with additional
		 * properties than included in the default configuration. If a theme doesn't include one, then the meta viewport
		 * will be added when output buffering is finished. Note that meta charset _is_ output here because the output
		 * buffer will need it to parse the document properly, and it must be exactly as is to be valid AMP. Nevertheless,
		 * in this case too we should defer to the theme as well to output the meta charset because it is possible the
		 * install is not on utf-8 and we may need to do a encoding conversion.
		 */
		add_action( 'wp_print_styles', array( __CLASS__, 'print_amp_styles' ), 0 ); // Print boilerplate before theme and plugin stylesheets.
		add_action( 'wp_head', 'amp_add_generator_metadata', 20 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_customize_preview_scripts' ), 1000 );
		add_filter( 'customize_partial_render', array( __CLASS__, 'filter_customize_partial_render' ) );

		add_action( 'wp_footer', 'amp_print_analytics' );

		/*
		 * Disable admin bar because admin-bar.css (28K) and Dashicons (48K) alone
		 * combine to surpass the 50K limit imposed for the amp-custom style.
		 */
		add_filter( 'show_admin_bar', '__return_false', 100 );

		/*
		 * Start output buffering at very low priority for sake of plugins and themes that use template_redirect
		 * instead of template_include.
		 */
		$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.PHP.NewConstants.php_int_minFound
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffering' ), $priority );

		// Add validation hooks *after* output buffering has started for the response.
		if ( AMP_Validation_Utils::should_validate_response() ) {
			AMP_Validation_Utils::add_validation_hooks();
		}

		// Commenting hooks.
		add_filter( 'wp_list_comments_args', array( __CLASS__, 'set_comments_walker' ), PHP_INT_MAX );
		add_filter( 'comment_form_defaults', array( __CLASS__, 'filter_comment_form_defaults' ) );
		add_filter( 'comment_reply_link', array( __CLASS__, 'filter_comment_reply_link' ), 10, 4 );
		add_filter( 'cancel_comment_reply_link', array( __CLASS__, 'filter_cancel_comment_reply_link' ), 10, 3 );
		add_action( 'comment_form', array( __CLASS__, 'amend_comment_form' ), 100 );
		remove_action( 'comment_form', 'wp_comment_form_unfiltered_html_nonce' );
		add_filter( 'wp_kses_allowed_html', array( __CLASS__, 'whitelist_layout_in_wp_kses_allowed_html' ), 10 );

		// @todo Add character conversion.
	}

	/**
	 * Remove query vars that come in requests such as for amp-live-list.
	 *
	 * WordPress should generally not respond differently to requests when these parameters
	 * are present. In some cases, when a query param such as __amp_source_origin is present
	 * then it would normally get included into pagination links generated by get_pagenum_link().
	 * The whitelist sanitizer empties out links that contain this string as it matches the
	 * blacklisted_value_regex. So by preemptively scrubbing any reference to these query vars
	 * we can ensure that WordPress won't end up referencing them in any way.
	 *
	 * @since 0.7
	 */
	public static function purge_amp_query_vars() {
		$query_vars = array(
			'__amp_source_origin',
			'_wp_amp_action_xhr_converted',
			'amp_latest_update_time',
			'amp_last_check_time',
		);

		// Scrub input vars.
		foreach ( $query_vars as $query_var ) {
			if ( ! isset( $_GET[ $query_var ] ) ) { // phpcs:ignore
				continue;
			}
			self::$purged_amp_query_vars[ $query_var ] = wp_unslash( $_GET[ $query_var ] ); // phpcs:ignore
			unset( $_REQUEST[ $query_var ], $_GET[ $query_var ] );
			$scrubbed = true;
		}

		if ( isset( $scrubbed ) ) {
			$build_query = function( $query ) use ( $query_vars ) {
				$pattern = '/^(' . join( '|', $query_vars ) . ')(?==|$)/';
				$pairs   = array();
				foreach ( explode( '&', $query ) as $pair ) {
					if ( ! preg_match( $pattern, $pair ) ) {
						$pairs[] = $pair;
					}
				}
				return join( '&', $pairs );
			};

			// Scrub QUERY_STRING.
			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$_SERVER['QUERY_STRING'] = $build_query( $_SERVER['QUERY_STRING'] );
			}

			// Scrub REQUEST_URI.
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				list( $path, $query ) = explode( '?', $_SERVER['REQUEST_URI'], 2 );

				$pairs                  = $build_query( $query );
				$_SERVER['REQUEST_URI'] = $path;
				if ( ! empty( $pairs ) ) {
					$_SERVER['REQUEST_URI'] .= "?{$pairs}";
				}
			}
		}
	}

	/**
	 * Send an HTTP response header.
	 *
	 * This largely exists to facilitate unit testing but it also provides a better interface for sending headers.
	 *
	 * @since 0.7.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 * @param array  $args {
	 *     Args to header().
	 *
	 *     @type bool $replace     Whether to replace a header previously sent. Default true.
	 *     @type int  $status_code Status code to send with the sent header.
	 * }
	 * @return bool Whether the header was sent.
	 */
	public static function send_header( $name, $value, $args = array() ) {
		$args = array_merge(
			array(
				'replace'     => true,
				'status_code' => null,
			),
			$args
		);

		self::$headers_sent[] = array_merge( compact( 'name', 'value' ), $args );
		if ( headers_sent() ) {
			return false;
		}

		header(
			sprintf( '%s: %s', $name, $value ),
			$args['replace'],
			$args['status_code']
		);
		return true;
	}

	/**
	 * Hook into a POST form submissions, such as the comment form or some other form submission.
	 *
	 * @since 0.7.0
	 */
	public static function handle_xhr_request() {
		$is_amp_xhr = (
			! empty( self::$purged_amp_query_vars['_wp_amp_action_xhr_converted'] )
			&&
			! empty( self::$purged_amp_query_vars['__amp_source_origin'] )
			&&
			( ! empty( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] )
		);
		if ( ! $is_amp_xhr ) {
			return;
		}

		// Send AMP response header.
		$origin = wp_validate_redirect( wp_sanitize_redirect( esc_url_raw( self::$purged_amp_query_vars['__amp_source_origin'] ) ) );
		if ( $origin ) {
			self::send_header( 'AMP-Access-Control-Allow-Source-Origin', $origin, array( 'replace' => true ) );
		}

		// Intercept POST requests which redirect.
		add_filter( 'wp_redirect', array( __CLASS__, 'intercept_post_request_redirect' ), PHP_INT_MAX );

		// Add special handling for redirecting after comment submission.
		add_filter( 'comment_post_redirect', array( __CLASS__, 'filter_comment_post_redirect' ), PHP_INT_MAX, 2 );

		// Add die handler for AMP error display, most likely due to problem with comment.
		add_filter( 'wp_die_handler', function() {
			return array( __CLASS__, 'handle_wp_die' );
		} );

	}

	/**
	 * Strip tags that are not allowed in amp-mustache.
	 *
	 * @since 0.7.0
	 *
	 * @param string $text Text to sanitize.
	 * @return string Sanitized text.
	 */
	protected static function wp_kses_amp_mustache( $text ) {
		$amp_mustache_allowed_html_tags = array( 'strong', 'b', 'em', 'i', 'u', 's', 'small', 'mark', 'del', 'ins', 'sup', 'sub' );
		return wp_kses( $text, array_fill_keys( $amp_mustache_allowed_html_tags, array() ) );
	}

	/**
	 * Handle comment_post_redirect to ensure page reload is done when comments_live_list is not supported, while sending back a success message when it is.
	 *
	 * @since 0.7.0
	 *
	 * @param string     $url     Comment permalink to redirect to.
	 * @param WP_Comment $comment Posted comment.
	 * @return string URL.
	 */
	public static function filter_comment_post_redirect( $url, $comment ) {
		$theme_support = get_theme_support( 'amp' );

		// Cause a page refresh if amp-live-list is not implemented for comments via add_theme_support( 'amp', array( 'comments_live_list' => true ) ).
		if ( empty( $theme_support[0]['comments_live_list'] ) ) {
			/*
			 * Add the comment ID to the URL to force AMP to refresh the page.
			 * This is ideally a temporary workaround to deal with https://github.com/ampproject/amphtml/issues/14170
			 */
			$url = add_query_arg( 'comment', $comment->comment_ID, $url );

			// Pass URL along to wp_redirect().
			return $url;
		}

		// Create a success message to display to the user.
		if ( '1' === (string) $comment->comment_approved ) {
			$message = __( 'Your comment has been posted.', 'amp' );
		} else {
			$message = __( 'Your comment is awaiting moderation.', 'default' ); // Note core string re-use.
		}

		/**
		 * Filters the message when comment submitted success message when
		 *
		 * @since 0.7
		 */
		$message = apply_filters( 'amp_comment_posted_message', $message, $comment );

		// Message will be shown in template defined by AMP_Theme_Support::amend_comment_form().
		wp_send_json( array(
			'message' => self::wp_kses_amp_mustache( $message ),
		) );
	}

	/**
	 * New error handler for AMP form submission.
	 *
	 * @since 0.7.0
	 * @see wp_die()
	 *
	 * @param WP_Error|string  $error The error to handle.
	 * @param string|int       $title Optional. Error title. If `$message` is a `WP_Error` object,
	 *                                error data with the key 'title' may be used to specify the title.
	 *                                If `$title` is an integer, then it is treated as the response
	 *                                code. Default empty.
	 * @param string|array|int $args {
	 *     Optional. Arguments to control behavior. If `$args` is an integer, then it is treated
	 *     as the response code. Default empty array.
	 *
	 *     @type int $response The HTTP response code. Default 200 for Ajax requests, 500 otherwise.
	 * }
	 */
	public static function handle_wp_die( $error, $title = '', $args = array() ) {
		if ( is_int( $title ) ) {
			$status_code = $title;
		} elseif ( is_int( $args ) ) {
			$status_code = $args;
		} elseif ( is_array( $args ) && isset( $args['response'] ) ) {
			$status_code = $args['response'];
		} else {
			$status_code = 500;
		}
		status_header( $status_code );

		if ( is_wp_error( $error ) ) {
			$error = $error->get_error_message();
		}

		// Message will be shown in template defined by AMP_Theme_Support::amend_comment_form().
		wp_send_json( array(
			'error' => self::wp_kses_amp_mustache( $error ),
		) );
	}

	/**
	 * Intercept the response to a POST request.
	 *
	 * @since 0.7.0
	 * @see wp_redirect()
	 *
	 * @param string $location The location to redirect to.
	 */
	public static function intercept_post_request_redirect( $location ) {

		// Make sure relative redirects get made absolute.
		$parsed_location = array_merge(
			array(
				'scheme' => 'https',
				'host'   => wp_parse_url( home_url(), PHP_URL_HOST ),
				'path'   => isset( $_SERVER['REQUEST_URI'] ) ? strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ) : '/',
			),
			wp_parse_url( $location )
		);

		$absolute_location = '';
		if ( 'https' === $parsed_location['scheme'] ) {
			$absolute_location .= $parsed_location['scheme'] . ':';
		}
		$absolute_location .= '//' . $parsed_location['host'];
		if ( isset( $parsed_location['port'] ) ) {
			$absolute_location .= ':' . $parsed_location['port'];
		}
		$absolute_location .= $parsed_location['path'];
		if ( isset( $parsed_location['query'] ) ) {
			$absolute_location .= '?' . $parsed_location['query'];
		}
		if ( isset( $parsed_location['fragment'] ) ) {
			$absolute_location .= '#' . $parsed_location['fragment'];
		}

		self::send_header( 'AMP-Redirect-To', $absolute_location );
		self::send_header( 'Access-Control-Expose-Headers', 'AMP-Redirect-To' );

		wp_send_json_success();
	}

	/**
	 * Register/override widgets.
	 *
	 * @global WP_Widget_Factory
	 * @return void
	 */
	public static function register_widgets() {
		global $wp_widget_factory;
		foreach ( $wp_widget_factory->widgets as $registered_widget ) {
			$registered_widget_class_name = get_class( $registered_widget );
			if ( ! preg_match( '/^WP_Widget_(.+)$/', $registered_widget_class_name, $matches ) ) {
				continue;
			}
			$amp_class_name = 'AMP_Widget_' . $matches[1];
			if ( ! class_exists( $amp_class_name ) || is_a( $amp_class_name, $registered_widget_class_name ) ) {
				continue;
			}

			unregister_widget( $registered_widget_class_name );
			register_widget( $amp_class_name );
		}
	}

	/**
	 * Register content embed handlers.
	 *
	 * This was copied from `AMP_Content::register_embed_handlers()` due to being a private method
	 * and due to `AMP_Content` not being well suited for use in AMP canonical.
	 *
	 * @see AMP_Content::register_embed_handlers()
	 * @global int $content_width
	 * @return AMP_Base_Embed_Handler[] Handlers.
	 */
	public static function register_content_embed_handlers() {
		global $content_width;

		$embed_handlers = array();
		foreach ( amp_get_content_embed_handlers() as $embed_handler_class => $args ) {

			/**
			 * Embed handler.
			 *
			 * @type AMP_Base_Embed_Handler $embed_handler
			 */
			$embed_handler = new $embed_handler_class( array_merge(
				array(
					'content_max_width' => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH, // Back-compat.
				),
				$args
			) );

			if ( ! is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) ) {
				/* translators: %s is embed handler */
				_doing_it_wrong( __METHOD__, esc_html( sprintf( __( 'Embed Handler (%s) must extend `AMP_Embed_Handler`', 'amp' ), $embed_handler_class ) ), '0.1' );
				continue;
			}

			$embed_handler->register_embed();
			$embed_handlers[] = $embed_handler;
		}

		return $embed_handlers;
	}

	/**
	 * Add the comments template placeholder marker
	 *
	 * @param array $args the args for the comments list..
	 * @return array Args to return.
	 */
	public static function set_comments_walker( $args ) {
		$amp_walker     = new AMP_Comment_Walker();
		$args['walker'] = $amp_walker;
		return $args;
	}

	/**
	 * Adds the form submit success and fail templates.
	 */
	public static function amend_comment_form() {
		?>
		<?php if ( is_singular() && ! amp_is_canonical() ) : ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( amp_get_permalink( get_the_ID() ) ); ?>">
		<?php endif; ?>

		<div submit-success>
			<template type="amp-mustache">
				<p>{{{message}}}</p>
			</template>
		</div>
		<div submit-error>
			<template type="amp-mustache">
				<p class="amp-comment-submit-error">{{{error}}}</p>
			</template>
		</div>
		<?php
	}

	/**
	 * Prepends template hierarchy with template_dir for AMP paired mode templates.
	 *
	 * @see get_query_template()
	 *
	 * @param array $templates Template hierarchy.
	 * @returns array Templates.
	 */
	public static function filter_paired_template_hierarchy( $templates ) {
		$support = get_theme_support( 'amp' );
		$args    = array_shift( $support );
		if ( isset( $args['template_dir'] ) ) {
			$amp_templates = array();
			foreach ( $templates as $template ) {
				$amp_templates[] = $args['template_dir'] . '/' . $template;
			}
			$templates = $amp_templates;
		}
		return $templates;
	}

	/**
	 * Redirect to the non-canonical URL when the template to include is empty.
	 *
	 * This is a failsafe in case an index.php is not located in the AMP template_dir,
	 * and the available_callback fails to omit a given request from being available in AMP.
	 *
	 * @param string $template Template to include.
	 * @return string Template to include.
	 */
	public static function filter_paired_template_include( $template ) {
		if ( empty( $template ) || ! self::is_paired_available() ) {
			wp_safe_redirect( self::get_current_canonical_url(), 302 ); // Temporary redirect because support may come later.
			exit;
		}
		return $template;
	}

	/**
	 * Get canonical URL for current request.
	 *
	 * @see rel_canonical()
	 * @global WP $wp
	 * @global WP_Rewrite $wp_rewrite
	 * @link https://www.ampproject.org/docs/reference/spec#canon.
	 * @link https://core.trac.wordpress.org/ticket/18660
	 *
	 * @return string Canonical non-AMP URL.
	 */
	public static function get_current_canonical_url() {
		global $wp, $wp_rewrite;

		$url = null;
		if ( is_singular() ) {
			$url = wp_get_canonical_url();
		}

		// For non-singular queries, make use of the request URI and public query vars to determine canonical URL.
		if ( empty( $url ) ) {
			$added_query_vars = $wp->query_vars;
			if ( ! $wp_rewrite->permalink_structure || empty( $wp->request ) ) {
				$url = home_url( '/' );
			} else {
				$url = home_url( user_trailingslashit( $wp->request ) );
				parse_str( $wp->matched_query, $matched_query_vars );
				foreach ( $wp->query_vars as $key => $value ) {

					// Remove query vars that were matched in the rewrite rules for the request.
					if ( isset( $matched_query_vars[ $key ] ) ) {
						unset( $added_query_vars[ $key ] );
					}
				}
			}
		}

		if ( ! empty( $added_query_vars ) ) {
			$url = add_query_arg( $added_query_vars, $url );
		}

		return amp_remove_endpoint( $url );
	}

	/**
	 * Get the ID for the amp-state.
	 *
	 * @since 0.7
	 *
	 * @param int $post_id Post ID.
	 * @return string ID for amp-state.
	 */
	public static function get_comment_form_state_id( $post_id ) {
		return sprintf( 'commentform_post_%d', $post_id );
	}

	/**
	 * Filter comment form args to an element with [text] AMP binding wrap the title reply.
	 *
	 * @since 0.7
	 * @see comment_form()
	 *
	 * @param array $args Comment form args.
	 * @return array Filtered comment form args.
	 */
	public static function filter_comment_form_defaults( $args ) {
		$state_id = self::get_comment_form_state_id( get_the_ID() );

		$text_binding = sprintf(
			'%s.replyToName ? %s : %s',
			$state_id,
			str_replace(
				'%s',
				sprintf( '" + %s.replyToName + "', $state_id ),
				wp_json_encode( $args['title_reply_to'] )
			),
			wp_json_encode( $args['title_reply'] )
		);

		$args['title_reply_before'] .= sprintf(
			'<span [text]="%s">',
			esc_attr( $text_binding )
		);
		$args['cancel_reply_before'] = '</span>' . $args['cancel_reply_before'];
		return $args;
	}

	/**
	 * Modify the comment reply link for AMP.
	 *
	 * @since 0.7
	 * @see get_comment_reply_link()
	 *
	 * @param string     $link    The HTML markup for the comment reply link.
	 * @param array      $args    An array of arguments overriding the defaults.
	 * @param WP_Comment $comment The object of the comment being replied.
	 * @return string Comment reply link.
	 */
	public static function filter_comment_reply_link( $link, $args, $comment ) {

		// Continue to show default link to wp-login when user is not logged-in.
		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			return $args['before'] . $link . $args['after'];
		}

		$state_id  = self::get_comment_form_state_id( get_the_ID() );
		$tap_state = array(
			$state_id => array(
				'replyToName' => $comment->comment_author,
				'values'      => array(
					'comment_parent' => (string) $comment->comment_ID,
				),
			),
		);

		// @todo Figure out how to support add_below. Instead of moving the form, what about letting the form get a fixed position?
		$link = sprintf(
			'<a rel="nofollow" class="comment-reply-link" href="%s" on="%s" aria-label="%s">%s</a>',
			esc_attr( '#' . $args['respond_id'] ),
			esc_attr( sprintf( 'tap:AMP.setState( %s )', wp_json_encode( $tap_state ) ) ),
			esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
			$args['reply_text']
		);
		return $args['before'] . $link . $args['after'];
	}

	/**
	 * Filters the cancel comment reply link HTML.
	 *
	 * @since 0.7
	 * @see get_cancel_comment_reply_link()
	 *
	 * @param string $formatted_link The HTML-formatted cancel comment reply link.
	 * @param string $link           Cancel comment reply link URL.
	 * @param string $text           Cancel comment reply link text.
	 * @return string Cancel reply link.
	 */
	public static function filter_cancel_comment_reply_link( $formatted_link, $link, $text ) {
		unset( $formatted_link, $link );
		if ( empty( $text ) ) {
			$text = __( 'Click here to cancel reply.', 'default' );
		}

		$state_id  = self::get_comment_form_state_id( get_the_ID() );
		$tap_state = array(
			$state_id => array(
				'replyToName' => '',
				'values'      => array(
					'comment_parent' => '0',
				),
			),
		);

		$respond_id = 'respond'; // Hard-coded in comment_form() and default value in get_comment_reply_link().
		return sprintf(
			'<a id="cancel-comment-reply-link" href="%s" %s [hidden]="%s" on="%s">%s</a>',
			esc_url( remove_query_arg( 'replytocom' ) . '#' . $respond_id ),
			isset( $_GET['replytocom'] ) ? '' : ' hidden', // phpcs:ignore
			esc_attr( sprintf( '%s.values.comment_parent == "0"', self::get_comment_form_state_id( get_the_ID() ) ) ),
			esc_attr( sprintf( 'tap:AMP.setState( %s )', wp_json_encode( $tap_state ) ) ),
			esc_html( $text )
		);
	}

	/**
	 * Print AMP boilerplate and custom styles.
	 */
	public static function print_amp_styles() {
		echo amp_get_boilerplate_code() . "\n"; // WPCS: XSS OK.
		echo "<style amp-custom></style>\n"; // This will by populated by AMP_Style_Sanitizer.
	}

	/**
	 * Ensure markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
	 *
	 * Ensure meta[charset], meta[name=viewport], and link[rel=canonical]; a the whitelist sanitizer
	 * may have removed an illegal meta[http-equiv] or meta[name=viewport]. Core only outputs a
	 * canonical URL by default if a singular post.
	 *
	 * @since 0.7
	 * @todo All of this might be better placed inside of a sanitizer.
	 *
	 * @param DOMDocument $dom Doc.
	 */
	public static function ensure_required_markup( DOMDocument $dom ) {
		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		if ( ! $head ) {
			$head = $dom->createElement( 'head' );
			$dom->documentElement->insertBefore( $head, $dom->documentElement->firstChild );
		}
		$meta_charset  = null;
		$meta_viewport = null;
		foreach ( $head->getElementsByTagName( 'meta' ) as $meta ) {
			/**
			 * Meta.
			 *
			 * @var DOMElement $meta
			 */
			if ( $meta->hasAttribute( 'charset' ) && 'utf-8' === strtolower( $meta->getAttribute( 'charset' ) ) ) { // @todo Also look for meta[http-equiv="Content-Type"]?
				$meta_charset = $meta;
			} elseif ( 'viewport' === $meta->getAttribute( 'name' ) ) {
				$meta_viewport = $meta;
			}
		}
		if ( ! $meta_charset ) {
			// Warning: This probably means the character encoding needs to be converted.
			$meta_charset = AMP_DOM_Utils::create_node( $dom, 'meta', array(
				'charset' => 'utf-8',
			) );
			$head->insertBefore( $meta_charset, $head->firstChild );
		}
		if ( ! $meta_viewport ) {
			$meta_viewport = AMP_DOM_Utils::create_node( $dom, 'meta', array(
				'name'    => 'viewport',
				'content' => 'width=device-width,minimum-scale=1',
			) );
			$head->insertBefore( $meta_viewport, $meta_charset->nextSibling );
		}
		// Prevent schema.org duplicates.
		$has_schema_org_metadata = false;
		foreach ( $head->getElementsByTagName( 'script' ) as $script ) {
			if ( 'application/ld+json' === $script->getAttribute( 'type' ) && false !== strpos( $script->nodeValue, 'schema.org' ) ) {
				$has_schema_org_metadata = true;
				break;
			}
		}
		if ( ! $has_schema_org_metadata ) {
			$script = $dom->createElement( 'script', wp_json_encode( amp_get_schemaorg_metadata() ) );
			$script->setAttribute( 'type', 'application/ld+json' );
			$head->appendChild( $script );
		}
		// Ensure rel=canonical link.
		$rel_canonical = null;
		foreach ( $head->getElementsByTagName( 'link' ) as $link ) {
			if ( 'canonical' === $link->getAttribute( 'rel' ) ) {
				$rel_canonical = $link;
				break;
			}
		}
		if ( ! $rel_canonical ) {
			$rel_canonical = AMP_DOM_Utils::create_node( $dom, 'link', array(
				'rel'  => 'canonical',
				'href' => self::get_current_canonical_url(),
			) );
			$head->appendChild( $rel_canonical );
		}
	}

	/**
	 * Dequeue Customizer assets which are not necessary outside the preview iframe.
	 *
	 * Prevent enqueueing customize-preview styles if not in customizer preview iframe.
	 * These are only needed for when there is live editing of content, such as selective refresh.
	 *
	 * @since 0.7
	 */
	public static function dequeue_customize_preview_scripts() {

		// Dequeue styles unnecessary unless in customizer preview iframe when editing (such as for edit shortcuts).
		if ( ! self::is_customize_preview_iframe() ) {
			wp_dequeue_style( 'customize-preview' );
			foreach ( wp_styles()->registered as $handle => $dependency ) {
				if ( in_array( 'customize-preview', $dependency->deps, true ) ) {
					wp_dequeue_style( $handle );
				}
			}
		}
	}

	/**
	 * Start output buffering.
	 *
	 * @since 0.7
	 * @see AMP_Theme_Support::finish_output_buffering()
	 */
	public static function start_output_buffering() {
		/*
		 * Disable the New Relic Browser agent on AMP responses.
		 * This prevents th New Relic from causing invalid AMP responses due the NREUM script it injects after the meta charset:
		 * https://docs.newrelic.com/docs/browser/new-relic-browser/troubleshooting/google-amp-validator-fails-due-3rd-party-script
		 * Sites with New Relic will need to specially configure New Relic for AMP:
		 * https://docs.newrelic.com/docs/browser/new-relic-browser/installation/monitor-amp-pages-new-relic-browser
		 */
		if ( function_exists( 'newrelic_disable_autorum' ) ) {
			newrelic_disable_autorum();
		}

		ob_start( array( __CLASS__, 'finish_output_buffering' ) );
		self::$is_output_buffering = true;
	}

	/**
	 * Determine whether output buffering has started.
	 *
	 * @since 0.7
	 * @see AMP_Theme_Support::start_output_buffering()
	 * @see AMP_Theme_Support::finish_output_buffering()
	 *
	 * @return bool Whether output buffering has started.
	 */
	public static function is_output_buffering() {
		return self::$is_output_buffering;
	}

	/**
	 * Finish output buffering.
	 *
	 * @since 0.7
	 * @see AMP_Theme_Support::start_output_buffering()
	 *
	 * @param string $response Buffered Response.
	 * @return string Processed Response.
	 */
	public static function finish_output_buffering( $response ) {
		self::$is_output_buffering = false;
		return self::prepare_response( $response );
	}

	/**
	 * Filter rendered partial to convert to AMP.
	 *
	 * @see WP_Customize_Partial::render()
	 *
	 * @param string|mixed $partial Rendered partial.
	 * @return string|mixed Filtered partial.
	 * @global int $content_width
	 */
	public static function filter_customize_partial_render( $partial ) {
		global $content_width;
		if ( is_string( $partial ) && preg_match( '/<\w/', $partial ) ) {
			$dom  = AMP_DOM_Utils::get_dom_from_content( $partial );
			$args = array(
				'content_max_width'    => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH, // Back-compat.
				'use_document_element' => false,
				'allow_dirty_styles'   => true,
				'allow_dirty_scripts'  => false,
			);
			AMP_Content_Sanitizer::sanitize_document( $dom, self::$sanitizer_classes, $args ); // @todo Include script assets in response?
			$partial = AMP_DOM_Utils::get_content_from_dom( $dom );
		}
		return $partial;
	}

	/**
	 * Process response to ensure AMP validity.
	 *
	 * @since 0.7
	 *
	 * @param string $response HTML document response. By default it expects a complete document.
	 * @param array  $args {
	 *     Args to send to the preprocessor/sanitizer.
	 *
	 *     @type callable $remove_invalid_callback Function to call whenever a node is removed due to being invalid.
	 * }
	 * @return string AMP document response.
	 * @global int $content_width
	 */
	public static function prepare_response( $response, $args = array() ) {
		global $content_width;

		/*
		 * Check if the response starts with HTML markup.
		 * Without this check, JSON responses will be erroneously corrupted,
		 * being wrapped in HTML documents.
		 */
		if ( '<' !== substr( ltrim( $response ), 0, 1 ) ) {
			return $response;
		}

		$is_validation_debug_mode = ! empty( $_REQUEST[ AMP_Validation_Utils::DEBUG_QUERY_VAR ] ); // WPCS: csrf ok.

		$args = array_merge(
			array(
				'content_max_width'       => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH, // Back-compat.
				'use_document_element'    => true,
				'allow_dirty_styles'      => self::is_customize_preview_iframe(), // Dirty styles only needed when editing (e.g. for edit shortcodes).
				'allow_dirty_scripts'     => is_customize_preview(), // Scripts are always needed to inject changeset UUID.
				'disable_invalid_removal' => $is_validation_debug_mode,
			),
			$args
		);

		/*
		 * Make sure that <meta charset> is present in output prior to parsing.
		 * Note that the meta charset is supposed to appear within the first 1024 bytes.
		 * See <https://www.w3.org/International/questions/qa-html-encoding-declarations>.
		 */
		if ( ! preg_match( '#<meta[^>]+charset=#i', substr( $response, 0, 1024 ) ) ) {
			$response = preg_replace(
				'/(<head[^>]*>)/i',
				'$1' . sprintf( '<meta charset="%s">', esc_attr( get_bloginfo( 'charset' ) ) ),
				$response,
				1
			);
		}
		$dom = AMP_DOM_Utils::get_dom( $response );

		$xpath = new DOMXPath( $dom );

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );

		if ( isset( $head ) ) {
			// Make sure scripts from the body get moved to the head.
			foreach ( $xpath->query( '//body//script[ @custom-element or @custom-template ]' ) as $script ) {
				$head->appendChild( $script );
			}
		}

		// Ensure the mandatory amp attribute is present on the html element, as otherwise it will be stripped entirely.
		if ( ! $dom->documentElement->hasAttribute( 'amp' ) && ! $dom->documentElement->hasAttribute( '⚡️' ) ) {
			$dom->documentElement->setAttribute( 'amp', '' );
		}

		$assets = AMP_Content_Sanitizer::sanitize_document( $dom, self::$sanitizer_classes, $args );

		self::ensure_required_markup( $dom );

		// @todo If 'utf-8' is not the blog charset, then we'll need to do some character encoding conversation or "entityification".
		if ( 'utf-8' !== strtolower( get_bloginfo( 'charset' ) ) ) {
			/* translators: %s is the charset of the current site */
			trigger_error( esc_html( sprintf( __( 'The database has the %s encoding when it needs to be utf-8 to work with AMP.', 'amp' ), get_bloginfo( 'charset' ) ) ), E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		}

		if ( AMP_Validation_Utils::should_validate_response() ) {
			AMP_Validation_Utils::finalize_validation( $dom, array(
				'remove_source_comments' => ! $is_validation_debug_mode,
			) );
		}

		$response  = "<!DOCTYPE html>\n";
		$response .= AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		$amp_scripts = $assets['scripts'];
		foreach ( self::$embed_handlers as $embed_handler ) {
			$amp_scripts = array_merge(
				$amp_scripts,
				$embed_handler->get_scripts()
			);
		}

		// Inject additional AMP component scripts which have been discovered by the sanitizers into the head.
		$script_tags = amp_render_scripts( $amp_scripts );
		if ( ! empty( $script_tags ) ) {
			$response = preg_replace(
				'#(?=</head>)#',
				$script_tags,
				$response,
				1
			);
		}

		return $response;
	}

	/**
	 * Adds 'data-amp-layout' to the allowed <img> attributes for wp_kses().
	 *
	 * @since 0.7
	 *
	 * @param array $context Allowed tags and their allowed attributes.
	 * @return array $context Filtered allowed tags and attributes.
	 */
	public static function whitelist_layout_in_wp_kses_allowed_html( $context ) {
		if ( ! empty( $context['img']['width'] ) && ! empty( $context['img']['height'] ) ) {
			$context['img']['data-amp-layout'] = true;
		}

		return $context;
	}

	/**
	 * Enqueue AMP assets if this is an AMP endpoint.
	 *
	 * @since 0.7
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		wp_enqueue_script( 'amp-runtime' );

		// Enqueue default styles expected by sanitizer.
		wp_enqueue_style( 'amp-default', amp_get_asset_url( 'css/amp-default.css' ), array(), AMP__VERSION );
	}
}
