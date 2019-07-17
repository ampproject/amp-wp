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
	 * Minimum required version of Gutenberg required.
	 *
	 * @var string
	 */
	const REQUIRED_GUTENBERG_VERSION = '5.8';

	/**
	 * The image size for the AMP story card, used in an embed and the Latest Stories block.
	 *
	 * @var string
	 */
	const STORY_CARD_IMAGE_SIZE = 'amp-story-poster-portrait';

	/**
	 * The image size for the poster-landscape-src.
	 *
	 * @var string
	 */
	const STORY_LANDSCAPE_IMAGE_SIZE = 'amp-story-poster-landscape';

	/**
	 * The image size for the poster-square-src.
	 *
	 * @var string
	 */
	const STORY_SQUARE_IMAGE_SIZE = 'amp-story-poster-square';

	/**
	 * The slug of the largest image size allowed in an AMP Story page.
	 *
	 * @var string
	 */
	const MAX_IMAGE_SIZE_SLUG = 'amp_story_page';

	/**
	 * The large dimension of the AMP Story poster images.
	 *
	 * @var int
	 */
	const STORY_LARGE_IMAGE_DIMENSION = 928;

	/**
	 * The small dimension of the AMP Story poster images.
	 *
	 * @var int
	 */
	const STORY_SMALL_IMAGE_DIMENSION = 696;

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
	 * Check if the required version of block capabilities available.
	 *
	 * Note that Gutenberg requires WordPress 5.0, so this check also accounts for that.
	 *
	 * @todo Eventually the Gutenberg requirement should be removed.
	 *
	 * @return bool Whether capabilities are available.
	 */
	public static function has_required_block_capabilities() {
		if ( ! function_exists( 'register_block_type' ) || version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			return false;
		}
		return (
			( defined( 'GUTENBERG_DEVELOPMENT_MODE' ) && GUTENBERG_DEVELOPMENT_MODE )
			||
			( defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, self::REQUIRED_GUTENBERG_VERSION, '>=' ) )
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

		// Remove unnecessary settings.
		add_filter( 'block_editor_settings', [ __CLASS__, 'filter_block_editor_settings' ], 10, 2 );

		// Used for amp-story[publisher-logo-src]: The publisher's logo in square format (1x1 aspect ratio). This will be supplied by the custom logo or else site icon.
		add_image_size( 'amp-publisher-logo', 100, 100, true );

		// Used for amp-story[poster-portrait-src]: The story poster in portrait format (3x4 aspect ratio).
		add_image_size( self::STORY_CARD_IMAGE_SIZE, self::STORY_SMALL_IMAGE_DIMENSION, self::STORY_LARGE_IMAGE_DIMENSION, true );

		// Used for amp-story[poster-square-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_SQUARE_IMAGE_SIZE, self::STORY_LARGE_IMAGE_DIMENSION, self::STORY_LARGE_IMAGE_DIMENSION, true );

		// Used for amp-story[poster-landscape-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_LANDSCAPE_IMAGE_SIZE, self::STORY_LARGE_IMAGE_DIMENSION, self::STORY_SMALL_IMAGE_DIMENSION, true );

		// The default image size for AMP Story image block and background media image.
		add_image_size( self::MAX_IMAGE_SIZE_SLUG, 99999, 1440 );

		// In case there is no featured image for the poster-portrait-src, add a fallback image.
		add_filter( 'wp_get_attachment_image_src', [ __CLASS__, 'poster_portrait_fallback' ], 10, 3 );

		// If the image is for a poster-square-src or poster-landscape-src, this ensures that it's not too small.
		add_filter( 'wp_get_attachment_image_src', [ __CLASS__, 'ensure_correct_poster_size' ], 10, 3 );

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

		// The AJAX handler for when an image is cropped and sent via POST.
		add_action( 'wp_ajax_custom-header-crop', [ __CLASS__, 'crop_featured_image' ] );

		// The AJAX handler for exporting an AMP story.
		add_action( 'wp_ajax_' . self::AMP_STORIES_AJAX_ACTION, [ __CLASS__, 'handle_export' ] );

		// Register render callback for just-in-time inclusion of dependent Google Font styles.
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_google_fonts' ], 10, 2 );

		add_filter( 'use_block_editor_for_post_type', [ __CLASS__, 'use_block_editor_for_story_post_type' ], PHP_INT_MAX, 2 );
		add_filter( 'classic_editor_enabled_editors_for_post_type', [ __CLASS__, 'filter_enabled_editors_for_story_post_type' ], PHP_INT_MAX, 2 );

		add_filter( 'image_size_names_choose', [ __CLASS__, 'add_new_max_image_size' ] );

		self::register_block_latest_stories();

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
					function ( $matches ) use ( $style_attr_values ) {
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
			amp_get_asset_url( 'css/amp-stories-editor.css' ),
			[ 'wp-edit-blocks' ],
			AMP__VERSION
		);

		wp_enqueue_style(
			self::AMP_STORIES_EDITOR_STYLE_HANDLE . '-compiled',
			amp_get_asset_url( 'css/amp-stories-editor-compiled.css' ),
			[ 'wp-edit-blocks', 'amp-stories' ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::AMP_STORIES_EDITOR_STYLE_HANDLE . '-compiled', 'rtl', true );

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

		$fonts = self::get_fonts();
		foreach ( $fonts as $font ) {
			wp_add_inline_style(
				self::AMP_STORIES_STYLE_HANDLE,
				self::get_inline_font_style_rule( $font )
			);
		}
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
	 * If there's no featured image for the poster-portrait-src, this adds a fallback.
	 *
	 * @param array|false  $image The featured image, or false.
	 * @param int          $attachment_id The ID of the image.
	 * @param string|array $size The size of the image.
	 * @return array|false The featured image, or false.
	 */
	public static function poster_portrait_fallback( $image, $attachment_id, $size ) {
		if ( ! $image && self::STORY_CARD_IMAGE_SIZE === $size ) {
			return [
				amp_get_asset_url( 'images/story-fallback-poster.jpg' ),
				self::STORY_LARGE_IMAGE_DIMENSION,
				self::STORY_SMALL_IMAGE_DIMENSION,
			];
		}

		return $image;
	}

	/**
	 * Helps to ensure that the poster-square-src and poster-landscape-src images aren't too small.
	 *
	 * These values come from the featured image.
	 * But the featured image is often cropped down to 696 x 928.
	 * So from that, it's not possible to get a 928 x 928 image, for example.
	 * So instead, use the source image that was cropped, instead of the cropped image.
	 * This is more likely to produce the right size image.
	 *
	 * @param array|false  $image The featured image, or false.
	 * @param int          $attachment_id The ID of the image.
	 * @param string|array $size The size of the image.
	 * @return array|false The featured image, or false.
	 */
	public static function ensure_correct_poster_size( $image, $attachment_id, $size ) {
		if ( self::STORY_LANDSCAPE_IMAGE_SIZE === $size || self::STORY_SQUARE_IMAGE_SIZE === $size ) {
			$attachment_meta = wp_get_attachment_metadata( $attachment_id );
			// The source image that was cropped.
			if ( ! empty( $attachment_meta['attachment_parent'] ) ) {
				return wp_get_attachment_image_src( $attachment_meta['attachment_parent'], $size );
			}
		}
		return $image;
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
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			return;
		}

		$script_deps_path    = AMP__DIR__ . '/assets/js/' . self::AMP_STORIES_SCRIPT_HANDLE . '.deps.json';
		$script_dependencies = file_exists( $script_deps_path )
			? json_decode( file_get_contents( $script_deps_path ), false ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			: [];

		wp_enqueue_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			amp_get_asset_url( 'js/' . self::AMP_STORIES_SCRIPT_HANDLE . '.js' ),
			$script_dependencies,
			AMP__VERSION,
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
		$post = get_post();
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		wp_enqueue_style(
			'amp-stories-frontend',
			amp_get_asset_url( 'css/amp-stories-frontend.css' ),
			[ self::AMP_STORIES_STYLE_HANDLE ],
			AMP__VERSION,
			false
		);

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
				'name'      => 'Arimo',
				'gfont'     => 'Arimo:400,700',
				'fallbacks' => [ 'sans-serif' ],
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
				'name'      => 'Lato',
				'gfont'     => 'Lato:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Lora',
				'gfont'     => 'Lora:400,700',
				'fallbacks' => [ 'serif' ],
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
				'name'      => 'Merriweather',
				'gfont'     => 'Merriweather:400,700',
				'fallbacks' => [ 'serif' ],
			],
			[
				'name'      => 'Montserrat',
				'gfont'     => 'Montserrat:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Noto Sans',
				'gfont'     => 'Noto Sans:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Open Sans',
				'gfont'     => 'Open Sans:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Open Sans Condensed',
				'gfont'     => 'Open Sans Condensed:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Oswald',
				'gfont'     => 'Oswald:400,700',
				'fallbacks' => [ 'sans-serif' ],
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
				'name'      => 'Playfair Display',
				'gfont'     => 'Playfair Display:400,700',
				'fallbacks' => [ 'serif' ],
			],
			[
				'name'      => 'PT Sans',
				'gfont'     => 'PT Sans:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'PT Sans Narrow',
				'gfont'     => 'PT Sans Narrow:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'PT Serif',
				'gfont'     => 'PT Serif:400,700',
				'fallbacks' => [ 'serif' ],
			],
			[
				'name'      => 'Raleway',
				'gfont'     => 'Raleway:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Roboto',
				'gfont'     => 'Roboto:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Roboto Condensed',
				'gfont'     => 'Roboto Condensed:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Roboto Slab',
				'gfont'     => 'Roboto Slab:400,700',
				'fallbacks' => [ 'serif' ],
			],
			[
				'name'      => 'Slabo 27px',
				'gfont'     => 'Slabo 27px:400,700',
				'fallbacks' => [ 'serif' ],
			],
			[
				'name'      => 'Source Sans Pro',
				'gfont'     => 'Source Sans Pro:400,700',
				'fallbacks' => [ 'sans-serif' ],
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
				'name'      => 'Ubuntu',
				'gfont'     => 'Ubuntu:400,700',
				'fallbacks' => [ 'sans-serif' ],
			],
			[
				'name'      => 'Verdana',
				'fallbacks' => [ 'Geneva', 'sans-serif' ],
			],
		];

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

				if ( isset( $font['gfont'] ) ) {
					$font['handle'] = sprintf( '%s-font', $font['slug'] );
					$font['src']    = add_query_arg(
						[
							'family' => rawurlencode( $font['gfont'] ),
							'subset' => rawurlencode( implode( ',', $subsets ) ),
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
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Block content.
	 */
	public static function render_block_with_google_fonts( $block_content, $block ) {
		$font_family_attribute = 'ampFontFamily';

		// Short-circuit if no font family present.
		if ( empty( $block['attrs'][ $font_family_attribute ] ) ) {
			return $block_content;
		}

		// Short-circuit if there is no Google Font or the font is already enqueued.
		$font = self::get_font( $block['attrs'][ $font_family_attribute ] );
		if ( ! isset( $font['handle'], $font['src'] ) || ! $font || wp_style_is( $font['handle'] ) ) {
			return $block_content;
		}

		if ( ! wp_style_is( $font['handle'], 'registered' ) ) {
			wp_register_style( $font['handle'], $font['src'], [], null, 'all' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}

		wp_enqueue_style( $font['handle'] );
		wp_add_inline_style(
			$font['handle'],
			self::get_inline_font_style_rule( $font )
		);

		return $block_content;
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
				esc_attr( self::STORY_SMALL_IMAGE_DIMENSION / 2 ),
				esc_attr( self::STORY_LARGE_IMAGE_DIMENSION / 2 ),
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
					'size' => self::STORY_CARD_IMAGE_SIZE,
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
		$size        = self::STORY_CARD_IMAGE_SIZE;
		$meta_height = 76;
		$min_height  = self::STORY_LARGE_IMAGE_DIMENSION / 2 + $meta_height;

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
	 * Crops the image and returns the object as JSON.
	 *
	 * Forked from Custom_Image_Header::ajax_header_crop().
	 */
	public static function crop_featured_image() {
		check_ajax_referer( 'image_editor-' . $_POST['id'], 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error();
		}

		$crop_details = $_POST['cropDetails'];

		$dimensions = [
			'dst_width'  => self::STORY_SMALL_IMAGE_DIMENSION,
			'dst_height' => self::STORY_LARGE_IMAGE_DIMENSION,
		];

		$attachment_id = absint( $_POST['id'] );

		$cropped = wp_crop_image(
			$attachment_id,
			(int) $crop_details['x1'],
			(int) $crop_details['y1'],
			(int) $crop_details['width'],
			(int) $crop_details['height'],
			(int) $dimensions['dst_width'],
			(int) $dimensions['dst_height']
		);

		if ( ! $cropped || is_wp_error( $cropped ) ) {
			wp_send_json_error( [ 'message' => __( 'Image could not be processed. Please go back and try again.', 'default' ) ] );
		}

		/** This filter is documented in wp-admin/custom-header.php */
		$cropped = apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.
		$object  = self::create_attachment_object( $cropped, $attachment_id );
		unset( $object['ID'] );

		$new_attachment_id       = self::insert_attachment( $object, $cropped );
		$object['attachment_id'] = $new_attachment_id;
		$object['url']           = wp_get_attachment_url( $new_attachment_id );
		$object['width']         = $dimensions['dst_width'];
		$object['height']        = $dimensions['dst_height'];

		wp_send_json_success( $object );
	}

	/**
	 * Create an attachment 'object'.
	 *
	 * Forked from Custom_Image_Header::create_attachment_object() in Core.
	 *
	 * @param string $cropped Cropped image URL.
	 * @param int    $parent_attachment_id Attachment ID of parent image.
	 * @return array Attachment object.
	 */
	public static function create_attachment_object( $cropped, $parent_attachment_id ) {
		$parent     = get_post( $parent_attachment_id );
		$parent_url = wp_get_attachment_url( $parent->ID );
		$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );
		try {
			$size = getimagesize( $cropped );
		} catch ( Exception $error ) {
			unset( $error );
		}

		$image_type = $size ? $size['mime'] : 'image/jpeg';
		$object     = [
			'ID'             => $parent_attachment_id,
			'post_title'     => basename( $cropped ),
			'post_mime_type' => $image_type,
			'guid'           => $url,
			'context'        => 'amp-story-poster',
			'post_parent'    => $parent_attachment_id,
		];

		return $object;
	}

	/**
	 * Insert an attachment and its metadata.
	 *
	 * Forked from Custom_Image_Header::insert_attachment() in Core.
	 *
	 * @param array  $object  Attachment object.
	 * @param string $cropped Cropped image URL.
	 * @return int Attachment ID.
	 */
	public static function insert_attachment( $object, $cropped ) {
		$parent_id = isset( $object['post_parent'] ) ? $object['post_parent'] : null;
		unset( $object['post_parent'] );

		$attachment_id = wp_insert_attachment( $object, $cropped );
		$metadata      = wp_generate_attachment_metadata( $attachment_id, $cropped );

		// If this is a crop, save the original attachment ID as metadata.
		if ( $parent_id ) {
			$metadata['attachment_parent'] = $parent_id;
		}
		wp_update_attachment_metadata( $attachment_id, $metadata );

		return $attachment_id;
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
		$new_height = (string) ( ( self::STORY_LARGE_IMAGE_DIMENSION / 2 ) + 4 );
		return preg_replace(
			'/(<iframe sandbox="allow-scripts"[^>]*\sheight=")(\w+)("[^>]*>)/',
			sprintf( '${1}%s${3}', $new_height ),
			$output
		);
	}

	/**
	 * Adds a new max image size to the image sizes available.
	 *
	 * In the AMP story editor, when selecting Background Media,
	 * it will use this custom image size.
	 * This filter will also make it available in the Image block's 'Image Size' <select> element.
	 *
	 * @param array $image_sizes {
	 *     An associative array of image sizes.
	 *
	 *     @type string $slug Image size slug, like 'medium'.
	 *     @type string $name Image size name, like 'Medium'.
	 * }
	 * @return array $image_sizes The filtered image sizes.
	 */
	public static function add_new_max_image_size( $image_sizes ) {
		$full_size_name = __( 'Story Max Size', 'amp' );

		if ( isset( $_POST['action'] ) && ( 'query-attachments' === $_POST['action'] || 'upload-attachment' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$image_sizes[ self::MAX_IMAGE_SIZE_SLUG ] = $full_size_name;
		} elseif ( get_post_type() && self::POST_TYPE_SLUG === get_post_type() ) {
			$image_sizes[ self::MAX_IMAGE_SIZE_SLUG ] = $full_size_name;
			unset( $image_sizes['full'] );
		}

		return $image_sizes;
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
			function( $matches ) {
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
}
