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
	 * AMP Stories style handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_STYLE_HANDLE = 'amp-story-style';

	/**
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_post_type(
			self::POST_TYPE_SLUG,
			array(
				'labels'       => array(
					'name'               => _x( 'AMP Stories', 'post type general name', 'amp' ),
					'singular_name'      => _x( 'AMP Story', 'post type singular name', 'amp' ),
					'menu_name'          => _x( 'AMP Stories', 'admin menu', 'amp' ),
					'name_admin_bar'     => _x( 'AMP Story', 'add new on admin bar', 'amp' ),
					'add_new'            => _x( 'Add New', 'amp_story', 'amp' ),
					'add_new_item'       => __( 'Add New AMP Story', 'amp' ),
					'new_item'           => __( 'New AMP Story', 'amp' ),
					'edit_item'          => __( 'Edit AMP Story', 'amp' ),
					'view_item'          => __( 'View AMP Story', 'amp' ),
					'all_items'          => __( 'All AMP Stories', 'amp' ),
					'not_found'          => __( 'No AMP Stories found.', 'amp' ),
					'not_found_in_trash' => __( 'No AMP Stories found in Trash.', 'amp' ),
				),
				'supports'     => array(
					'title', // Used for amp-story[title[.
					'editor',
					'thumbnail', // Used for poster images.
					'amp',
					'revisions', // Without this, the REST API will return 404 for an autosave request.
				),
				'rewrite'      => array(
					'slug' => self::REWRITE_SLUG,
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'template'     => array(
					array(
						'amp/amp-story-page',
						array(),
						array(
							array(
								'amp/amp-story-text',
								array(
									'placeholder' => __( 'Write something!', 'amp' ),
								),
							),
						),
					),
				),
			)
		);

		add_filter( 'post_row_actions', array( __CLASS__, 'remove_classic_editor_link' ), 11, 2 );

		add_filter( 'wp_kses_allowed_html', array( __CLASS__, 'filter_kses_allowed_html' ), 10, 2 );

		add_action( 'wp_default_styles', array( __CLASS__, 'register_story_card_styling' ) );

		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_styles' ) );

		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_custom_stories_styles' ) );

		// Remove unnecessary settings.
		add_filter( 'block_editor_settings', array( __CLASS__, 'filter_block_editor_settings' ), 10, 2 );

		// Used for amp-story[publisher-logo-src]: The publisher's logo in square format (1x1 aspect ratio). This will be supplied by the custom logo or else site icon.
		add_image_size( 'amp-publisher-logo', 100, 100, true );

		// Used for amp-story[poster-portrait-src]: The story poster in portrait format (3x4 aspect ratio).
		add_image_size( self::STORY_CARD_IMAGE_SIZE, 696, 928, true );

		// Used for amp-story[poster-square-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_SQUARE_IMAGE_SIZE, 928, 928, true );

		// Used for amp-story[poster-landscape-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_LANDSCAPE_IMAGE_SIZE, 928, 696, true );

		// In case there is no featured image for the poster-portrait-src, add a fallback image.
		add_filter( 'wp_get_attachment_image_src', array( __CLASS__, 'poster_portrait_fallback' ), 10, 3 );

		// If the image is for a poster-square-src or poster-landscape-src, this ensures that it's not too small.
		add_filter( 'wp_get_attachment_image_src', array( __CLASS__, 'ensure_correct_poster_size' ), 10, 3 );

		// Limit the styles that are printed in a story.
		add_filter( 'print_styles_array', array( __CLASS__, 'filter_frontend_print_styles_array' ) );
		add_filter( 'print_styles_array', array( __CLASS__, 'filter_editor_print_styles_array' ) );

		// Select the single-amp_story.php template for AMP Stories.
		add_filter( 'template_include', array( __CLASS__, 'filter_template_include' ) );

		// Get an embed template for this post type.
		add_filter( 'embed_template', array( __CLASS__, 'get_embed_template' ), 10, 3 );

		// Enqueue the styling for the /embed endpoint.
		add_action( 'embed_footer', array( __CLASS__, 'enqueue_embed_styling' ) );

		// Override the render_callback for AMP story embeds.
		add_filter( 'pre_render_block', array( __CLASS__, 'override_story_embed_callback' ), 10, 2 );

		// Register the Latest Stories block.
		add_action( 'wp_loaded', array( __CLASS__, 'register_block_latest_stories' ), 11 );

		// The AJAX handler for when an image is cropped and sent via POST.
		add_action( 'wp_ajax_custom-header-crop', array( __CLASS__, 'crop_featured_image' ) );

		// Register render callback for just-in-time inclusion of dependent Google Font styles.
		register_block_type(
			'amp/amp-story-text',
			array(
				'render_callback' => array( __CLASS__, 'render_text_block' ),
			)
		);

		// Omit the core theme sanitizer for the story template.
		add_filter(
			'amp_content_sanitizers',
			function( $sanitizers ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					unset( $sanitizers['AMP_Core_Theme_Sanitizer'] );
				}
				return $sanitizers;
			}
		);

		self::maybe_flush_rewrite_rules();
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
	 * Filter the allowed tags for Kses to allow for amp-story children.
	 *
	 * @param array $allowed_tags Allowed tags.
	 * @return array Allowed tags.
	 */
	public static function filter_kses_allowed_html( $allowed_tags ) {
		$story_components = array(
			'amp-story-page',
			'amp-story-grid-layer',
			'amp-story-cta-layer',
		);
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
			$allowed_tag['animate-in']          = true;
			$allowed_tag['animate-in-duration'] = true;
			$allowed_tag['animate-in-delay']    = true;
			$allowed_tag['animate-in-after']    = true;
			$allowed_tag['data-font-family']    = true;
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
			function( $handle ) {
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
			function( $handle ) {
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
			'amp-stories-editor',
			amp_get_asset_url( 'css/amp-stories-compiled.css' ),
			array( 'wp-edit-blocks' ),
			AMP__VERSION
		);

		wp_styles()->add_data( 'amp-stories-editor', 'rtl', true );

		wp_enqueue_style(
			'amp-stories-editor-blocks',
			amp_get_asset_url( 'css/amp-editor-story-blocks.css' ),
			array( 'wp-edit-blocks', 'amp-stories-editor' ),
			AMP__VERSION
		);

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
			array(),
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
		if ( self::POST_TYPE_SLUG === $post->post_type ) {
			unset( $editor_settings['colors'] );
		}

		if ( get_current_screen()->is_block_editor && isset( $editor_settings['styles'] ) ) {
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
			return array(
				amp_get_asset_url( 'images/story-fallback-poster.jpg' ),
				928,
				696,
			);
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
			array(),
			AMP__VERSION
		);
	}

	/**
	 * Enqueue scripts for the block editor.
	 */
	public static function enqueue_block_editor_scripts() {
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			return;
		}

		wp_enqueue_script(
			'amp-story-editor',
			amp_get_asset_url( 'js/amp-stories-compiled.js' ),
			array( 'wp-dom-ready', 'wp-editor', 'wp-edit-post', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components', 'amp-editor-blocks' ),
			AMP__VERSION,
			false
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			$translations = wp_set_script_translations( 'amp-editor-story-blocks-build', 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp-editor-story-blocks-build' ) : gutenberg_get_jed_locale_data( 'amp-editor-story-blocks-build' );
			$translations = wp_json_encode( $locale_data );
		}

		if ( ! empty( $translations ) ) {
			wp_add_inline_script(
				'amp-story-editor',
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'before'
			);
		}

		wp_localize_script(
			'amp-story-editor',
			'ampStoriesFonts',
			self::get_fonts()
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
			array( self::AMP_STORIES_STYLE_HANDLE ),
			AMP__VERSION,
			false
		);

		// Also enqueue this since it's possible to embed another story into a story.
		wp_enqueue_style(
			'amp-story-card',
			amp_get_asset_url( 'css/amp-story-card.css' ),
			array( self::AMP_STORIES_STYLE_HANDLE ),
			AMP__VERSION,
			false
		);

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

		$fonts = array(
			array(
				'name'      => 'Arial',
				'fallbacks' => array( 'Helvetica Neue', 'Helvetica', 'sans-serif' ),
			),
			array(
				'name'      => 'Arial Black',
				'fallbacks' => array( 'Arial Black', 'Arial Bold', 'Gadget', 'sans-serif' ),
			),
			array(
				'name'      => 'Arial Narrow',
				'fallbacks' => array( 'Arial', 'sans-serif' ),
			),
			array(
				'name'      => 'Arimo',
				'gfont'     => 'Arimo:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Baskerville',
				'fallbacks' => array( 'Baskerville Old Face', 'Hoefler Text', 'Garamond', 'Times New Roman', 'serif' ),
			),
			array(
				'name'      => 'Brush Script MT',
				'fallbacks' => array( 'cursive' ),
			),
			array(
				'name'      => 'Copperplate',
				'fallbacks' => array( 'Copperplate Gothic Light', 'fantasy' ),
			),
			array(
				'name'      => 'Courier New',
				'fallbacks' => array( 'Courier', 'Lucida Sans Typewriter', 'Lucida Typewriter', 'monospace' ),
			),
			array(
				'name'      => 'Century Gothic',
				'fallbacks' => array( 'CenturyGothic', 'AppleGothic', 'sans-serif' ),
			),
			array(
				'name'      => 'Garamond',
				'fallbacks' => array( 'Baskerville', 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', 'serif' ),
			),
			array(
				'name'      => 'Georgia',
				'fallbacks' => array( 'Times', 'Times New Roman', 'serif' ),
			),
			array(
				'name'      => 'Gill Sans',
				'fallbacks' => array( 'Gill Sans MT', 'Calibri', 'sans-serif' ),
			),
			array(
				'name'      => 'Lato',
				'gfont'     => 'Lato:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Lora',
				'gfont'     => 'Lora:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'Lucida Bright',
				'fallbacks' => array( 'Georgia', 'serif' ),
			),
			array(
				'name'      => 'Lucida Sans Typewriter',
				'fallbacks' => array( 'Lucida Console', 'monaco', 'Bitstream Vera Sans Mono', 'monospace' ),
			),
			array(
				'name'      => 'Merriweather',
				'gfont'     => 'Merriweather:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'Montserrat',
				'gfont'     => 'Montserrat:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Noto Sans',
				'gfont'     => 'Noto Sans:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Open Sans',
				'gfont'     => 'Open Sans:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Open Sans Condensed',
				'gfont'     => 'Open Sans Condensed:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Oswald',
				'gfont'     => 'Oswald:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Palatino',
				'fallbacks' => array( 'Palatino Linotype', 'Palatino LT STD', 'Book Antiqua', 'Georgia', 'serif' ),
			),
			array(
				'name'      => 'Papyrus',
				'fallbacks' => array( 'fantasy' ),
			),
			array(
				'name'      => 'Playfair Display',
				'gfont'     => 'Playfair Display:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'PT Sans',
				'gfont'     => 'PT Sans:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'PT Sans Narrow',
				'gfont'     => 'PT Sans Narrow:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'PT Serif',
				'gfont'     => 'PT Serif:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'Raleway',
				'gfont'     => 'Raleway:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Roboto',
				'gfont'     => 'Roboto:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Roboto Condensed',
				'gfont'     => 'Roboto Condensed:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Roboto Slab',
				'gfont'     => 'Roboto Slab:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'Slabo 27px',
				'gfont'     => 'Slabo 27px:400,700',
				'fallbacks' => array( 'serif' ),
			),
			array(
				'name'      => 'Source Sans Pro',
				'gfont'     => 'Source Sans Pro:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Tahoma',
				'fallbacks' => array( 'Verdana', 'Segoe', 'sans-serif' ),
			),
			array(
				'name'      => 'Times New Roman',
				'fallbacks' => array( 'Times New Roman', 'Times', 'Baskerville', 'Georgia', 'serif' ),
			),
			array(
				'name'      => 'Trebuchet MS',
				'fallbacks' => array( 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', 'Tahoma', 'sans-serif' ),
			),
			array(
				'name'      => 'Ubuntu',
				'gfont'     => 'Ubuntu:400,700',
				'fallbacks' => array( 'sans-serif' ),
			),
			array(
				'name'      => 'Verdana',
				'fallbacks' => array( 'Geneva', 'sans-serif' ),
			),
		);

		$fonts_url = 'https://fonts.googleapis.com/css';
		$subsets   = array( 'latin', 'latin-ext' );

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
			function ( $font ) use ( $fonts_url, $subsets ) {
				$font['slug'] = sanitize_title( $font['name'] );

				if ( isset( $font['gfont'] ) ) {
					$font['handle'] = sprintf( '%s-font', $font['slug'] );
					$font['src']    = add_query_arg(
						array(
							'family' => rawurlencode( $font['gfont'] ),
							'subset' => rawurlencode( implode( ',', $subsets ) ),
						),
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
			function ( $font ) use ( $name ) {
				return $font['name'] === $name;
			}
		);
		return array_shift( $fonts );
	}

	/**
	 * Include any required Google Font styles when rendering a Text block.
	 *
	 * @param array  $props   Props.
	 * @param string $content Content.
	 * @return string Text block.
	 */
	public static function render_text_block( $props, $content ) {
		$prop_name = 'ampFontFamily';

		// Short-circuit if no font family present.
		if ( empty( $props[ $prop_name ] ) ) {
			return $content;
		}

		// Short-circuit if there is no Google Font or the font is already enqueued.
		$font = self::get_font( $props[ $prop_name ] );
		if ( ! $font || ! isset( $font['handle'] ) || ! isset( $font['src'] ) || wp_style_is( $font['handle'], 'enqueued' ) ) {
			return $content;
		}

		if ( ! wp_style_is( $font['handle'], 'registered' ) ) {
			wp_register_style( $font['handle'], $font['src'], array(), null, 'all' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
		wp_enqueue_style( $font['handle'] );
		wp_add_inline_style(
			$font['handle'],
			self::get_inline_font_style_rule( $font )
		);

		return $content;
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
	 * Used for a slide in the Latest Stories block.
	 *
	 * @param WP_Post $post The AMP story post.
	 * @return void
	 */
	public static function the_single_story_card( $post ) {
		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( ! $thumbnail_id ) {
			return;
		}

		$author_id           = $post->post_author;
		$author_display_name = get_the_author_meta( 'display_name', $author_id );
		$avatar              = get_avatar(
			$author_id,
			24,
			'',
			'',
			array(
				'class' => 'latest-stories__avatar',
			)
		);

		?>
		<a class="latest_stories__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php
			echo wp_get_attachment_image(
				$thumbnail_id,
				self::STORY_CARD_IMAGE_SIZE,
				false,
				array(
					'alt'   => get_the_title( $post ),
					'class' => 'latest-stories__featured-img',
				)
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
						esc_html( human_time_diff( get_post_time( 'U', false, $post->ID ), current_time( 'timestamp' ) ) )
					);
					?>
				</span>
			</div>
		</a>
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
		if ( ! isset( $block['attrs']['url'], $block['blockName'] ) || ! in_array( $block['blockName'], array( 'core-embed/wordpress', 'core/embed' ), true ) ) {
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
			<?php self::the_single_story_card( $post ); ?>
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
			array(
				'attributes'      => array(
					'className'     => array(
						'type' => 'string',
					),
					'storiesToShow' => array(
						'type'    => 'number',
						'default' => 5,
					),
					'order'         => array(
						'type'    => 'string',
						'default' => 'desc',
					),
					'orderBy'       => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'useCarousel'   => array(
						'type'    => 'boolean',
						'default' => ! is_admin(),
					),
				),
				'render_callback' => array( __CLASS__, 'render_block_latest_stories' ),
			)
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
		/*
		 * There should only be an <amp-carousel> on the front-end,
		 * so the editor component passes useCarousel=false to <ServerSideRender>.
		 * This detects whether this render_callback is called in the editor context.
		 */
		$is_amp_carousel = ! empty( $attributes['useCarousel'] );
		$args            = array(
			'post_type'        => self::POST_TYPE_SLUG,
			'posts_per_page'   => $attributes['storiesToShow'],
			'post_status'      => 'publish',
			'order'            => $attributes['order'],
			'orderby'          => $attributes['orderBy'],
			'suppress_filters' => false,
			'meta_key'         => '_thumbnail_id',
		);
		$story_query     = new WP_Query( $args );
		$min_height      = self::get_featured_image_minimum_height( $story_query->posts );
		$class           = 'amp-block-latest-stories';
		if ( isset( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<?php if ( $is_amp_carousel ) : ?>
				<amp-carousel layout="fixed-height" height="<?php echo esc_attr( $min_height ); ?>" type="carousel" class="latest-stories-carousel">
			<?php else : ?>
				<ul class="latest-stories-carousel" style="height:<?php echo esc_attr( $min_height ); ?>px;">
			<?php endif; ?>
				<?php foreach ( $story_query->posts as $post ) : ?>
					<<?php echo $is_amp_carousel ? 'div' : 'li'; ?> class="slide latest-stories__slide">
						<?php self::the_single_story_card( $post ); ?>
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
	 * Flushes rewrite rules if it hasn't been done yet after having AMP Stories Post type.
	 */
	public static function maybe_flush_rewrite_rules() {
		$current_rules = get_option( 'rewrite_rules' );

		// If we're not using permalinks.
		if ( empty( $current_rules ) ) {
			return;
		}

		// Check if the rewrite rule for showing preview exists for different permalink settings.
		$story_rules = array_filter(
			array_keys( $current_rules ),
			function( $rule ) {
				return 0 === strpos( $rule, self::REWRITE_SLUG ) || false !== strpos( $rule, '/' . self::REWRITE_SLUG . '/' );
			}
		);
		if ( empty( $story_rules ) ) {
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Gets the smallest height of any of the featured images.
	 *
	 * This iterates through all of the posts, to find their featured image.
	 * Then, this returns the smallest height.
	 * For example, if $posts has 3 posts, with featured image heights of 100, 200 and 300,
	 * this will return 100.
	 *
	 * @param array $posts An array or WP_Post objects.
	 * @return int $minimum_dimension The smallest dimension of a featured image.
	 */
	public static function get_featured_image_minimum_height( $posts ) {
		$index = 2;

		$minimum_height = 0;
		foreach ( $posts as $post ) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if ( ! $thumbnail_id ) {
				continue;
			}

			$image = wp_get_attachment_image_src( $thumbnail_id, self::STORY_CARD_IMAGE_SIZE );
			if (
				isset( $image[ $index ] )
				&&
				(
					! $minimum_height
					||
					$image[ $index ] < $minimum_height
				)
			) {
				$minimum_height = $image[ $index ];
			}
		}

		return $minimum_height;
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

		$dimensions = array(
			'dst_width'  => 696,
			'dst_height' => 928,
		);

		$attachment_id = absint( $_POST['id'] );

		$cropped = wp_crop_image(
			$attachment_id,
			intval( $crop_details['x1'] ),
			intval( $crop_details['y1'] ),
			intval( $crop_details['width'] ),
			intval( $crop_details['height'] ),
			intval( $dimensions['dst_width'] ),
			intval( $dimensions['dst_height'] )
		);

		if ( ! $cropped || is_wp_error( $cropped ) ) {
			wp_send_json_error( array( 'message' => __( 'Image could not be processed. Please go back and try again.', 'default' ) ) );
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

		$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
		$object     = array(
			'ID'             => $parent_attachment_id,
			'post_title'     => basename( $cropped ),
			'post_mime_type' => $image_type,
			'guid'           => $url,
			'context'        => 'amp-story-poster',
			'post_parent'    => $parent_attachment_id,
		);

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
}
