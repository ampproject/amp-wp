<?php
/**
 * Class AMP_Story_Post_Type
 *
 * @package AMP
 */

/**
 * Class AMP_Story_Post_Type
 */
class AMP_Story_Post_Type {

	/**
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_story';

	/**
	 * The option name where story settings are stored.
	 */
	const STORY_SETTINGS_OPTION = 'story_settings';

	/**
	 * The meta prefix applied to story settings options saved as individual post meta.
	 */
	const STORY_SETTINGS_META_PREFIX = 'amp_story_';

	/**
	 * Minimum required version of Gutenberg required.
	 *
	 * @var string
	 */
	const REQUIRED_GUTENBERG_VERSION = '6.6';

	/**
	 * The slug of the story card CSS file.
	 *
	 * @var string
	 */
	const STORY_CARD_CSS_SLUG = 'amp-story-card';

	/**
	 * The rewrite slug for this post type.
	 *
	 * @var string
	 */
	const REWRITE_SLUG = 'stories';

	/**
	 * AMP Stories script handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_SCRIPT_HANDLE = 'amp-stories-editor';

	/**
	 * AMP Stories style handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_STYLE_HANDLE = 'amp-stories';

	/**
	 * AMP Stories editor style handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_EDITOR_STYLE_HANDLE = 'amp-stories-editor';

	/**
	 * AMP Stories Ajax action.
	 *
	 * @var string
	 */
	const AMP_STORIES_AJAX_ACTION = 'amp-story-export';

	/**
	 * Story page inner width in the editor.
	 *
	 * @var number
	 */
	const STORY_PAGE_INNER_WIDTH = 328;

	/**
	 * Story page inner height in the editor.
	 *
	 * @var number
	 */
	const STORY_PAGE_INNER_HEIGHT = 553;

	/**
	 * Check if the required version of block capabilities available.
	 *
	 * Requires either Gutenberg 6.6+ or WordPress 5.3+ (which includes Gutenberg 6.6)
	 *
	 * @todo Eventually the Gutenberg requirement should be removed.
	 *
	 * @return bool Whether capabilities are available.
	 */
	public static function has_required_block_capabilities() {
		return (
			( defined( 'GUTENBERG_DEVELOPMENT_MODE' ) && GUTENBERG_DEVELOPMENT_MODE )
			||
			( defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, self::REQUIRED_GUTENBERG_VERSION, '>=' ) )
			||
			version_compare( get_bloginfo( 'version' ), '5.3-RC2', '>=' )
		);
	}

	/**
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! AMP_Options_Manager::is_stories_experience_enabled() || ! self::has_required_block_capabilities() ) {
			return;
		}

		register_post_type(
			self::POST_TYPE_SLUG,
			[
				'labels'       => [
					'name'                     => _x( 'Stories', 'post type general name', 'amp' ),
					'singular_name'            => _x( 'Story', 'post type singular name', 'amp' ),
					'add_new'                  => _x( 'New', 'story', 'amp' ),
					'add_new_item'             => __( 'Add New Story', 'amp' ),
					'edit_item'                => __( 'Edit Story', 'amp' ),
					'new_item'                 => __( 'New Story', 'amp' ),
					'view_item'                => __( 'View Story', 'amp' ),
					'view_items'               => __( 'View Stories', 'amp' ),
					'search_items'             => __( 'Search Stories', 'amp' ),
					'not_found'                => __( 'No stories found.', 'amp' ),
					'not_found_in_trash'       => __( 'No stories found in Trash.', 'amp' ),
					'all_items'                => __( 'All Stories', 'amp' ),
					'archives'                 => __( 'Story Archives', 'amp' ),
					'attributes'               => __( 'Story Attributes', 'amp' ),
					'insert_into_item'         => __( 'Insert into story', 'amp' ),
					'uploaded_to_this_item'    => __( 'Uploaded to this story', 'amp' ),
					'featured_image'           => __( 'Featured Image', 'amp' ),
					'set_featured_image'       => __( 'Set featured image', 'amp' ),
					'remove_featured_image'    => __( 'Remove featured image', 'amp' ),
					'use_featured_image'       => __( 'Use as featured image', 'amp' ),
					'filter_items_list'        => __( 'Filter stories list', 'amp' ),
					'items_list_navigation'    => __( 'Stories list navigation', 'amp' ),
					'items_list'               => __( 'Stories list', 'amp' ),
					'item_published'           => __( 'Story published.', 'amp' ),
					'item_published_privately' => __( 'Story published privately.', 'amp' ),
					'item_reverted_to_draft'   => __( 'Story reverted to draft.', 'amp' ),
					'item_scheduled'           => __( 'Story scheduled', 'amp' ),
					'item_updated'             => __( 'Story updated.', 'amp' ),
					'menu_name'                => _x( 'Stories', 'admin menu', 'amp' ),
					'name_admin_bar'           => _x( 'Story', 'add new on admin bar', 'amp' ),
				],
				'menu_icon'    => 'dashicons-book',
				'taxonomies'   => [
					'post_tag',
					'category',
				],
				'supports'     => [
					'title', // Used for amp-story[title].
					'author', // Used for the amp/amp-story-post-author block.
					'editor',
					'thumbnail', // Used for poster images.
					'amp',
					'revisions', // Without this, the REST API will return 404 for an autosave request.
					'custom-fields', // Used for global stories settings.
				],
				'rewrite'      => [
					'slug' => self::REWRITE_SLUG,
				],
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'template'     => [
					[
						'amp/amp-story-page',
						[],
						[
							[
								'amp/amp-story-text',
								[
									'placeholder' => __( 'Write text…', 'amp' ),
								],
							],
						],
					],
				],
			]
		);

		add_filter( 'post_row_actions', [ __CLASS__, 'remove_classic_editor_link' ], 11, 2 );

		add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'filter_kses_allowed_html' ], 10, 2 );

		add_filter( 'rest_request_before_callbacks', [ __CLASS__, 'filter_rest_request_for_kses' ], 100, 3 );

		add_action( 'wp_default_styles', [ __CLASS__, 'register_story_card_styling' ] );

		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_styles' ] );

		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_scripts' ] );

		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'export_latest_stories_block_editor_data' ], 100 );

		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'add_custom_stories_styles' ] );

		add_action(
			'amp_story_head',
			function() {
				// Theme support for title-tag is implied for stories. See _wp_render_title_tag().
				echo '<title>' . esc_html( wp_get_document_title() ) . '</title>' . "\n";
			},
			1
		);
		add_action( 'amp_story_head', 'wp_enqueue_scripts', 1 );
		add_action(
			'amp_story_head',
			function() {
				/*
				 * Same as wp_print_styles() but importantly omitting the wp_print_styles action, which themes/plugins
				 * can use to output arbitrary styling. Styling is constrained in story template via the
				 * \AMP_Story_Post_Type::filter_frontend_print_styles_array() method.
				 */
				wp_styles()->do_items();
			},
			8
		);
		add_action( 'amp_story_head', 'amp_add_generator_metadata' );
		add_action( 'amp_story_head', 'rest_output_link_wp_head', 10, 0 );
		add_action( 'amp_story_head', 'wp_resource_hints', 2 );
		add_action( 'amp_story_head', 'feed_links', 2 );
		add_action( 'amp_story_head', 'feed_links_extra', 3 );
		add_action( 'amp_story_head', 'rsd_link' );
		add_action( 'amp_story_head', 'wlwmanifest_link' );
		add_action( 'amp_story_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		add_action( 'amp_story_head', 'noindex', 1 );
		add_action( 'amp_story_head', 'wp_generator' );
		add_action( 'amp_story_head', 'rel_canonical' );
		add_action( 'amp_story_head', 'wp_shortlink_wp_head', 10, 0 );
		add_action( 'amp_story_head', 'wp_site_icon', 99 );
		add_action( 'amp_story_head', 'wp_oembed_add_discovery_links' );

		// Disable admin bar from even trying to be output, since wp_head and wp_footer hooks are not on the template.
		add_filter(
			'show_admin_bar',
			static function( $show ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					$show = false;
				}
				return $show;
			}
		);

		// Remove unnecessary settings.
		add_filter( 'block_editor_settings', [ __CLASS__, 'filter_block_editor_settings' ], 10, 2 );

		// Limit the styles that are printed in a story.
		add_filter( 'print_styles_array', [ __CLASS__, 'filter_frontend_print_styles_array' ] );
		add_filter( 'print_styles_array', [ __CLASS__, 'filter_editor_print_styles_array' ] );

		// Select the single-amp_story.php template for AMP Stories.
		add_filter( 'template_include', [ __CLASS__, 'filter_template_include' ] );

		// Get an embed template for this post type.
		add_filter( 'embed_template', [ __CLASS__, 'get_embed_template' ], 10, 3 );

		// Enqueue the styling for the /embed endpoint.
		add_action( 'embed_footer', [ __CLASS__, 'enqueue_embed_styling' ] );

		// In the block editor, remove the title from above the AMP Stories embed.
		add_filter( 'embed_html', [ __CLASS__, 'remove_title_from_embed' ], 10, 2 );

		// Change some attributes for the AMP story embed.
		add_filter( 'embed_html', [ __CLASS__, 'change_embed_iframe_attributes' ], 10, 2 );

		// Override the render_callback for AMP story embeds.
		add_filter( 'pre_render_block', [ __CLASS__, 'override_story_embed_callback' ], 10, 2 );

		// The AJAX handler for exporting an AMP story.
		add_action( 'wp_ajax_' . self::AMP_STORIES_AJAX_ACTION, [ __CLASS__, 'handle_export' ] );

		// Register render callback for just-in-time inclusion of dependent Google Font styles.
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_google_fonts' ], 10, 2 );

		// Wrap each movable inner block in amp-story-grid-layer.
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_grid_layer' ], 10, 2 );

		add_filter( 'use_block_editor_for_post_type', [ __CLASS__, 'use_block_editor_for_story_post_type' ], PHP_INT_MAX, 2 );
		add_filter( 'classic_editor_enabled_editors_for_post_type', [ __CLASS__, 'filter_enabled_editors_for_story_post_type' ], PHP_INT_MAX, 2 );

		self::register_block_latest_stories();
		self::register_block_page_attachment();

		register_block_type(
			'amp/amp-story-post-author',
			[
				'render_callback' => [ __CLASS__, 'render_post_author_block' ],
			]
		);

		register_block_type(
			'amp/amp-story-post-date',
			[
				'render_callback' => [ __CLASS__, 'render_post_date_block' ],
			]
		);

		register_block_type(
			'amp/amp-story-post-title',
			[
				'render_callback' => [ __CLASS__, 'render_post_title_block' ],
			]
		);

		add_filter(
			'amp_content_sanitizers',
			static function( $sanitizers ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					$sanitizers['AMP_Story_Sanitizer'] = [];

					// Disable noscript fallbacks since not allowed in AMP Stories.
					$sanitizers['AMP_Img_Sanitizer']['add_noscript_fallback']    = false;
					$sanitizers['AMP_Audio_Sanitizer']['add_noscript_fallback']  = false;
					$sanitizers['AMP_Video_Sanitizer']['add_noscript_fallback']  = false;
					$sanitizers['AMP_Iframe_Sanitizer']['add_noscript_fallback'] = false; // Note that iframe is not yet allowed in an AMP Story.
				}
				return $sanitizers;
			}
		);

		// Omit the core theme sanitizer for the story template.
		add_filter(
			'amp_content_sanitizers',
			static function( $sanitizers ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					unset( $sanitizers['AMP_Core_Theme_Sanitizer'] );
				}
				return $sanitizers;
			}
		);

		add_filter(
			'amp_content_sanitizers',
			static function( $sanitizers ) {
				if ( self::can_export() ) {
					$post = get_queried_object();
					$slug = sanitize_title( $post->post_title, $post->ID );

					$sanitizers['AMP_Story_Export_Sanitizer'] = self::get_export_args( $slug );

					$sanitizers['AMP_Style_Sanitizer']['include_manifest_comment'] = 'never';
				}
				return $sanitizers;
			},
			100 // Run sanitizer after the others (but before style sanitizer and validating sanitizer).
		);

		add_action( 'wp_head', [ __CLASS__, 'print_feed_link' ] );

		// Register story settings meta.
		$stories_settings_definitions = self::get_stories_settings_definitions();

		foreach ( $stories_settings_definitions as $option_key => $definition ) {
			$meta_args = isset( $definition['meta_args'] )
				? (array) $definition['meta_args']
				: [];

			$meta_args_defaults = [
				'type'           => 'string',
				'object_subtype' => self::POST_TYPE_SLUG,
				'description'    => '',
				'single'         => true,
				'show_in_rest'   => true,
			];

			register_meta(
				'post',
				self::STORY_SETTINGS_META_PREFIX . $option_key,
				wp_parse_args( $meta_args, $meta_args_defaults )
			);
		}

		add_action( 'wp_insert_post', [ __CLASS__, 'add_story_settings_meta_to_new_story' ], 10, 3 );

		AMP_Story_Media::init();
	}

	/**
	 * Remove classic editor action from AMP Story listing.
	 *
	 * @param array   $actions AMP Story row actions.
	 * @param WP_Post $post WP_Post object.
	 * @return array Actions.
	 */
	public static function remove_classic_editor_link( $actions, $post ) {
		if ( 'amp_story' === $post->post_type ) {
			unset( $actions['classic'] );
		}
		return $actions;
	}

	/**
	 * Filters an inline style attribute and removes disallowed rules.
	 *
	 * This is equivalent to the WordPress core function of the same name,
	 * except that this does not remove CSS with parentheses in it.
	 *
	 * Also, it adds a few more allowed attributes.
	 *
	 * @see safecss_filter_attr()
	 *
	 * @param string $css A string of CSS rules.
	 *
	 * @return string Filtered string of CSS rules.
	 */
	private static function safecss_filter_attr( $css ) {
		$css = wp_kses_no_null( $css );
		$css = str_replace( [ "\n", "\r", "\t" ], '', $css );

		$allowed_protocols = wp_allowed_protocols();

		$css_array = explode( ';', trim( $css ) );

		/** This filter is documented in wp-includes/kses.php */
		$allowed_attr = apply_filters(
			'safe_style_css',
			[
				'background',
				'background-color',
				'background-image',
				'background-position',

				'border',
				'border-width',
				'border-color',
				'border-style',
				'border-right',
				'border-right-color',
				'border-right-style',
				'border-right-width',
				'border-bottom',
				'border-bottom-color',
				'border-bottom-style',
				'border-bottom-width',
				'border-left',
				'border-left-color',
				'border-left-style',
				'border-left-width',
				'border-top',
				'border-top-color',
				'border-top-style',
				'border-top-width',

				'border-spacing',
				'border-collapse',
				'caption-side',

				'color',
				'font',
				'font-family',
				'font-size',
				'font-style',
				'font-variant',
				'font-weight',
				'letter-spacing',
				'line-height',
				'text-align',
				'text-decoration',
				'text-indent',
				'text-transform',

				'height',
				'min-height',
				'max-height',

				'width',
				'min-width',
				'max-width',

				'margin',
				'margin-right',
				'margin-bottom',
				'margin-left',
				'margin-top',

				'padding',
				'padding-right',
				'padding-bottom',
				'padding-left',
				'padding-top',

				'flex',
				'flex-grow',
				'flex-shrink',
				'flex-basis',

				'clear',
				'cursor',
				'direction',
				'float',
				'overflow',
				'vertical-align',
				'list-style-type',
				'grid-template-columns',
			]
		);

		// Add some more allowed attributes.
		$allowed_attr[] = 'display';
		$allowed_attr[] = 'opacity';
		$allowed_attr[] = 'object-position';
		$allowed_attr[] = 'position';
		$allowed_attr[] = 'top';
		$allowed_attr[] = 'left';
		$allowed_attr[] = 'transform';

		/*
		 * CSS attributes that accept URL data types.
		 *
		 * This is in accordance to the CSS spec and unrelated to
		 * the sub-set of supported attributes above.
		 *
		 * See: https://developer.mozilla.org/en-US/docs/Web/CSS/url
		 */
		$css_url_data_types = [
			'background',
			'background-image',

			'cursor',

			'list-style',
			'list-style-image',
		];

		if ( empty( $allowed_attr ) ) {
			return $css;
		}

		$css = '';
		foreach ( $css_array as $css_item ) {
			if ( '' === $css_item ) {
				continue;
			}

			$css_item        = trim( $css_item );
			$css_test_string = $css_item;
			$found           = false;
			$url_attr        = false;

			if ( strpos( $css_item, ':' ) === false ) {
				$found = true;
			} else {
				$parts        = explode( ':', $css_item, 2 );
				$css_selector = trim( $parts[0] );

				if ( in_array( $css_selector, $allowed_attr, true ) ) {
					$found    = true;
					$url_attr = in_array( $css_selector, $css_url_data_types, true );
				}
			}

			if ( $found && $url_attr ) {
				// Simplified: matches the sequence `url(*)`.
				preg_match_all( '/url\([^)]+\)/', $parts[1], $url_matches );

				foreach ( $url_matches[0] as $url_match ) {
					// Clean up the URL from each of the matches above.
					preg_match( '/^url\(\s*([\'\"]?)(.*)(\g1)\s*\)$/', $url_match, $url_pieces );

					if ( empty( $url_pieces[2] ) ) {
						$found = false;
						break;
					}

					$url = trim( $url_pieces[2] );

					if ( empty( $url ) || wp_kses_bad_protocol( $url, $allowed_protocols ) !== $url ) {
						$found = false;
						break;
					} else {
						// Remove the whole `url(*)` bit that was matched above from the CSS.
						$css_test_string = str_replace( $url_match, '', $css_test_string );
					}
				}
			}

			if ( $found ) {
				if ( '' !== $css ) {
					$css .= ';';
				}

				$css .= $css_item;
			}
		}

		return $css;
	}

	/**
	 * Filters the response before executing any REST API callbacks.
	 *
	 * Temporarily modifies post content during saving in a way that KSES
	 * does not strip actually valid CSS from post content, making block content invalid.
	 *
	 * @todo Remove once core has better CSS parsing.
	 *
	 * @link https://core.trac.wordpress.org/ticket/37134
	 *
	 * @param WP_HTTP_Response|WP_Error $response Result to send to the client. Usually a WP_REST_Response or WP_Error.
	 * @param array                     $handler  Route handler used for the request.
	 * @param WP_REST_Request           $request  Request used to generate the response.
	 *
	 * @return WP_HTTP_Response|WP_Error The filtered response.
	 */
	public static function filter_rest_request_for_kses( $response, $handler, $request ) {

		// Short-circuit since this is relevant only for users without unfiltered_html capability.
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $response;
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$obj  = get_post_type_object( self::POST_TYPE_SLUG );
		$slug = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

		$editable_request_methods = array_map( 'trim', explode( ',', WP_REST_Server::EDITABLE ) );

		if ( ! in_array( $request->get_method(), $editable_request_methods, true ) || ! preg_match( "#^/wp/v2/{$slug}/#s", $request->get_route() ) ) {
			return $response;
		}

		if ( ! current_user_can( 'edit_post', $request['id'] ) ) {
			return $response;
		}

		$style_attr_values = [];

		// Replace inline styles with temporary data-temp-style-hash attribute before KSES...
		add_filter(
			'content_save_pre',
			static function ( $post_content ) use ( &$style_attr_values ) {
				$post_content = preg_replace_callback(
					'|(?P<before><\w+(?:-\w+)*\s[^>]*?)style=\\\"(?P<styles>[^"]*)\\\"(?P<after>([^>]+?)*>)|', // Extra slashes appear here because $post_content is pre-slashed..
					static function ( $matches ) use ( &$style_attr_values ) {
						$hash                       = md5( $matches['styles'] );
						$style_attr_values[ $hash ] = self::safecss_filter_attr( wp_unslash( $matches['styles'] ) );

						// Replaces the complete style attribute value with its hashed version.
						return $matches['before'] . sprintf( ' data-temp-style-hash="%s" ', $hash ) . $matches['after'];
					},
					$post_content
				);

				return $post_content;
			},
			0
		);

		// ...And bring it back afterwards.
		add_filter(
			'content_save_pre',
			static function ( $post_content ) use ( &$style_attr_values ) {
				// Replaces hashed style attribute value with the original value again.
				return preg_replace_callback(
					'/ data-temp-style-hash=\\\"(?P<hash>[0-9a-f]+)\\\"/',
					static function ( $matches ) use ( $style_attr_values ) {
						return isset( $style_attr_values[ $matches['hash'] ] ) ? sprintf( ' style="%s"', esc_attr( wp_slash( $style_attr_values[ $matches['hash'] ] ) ) ) : '';
					},
					$post_content
				);
			},
			20
		);

		return $response;
	}

	/**
	 * Filter the allowed tags for KSES to allow for amp-story children.
	 *
	 * @param array $allowed_tags Allowed tags.
	 * @return array Allowed tags.
	 */
	public static function filter_kses_allowed_html( $allowed_tags ) {
		$story_components = [
			'amp-story-page',
			'amp-story-grid-layer',
			'amp-story-cta-layer',
			'amp-img',
			'amp-video',
			'img',
		];
		foreach ( $story_components as $story_component ) {
			$attributes = array_fill_keys( array_keys( AMP_Allowed_Tags_Generated::get_allowed_attributes() ), true );
			$rule_specs = AMP_Allowed_Tags_Generated::get_allowed_tag( $story_component );
			foreach ( $rule_specs as $rule_spec ) {
				$attributes = array_merge( $attributes, array_fill_keys( array_keys( $rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ), true ) );
			}
			$allowed_tags[ $story_component ] = $attributes;
		}

		// @todo This perhaps should not be allowed if user does not have capability.
		foreach ( $allowed_tags as &$allowed_tag ) {
			$allowed_tag['animate-in']           = true;
			$allowed_tag['animate-in-duration']  = true;
			$allowed_tag['animate-in-delay']     = true;
			$allowed_tag['animate-in-after']     = true;
			$allowed_tag['data-font-family']     = true;
			$allowed_tag['data-block-name']      = true;
			$allowed_tag['data-temp-style-hash'] = true;
			$allowed_tag['layout']               = true;
			$allowed_tag['object-position']      = true;
		}

		return $allowed_tags;
	}

	/**
	 * Filter which styles will be used in the edit page of an AMP Story.
	 *
	 * @param array $handles Style handles.
	 * @return array Styles to print.
	 */
	public static function filter_editor_print_styles_array( $handles ) {
		if (
			! function_exists( 'get_current_screen' ) ||
			! get_current_screen() ||
			self::POST_TYPE_SLUG !== get_current_screen()->post_type ||
			! get_current_screen()->is_block_editor
		) {
			return $handles;
		}

		return array_filter(
			$handles,
			static function( $handle ) {
				if ( ! isset( wp_styles()->registered[ $handle ] ) ) {
					return false;
				}
				$dep = wp_styles()->registered[ $handle ];

				// If we have amp-stories as dependency, allow the style.
				if ( is_array( $dep->deps ) && in_array( self::AMP_STORIES_STYLE_HANDLE, $dep->deps, true ) ) {
					return true;
				}

				// Disable the active theme's style.
				if ( self::is_theme_stylesheet( $dep->src ) ) {
					return false;
				}
				return true;
			}
		);
	}

	/**
	 * Filter which styles will be printed on an AMP Story.
	 *
	 * @param array $handles Style handles.
	 * @return array Styles to print.
	 */
	public static function filter_frontend_print_styles_array( $handles ) {
		if ( ! is_singular( self::POST_TYPE_SLUG ) || is_embed() ) {
			return $handles;
		}

		return array_filter(
			$handles,
			static function( $handle ) {
				if ( ! isset( wp_styles()->registered[ $handle ] ) ) {
					return false;
				}
				$dep = wp_styles()->registered[ $handle ];

				if ( 'fonts.googleapis.com' === wp_parse_url( $dep->src, PHP_URL_HOST ) ) {
					return true;
				}

				if ( 'wp-block-library' === $handle || self::AMP_STORIES_STYLE_HANDLE === $handle ) {
					return true;
				}

				if ( in_array( self::AMP_STORIES_STYLE_HANDLE, $dep->deps, true ) ) {
					return true;
				}

				return false;
			}
		);
	}

	/**
	 * Enqueue the styles for the block editor.
	 */
	public static function enqueue_block_editor_styles() {
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			return;
		}

		wp_enqueue_style(
			self::AMP_STORIES_EDITOR_STYLE_HANDLE,
			amp_get_asset_url( 'css/amp-stories-editor-compiled.css' ),
			[ 'wp-edit-blocks', 'amp-stories' ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::AMP_STORIES_EDITOR_STYLE_HANDLE, 'rtl', 'replace' );

		// Include all fonts in the editor since new fonts can be selected at runtime.
		// In a frontend context, the fonts are added only as needed via \AMP_Story_Post_Type::render_block_with_google_fonts().
		$fonts = self::get_fonts();
		foreach ( $fonts as $font ) {
			wp_add_inline_style(
				self::AMP_STORIES_EDITOR_STYLE_HANDLE,
				self::get_inline_font_style_rule( $font )
			);
		}

		self::enqueue_general_styles();
	}

	/**
	 * Enqueue styles that are needed for frontend and editor both.
	 */
	public static function enqueue_general_styles() {
		// This CSS is separate since it's used both in front-end and in the editor.
		wp_enqueue_style(
			self::AMP_STORIES_STYLE_HANDLE,
			amp_get_asset_url( 'css/amp-stories.css' ),
			[],
			AMP__VERSION
		);

		wp_styles()->add_data( self::AMP_STORIES_STYLE_HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Filters the settings to pass to the block editor.
	 *
	 * Removes support for custom color palettes for AMP stories.
	 * Removes custom theme stylesheets for editing AMP Stories.
	 *
	 * @param array   $editor_settings Default editor settings.
	 * @param WP_Post $post            Post being edited.
	 *
	 * @return array Modified editor settings.
	 */
	public static function filter_block_editor_settings( $editor_settings, $post ) {
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			return $editor_settings;
		}

		unset( $editor_settings['fontSizes'], $editor_settings['colors'] );

		if ( isset( $editor_settings['styles'] ) ) {
			foreach ( $editor_settings['styles'] as $key => $style ) {

				// If the baseURL is not set or if the URL doesn't include theme styles, move to next.
				if ( ! isset( $style['baseURL'] ) || ! self::is_theme_stylesheet( $style['baseURL'] ) ) {
					continue;
				}

				/**
				 * Filters the editor style to allow whitelisting it for AMP Stories editor.
				 *
				 * @param bool   $whitelisted If to whitelist the stylesheet.
				 * @param string $base_url    The URL for the stylesheet.
				 */
				if ( false === apply_filters( 'amp_stories_whitelist_editor_style', false, $style['baseURL'] ) ) {
					unset( $editor_settings['styles'][ $key ] );
				}
			}
		}

		$editor_settings['codeEditingEnabled'] = false;
		$editor_settings['richEditingEnabled'] = true;

		return $editor_settings;
	}

	/**
	 * Checks if a stylesheet is from the theme or parent theme.
	 *
	 * @param string $url Stylesheet URL.
	 * @return bool If the stylesheet comes from the theme.
	 */
	public static function is_theme_stylesheet( $url ) {
		return (
			0 === strpos( $url, get_stylesheet_directory_uri() )
			||
			0 === strpos( $url, get_template_directory_uri() )
		);
	}

	/**
	 * Registers the story card styling.
	 *
	 * This can't take place on the 'wp_enqueue_scripts' hook, as the /embed endpoint doesn't trigger that.
	 *
	 * @param WP_Styles $wp_styles The styles.
	 */
	public static function register_story_card_styling( WP_Styles $wp_styles ) {
		// Register the styling for the /embed endpoint and the Latest Stories block.
		$wp_styles->add(
			self::STORY_CARD_CSS_SLUG,
			amp_get_asset_url( '/css/' . self::STORY_CARD_CSS_SLUG . '.css' ),
			[],
			AMP__VERSION
		);
	}

	/**
	 * Export data used for Latest Stories block.
	 */
	public static function export_latest_stories_block_editor_data() {
		if ( self::POST_TYPE_SLUG === get_current_screen()->post_type ) {
			return;
		}

		$url = add_query_arg(
			'ver',
			wp_styles()->registered[ self::STORY_CARD_CSS_SLUG ]->ver,
			wp_styles()->registered[ self::STORY_CARD_CSS_SLUG ]->src
		);

		/** This filter is documented in wp-includes/class.wp-styles.php */
		$url = apply_filters( 'style_loader_src', $url, self::STORY_CARD_CSS_SLUG );

		wp_add_inline_script(
			AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE,
			sprintf(
				'var ampLatestStoriesBlockData = %s;',
				wp_json_encode(
					[
						'storyCardStyleURL' => $url,
					]
				)
			),
			'before'
		);
	}

	/**
	 * Enqueue scripts for the block editor.
	 */
	public static function enqueue_block_editor_scripts() {
		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen ) {
			return;
		}

		if ( self::POST_TYPE_SLUG !== $screen->post_type ) {
			return;
		}

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::AMP_STORIES_SCRIPT_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			amp_get_asset_url( 'js/' . self::AMP_STORIES_SCRIPT_HANDLE . '.js' ),
			$dependencies,
			$version,
			false
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'amp-editor-story-blocks-build', 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp-editor-story-blocks-build' ) : gutenberg_get_jed_locale_data( 'amp-editor-story-blocks-build' );
			$translations = wp_json_encode( $locale_data );

			wp_add_inline_script(
				self::AMP_STORIES_SCRIPT_HANDLE,
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'before'
			);
		}

		/**
		 * Filter list of allowed video mime types.
		 *
		 * This can be used to add additionally supported formats, for example by plugins
		 * that do video transcoding.
		 *
		 * @since 1.3
		 *
		 * @param array Allowed video mime types.
		 */
		$allowed_video_mime_types = apply_filters( 'amp_story_allowed_video_types', [ 'video/mp4' ] );

		// If `$allowed_video_mime_types` doesn't have valid data or is empty add default supported type.
		if ( ! is_array( $allowed_video_mime_types ) || empty( $allowed_video_mime_types ) ) {
			$allowed_video_mime_types = [ 'video/mp4' ];
		}

		// Only add currently supported mime types.
		$allowed_video_mime_types = array_values( array_intersect( $allowed_video_mime_types, wp_get_mime_types() ) );

		// Convert auto advancement.
		$meta_definitions         = self::get_stories_settings_definitions();
		$auto_advancement_options = $meta_definitions['auto_advance_after']['data']['options'];

		/**
		 * Filters the list of allowed post types for use in page attachments.
		 *
		 * @since 1.3
		 *
		 * @param array Allowed post types.
		 */
		$page_attachment_post_types = apply_filters( 'amp_story_allowed_page_attachment_post_types', [ 'page', 'post' ] );
		$post_types                 = [];
		foreach ( $page_attachment_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			if ( $post_type_object ) {
				$post_types[ $post_type ] = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
			}
		}

		wp_localize_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			'ampStoriesEditorSettings',
			[
				'allowedVideoMimeTypes'          => $allowed_video_mime_types,
				'allowedPageAttachmentPostTypes' => $post_types,
				'storySettings'                  => [
					'autoAdvanceAfterOptions' => $auto_advancement_options,
				],
			]
		);

		wp_localize_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			'ampStoriesFonts',
			self::get_fonts()
		);

		wp_localize_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			'ampStoriesExport',
			[
				'action'  => self::AMP_STORIES_AJAX_ACTION,
				'nonce'   => wp_create_nonce( self::AMP_STORIES_AJAX_ACTION ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Get inline font style rule.
	 *
	 * @param array $font Font.
	 * @return string Font style rule.
	 */
	public static function get_inline_font_style_rule( $font ) {
		$families = array_map(
			'wp_json_encode',
			array_merge( (array) $font['name'], $font['fallbacks'] )
		);
		return sprintf(
			'[data-font-family="%s"] { font-family: %s; }',
			$font['name'],
			implode( ', ', $families )
		);
	}

	/**
	 * Set template for amp_story post type.
	 *
	 * @param string $template Template.
	 * @return string Template.
	 */
	public static function filter_template_include( $template ) {
		if ( is_singular( self::POST_TYPE_SLUG ) && ! is_embed() ) {
			$template = AMP__DIR__ . '/includes/templates/single-amp_story.php';
		}
		return $template;
	}

	/**
	 * Add CSS to AMP Stories' frontend.
	 *
	 * @see /assets/css/amp-stories.css
	 */
	public static function add_custom_stories_styles() {
		if ( ! is_singular( self::POST_TYPE_SLUG ) ) {
			return;
		}

		wp_enqueue_style(
			'amp-stories-frontend',
			amp_get_asset_url( 'css/amp-stories-frontend.css' ),
			[ self::AMP_STORIES_STYLE_HANDLE ],
			AMP__VERSION,
			false
		);

		wp_styles()->add_data( 'amp-stories-frontend', 'rtl', 'replace' );
		wp_styles()->add_data( self::STORY_CARD_CSS_SLUG, 'rtl', 'replace' );

		// Also enqueue this since it's possible to embed another story into a story.
		wp_enqueue_style( self::STORY_CARD_CSS_SLUG );

		self::enqueue_general_styles();
	}

	/**
	 * Get list of fonts used in AMP Stories.
	 *
	 * @return array Fonts.
	 */
	public static function get_fonts() {
		static $fonts = null;

		if ( isset( $fonts ) ) {
			return $fonts;
		}

		// Default system fonts.
		$fonts = [
			[
				'name'      => 'Arial',
				'fallbacks' => [ 'Helvetica Neue', 'Helvetica', 'sans-serif' ],
			],
			[
				'name'      => 'Arial Black',
				'fallbacks' => [ 'Arial Black', 'Arial Bold', 'Gadget', 'sans-serif' ],
			],
			[
				'name'      => 'Arial Narrow',
				'fallbacks' => [ 'Arial', 'sans-serif' ],
			],
			[
				'name'      => 'Baskerville',
				'fallbacks' => [ 'Baskerville Old Face', 'Hoefler Text', 'Garamond', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Brush Script MT',
				'fallbacks' => [ 'cursive' ],
			],
			[
				'name'      => 'Copperplate',
				'fallbacks' => [ 'Copperplate Gothic Light', 'fantasy' ],
			],
			[
				'name'      => 'Courier New',
				'fallbacks' => [ 'Courier', 'Lucida Sans Typewriter', 'Lucida Typewriter', 'monospace' ],
			],
			[
				'name'      => 'Century Gothic',
				'fallbacks' => [ 'CenturyGothic', 'AppleGothic', 'sans-serif' ],
			],
			[
				'name'      => 'Garamond',
				'fallbacks' => [ 'Baskerville', 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Georgia',
				'fallbacks' => [ 'Times', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Gill Sans',
				'fallbacks' => [ 'Gill Sans MT', 'Calibri', 'sans-serif' ],
			],
			[
				'name'      => 'Lucida Bright',
				'fallbacks' => [ 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Lucida Sans Typewriter',
				'fallbacks' => [ 'Lucida Console', 'monaco', 'Bitstream Vera Sans Mono', 'monospace' ],
			],
			[
				'name'      => 'Palatino',
				'fallbacks' => [ 'Palatino Linotype', 'Palatino LT STD', 'Book Antiqua', 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Papyrus',
				'fallbacks' => [ 'fantasy' ],
			],
			[
				'name'      => 'Tahoma',
				'fallbacks' => [ 'Verdana', 'Segoe', 'sans-serif' ],
			],
			[
				'name'      => 'Times New Roman',
				'fallbacks' => [ 'Times New Roman', 'Times', 'Baskerville', 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Trebuchet MS',
				'fallbacks' => [ 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', 'Tahoma', 'sans-serif' ],
			],
			[
				'name'      => 'Verdana',
				'fallbacks' => [ 'Geneva', 'sans-serif' ],
			],
		];
		$file  = __DIR__ . '/data/fonts.json';
		$fonts = array_merge( $fonts, self::get_google_fonts( $file ) );

		$columns = wp_list_pluck( $fonts, 'name' );
		array_multisort( $columns, SORT_ASC, $fonts );

		$fonts_url = 'https://fonts.googleapis.com/css';
		$subsets   = [ 'latin', 'latin-ext' ];

		/*
		 * Translators: To add an additional character subset specific to your language,
		 * translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'amp' );

		if ( 'cyrillic' === $subset ) {
			$subsets[] = 'cyrillic';
			$subsets[] = 'cyrillic-ext';
		} elseif ( 'greek' === $subset ) {
			$subsets[] = 'greek';
			$subsets[] = 'greek-ext';
		} elseif ( 'devanagari' === $subset ) {
			$subsets[] = 'devanagari';
		} elseif ( 'vietnamese' === $subset ) {
			$subsets[] = 'vietnamese';
		}

		$fonts = array_map(
			static function ( $font ) use ( $fonts_url, $subsets ) {
				$font['slug'] = sanitize_title( $font['name'] );

				if ( ! empty( $font['gfont'] ) ) {
					$font['handle'] = sprintf( '%s-font', $font['slug'] );
					$font['src']    = add_query_arg(
						[
							'family'  => rawurlencode( $font['gfont'] ),
							'subset'  => rawurlencode( implode( ',', $subsets ) ),
							'display' => 'swap',
						],
						$fonts_url
					);
				}

				return $font;
			},
			$fonts
		);

		return $fonts;
	}

	/**
	 * Get list of Google Fonts from a given JSON file.
	 *
	 * @param string $file  Path to file containing Google Fonts definitions.
	 *
	 * @return array $fonts Fonts list.
	 */
	public static function get_google_fonts( $file ) {
		if ( ! is_readable( $file ) ) {
			return [];
		}
		$file_content = file_get_contents( $file );  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$google_fonts = json_decode( $file_content, true );

		if ( empty( $google_fonts ) ) {
			return [];
		}

		$fonts = [];

		foreach ( $google_fonts as $font ) {
			$variants = array_intersect(
				$font['variants'],
				[
					'regular',
					'italic',
					'700',
					'700italic',
				]
			);

			$variants = array_map(
				static function ( $variant ) {
					$variant = str_replace(
						[ '0italic', 'regular', 'italic' ],
						[ '0i', '400', '400i' ],
						$variant
					);

					return $variant;
				},
				$variants
			);

			$gfont = '';

			if ( $variants ) {
				$gfont = $font['family'] . ':' . implode( ',', $variants );
			}

			$fonts[] = [
				'name'      => $font['family'],
				'fallbacks' => (array) self::get_font_fallback( $font['category'] ),
				'gfont'     => $gfont,
			];
		}

		return $fonts;
	}

	/**
	 * Helper method to lookup fallback font.
	 *
	 * @param string $category Google font category.
	 *
	 * @return string $fallback Fallback font.
	 */
	public static function get_font_fallback( $category ) {
		switch ( $category ) {
			case 'sans-serif':
				return 'sans-serif';
			case 'handwriting':
			case 'display':
				return 'cursive';
			case 'monospace':
				return 'monospace';
			default:
				return 'serif';
		}
	}

	/**
	 * Get a font.
	 *
	 * @param string $name Font family name.
	 * @return array|null The font or null if not defined.
	 */
	public static function get_font( $name ) {
		$fonts = array_filter(
			self::get_fonts(),
			static function ( $font ) use ( $name ) {
				return $font['name'] === $name;
			}
		);
		return array_shift( $fonts );
	}

	/**
	 * Renders the amp/amp-story-post-author block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Block content.
	 */
	public static function render_post_author_block( $attributes, $content ) {
		return str_replace( '{content}', get_the_author(), $content );
	}

	/**
	 * Renders the amp/amp-story-post-date block.
	 *
	 * @todo Consider allowing to change the date format in the block settings.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Block content.
	 */
	public static function render_post_date_block( $attributes, $content ) {
		return str_replace( '{content}', get_the_date(), $content );
	}

	/**
	 * Renders the amp/amp-story-post-title block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Block content.
	 */
	public static function render_post_title_block( $attributes, $content ) {
		return str_replace( '{content}', get_the_title(), $content );
	}

	/**
	 * Include any required Google Font styles when rendering a block in AMP Stories.
	 *
	 * @see AMP_Story_Post_Type::enqueue_block_editor_styles() Where fonts are added in the story editor.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Block content.
	 */
	public static function render_block_with_google_fonts( $block_content, $block ) {
		$font_family_attribute = 'ampFontFamily';

		if ( empty( $block['attrs'][ $font_family_attribute ] ) ) {
			return $block_content;
		}

		$font = self::get_font( $block['attrs'][ $font_family_attribute ] );
		if ( ! $font ) {
			return $block_content;
		}

		// Create style rule for the custom font. The style sanitizer will de-duplicate.
		$style = sprintf(
			'<style data-font-family="%s">%s</style>',
			esc_attr( $font['name'] ),
			self::get_inline_font_style_rule( $font )
		);

		// Make sure that the Google Font is enqueued.
		if ( isset( $font['src'], $font['handle'] ) && ! wp_style_is( $font['handle'] ) ) {
			wp_enqueue_style( $font['handle'], $font['src'], [], null, 'all' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}

		return $style . $block_content;
	}

	/**
	 * Converts pixel to percentage based on the editor page sizes.
	 *
	 * @param string $axis Axis: x or y.
	 * @param number $pixels Pixel value.
	 *
	 * @return int|string Percentage value compared to AMP Story editor page.
	 */
	public static function get_percentage_from_pixels( $axis, $pixels ) {
		if ( 'x' === $axis ) {
			return number_format( ( ( $pixels / self::STORY_PAGE_INNER_WIDTH ) * 100 ), 2 );
		} elseif ( 'y' === $axis ) {
			return number_format( ( ( $pixels / self::STORY_PAGE_INNER_HEIGHT ) * 100 ), 2 );
		}
		return 0;
	}

	/**
	 * Get default height by block name.
	 *
	 * @param string $block Block name.
	 *
	 * @return int Height in pixels.
	 */
	protected static function get_blocks_default_height( $block ) {
		switch ( $block ) {
			case 'core/quote':
			case 'core/video':
			case 'core/embed':
				return 200;

			case 'core/pullquote':
				return 250;

			case 'core/table':
				return 100;

			case 'amp/amp-story-post-author':
			case 'amp/amp-story-post-date':
				return 50;

			case 'amp/amp-story-post-title':
				return 100;

			default:
				return 60;
		}
	}

	/**
	 * Wraps each movable block into amp-story-grid-layer and animation wrapper with necessary attributes.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 *
	 * @return string Modified content.
	 */
	public static function render_block_with_grid_layer( $block_content, $block ) {

		$post = get_post();
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return $block_content;
		}

		// If the block content already includes amp-story-grid-layer, stop.
		if ( false !== strpos( $block_content, 'amp-story-grid-layer' ) ) {
			return $block_content;
		}

		$movable_blocks = [
			'core/code',
			'core/embed',
			'core/image',
			'core/list',
			'core/preformatted',
			'core/pullquote',
			'core/quote',
			'core/table',
			'core/verse',
			'core/video',
			'amp/amp-story-text',
			'amp/amp-story-post-author',
			'amp/amp-story-post-date',
			'amp/amp-story-post-title',
			'core/html',
			'core/block', // Reusable blocks.
			'core/template', // Reusable blocks.
		];

		$name = $block['blockName'];

		// If the block is not movable, it doesn't need the wrapper.
		if ( ! in_array( $name, $movable_blocks, true ) ) {
			return $block_content;
		}

		$atts         = $block['attrs'];
		$wrapper_atts = [];

		$style = [
			'position' => 'absolute',
		];

		// Set default values if missing.
		$width  = isset( $atts['width'] ) ? $atts['width'] : 250;
		$height = isset( $atts['height'] ) ? $atts['height'] : self::get_blocks_default_height( $name );

		// Set passed attributes or default values (0, 5) for top and left.
		$style['top']    = ! isset( $atts['positionTop'] ) ? '0%' : $atts['positionTop'] . '%';
		$style['left']   = ! isset( $atts['positionLeft'] ) ? '5%' : $atts['positionLeft'] . '%';
		$style['width']  = self::get_percentage_from_pixels( 'x', $width ) . '%';
		$style['height'] = self::get_percentage_from_pixels( 'y', $height ) . '%';

		$wrapper_style = isset( $atts['style'] ) ? $atts['style'] : '';

		foreach ( $style as $att => $value ) {
			$wrapper_style .= "$att:$value;";
		}

		if ( ! empty( $wrapper_style ) ) {
			$wrapper_atts['style'] = $wrapper_style;
		}

		if ( ! empty( $atts['ampAnimationType'] ) ) {
			$wrapper_atts['animate-in'] = $atts['ampAnimationType'];
			if ( ! empty( $atts['ampAnimationDelay'] ) ) {
				$wrapper_atts['animate-in-delay'] = $atts['ampAnimationDelay'];
			}
			if ( ! empty( $atts['ampAnimationDuration'] ) ) {
				$wrapper_atts['animate-in-duration'] = $atts['ampAnimationDuration'];
			}
			if ( ! empty( $atts['ampAnimationAfter'] ) ) {
				$wrapper_atts['animate-in-after'] = $atts['ampAnimationAfter'];
			}
		}

		if ( isset( $atts['anchor'] ) ) {
			$wrapper_atts['id'] = $atts['anchor'];
		}

		$before = '<amp-story-grid-layer template="vertical"><div class="amp-story-block-wrapper"';
		foreach ( $wrapper_atts as $att => $value ) {
			$before .= ' ' . $att . '="' . esc_attr( $value ) . '"';
		}
		$before .= '>';
		$after   = '</div></amp-story-grid-layer>';

		return $before . $block_content . $after;
	}

	/**
	 * Filters whether a post is able to be edited in the block editor.
	 *
	 * Forces the block editor to be used for stories.
	 *
	 * @param bool   $use_block_editor Whether the post type can be edited or not.
	 * @param string $post_type        The current post type.
	 *
	 * @return bool Whether to use the block editor for the given post type.
	 */
	public static function use_block_editor_for_story_post_type( $use_block_editor, $post_type ) {
		if ( self::POST_TYPE_SLUG === $post_type ) {
			return true;
		}

		return $use_block_editor;
	}

	/**
	 * Filters the editors that are enabled for the given post type.
	 *
	 * Forces the block editor to be used for stories.
	 *
	 * @param array  $editors   Associative array of the editors and whether they are enabled for the post type.
	 * @param string $post_type The post type.
	 *
	 * @return array Filtered list of enabled editors.
	 */
	public static function filter_enabled_editors_for_story_post_type( $editors, $post_type ) {
		if ( self::POST_TYPE_SLUG === $post_type ) {
			$editors['classic_editor'] = false;
		}

		return $editors;
	}

	/**
	 * Get the AMP story's embed template.
	 *
	 * This is used when an AMP story is embedded in a post,
	 * often with the WordPress (embed) block.
	 *
	 * @param string $template  The path of the template, from locate_template().
	 * @param string $type The file name.
	 * @param array  $templates An array of possible templates.
	 * @return string $template  The path of the template, from locate_template().
	 */
	public static function get_embed_template( $template, $type, $templates ) {
		$old_amp_story_template = sprintf( 'embed-%s.php', self::POST_TYPE_SLUG );
		if ( 'embed' === $type && in_array( $old_amp_story_template, $templates, true ) ) {
			$template = AMP__DIR__ . '/includes/templates/embed-amp-story.php';
		}
		return $template;
	}

	/**
	 * Outputs a card of a single AMP story.
	 *
	 * Used for a slide in the Latest Stories block.
	 * The 'disable_link' parameter can prevent a link from appearing in the block editor.
	 * So on clicking the story card, it does not redirect to the story's URL.
	 *
	 * @param array $args {
	 *     The arguments to create a single story card.
	 *
	 *     @type WP_Post|int post The post object or ID in which to search for the featured image.
	 *     @type string      size The size of the image.
	 *     @type bool        disable_link Whether to disable the link in the card container.
	 * }
	 */
	public static function the_single_story_card( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'post'         => null,
				'size'         => 'full',
				'disable_link' => false,
			]
		);

		$post = get_post( $args['post'] );
		if ( ! $post ) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( ! $thumbnail_id || ! is_object( $post ) ) {
			return;
		}

		$author_id           = $post->post_author;
		$author_display_name = get_the_author_meta( 'display_name', $author_id );
		$wrapper_tag_name    = $args['disable_link'] ? 'div' : 'a';
		$avatar              = get_avatar(
			$author_id,
			24,
			'',
			'',
			[
				'class' => 'latest-stories__avatar',
			]
		);
		if ( ! $args['disable_link'] ) {
			$href = sprintf(
				'href="%s"',
				esc_url( get_permalink( $post ) )
			);
		}

		?>
		<<?php echo esc_attr( $wrapper_tag_name ); ?> <?php echo isset( $href ) ? wp_kses_post( $href ) : ''; ?> class="latest_stories__link">
			<?php
			$url = wp_get_attachment_image_url( $thumbnail_id, $args['size'] );
			printf(
				'<img src="%s" width="%d" height="%d" alt="%s" class="latest-stories__featured-img" data-amp-layout="fixed">',
				esc_url( $url ),
				esc_attr( AMP_Story_Media::STORY_SMALL_IMAGE_DIMENSION / 2 ),
				esc_attr( AMP_Story_Media::STORY_LARGE_IMAGE_DIMENSION / 2 ),
				esc_attr( get_the_title( $post ) )
			);
			?>
			<span class="latest-stories__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
			<div class="latest-stories__meta">
				<?php echo wp_kses_post( $avatar ); ?>
				<span class="latest-stories__author">
					<?php
					printf(
						/* translators: 1: the post author. 2: the amount of time ago. */
						esc_html__( '%1$s &#8226; %2$s ago', 'amp' ),
						esc_html( $author_display_name ),
						esc_html( human_time_diff( get_post_time( 'U', false, $post ), current_time( 'timestamp' ) ) )
					);
					?>
				</span>
			</div>
		</<?php echo esc_attr( $wrapper_tag_name ); ?>>
		<?php
	}

	/**
	 * Enqueues this post type's stylesheet for the embed endpoint and Latest Stories block.
	 */
	public static function enqueue_embed_styling() {
		if ( is_embed() && is_singular( self::POST_TYPE_SLUG ) ) {
			wp_enqueue_style( self::STORY_CARD_CSS_SLUG );
		}
	}

	/**
	 * Overrides the render_callback of an AMP story post embed, when using the WordPress (embed) block.
	 *
	 * WordPress post embeds are usually wrapped in an <iframe>,
	 * which can cause validation and display issues in AMP.
	 * This overrides the embed callback in that case, replacing the <iframe> with the simple AMP story card.
	 *
	 * @param string $pre_render The pre-rendered markup, default null.
	 * @param array  $block The block to render.
	 * @return string|null $rendered_markup The rendered markup, or null to not override the existing render_callback.
	 */
	public static function override_story_embed_callback( $pre_render, $block ) {
		if ( ! isset( $block['attrs']['url'], $block['blockName'] ) || ! in_array( $block['blockName'], [ 'core-embed/wordpress', 'core/embed' ], true ) ) {
			return $pre_render;
		}

		// Taken from url_to_postid(), ensures that the URL is from this site.
		$url           = $block['attrs']['url'];
		$url_host      = wp_parse_url( $url, PHP_URL_HOST );
		$home_url_host = wp_parse_url( home_url(), PHP_URL_HOST );

		// Exit if the URL isn't from this site.
		if ( $url_host !== $home_url_host ) {
			return $pre_render;
		}

		$embed_url_path = wp_parse_url( $url, PHP_URL_PATH );
		$base_url_path  = wp_parse_url( trailingslashit( home_url( self::REWRITE_SLUG ) ), PHP_URL_PATH );
		if ( 0 !== strpos( $embed_url_path, $base_url_path ) ) {
			return $pre_render;
		}
		$path = substr( $embed_url_path, strlen( $base_url_path ) );
		$post = get_post( get_page_by_path( $path, OBJECT, self::POST_TYPE_SLUG ) );

		if ( self::POST_TYPE_SLUG !== get_post_type( $post ) ) {
			return $pre_render;
		}

		wp_enqueue_style( self::STORY_CARD_CSS_SLUG );
		ob_start();
		?>
		<div class="amp-story-embed">
			<?php
			self::the_single_story_card(
				[
					'post' => $post,
					'size' => AMP_Story_Media::STORY_CARD_IMAGE_SIZE,
				]
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Registers the dynamic block Latest Stories.
	 * Much of this is taken from the Core block Latest Posts.
	 *
	 * @see register_block_core_latest_posts()
	 */
	public static function register_block_latest_stories() {
		register_block_type(
			'amp/amp-latest-stories',
			[
				'attributes'      => [
					'className'     => [
						'type' => 'string',
					],
					'storiesToShow' => [
						'type'    => 'number',
						'default' => 5,
					],
					'order'         => [
						'type'    => 'string',
						'default' => 'desc',
					],
					'orderBy'       => [
						'type'    => 'string',
						'default' => 'date',
					],
					'useCarousel'   => [
						'type'    => 'boolean',
						'default' => ! is_admin(),
					],
				],
				'render_callback' => [ __CLASS__, 'render_block_latest_stories' ],
			]
		);
	}

	/**
	 * Renders the dynamic block Latest Stories.
	 * Much of this is taken from the Core block Latest Posts.
	 *
	 * @see render_block_core_latest_posts()
	 * @param array $attributes The block attributes.
	 * @return string $markup The rendered block markup.
	 */
	public static function render_block_latest_stories( $attributes ) {
		$is_amp_carousel = ! empty( $attributes['useCarousel'] );
		$args            = [
			'post_type'        => self::POST_TYPE_SLUG,
			'posts_per_page'   => $attributes['storiesToShow'],
			'post_status'      => 'publish',
			'order'            => $attributes['order'],
			'orderby'          => $attributes['orderBy'],
			'suppress_filters' => false,
			'meta_key'         => '_thumbnail_id',
		];
		$story_query     = new WP_Query( $args );
		$class           = 'amp-block-latest-stories';
		if ( isset( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}
		$size        = AMP_Story_Media::STORY_CARD_IMAGE_SIZE;
		$meta_height = 76;
		$min_height  = AMP_Story_Media::STORY_LARGE_IMAGE_DIMENSION / 2 + $meta_height;

		ob_start();
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<?php if ( $is_amp_carousel ) : ?>
				<amp-carousel layout="fixed-height" height="<?php echo esc_attr( $min_height ); ?>" type="carousel" class="latest-stories-carousel">
			<?php else : ?>
				<ul class="latest-stories-carousel">
			<?php endif; ?>
				<?php foreach ( $story_query->posts as $post ) : ?>
					<<?php echo $is_amp_carousel ? 'div' : 'li'; ?> class="slide latest-stories__slide">
						<?php
						self::the_single_story_card(
							[
								'post'         => $post,
								'size'         => $size,
								'disable_link' => ! $is_amp_carousel,
							]
						);
						?>
					</<?php echo $is_amp_carousel ? 'div' : 'li'; ?>>
					<?php
				endforeach;
				?>
			</<?php echo $is_amp_carousel ? 'amp-carousel' : 'ul'; ?>>
		</div>
		<?php

		wp_enqueue_style( self::STORY_CARD_CSS_SLUG );
		if ( $is_amp_carousel ) {
			wp_enqueue_script( 'amp-carousel' );
		}

		return ob_get_clean();
	}

	/**
	 * Registers the Page Attachment block.
	 */
	public static function register_block_page_attachment() {
		register_block_type(
			'amp/amp-story-page-attachment',
			[
				'attributes'      => [
					'postId'          => [
						'type' => 'number',
					],
					'title'           => [
						'type'    => 'string',
						'default' => '',
					],
					'openText'        => [
						'type'    => 'string',
						'default' => __( 'Swipe up', 'amp' ),
					],
					'theme'           => [
						'type'    => 'string',
						'default' => 'light',
					],
					'wrapperStyle'    => [
						'type'    => 'object',
						'default' => [],
					],
					'attachmentClass' => [
						'type'    => 'string',
						'default' => 'amp-page-attachment-content',
					],
				],
				'render_callback' => [ __CLASS__, 'render_block_page_attachment' ],
			]
		);
	}

	/**
	 * Renders the dynamic block Page Attachment.
	 *
	 * @param array $attributes The block attributes.
	 * @return string $markup The rendered block markup.
	 */
	public static function render_block_page_attachment( $attributes ) {
		global $post;

		if ( empty( $attributes['postId'] ) ) {
			return null;
		}

		$content_post = get_post( absint( $attributes['postId'] ) );

		if ( empty( $content_post ) ) {
			return null;
		}

		$original_post = $post;
		$post          = $content_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Remove filter for not adding grid layer to blocks within the attachment content.
		remove_filter( 'render_block', [ __CLASS__, 'render_block_with_grid_layer' ], 10 );

		setup_postdata( $content_post );

		$style = '';
		if ( isset( $attributes['wrapperStyle']['backgroundColor'] ) ) {
			$style .= 'background-color:' . $attributes['wrapperStyle']['backgroundColor'] . ';';
		}
		if ( isset( $attributes['wrapperStyle']['color'] ) ) {
			$style .= 'color:' . $attributes['wrapperStyle']['color'] . ';';
		}

		ob_start();
		?>
		<amp-story-page-attachment layout="nodisplay" theme="light" data-cta-text="<?php echo esc_attr( $attributes['openText'] ); ?>" data-title="<?php echo esc_attr( $attributes['title'] ); ?>">
			<div class="<?php echo esc_attr( $attributes['attachmentClass'] ); ?>" style="<?php echo esc_attr( $style ); ?>">
				<h2><?php the_title(); ?></h2>
				<div class="amp-page-attachment-inner-content">
					<?php the_content(); ?>
				</div>
			</div>
		</amp-story-page-attachment>
		<?php
		wp_reset_postdata();

		// Add filter back.
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_grid_layer' ], 10, 2 );

		$output = ob_get_clean();

		$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $output;
	}

	/**
	 * Add RSS feed link for stories.
	 *
	 * @since 1.2
	 */
	public static function print_feed_link() {
		$post_type_object = get_post_type_object( self::POST_TYPE_SLUG );
		$feed_url         = add_query_arg(
			'post_type',
			self::POST_TYPE_SLUG,
			get_feed_link()
		);
		printf(
			'<link rel="alternate" type="%s" title="%s" href="%s">',
			esc_attr( feed_content_type() ),
			esc_attr( $post_type_object->labels->name ),
			esc_url( $feed_url )
		);
	}

	/**
	 * For amp_story embeds, removes the title from above the <iframe>.
	 *
	 * @param string  $output The output to filter.
	 * @param WP_Post $post The post for the embed.
	 * @return string $output The filtered output.
	 */
	public static function remove_title_from_embed( $output, $post ) {
		if ( self::POST_TYPE_SLUG !== get_post_type( $post ) ) {
			return $output;
		}

		return preg_replace( '/<blockquote class="wp-embedded-content">.*?<\/blockquote>/', '', $output );
	}

	/**
	 * Changes the height of the AMP Story embed <iframe>.
	 *
	 * In the block editor, this embed typically appears in an <iframe>, though on the front-end it's not in an <iframe>.
	 * The height of the <iframe> isn't enough to display the full story, so this increases it.
	 *
	 * @param string  $output The embed output.
	 * @param WP_Post $post The post for the embed.
	 * @return string The filtered embed output.
	 */
	public static function change_embed_iframe_attributes( $output, $post ) {
		if ( self::POST_TYPE_SLUG !== get_post_type( $post ) ) {
			return $output;
		}

		// Add 4px more height, as the <iframe> needs that to display the full image.
		$new_height = (string) ( ( AMP_Story_Media::STORY_LARGE_IMAGE_DIMENSION / 2 ) + 4 );
		return preg_replace(
			'/(<iframe sandbox="allow-scripts"[^>]*\sheight=")(\w+)("[^>]*>)/',
			sprintf( '${1}%s${3}', $new_height ),
			$output
		);
	}

	/**
	 * Checks for `story_export` and valid Ajax nonce.
	 *
	 * @return bool
	 */
	public static function can_export() {
		return is_singular( self::POST_TYPE_SLUG ) && isset( $_GET['story_export'] ) && check_ajax_referer( self::AMP_STORIES_AJAX_ACTION, 'nonce', false );
	}

	/**
	 * Get the args used during a story export.
	 *
	 * @param string $slug The slug used to build the new `canonical_url`.
	 *
	 * @return array
	 */
	public static function get_export_args( $slug = '' ) {
		$base_url = untrailingslashit( AMP_Options_Manager::get_option( 'story_export_base_url' ) );

		return [
			'base_url'      => esc_url( $base_url ),
			'canonical_url' => ( $base_url && $slug ) ? esc_url( trailingslashit( $base_url ) . $slug ) : false,
		];
	}

	/**
	 * Returns an asset basename where the date directory structure is retained to avoid filename collisions.
	 *
	 * This means that an asset like `https://sample.org/wp-content/uploads/2019/07/sample.jpg`
	 * returns the basename `2019-07-sample.jpg` instead of `sample.jpg`.
	 *
	 * @param string $asset The URL of the export asset.
	 *
	 * @return string
	 */
	public static function export_image_basename( $asset ) {
		$asset = preg_replace_callback(
			'/uploads\/(.*)/',
			static function( $matches ) {
				return str_replace( '/', '-', $matches[1] );
			},
			$asset
		);

		return basename( $asset );
	}

	/**
	 * Ajax handler to export the story ZIP archive.
	 *
	 * This method returns an error as JSON and the binary data on success.
	 */
	public static function handle_export() {
		check_ajax_referer( self::AMP_STORIES_AJAX_ACTION, 'nonce' );

		// Get the post ID.
		$post_id = isset( $_POST['post_ID'] ) ? absint( wp_unslash( $_POST['post_ID'] ) ) : 0;

		// The user must have the correct permissions.
		if ( ! current_user_can( 'publish_post', $post_id ) ) {
			wp_send_json_error(
				[
					'errorMessage' => esc_html__( 'You do not have the required permissions to export stories.', 'amp' ),
				],
				403
			);
		}

		// We need the ZipArchive class to make this work.
		if ( ! class_exists( 'ZipArchive', false ) ) {
			wp_send_json_error(
				[
					/* translators: %s is the ZipArchive class name. */
					'errorMessage' => sprintf( esc_html__( 'The %s class is required to export stories.', 'amp' ), 'ZipArchive' ),
				],
				400
			);
		}

		// Bail if the user has not saved the story yet.
		if ( 'auto-draft' === get_post_status( $post_id ) ) {
			wp_send_json_error(
				[
					'errorMessage' => esc_html__( 'Save the story before exporting.', 'amp' ),
				],
				401
			);
		}

		// Generate and export the archive.
		$export = self::generate_export( $post_id );

		// Export failed.
		if ( is_wp_error( $export ) ) {
			$error_data = $export->get_error_data();

			if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
				$status = $error_data['status'];
			} else {
				$status = 500;
			}

			wp_send_json_error(
				[
					'errorMessage' => $export->get_error_message(),
				],
				$status
			);
		}

		// Failed to export for an unknown reason not related to generating the archive.
		wp_send_json_error(
			[
				'errorMessage' => esc_html__( 'Could not generate the story archive.', 'amp' ),
			],
			500
		);
	}

	/**
	 * Generates a Zip archive from the AMP Story.
	 *
	 * @param int $post_id The post ID of the AMP Story.
	 * @return WP_Error
	 */
	private static function generate_export( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'amp_story_export_invalid_post', esc_html__( 'The story does not exist.', 'amp' ) );
		}

		$slug = sanitize_title( $post->post_title, $post->ID );
		$file = wp_tempnam( $slug );

		$zip = new ZipArchive();
		$res = $zip->open( $file, ZipArchive::CREATE | ZipArchive::OVERWRITE );

		if ( true !== $res ) {
			/* translators: %s is the ZipArchive error code. */
			return new WP_Error( 'amp_story_zip_archive_error', sprintf( esc_html__( 'There was an error generating the ZIP archive. Error code: %s', 'amp' ), $res ) );
		}

		// Passed to `get_preview_post_link()` for nonce access and to sanitize the output.
		$query_args = [
			'story_export' => true,
			'_wpnonce'     => wp_create_nonce( self::AMP_STORIES_AJAX_ACTION ),
		];

		// Passed to `wp_remote_get()`.
		$args = [
			'cookies'     => wp_unslash( $_COOKIE ), // Pass along cookies so private pages and drafts can be accessed.
			'timeout'     => 20, // Increase from default of 5 to give extra time for the plugin to process story for exporting.
			'sslverify'   => false,
			'redirection' => 0, // Because we're in a loop for redirection.
			'headers'     => [
				'Cache-Control' => 'no-cache',
			],
		];

		// Get the preview URL.
		$response = wp_remote_get( get_preview_post_link( $post, $query_args ), $args );

		// Ensure we have the required data.
		if ( ! ( is_array( $response ) && isset( $response['body'] ) ) ) {
			return new WP_Error( 'amp_story_export_response', esc_html__( 'Could not retrieve story HTML.', 'amp' ) );
		}

		// Get the HTML from the response body.
		$html   = $response['body'];
		$assets = [];
		$regex  = '<!--\s*AMP_EXPORT_ASSETS\s*:\s*(\[.*?\])\s*-->';

		// Get the assets from the AMP_EXPORT_ASSETS comment.
		if ( preg_match( '#</body>.*?' . $regex . '#s', $html, $matches ) ) {
			$assets = json_decode( $matches[1], true );

			// Remove the comment.
			$html = preg_replace( '/' . $regex . '/s', '', $html );
		}

		// Create the zip directory.
		$zip->addEmptyDir( $slug );

		// Add README.txt file.
		$zip->addFromString(
			$slug . '/README.txt',
			file_get_contents( AMP__DIR__ . '/includes/story-export/readme.txt' ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		);

		// Add index.html file.
		$zip->addFromString( $slug . '/index.html', $html );

		// Add the assets.
		if ( ! empty( $assets ) ) {

			// Create the empty assets directory.
			$zip->addEmptyDir( $slug . '/assets' );

			foreach ( $assets as $asset ) {
				$response = wp_remote_get( $asset, [ 'sslverify' => false ] );
				if ( is_array( $response ) && ! empty( $response['body'] ) ) {
					$zip->addFromString( $slug . '/assets/' . self::export_image_basename( $asset ), $response['body'] );
				}
			}
		}

		// Close the active archive.
		$zip->close();

		// Read the file.
		$fo = @fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen, WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! $fo ) {
			return new WP_Error( 'amp_story_export_file_open', esc_html__( 'Could not open the generated ZIP archive.', 'amp' ) );
		}

		header( 'Content-type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sprintf( '%s.zip', $slug ) . '"' );
		header( 'Content-length: ' . filesize( $file ) );
		fpassthru( $fo );
		unlink( $file );
		die();
	}

	/**
	 * Returns the definitions for the stories settings.
	 *
	 * @since 1.3
	 *
	 * @return array
	 *
	 * - meta_args array Arguments passed to `register_meta`; sanitize_callback is required.
	 * - data      array Any additional data.
	 */
	public static function get_stories_settings_definitions() {
		return [
			'auto_advance_after'          => [
				'meta_args' => [
					'type'              => 'string',
					'sanitize_callback' => function( $value ) {
						$valid_values = [ '', 'auto', 'time', 'media' ];

						if ( ! in_array( $value, $valid_values, true ) ) {
							return '';
						}
						return $value;
					},
				],
				'data'      => [
					'options' => [
						[
							'value'       => '',
							'label'       => __( 'Manual', 'amp' ),
							'description' => '',
						],
						[
							'value'       => 'auto',
							'label'       => __( 'Automatic', 'amp' ),
							'description' => __( 'Based on the duration of all animated blocks on the page', 'amp' ),
						],
						[
							'value'       => 'time',
							'label'       => __( 'After a certain time', 'amp' ),
							'description' => '',
						],
						[
							'value'       => 'media',
							'label'       => __( 'After media has played', 'amp' ),
							'description' => __( 'Based on the first media block encountered on the page', 'amp' ),
						],
					],
				],
			],
			'auto_advance_after_duration' => [
				'meta_args' => [
					'type'              => 'integer',
					'sanitize_callback' => function( $value ) {
						$value = intval( $value );

						return filter_var(
							$value,
							FILTER_VALIDATE_INT,
							[
								'default'   => 0,
								'min_range' => 1,
								'max_range' => 100,
							]
						);
					},
				],
				'data'      => [],
			],
		];
	}

	/**
	 * Adds stories global settings as post meta to all new Stories.
	 *
	 * @param int      $post_id New Story post ID.
	 * @param \WP_Post $post    Story post object.
	 * @param bool     $update  Whether this is an update or a new post being created.
	 *
	 * @return void
	 */
	public static function add_story_settings_meta_to_new_story( $post_id, $post, $update ) {
		$is_story = ( self::POST_TYPE_SLUG === $post->post_type );

		if ( $update || ! $is_story ) {
			return;
		}

		$meta_definitions = self::get_stories_settings_definitions();
		$story_settings   = AMP_Options_Manager::get_option( self::STORY_SETTINGS_OPTION );

		foreach ( $story_settings as $option_key => $value ) {
			$sanitized_value = call_user_func( $meta_definitions[ $option_key ]['meta_args']['sanitize_callback'], $value );
			add_post_meta( $post_id, self::STORY_SETTINGS_META_PREFIX . $option_key, $sanitized_value, true );
		}
	}
}
