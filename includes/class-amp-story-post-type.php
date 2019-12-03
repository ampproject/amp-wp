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
			]
		);

		$meta_args = [
			'type'           => 'array',
			'object_subtype' => self::POST_TYPE_SLUG,
			'single'         => true,
			'show_in_rest'   => [
				'schema' => [
					'type'  => 'array',
					'items' => [
						'id'       => [
							'type' => 'string',
						],
						'type'     => [
							'type'    => 'string',
							'default' => 'page',
						],
						'elements' => [
							'type'    => 'array',
							'default' => [],
						],
						'index'    => [
							'type'    => 'integer',
							'default' => 0,
						],
					],
				],
			],
		];
		// Hide the pages data from none logged in users.
		if ( ! is_user_logged_in() ) {
			$meta_args['show_in_rest'] = false;
		}
		register_meta( 'post', 'amp_pages', $meta_args );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
		add_filter( 'show_admin_bar', [ __CLASS__, 'show_admin_bar' ] );
		add_filter( 'replace_editor', [ __CLASS__, 'replace_editor' ], 10, 2 );
		add_filter( 'admin_body_class', [ __CLASS__, 'admin_body_class' ], 99 );
		add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'filter_kses_allowed_html' ], 10, 2 );
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

		$post             = get_post();
		$story_id         = ( $post ) ? $post->ID : null;
		$post_type_object = get_post_type_object( self::POST_TYPE_SLUG );
		$rest_base        = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;

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
	 * @return array Allowed tags.
	 */
	public static function filter_kses_allowed_html( $allowed_tags ) {
		$story_components = [
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
			$allowed_tag['animate-in']           = true;
			$allowed_tag['animate-in-duration']  = true;
			$allowed_tag['animate-in-delay']     = true;
			$allowed_tag['animate-in-after']     = true;
			$allowed_tag['layout']               = true;
		}

		return $allowed_tags;
	}
}
