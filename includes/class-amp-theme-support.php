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
	const COMPONENT_SCRIPTS_PLACEHOLDER = '<!-- AMP:COMPONENT_SCRIPTS_PLACEHOLDER -->';

	/**
	 * Replaced with the necessary styles.
	 *
	 * @var string
	 */
	const CUSTOM_STYLES_PLACEHOLDER = '/* AMP:CUSTOM_STYLES_PLACEHOLDER */';

	/**
	 * Replaced with the comments template.
	 *
	 * @var string
	 */
	const COMMENTS_TEMPLATE_PLACEHOLDER = '/* AMP:COMMENTS_TEMPLATE_PLACEHOLDER */';

	/**
	 * AMP Scripts.
	 *
	 * @var array
	 */
	protected static $amp_scripts = array();

	/**
	 * AMP Styles.
	 *
	 * @var array
	 */
	protected static $amp_styles = array();

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
		remove_action( 'wp_head', 'locale_stylesheet' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_print_styles', 8 );
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
		remove_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		/*
		 * Replace core's canonical link functionality with one that outputs links for non-singular queries as well.
		 * See WP Core #18660.
		 */
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( __CLASS__, 'add_canonical_link' ), 1 );

		// @todo Add add_schemaorg_metadata(), add_analytics_data(), etc.
		// Add additional markup required by AMP <https://www.ampproject.org/docs/reference/spec#required-markup>.
		add_action( 'wp_head', array( __CLASS__, 'add_meta_charset' ), 0 );
		add_action( 'wp_head', array( __CLASS__, 'add_meta_viewport' ), 2 );
		add_action( 'wp_head', 'amp_print_boilerplate_code', 3 );
		add_action( 'wp_head', array( __CLASS__, 'add_amp_component_scripts' ), 4 );
		add_action( 'wp_head', array( __CLASS__, 'add_amp_custom_style_placeholder' ), 5 );
		add_action( 'wp_head', 'amp_add_generator_metadata', 6 );

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

		add_filter( 'the_content', array( __CLASS__, 'filter_the_content' ), PHP_INT_MAX );

		// Add Comments hooks.
		add_action( 'wp_list_comments_args', array( __CLASS__, 'add_amp_comments_template' ), PHP_INT_MAX );
		// @todo Add character conversion.
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
		echo '<script async src="https://cdn.ampproject.org/v0.js"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Replaced after output buffering with all AMP component scripts.
		echo self::COMPONENT_SCRIPTS_PLACEHOLDER; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.XSS.EscapeOutput
	}

	/**
	 * Add the comments template placeholder marker
	 *
	 * @param array $args the args for the comments list..
	 * @return array Args to return.
	 */
	public static function add_amp_comments_template( $args ) {
		if ( ! isset( $args['amp_comments'] ) ) {
			$amp_walker           = new AMP_Comment_Walker();
			$args['walker']       = $amp_walker;
			$args['amp_comments'] = true;
			$template             = self::get_comments_template( $args );
			wp_cache_add( 'amp_comments_template', $template, 'amp' );
			$args['comments_template_placeholder'] = self::COMMENTS_TEMPLATE_PLACEHOLDER;
		}

		return $args;
	}

	/**
	 * Generate a threaded mustache template based on the themes settings.
	 *
	 * @param array $args the args for the comments list.
	 * @return string HTML mustache template.
	 */
	public static function get_comments_template( $args ) {

		$_comment = array(
			'comment_ID'           => '{{comment_ID}}',
			'comment_post_ID'      => get_the_ID(),
			'comment_author'       => '{{comment_author}}',
			'comment_author_email' => '{{comment_author_email}}',
			'comment_author_url'   => '{{comment_author_url}}',
			'comment_author_IP'    => '{{comment_author_IP}}',
			'comment_date'         => '{{comment_date}}',
			'comment_date_gmt'     => '{{comment_date_gmt}}',
			'comment_content'      => '{{comment_content}}',
			'comment_karma'        => '{{comment_karma}}',
			'comment_approved'     => '{{comment_approved}}',
			'comment_agent'        => '{{comment_agent}}',
			'comment_type'         => '{{comment_type}}',
			'comment_parent'       => '',
			'user_id'              => '{{user_id}}',
		);

		$comments = array();
		$depth    = ( $args['max_depth'] ? $args['max_depth'] : 5 );
		for ( $i = 0; $i < $depth; $i ++ ) {
			$comment = new stdClass();
			foreach ( $_comment as $key => $value ) {
				if ( 'comment_ID' === $key ) {
					$value .= $i + 1;
				}
				if ( 'comment_parent' === $key && $i > 0 ) {
					$value = $i;
				}
				$comment->{$key} = $value;
			}
			$comments[] = $comment;
		}

		$args['echo']     = false;
		$args['amp_pass'] = true;
		if ( empty( $args['walker'] ) ) {
			$args['walker'] = new AMP_Comment_Walker();
		}

		// Filter dynamic data with musatche variable strings.
		// @todo add additional filters for dynamic data like "get_comment_author_link", "get_comment_author_IP" etc...
		add_filter( 'get_comment_date', array( __CLASS__, 'get_comment_date_template_string' ) );
		add_filter( 'get_comment_time', array( __CLASS__, 'get_comment_time_template_string' ) );

		return wp_list_comments( $args, $comments );

	}

	/**
	 * Get somment date string for template.
	 *
	 * @return string Mustache template string.
	 */
	public static function get_comment_date_template_string() {
		return '{{comment_date}}';
	}

	/**
	 * Get somment time string for template.
	 *
	 * @return string Mustache template string.
	 */
	public static function get_comment_time_template_string() {
		return '{{comment_time}}';
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
	 * Print placeholder for Custom AMP styles.
	 *
	 * The actual styles for the page injected into the placeholder when output buffering is completed.
	 *
	 * @see AMP_Theme_Support::finish_output_buffering()
	 */
	public static function add_amp_custom_style_placeholder() {
		echo '<style amp-custom>';
		echo self::CUSTOM_STYLES_PLACEHOLDER; // WPCS: XSS OK.
		echo '</style>';
	}

	/**
	 * Get custom styles.
	 *
	 * @see wp_custom_css_cb()
	 * @return string Styles.
	 */
	public static function get_amp_custom_styles() {

		// @todo Grab source of all enqueued styles and concatenate here?
		// @todo Print contents of get_locale_stylesheet_uri()?
		$path = get_template_directory() . '/style.css'; // @todo Honor filter in get_stylesheet_directory_uri()? Style must be local.
		$css  = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- It's not a remote file.

		// Add styles gleaned from sanitizers.
		foreach ( self::$amp_styles as $selector => $properties ) {
			$css .= sprintf(
				'%s{%s}',
				$selector,
				join( ';', $properties ) . ';'
			);
		}

		// Do AMP version of wp_custom_css_cb().
		$css .= wp_get_custom_css();

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
		return $css;
	}

	/**
	 * Filter the content to be valid AMP.
	 *
	 * @param string $content Content.
	 * @return string Amplified content.
	 */
	public static function filter_the_content( $content ) {
		$args = array(
			'content_max_width' => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH, // Back-compat.
		);

		list( $sanitized_content, $scripts, $styles ) = AMP_Content_Sanitizer::sanitize( $content, self::$sanitizer_classes, $args );

		self::$amp_scripts = array_merge( self::$amp_scripts, $scripts );
		self::$amp_styles  = array_merge( self::$amp_styles, $styles );

		return $sanitized_content;
	}

	/**
	 * Determine required AMP scripts.
	 *
	 * @param string $html Output HTML.
	 * @return string Scripts to inject into the HEAD.
	 */
	public static function get_amp_component_scripts( $html ) {

		// @todo This should be integrated with the existing Sanitizer classes so that duplication is not done here.
		$amp_components = array(
			'amp-form' => array(
				'pattern' => '#<(form|input)\b#i',
				'source'  => 'https://cdn.ampproject.org/v0/amp-form-0.1.js',
			),
			// @todo Add more.
		);

		$amp_scripts = self::$amp_scripts;

		foreach ( self::$embed_handlers as $embed_handler ) {
			$amp_scripts = array_merge(
				$amp_scripts,
				$embed_handler->get_scripts()
			);
		}

		foreach ( $amp_components as $component => $props ) {
			if ( preg_match( $props['pattern'], $html ) ) {
				$amp_scripts[ $component ] = $props['source'];
			}
		}

		/**
		 * Filters AMP component scripts before they are injected onto the output buffer for the response.
		 *
		 * Plugins may add their own component scripts which have been rendered but which the plugin doesn't yet
		 * recognize.
		 *
		 * @since 0.7
		 *
		 * @param string $amp_scripts AMP Component scripts, mapping component names to component source URLs.
		 */
		$amp_scripts = apply_filters( 'amp_component_scripts', $amp_scripts );

		$scripts = '';
		foreach ( $amp_scripts as $amp_script_component => $amp_script_source ) {
			$scripts .= sprintf(
				'<script async custom-element="%s" src="%s"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources, WordPress.XSS.EscapeOutput.OutputNotEscaped
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
	 * @param string $output Buffered output.
	 * @return string Finalized output.
	 */
	public static function finish_output_buffering( $output ) {

		// Inject required scripts.
		$output = preg_replace(
			'#' . preg_quote( self::COMPONENT_SCRIPTS_PLACEHOLDER, '#' ) . '#',
			self::get_amp_component_scripts( $output ),
			$output,
			1
		);

		// Inject styles.
		$output = preg_replace(
			'#' . preg_quote( self::CUSTOM_STYLES_PLACEHOLDER, '#' ) . '#',
			self::get_amp_custom_styles(),
			$output,
			1
		);

		// Inject comments template.
		$output   = preg_replace(
			'#' . preg_quote( self::COMMENTS_TEMPLATE_PLACEHOLDER, '#' ) . '#',
			$args = wp_cache_get( 'amp_comments_template', 'amp' ),
			$output,
			1
		);

		$dom = AMP_DOM_Utils::get_dom( $output );
		// Sanitize forms in the document.
		$sanitizer = new AMP_Form_Sanitizer( $dom );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Comments_Sanitizer( $dom );
		$sanitizer->sanitize();

		// @todo Add more validation checking and potentially the whitelist sanitizer.
		$output = $dom->saveHTML();

		return $output;
	}
}
