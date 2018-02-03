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
	 * Replaced with the necessary styles.
	 *
	 * @var string
	 */
	const STYLES_PLACEHOLDER = '<!-- AMP:STYLES_PLACEHOLDER -->';

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
	 * Initialize.
	 */
	public static function init() {
		require_once AMP__DIR__ . '/includes/amp-post-template-actions.php';

		// Validate theme support usage.
		$support = get_theme_support( 'amp' );
		if ( WP_DEBUG && is_array( $support ) ) {
			$args = array_shift( $support );
			if ( ! is_array( $args ) ) {
				trigger_error( esc_html__( 'Expected AMP theme support arg to be array.', 'amp' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			} elseif ( count( array_diff( array_keys( $args ), array( 'template_dir', 'available_callback' ) ) ) !== 0 ) {
				trigger_error( esc_html__( 'Expected AMP theme support to only have template_dir and/or available_callback.', 'amp' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			}
		}

		if ( amp_is_canonical() ) {

			// Redirect to canonical URL if the AMP URL was loaded, since canonical is now AMP.
			if ( false !== get_query_var( AMP_QUERY_VAR, false ) ) { // Because is_amp_endpoint() now returns true if amp_is_canonical().
				wp_safe_redirect( self::get_current_canonical_url(), 302 ); // Temporary redirect because canonical may change in future.
				exit;
			}
		} else {
			self::register_paired_hooks();
		}

		self::purge_amp_query_vars(); // Note that amp_prepare_comment_post() still looks at $_GET['__amp_source_origin'].
		self::register_hooks();
		self::$embed_handlers    = self::register_content_embed_handlers();
		self::$sanitizer_classes = amp_get_content_sanitizers();
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
	public static function register_hooks() {

		// Remove core actions which are invalid AMP.
		remove_action( 'wp_head', 'wp_post_preview_js', 1 );
		remove_action( 'wp_head', 'locale_stylesheet' ); // Replaced below in add_amp_styles_placeholder() method.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_print_styles', 8 ); // Replaced below in add_amp_styles_placeholder() method.
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 ); // Replaced below in add_amp_styles_placeholder() method.
		remove_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'override_wp_styles' ), -1 );

		/*
		 * Replace core's canonical link functionality with one that outputs links for non-singular queries as well.
		 * See WP Core #18660.
		 */
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( __CLASS__, 'add_canonical_link' ), 1 );

		// Add additional markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
		add_action( 'wp_head', array( __CLASS__, 'add_meta_charset' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'add_meta_viewport' ), 5 );
		add_action( 'wp_head', array( __CLASS__, 'add_amp_styles_placeholder' ), 8 ); // Because wp_print_styles() normally happens at 8.
		add_action( 'wp_head', array( __CLASS__, 'add_amp_component_scripts' ), 10 );
		add_action( 'wp_head', 'amp_add_generator_metadata', 20 );
		add_action( 'wp_head', 'amp_print_schemaorg_metadata' );

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
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffering' ), 0 );

		add_filter( 'wp_list_comments_args', array( __CLASS__, 'amp_set_comments_walker' ), PHP_INT_MAX );
		add_filter( 'comment_form_defaults', array( __CLASS__, 'filter_comment_form_defaults' ) );
		add_filter( 'comment_reply_link', array( __CLASS__, 'filter_comment_reply_link' ), 10, 4 );
		add_filter( 'cancel_comment_reply_link', array( __CLASS__, 'filter_cancel_comment_reply_link' ), 10, 3 );
		add_action( 'comment_form', array( __CLASS__, 'add_amp_comment_form_templates' ), 100 );

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
			'amp_latest_update_time',
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
	 * Set up commenting.
	 */
	public static function setup_commenting() {
		if ( ! current_theme_supports( AMP_QUERY_VAR ) ) {
			return;
		}

		/*
		 * Temporarily force comments to be listed in descending order.
		 *
		 * The following hooks are temporary while waiting for amphtml#5396 to be resolved.
		 */
		add_filter( 'option_comment_order', function() {
			return 'desc';
		}, PHP_INT_MAX );

		add_action( 'admin_print_footer_scripts-options-discussion.php', function() {
			?>
			<div class="notice notice-info inline" id="amp-comment-notice"><p><?php echo wp_kses_post( __( 'Note: AMP does not yet <a href="https://github.com/ampproject/amphtml/issues/5396" target="_blank">support ascending</a> comments with newer entries appearing at the bottom.', 'amp' ) ); ?></p></div>
			<script>
			// Move the notice below the selector and disable selector.
			jQuery( function( $ ) {
				var orderSelect = $( '#comment_order' ),
					notice = $( '#amp-comment-notice' );
				orderSelect.prop( 'disabled', true );
				orderSelect.closest( 'fieldset' ).append( notice );
			} );
			</script>
			<?php
		} );
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
	 * Override $wp_styles as AMP_WP_Styles, ideally before first instantiated as WP_Styles.
	 *
	 * @see wp_styles()
	 * @global AMP_WP_Styles $wp_styles
	 * @return AMP_WP_Styles Instance.
	 */
	public static function override_wp_styles() {
		global $wp_styles;
		if ( ! ( $wp_styles instanceof AMP_WP_Styles ) ) {
			$wp_styles = new AMP_WP_Styles(); // WPCS: global override ok.
		}
		return $wp_styles;
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
	public static function amp_set_comments_walker( $args ) {
		$amp_walker     = new AMP_Comment_Walker();
		$args['walker'] = $amp_walker;
		// Add reverse order here as well, in case theme overrides it.
		$args['reverse_top_level'] = true;

		return $args;
	}

	/**
	 * Adds the form submit success and fail templates.
	 */
	public static function add_amp_comment_form_templates() {
		?>
		<div submit-success>
			<template type="amp-mustache">
				<?php esc_html_e( 'Your comment has been posted, but may be subject to moderation.', 'amp' ); ?>
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
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#chrs
	 */
	public static function add_meta_charset() {
		echo '<meta charset="utf-8">';
	}

	/**
	 * Print meta charset tag.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#vprt
	 */
	public static function add_meta_viewport() {
		echo '<meta name="viewport" content="width=device-width,minimum-scale=1">';
	}

	/**
	 * Print AMP script and placeholder for others.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#scrpt
	 */
	public static function add_amp_component_scripts() {
		// Replaced after output buffering with all AMP component scripts.
		echo self::SCRIPTS_PLACEHOLDER; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.XSS.EscapeOutput
	}

	/**
	 * Get canonical URL for current request.
	 *
	 * @see rel_canonical()
	 * @global WP $wp
	 * @global WP_Rewrite $wp_rewrite
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

		// Strip endpoint.
		$url = preg_replace( ':/' . preg_quote( AMP_QUERY_VAR, ':' ) . '(?=/?(\?|#|$)):', '', $url );

		// Strip query var.
		$url = remove_query_arg( AMP_QUERY_VAR, $url );

		return $url;
	}

	/**
	 * Add canonical link.
	 *
	 * Replaces `rel_canonical()` which only outputs canonical URLs for singular posts and pages.
	 * This can be removed once WP Core #18660 lands.
	 *
	 * @link https://www.ampproject.org/docs/reference/spec#canon.
	 * @link https://core.trac.wordpress.org/ticket/18660
	 */
	public static function add_canonical_link() {
		$url = self::get_current_canonical_url();
		if ( ! empty( $url ) ) {
			printf( '<link rel="canonical" href="%s">', esc_url( $url ) );
		}
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
			return $link;
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
		return $link;
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
	 * Print placeholder for Custom AMP styles.
	 *
	 * The actual styles for the page injected into the placeholder when output buffering is completed.
	 *
	 * @see AMP_Theme_Support::finish_output_buffering()
	 */
	public static function add_amp_styles_placeholder() {
		echo self::STYLES_PLACEHOLDER; // WPCS: XSS OK.

		$wp_styles = wp_styles();
		if ( ! ( $wp_styles instanceof AMP_WP_Styles ) ) {
			trigger_error( esc_html__( 'wp_styles() does not return an instance of AMP_WP_Styles as required.', 'amp' ), E_USER_WARNING ); // phpcs:ignore
			return;
		}

		$wp_styles->do_items(); // Normally done at wp_head priority 8.
		$wp_styles->do_locale_stylesheet(); // Normally done at wp_head priority 10.
		$wp_styles->do_custom_css(); // Normally done at wp_head priority 101.
	}

	/**
	 * Get AMP boilerplate and custom styles.
	 *
	 * @param string[] $stylesheets Initial stylesheets.
	 * @see wp_custom_css_cb()
	 * @return string Concatenated stylesheets.
	 */
	public static function get_amp_styles( $stylesheets ) {
		$css = wp_styles()->print_code;

		$css .= join( $stylesheets );

		/**
		 * Filters AMP custom CSS before it is injected onto the output buffer for the response.
		 *
		 * Plugins may add their own styles, such as for rendered widgets, by amending them via this filter.
		 *
		 * @since 0.7
		 *
		 * @param string $css AMP CSS.
		 */
		$css = apply_filters( 'amp_custom_styles', $css );

		$css = wp_strip_all_tags( $css );

		return amp_get_boilerplate_code() . "\n"
			. '<style amp-custom>' . $css . '</style>';
	}

	/**
	 * Determine required AMP scripts.
	 *
	 * @param array $amp_scripts Initial scripts.
	 * @return string Scripts to inject into the HEAD.
	 */
	public static function get_amp_scripts( $amp_scripts ) {

		foreach ( self::$embed_handlers as $embed_handler ) {
			$amp_scripts = array_merge(
				$amp_scripts,
				$embed_handler->get_scripts()
			);
		}

		/**
		 * List of components that are custom elements.
		 *
		 * Per the spec, "Most extensions are custom-elements." In fact, there is only one custom template.
		 *
		 * @link https://github.com/ampproject/amphtml/blob/cd685d4e62153557519553ffa2183aedf8c93d62/validator/validator.proto#L326-L328
		 * @link https://github.com/ampproject/amphtml/blob/cd685d4e62153557519553ffa2183aedf8c93d62/extensions/amp-mustache/validator-amp-mustache.protoascii#L27
		 */
		$custom_templates = array( 'amp-mustache' );

		/**
		 * Filters AMP component scripts before they are injected onto the output buffer for the response.
		 *
		 * Plugins may add their own component scripts which have been rendered but which the plugin doesn't yet
		 * recognize.
		 *
		 * @since 0.7
		 *
		 * @param array $amp_scripts AMP Component scripts, mapping component names to component source URLs.
		 */
		$amp_scripts = apply_filters( 'amp_component_scripts', $amp_scripts );

		$scripts = '<script async src="https://cdn.ampproject.org/v0.js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		foreach ( $amp_scripts as $amp_script_component => $amp_script_source ) {

			$custom_type = 'custom-element';
			if ( in_array( $amp_script_component, $custom_templates, true ) ) {
				$custom_type = 'custom-template';
			}

			$scripts .= sprintf(
				'<script async %s="%s" src="%s"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources, WordPress.XSS.EscapeOutput.OutputNotEscaped
				$custom_type,
				$amp_script_component,
				$amp_script_source
			);
		}

		return $scripts;
	}

	/**
	 * Start output buffering.
	 */
	public static function start_output_buffering() {
		ob_start( array( __CLASS__, 'finish_output_buffering' ) );
	}

	/**
	 * Finish output buffering.
	 *
	 * @todo Do this in shutdown instead of output buffering callback?
	 * @global int $content_width
	 * @param string $output Buffered output.
	 * @return string Finalized output.
	 */
	public static function finish_output_buffering( $output ) {
		global $content_width;

		$dom  = AMP_DOM_Utils::get_dom( $output );
		$args = array(
			'content_max_width'    => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH, // Back-compat.
			'use_document_element' => true,
		);

		$assets = AMP_Content_Sanitizer::sanitize_document( $dom, self::$sanitizer_classes, $args );

		/*
		 * @todo The sanitize method needs to be updated to sanitize the entire HTML element and not just the BODY.
		 * This will require updating mandatory_parent_blacklist in amphtml-update.py to include elements that appear in the HEAD.
		 * This will ensure that the scripts and styles that plugins output via wp_head() will be sanitized as well. However,
		 * since the the old paired mode is sending content from the *body* we'll need to be able to filter out the elements
		 * from outside the body from being part of the whitelist sanitizer when it runs when theme support is not present,
		 * as otherwise elements from the HEAD could get added to the BODY.
		 */
		$output  = "<!DOCTYPE html>\n";
		$output .= AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		// Inject required scripts.
		$output = preg_replace(
			'#' . preg_quote( self::SCRIPTS_PLACEHOLDER, '#' ) . '#',
			self::get_amp_scripts( $assets['scripts'] ),
			$output,
			1
		);

		// Inject styles.
		$output = preg_replace(
			'#' . preg_quote( self::STYLES_PLACEHOLDER, '#' ) . '#',
			self::get_amp_styles( $assets['stylesheets'] ),
			$output,
			1
		);

		return $output;
	}
}
