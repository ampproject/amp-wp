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
					'slug' => 'stories',
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

		// Forcibly sanitize all validation errors.
		add_action(
			'wp',
			function() {
				if ( is_singular( AMP_Story_Post_Type::POST_TYPE_SLUG ) ) {
					add_filter( 'amp_validation_error_sanitized', '__return_true' );
				}
			}
		);

		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_custom_block_styles' ) );

		// Used for amp-story[publisher-logo-src]: The publisher's logo in square format (1x1 aspect ratio). This will be supplied by the custom logo or else site icon.
		add_image_size( 'amp-publisher-logo', 100, 100, true );

		// Used for amp-story[poster-portrait-src]: The story poster in portrait format (3x4 aspect ratio).
		add_image_size( 'amp-story-poster-portrait', 300, 400, true );

		// Used for amp-story[poster-square-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( 'amp-story-poster-square', 100, 100, true );

		// Used for amp-story[poster-landscape-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( 'amp-story-poster-landscape', 400, 300, true );

		add_filter( 'template_include', array( __CLASS__, 'filter_template_include' ) );
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
			$allowed_tag['data-font-family']    = true;
		}

		return $allowed_tags;
	}

	/**
	 * Enqueue block editor assets.
	 */
	public static function enqueue_block_editor_assets() {
		if ( self::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			return;
		}

		// This CSS is separately since it's used both in frontend and in the editor.
		$amp_stories_fonts_handle = 'amp-story-fonts';// @todo This should be renamed since no longer fonts?
		wp_enqueue_style(
			$amp_stories_fonts_handle,
			amp_get_asset_url( 'css/amp-stories.css' ),
			false,
			AMP__VERSION
		);

		wp_enqueue_style(
			'amp-editor-story-blocks-style',
			amp_get_asset_url( 'css/amp-editor-story-blocks.css' ),
			array(),
			AMP__VERSION
		);

		// @todo Name the script better to distinguish.
		wp_enqueue_script(
			'amp-story-editor-blocks',
			amp_get_asset_url( 'js/amp-story-editor-blocks-compiled.js' ),
			array( 'wp-dom-ready', 'wp-editor', 'wp-edit-post', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components', 'amp-editor-blocks' ),
			AMP__VERSION,
			false
		);

		wp_enqueue_script(
			'amp-editor-story-blocks-build',
			amp_get_asset_url( 'js/amp-story-blocks-compiled.js' ),
			array( 'wp-editor', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
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
				'amp-editor-story-blocks-build',
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'before'
			);
		}

		$fonts = self::get_fonts();
		foreach ( $fonts as $font ) {
			$families = array_map(
				'wp_json_encode',
				array_merge( (array) $font['name'], $font['fallbacks'] )
			);
			wp_add_inline_style(
				$amp_stories_fonts_handle,
				sprintf(
					'[data-font-family="%s"] { font-family: %s; }',
					$font['name'],
					implode( ', ', $families )
				)
			);
		}

		wp_localize_script(
			'amp-story-editor-blocks',
			'ampStoriesFonts',
			$fonts
		);
	}

	/**
	 * Set template for amp_story post type.
	 *
	 * @param string $template Template.
	 * @return string Template.
	 */
	public static function filter_template_include( $template ) {
		if ( is_singular( self::POST_TYPE_SLUG ) ) {
			$template = AMP__DIR__ . '/includes/templates/single-amp_story.php';
		}
		return $template;
	}

	/**
	 * Add CSS to AMP Stories' frontend.
	 *
	 * @see /assets/css/amp-stories.css
	 */
	public static function add_custom_block_styles() {
		$post = get_post();
		if ( ! $post || self::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}
		$css_src      = AMP__DIR__ . '/assets/css/amp-stories.css';
		$css_contents = file_get_contents( $css_src ); // phpcs:ignore -- It's a local filesystem path not a remote request.
		wp_add_inline_style( 'wp-block-library', $css_contents );
	}

	/**
	 * Get list of fonts used in AMP Stories.
	 *
	 * @return array Fonts.
	 */
	public static function get_fonts() {
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

		return array_map(
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
	}
}
