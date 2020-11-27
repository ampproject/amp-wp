<?php
/**
 * AmpRESTContext.
 *
 * @package AMP
 * @since   2.1
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Post;

/**
 * Class AmpRESTContext.
 */
final class StandaloneContent implements Service, Delayed, Registerable, Conditional {

	/**
	 * Query var for requesting standalone AMP content.
	 *
	 * @since 1.3
	 * @var string
	 */
	const STANDALONE_CONTENT_QUERY_VAR = 'amp_standalone_content';

	/**
	 * Provides the WordPress action on which to register.
	 *
	 * @return string
	 */
	public static function get_registration_action() {
		return 'wp';
	}

	/**
	 * Returns whether the class should instantiate in the current request.
	 *
	 * @return boolean
	 */
	public static function is_needed() {
		return self::is_standalone_content_request();
	}

	/**
	 * Determines whether the request is for standalone AMP content.
	 *
	 * @since 2.1
	 *
	 * @return bool Whether requesting standalone content.
	 */
	public static function is_standalone_content_request() {
		return isset( $_GET[ self::STANDALONE_CONTENT_QUERY_VAR ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Registers actions and filters to be used during the REST API initialization.
	 */
	public function register() {
		// Fail request if it is not for a single post that supports AMP.
		$queried_object = get_queried_object();
		if ( ! ( $queried_object instanceof WP_Post ) || ! amp_is_post_supported( $queried_object ) ) {
			wp_die(
				esc_html__( 'AMP content is not available for this URL.', 'amp' ),
				esc_html__( 'AMP Content Unavailable', 'amp' ),
				[ 'response' => 400 ]
			);
		}

		// Prevent including extraneous stylesheets.
		add_filter( 'show_admin_bar', '__return_false' );
		add_filter(
			'print_styles_array',
			function ( $handles ) {
				return array_intersect( $handles, [ 'amp-default', 'wp-block-library', 'wp-block-library-theme' ] );
			}
		);

		// Override template to only include standalone content.
		add_filter(
			'template_include',
			function () {
				return AMP__DIR__ . '/includes/templates/standalone-content.php';
			},
			PHP_INT_MAX
		);
	}
}
