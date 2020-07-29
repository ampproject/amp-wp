<?php
/**
 * Class ReaderThemeLoader.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Screen;

/**
 * Preloads REST responses for client-side applications to prevent having to call fetch on page load.
 *
 * @package AmpProject\AmpWP
 */
final class RESTPreloader implements Conditional, Delayed, Registerable, Service {

	/**
	 * Paths to preload.
	 *
	 * @var array
	 */
	private $paths = [];

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return function_exists( 'rest_preload_api_request' ) && is_admin() && has_filter( 'amp_preload_rest_paths' );
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_enqueue_scripts';
	}

	/**
	 * Adds hooks.
	 */
	public function register() {
		$screen = get_current_screen();

		/**
		 * Filters REST API paths to preload for the current page.
		 *
		 * @param array $paths Paths to preload.
		 * @param string|null $screen_id Current screen ID or null if no current screen is set.
		 */
		$this->paths = apply_filters( 'amp_preload_rest_paths', $this->paths, is_a( $screen, WP_Screen::class ) ? $screen->id : null );

		if ( ! empty( $this->paths ) ) {
			$this->preload_data();
		}
	}

	/**
	 * Preloads data using apiFetch preloading middleware.
	 */
	private function preload_data() {
		$preload_data = array_reduce( $this->paths, 'rest_preload_api_request', [] );

		wp_add_inline_script(
			'wp-api-fetch',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_data ) ),
			'after'
		);
	}
}
