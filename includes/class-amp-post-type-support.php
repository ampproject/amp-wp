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
	 * Add hooks.
	 */
	public static function add_hooks() {
		add_action( 'amp_init', array( __CLASS__, 'add_builtin_post_type_support' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'add_elected_post_type_support' ), 5 );
	}

	/**
	 * Get post types that plugin supports out of the box (which cannot be disabled).
	 *
	 * @return array Post types.
	 */
	public static function get_builtin_supported_post_types() {
		return array( 'post' );
	}

	/**
	 * Declare core post types support for built-in post types.
	 *
	 * @since 0.6
	 */
	public static function add_builtin_post_type_support() {
		foreach ( self::get_builtin_supported_post_types() as $post_type ) {
			add_post_type_support( $post_type, AMP_QUERY_VAR );
		}
	}

	/**
	 * Declare custom post types support.
	 *
	 * This function should only be invoked through the 'after_setup_theme' action to
	 * allow plugins/theme to overwrite the post types support.
	 *
	 * @since 0.6
	 */
	public static function add_elected_post_type_support() {
		$post_types = array_merge(
			array_keys( array_filter( AMP_Options_Manager::get_option( 'supported_post_types', array() ) ) ),
			self::get_builtin_supported_post_types()
		);
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, AMP_QUERY_VAR );
		}
	}
}
