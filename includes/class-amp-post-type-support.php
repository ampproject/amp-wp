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
	 * Get post types that plugin supports out of the box (which cannot be disabled).
	 *
	 * @deprecated
	 * @return string[] Post types.
	 */
	public static function get_builtin_supported_post_types() {
		_deprecated_function( __METHOD__, '1.0' );
		return array_filter( array( 'post' ), 'post_type_exists' );
	}

	/**
	 * Get post types that are eligible for AMP support.
	 *
	 * @since 0.6
	 * @return string[] Post types eligible for AMP.
	 */
	public static function get_eligible_post_types() {
		return array_values( get_post_types(
			array(
				'public' => true,
			),
			'names'
		) );
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
		if ( current_theme_supports( 'amp' ) && AMP_Options_Manager::get_option( 'all_templates_supported' ) ) {
			$post_types = self::get_eligible_post_types();
		} else {
			$post_types = AMP_Options_Manager::get_option( 'supported_post_types', array() );
		}
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, amp_get_slug() );
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
		$errors = array();

		if ( ! post_type_supports( $post->post_type, amp_get_slug() ) ) {
			$errors[] = 'post-type-support';
		}

		if ( post_password_required( $post ) ) {
			$errors[] = 'password-protected';
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

		return $errors;
	}
}
