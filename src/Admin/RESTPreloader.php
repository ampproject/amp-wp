<?php
/**
 * Class RESTPreloader.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Preloads REST responses for client-side applications to prevent having to call fetch on page load.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class RESTPreloader implements Service {

	/**
	 * Paths to preload.
	 *
	 * @var array
	 */
	private $paths = [];

	/**
	 * Adds a REST path to be preloaded.
	 *
	 * @param string $path A REST path to cache for apiFetch middleware.
	 */
	public function add_preloaded_path( $path ) {
		// Delay adding the preload_data action hook until after a path is added.
		if ( empty( $this->paths ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'preload_data' ], 99 );
		}

		if ( ! in_array( $path, $this->paths, true ) ) {
			$this->paths[] = $path;
		}
	}

	/**
	 * Preloads data using apiFetch preloading middleware.
	 */
	public function preload_data() {
		if ( ! function_exists( 'rest_preload_api_request' ) ) { // Not available pre-5.0.
			return;
		}

		$preload_data = array_reduce( $this->paths, 'rest_preload_api_request', [] );

		wp_add_inline_script(
			'wp-api-fetch',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_data ) ),
			'after'
		);
	}
}
