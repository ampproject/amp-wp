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
	const POST_TYPE_SLUG = 'amp-story';

	/**
	 * AMP Stories script handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_SCRIPT_HANDLE = 'amp-edit-story';

	/**
	 * AMP Stories style handle.
	 *
	 * @var string
	 */
	const AMP_STORIES_STYLE_HANDLE = 'amp-edit-story';

	/**
	 * The rewrite slug for this post type.
	 *
	 * @var string
	 */
	const REWRITE_SLUG = 'stories';

	/**
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! AMP_Options_Manager::is_stories_editor_enabled() ) {
			return;
		}
		register_post_type(
			self::POST_TYPE_SLUG,
			[
				'labels'                => [
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
				'menu_icon'             => 'dashicons-book',
				'taxonomies'            => [
					'post_tag',
					'category',
				],
				'supports'              => [
					'title', // Used for amp-story[title].
					'author', // Used for the amp/amp-story-post-author block.
					'editor',
					'thumbnail', // Used for poster images.
					'amp',
					'revisions', // Without this, the REST API will return 404 for an autosave request.
					'custom-fields', // Used for global stories settings.
				],
				'rewrite'               => [
					'slug' => self::REWRITE_SLUG,
				],
				'public'                => true,
				'show_ui'               => true,
				'show_in_rest'          => true,
				'rest_controller_class' => 'AMP_REST_Stories_Controller',
			]
		);

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'wp_enqueue_scripts' ] );
		add_filter( 'show_admin_bar', [ __CLASS__, 'show_admin_bar' ] );
		add_filter( 'replace_editor', [ __CLASS__, 'replace_editor' ], 10, 2 );
		add_filter( 'admin_body_class', [ __CLASS__, 'admin_body_class' ], 99 );
		add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'filter_kses_allowed_html' ], 10, 2 );

		// Select the single-amp-story.php template for Stories.
		add_filter( 'template_include', [ __CLASS__, 'filter_template_include' ] );

		add_action(
			'amp_story_head',
			function () {
				// Theme support for title-tag is implied for stories. See _wp_render_title_tag().
				echo '<title>' . esc_html( wp_get_document_title() ) . '</title>' . "\n";
			},
			1
		);
		add_action( 'amp_story_head', 'wp_enqueue_scripts', 1 );
		add_action(
			'amp_story_head',
			function () {
				/*
				 * Same as wp_print_styles() but importantly omitting the wp_print_styles action, which themes/plugins
				 * can use to output arbitrary styling. Styling is constrained in story template via the
				 * \AMP_Story_Legacy_Post_Type::filter_frontend_print_styles_array() method.
				 */
				wp_styles()->do_items();
			},
			8
		);

		add_filter(
			'amp_content_sanitizers',
			static function ( $sanitizers ) {
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

		add_filter(
			'the_content',
			static function ( $content ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					remove_filter( 'the_content', 'wpautop' );
				}

				return $content;
			},
			0
		);

		// @todo Check if there's something to skip in the new version.
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
	}

	/**
	 * Filter if show admin bar on single post type.
	 *
	 * @param boolean $show Current value of filter.
	 *
	 * @return bool
	 */
	public static function show_admin_bar( $show ) {
		if ( is_singular( self::POST_TYPE_SLUG ) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Highjack editor with custom editor.
	 *
	 * @param bool    $replace Bool if to replace editor or not.
	 * @param WP_Post $post Current post object.
	 *
	 * @return bool
	 */
	public static function replace_editor( $replace, $post ) {
		if ( self::POST_TYPE_SLUG === get_post_type( $post ) ) {
			$replace = true;
			// In lieu of an action being available to actually load the replacement editor, include it here
			// after the current_screen action has occurred because the replace_editor filter fires twice.
			if ( did_action( 'current_screen' ) ) {
				require_once AMP__DIR__ . '/includes/edit-story.php';
			}
		}

		return $replace;
	}

	/**
	 * Enqueue Google fonts.
	 */
	public static function wp_enqueue_scripts() {
		if ( is_singular( self::POST_TYPE_SLUG ) ) {
			$post = get_post();
			self::load_fonts( $post );
		}
	}

	/**
	 *
	 * Enqueue scripts for the element editor.
	 *
	 * @param string $hook The current admin page.
	 */
	public static function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen ) {
			return;
		}

		if ( self::POST_TYPE_SLUG !== $screen->post_type ) {
			return;
		}

		// Only output scripts and styles where in edit screens.
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		// Force media model to load.
		wp_enqueue_media();

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

		/**
		 * Filter list of allowed video mime types.
		 *
		 * This can be used to add additionally supported formats, for example by plugins
		 * that do video transcoding.
		 *
		 * @param array Allowed video mime types.
		 *
		 * @since 1.3
		 */
		$allowed_video_mime_types = apply_filters( 'amp_story_allowed_video_types', [ 'video/mp4' ] );

		// If `$allowed_video_mime_types` doesn't have valid data or is empty add default supported type.
		if ( ! is_array( $allowed_video_mime_types ) || empty( $allowed_video_mime_types ) ) {
			$allowed_video_mime_types = [ 'video/mp4' ];
		}

		// Only add currently supported mime types.
		$allowed_video_mime_types = array_values( array_intersect( $allowed_video_mime_types, wp_get_mime_types() ) );

		/**
		 * Filters the list of allowed post types for use in page attachments.
		 *
		 * @param array Allowed post types.
		 *
		 * @since 1.3
		 */
		$page_attachment_post_types = apply_filters(
			'amp_story_allowed_page_attachment_post_types',
			[
				'page',
				'post',
			]
		);
		$post_types                 = [];
		foreach ( $page_attachment_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			if ( $post_type_object ) {
				$post_types[ $post_type ] = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
			}
		}

		$post             = get_post();
		$story_id         = ( $post ) ? $post->ID : null;
		$post_type_object = get_post_type_object( self::POST_TYPE_SLUG );
		$rest_base        = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;

		self::load_admin_fonts( $post );

		wp_localize_script(
			self::AMP_STORIES_SCRIPT_HANDLE,
			'ampStoriesEditSettings',
			[
				'id'     => 'edit-story',
				'config' => [
					'allowedVideoMimeTypes'          => $allowed_video_mime_types,
					'allowedPageAttachmentPostTypes' => $post_types,
					'storyId'                        => $story_id,
					'previewLink'                    => get_preview_post_link( $story_id ),
					'api'                            => [
						'stories' => sprintf( '/wp/v2/%s', $rest_base ),
						'media'   => '/wp/v2/media',
						'fonts'   => '/amp/v1/fonts',
					],
				],
			]
		);

		wp_enqueue_style(
			self::AMP_STORIES_STYLE_HANDLE,
			amp_get_asset_url( 'css/amp-edit-story-compiled.css' ),
			[ 'wp-components' ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::AMP_STORIES_STYLE_HANDLE, 'rtl', 'replace' );

	}

	/**
	 * Load font from story data.
	 *
	 * @param WP_Post $post Post Object.
	 */
	public static function load_fonts( $post ) {
		$post_story_data = json_decode( $post->post_content_filtered, true );
		$g_fonts         = [];
		if ( $post_story_data ) {
			foreach ( $post_story_data as $page ) {
				foreach ( $page['elements'] as $element ) {
					$font = AMP_Fonts::get_font( $element['fontFamily'] );

					if ( $font && isset( $font['gfont'] ) && $font['gfont'] ) {
						if ( isset( $g_fonts[ $font['name'] ] ) && in_array( $element['fontWeight'], $g_fonts[ $font['name'] ], true ) ) {
							continue;
						}
						$g_fonts[ $font['name'] ][] = $element['fontWeight'];
					}
				}
			}

			if ( $g_fonts ) {
				$subsets        = AMP_Fonts::get_subsets();
				$g_font_display = '';
				foreach ( $g_fonts as $name => $numbers ) {
					$g_font_display .= $name . ':' . implode( ',', $numbers ) . '|';
				}

				$src = add_query_arg(
					[
						'family'  => rawurlencode( $g_font_display ),
						'subset'  => rawurlencode( implode( ',', $subsets ) ),
						'display' => 'swap',
					],
					AMP_Fonts::URL
				);
				wp_enqueue_style(
					self::AMP_STORIES_STYLE_HANDLE . '_fonts',
					$src,
					[],
					AMP__VERSION
				);

			}
		}
	}

	/**
	 * Load font in admin from story data.
	 *
	 * @param WP_Post $post Post Object.
	 */
	public static function load_admin_fonts( $post ) {
		$post_story_data = json_decode( $post->post_content_filtered, true );
		$fonts           = [ AMP_Fonts::get_font( 'Roboto' ) ];
		$font_slugs      = ['roboto'];
		if ( $post_story_data ) {
			foreach ( $post_story_data as $page ) {
				foreach ( $page['elements'] as $element ) {
					$font = AMP_Fonts::get_font( $element['fontFamily'] );
					if ( $font && ! in_array( $font['slug'], $font_slugs, true ) ) {
						$fonts[]      = $font;
						$font_slugs[] = $font['slug'];
					}
				}
			}

			if ( $fonts ) {
				foreach ( $fonts as $font ) {
					if ( isset( $font['src'] ) && $font['src'] ) {
						wp_enqueue_style(
							$font['handle'],
							$font['src'],
							[],
							AMP__VERSION
						);
					}
				}
			}
		}
	}

	/**
	 * Filter the list of admin classes.
	 *
	 * @param string $class Current classes.
	 *
	 * @return string $class List of Classes.
	 */
	public static function admin_body_class( $class ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen ) {
			return $class;
		}

		if ( self::POST_TYPE_SLUG !== $screen->post_type ) {
			return $class;
		}

		$class .= ' edit-story ';

		return $class;
	}

	/**
	 * Filter the allowed tags for KSES to allow for amp-story children.
	 *
	 * @param array $allowed_tags Allowed tags.
	 *
	 * @return array Allowed tags.
	 */
	public static function filter_kses_allowed_html( $allowed_tags ) {
		$story_components = [
			'amp-story',
			'amp-story-page',
			'amp-story-grid-layer',
			'amp-story-cta-layer',
			'amp-story-page-attachment',
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

		foreach ( $allowed_tags as &$allowed_tag ) {
			$allowed_tag['animate-in']          = true;
			$allowed_tag['animate-in-duration'] = true;
			$allowed_tag['animate-in-delay']    = true;
			$allowed_tag['animate-in-after']    = true;
			$allowed_tag['layout']              = true;
		}

		return $allowed_tags;
	}

	/**
	 * Set template for amp-story post type.
	 *
	 * @param string $template Template.
	 *
	 * @return string Template.
	 */
	public static function filter_template_include( $template ) {
		if ( is_singular( self::POST_TYPE_SLUG ) && ! is_embed() ) {
			$template = AMP__DIR__ . '/includes/templates/single-amp-story.php';
		}

		return $template;
	}
}
