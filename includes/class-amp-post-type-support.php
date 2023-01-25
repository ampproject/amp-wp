<?php
/**
 * AMP Post type support.
 *
 * @package AMP
 * @since 0.6
 */

use AmpProject\AmpWP\Option;

/**
 * Class AMP_Post_Type_Support.
 *
 * @internal
 */
class AMP_Post_Type_Support {

	/**
	 * Post type support slug.
	 *
	 * @var string
	 */
	const SLUG = 'amp';

	/**
	 * Get post types that plugin supports out of the box (which cannot be disabled).
	 *
	 * @deprecated
	 * @internal
	 * @codeCoverageIgnore
	 * @return string[] Post types.
	 */
	public static function get_builtin_supported_post_types() {
		_deprecated_function( __METHOD__, '1.0' );
		return array_filter( [ 'post' ], 'post_type_exists' );
	}

	/**
	 * Get post types that are eligible for AMP support.
	 *
	 * @since 0.6
	 * @return string[] Post types eligible for AMP.
	 */
	public static function get_eligible_post_types() {
		$post_types = get_post_types( [], 'names' );
		$post_types = array_filter( $post_types, 'is_post_type_viewable' );
		$post_types = array_values( $post_types );

		/**
		 * Filters the list of post types which may be supported for AMP.
		 *
		 * By default the list includes those which are public.
		 *
		 * @since 2.0
		 *
		 * @param string[] $post_types Post types.
		 */
		return array_values( (array) apply_filters( 'amp_supportable_post_types', $post_types ) );
	}

	/**
	 * Get post types that can be shown in the REST API and supports AMP.
	 *
	 * @since 2.0
	 *
	 * @return string[] Post types.
	 */
	public static function get_post_types_for_rest_api() {
		return array_intersect(
			self::get_supported_post_types(),
			get_post_types(
				[
					'show_in_rest' => true,
				]
			)
		);
	}

	/**
	 * Get supported post types.
	 *
	 * @return string[] List of post types that support AMP.
	 */
	public static function get_supported_post_types() {
		return array_intersect(
			AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES, [] ),
			self::get_eligible_post_types()
		);
	}

	/**
	 * Declare support for post types.
	 *
	 * This function should only be invoked through the 'after_setup_theme' action to
	 * allow plugins/theme to overwrite the post types support.
	 *
	 * @codeCoverageIgnore
	 * @since 0.6
	 * @deprecated The 'amp' post type support is no longer used at runtime to determine whether AMP is supported.
	 */
	public static function add_post_type_support() {
		_deprecated_function( __METHOD__, '2.0.0' );
		foreach ( self::get_supported_post_types() as $post_type ) {
			add_post_type_support( $post_type, self::SLUG );
		}
	}

	/**
	 * Return error codes for why a given post does not have AMP support.
	 *
	 * @since 0.6
	 *
	 * @param WP_Post|int $post Post.
	 * @return array Error codes for why a given post does not have AMP support.
	 */
	public static function get_support_errors( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		// If there's still not a valid post, then we have to abort.
		if ( ! $post instanceof WP_Post ) {
			return [ 'invalid-post' ];
		}

		$errors = [];

		if ( ! in_array( $post->post_type, self::get_supported_post_types(), true ) ) {
			$errors[] = 'post-type-support';
		}

		/**
		 * Filters whether to skip the post from AMP.
		 *
		 * @since 0.3
		 *
		 * @param bool    $skipped Skipped.
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post.
		 */
		if ( ! empty( $post->ID ) && true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
			$errors[] = 'skip-post';
		}

		$status = get_post_meta( $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true );
		if ( $status ) {
			if ( AMP_Post_Meta_Box::DISABLED_STATUS === $status ) {
				$errors[] = 'post-status-disabled';
			}
		} else {
			/*
			 * Disabled by default for custom page templates, page on front and page for posts, unless not using legacy
			 * Reader mode. In legacy Reader mode, there is no UI to enable AMP for various templates whereas in the new
			 * Reader mode there is the ability to enable AMP for the various templates, including the front page or
			 * else to enable AMP for all templates. Therefore, we do not need to disable AMP by default for the new
			 * Reader mode. Otherwise, in legacy Reader mode we disable AMP by default for special template pages
			 * because we can't make assumptions about whether the legacy template will be suitable for rendering the
			 * content.
			 */
			$enabled = (
				! amp_is_legacy()
				||
				(
					! (bool) get_page_template_slug( $post )
					&&
					! (
						'page' === $post->post_type
						&&
						'page' === get_option( 'show_on_front' )
						&&
						in_array(
							(int) $post->ID,
							[
								(int) get_option( 'page_on_front' ),
								(int) get_option( 'page_for_posts' ),
							],
							true
						)
					)
				)
			);

			/**
			 * Filters whether default AMP status should be enabled or not.
			 *
			 * @since 0.6
			 *
			 * @param string  $status Status.
			 * @param WP_Post $post   Post.
			 */
			$enabled = apply_filters( 'amp_post_status_default_enabled', $enabled, $post );
			if ( ! $enabled ) {
				$errors[] = 'post-status-disabled';
			}
		}
		return $errors;
	}
}
