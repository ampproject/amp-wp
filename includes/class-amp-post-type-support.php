<?php
/**
 * AMP Post type support.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Class AMP_Post_Type_Support.
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
		return array_values(
			get_post_types(
				[
					'public' => true,
				],
				'names'
			)
		);
	}

	/**
	 * Declare support for post types.
	 *
	 * This function should only be invoked through the 'after_setup_theme' action to
	 * allow plugins/theme to overwrite the post types support.
	 *
	 * @since 0.6
	 */
	public static function add_post_type_support() {
		if ( current_theme_supports( AMP_Theme_Support::SLUG ) && AMP_Options_Manager::get_option( 'all_templates_supported' ) ) {
			$post_types = self::get_eligible_post_types();
		} else {
			$post_types = AMP_Options_Manager::get_option( 'supported_post_types', [] );
		}
		foreach ( $post_types as $post_type ) {
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
		if ( ! ( $post instanceof WP_Post ) ) {
			$post = get_post( $post );
		}
		$errors = [];

		if ( ! post_type_supports( $post->post_type, self::SLUG ) ) {
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
		if ( isset( $post->ID ) && true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
			$errors[] = 'skip-post';
		}

		$status = get_post_meta( $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true );
		if ( $status ) {
			if ( AMP_Post_Meta_Box::DISABLED_STATUS === $status ) {
				$errors[] = 'post-status-disabled';
			}
		} else {
			/*
			 * Disabled by default for custom page templates, page on front and page for posts, unless 'amp' theme
			 * support is present (in which case AMP_Theme_Support::get_template_availability() determines availability).
			 */
			$enabled = (
				current_theme_supports( AMP_Theme_Support::SLUG )
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
