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
								'core/paragraph',
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
		wp_enqueue_style(
			'amp-story-fonts',
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
}
