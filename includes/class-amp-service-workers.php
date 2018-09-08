<?php
/**
 * AMP Service Workers.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Service_Workers.
 *
 * @todo Rename to AMP_Service_Worker.
 */
class AMP_Service_Workers {

	/**
	 * Query var that is used to signal a request to install the service worker in an iframe.
	 *
	 * @link https://www.ampproject.org/docs/reference/components/amp-install-serviceworker#data-iframe-src-(optional)
	 */
	const INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR = 'amp_install_service_worker_iframe';

	/**
	 * Init.
	 */
	public static function init() {
		if ( ! class_exists( 'WP_Service_Workers' ) ) {
			return;
		}

		add_filter( 'query_vars', function( $vars ) {
			$vars[] = AMP_Service_Workers::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR;
			return $vars;
		} );

		add_action( 'parse_request', array( __CLASS__, 'handle_service_worker_iframe_install' ) );
		add_action( 'wp', array( __CLASS__, 'add_install_hooks' ) );
		add_action( 'wp_front_service_worker', array( __CLASS__, 'add_amp_runtime_caching' ) );
		add_action( 'wp_front_service_worker', array( __CLASS__, 'add_image_runtime_caching' ) );
	}

	/**
	 * Configure the front service worker for AMP.
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/master/boilerplate-generator/templates/files/serviceworkerJs.js
	 *
	 * @param WP_Service_Worker_Cache_Registry $service_worker_registry Service worker registry.
	 */
	public static function add_amp_runtime_caching( $service_worker_registry ) {
		if ( ! ( $service_worker_registry instanceof WP_Service_Worker_Cache_Registry ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Expected argument to be WP_Service_Worker_Cache_Registry.', 'amp' ), '1.0' );
			return;
		}

		// Add AMP scripts to runtime cache which will then get stale-while-revalidate strategy.
		wp_service_workers()->register_script( 'amp-cdn-runtime-cache', function() {
			$urls = AMP_Service_Workers::get_runtime_precache_urls();
			if ( empty( $urls ) ) {
				return '';
			}

			$js = file_get_contents( AMP__DIR__ . '/assets/js/amp-service-worker-runtime-precaching.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents
			$js = preg_replace( '#/\*\s*global.+?\*/#', '', $js );
			$js = str_replace(
				'URLS',
				wp_json_encode( $urls ),
				$js
			);
			return $js;
		} );

		// Serve the AMP Runtime from cache and check for an updated version in the background. See <https://github.com/ampproject/amp-by-example/blob/a4d798cac6a534e0c46e78944a2718a8dab3c057/boilerplate-generator/templates/files/serviceworkerJs.js#L54-L58>.
		$service_worker_registry->register_cached_route(
			'^https:\/\/cdn\.ampproject\.org\/.*',
			WP_Service_Worker_Cache_Registry::STRATEGY_STALE_WHILE_REVALIDATE
		);
	}

	/**
	 * Configure the front service worker for AMP.
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/master/boilerplate-generator/templates/files/serviceworkerJs.js
	 *
	 * @param WP_Service_Worker_Cache_Registry $service_worker_registry Service workers.
	 */
	public static function add_image_runtime_caching( $service_worker_registry ) {
		if ( ! ( $service_worker_registry instanceof WP_Service_Worker_Cache_Registry ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Expected argument to be WP_Service_Worker_Cache_Registry.', 'amp' ), '1.0' );
			return;
		}

		$service_worker_registry->register_cached_route(
			'/wp-content/.*\.(?:png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
			WP_Service_Worker_Cache_Registry::STRATEGY_CACHE_FIRST,
			array(
				'cacheName' => 'images',
				'plugins'   => array(
					'cacheableResponse' => array(
						'statuses' => array( 0, 200 ),
					),
					'expiration'        => array(
						'maxEntries'    => 60,
						'maxAgeSeconds' => MONTH_IN_SECONDS,
					),
				),
			)
		);
	}

	/**
	 * Register URLs that will be precached in the runtime cache. (Yes, this sounds somewhat strange.)
	 *
	 * Note that the PWA plugin handles the precaching of custom logo, custom header,
	 * and custom background. The PWA plugin also automatically adds runtime caching
	 * for Google Fonts. The PWA plugin also handles precaching & serving of the
	 * offline/500 error pages, enabling navigation preload,
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/master/boilerplate-generator/templates/files/serviceworkerJs.js
	 *
	 * @return array Runtime pre-cached URLs.
	 */
	public static function get_runtime_precache_urls() {

		// List of AMP scripts that we know will be used in WordPress always.
		$precached_handles = array(
			'amp-runtime',
			'amp-bind', // Used by comments.
			'amp-form', // Used by comments.
		);

		$theme_support = AMP_Theme_Support::get_theme_support_args();
		if ( ! empty( $theme_support['comments_live_list'] ) ) {
			$precached_handles[] = 'amp-live-list';
		}

		if ( amp_get_analytics() ) {
			$precached_handles[] = 'amp-analytics';
		}

		$urls = array();
		foreach ( $precached_handles as $handle ) {
			if ( wp_script_is( $handle, 'registered' ) ) {
				$urls[] = wp_scripts()->registered[ $handle ]->src;
			}
		}

		return $urls;
	}

	/**
	 * Add hooks to install the service worker from AMP page.
	 */
	public static function add_install_hooks() {
		if ( current_theme_supports( 'amp' ) && is_amp_endpoint() ) {
			add_action( 'wp_footer', array( __CLASS__, 'install_service_worker' ) );

			// Prevent validation error due to the script that installs the service worker on non-AMP pages.
			$priority = has_action( 'wp_print_scripts', 'wp_print_service_workers' );
			if ( false !== $priority ) {
				remove_action( 'wp_print_scripts', 'wp_print_service_workers', $priority );
			}
		}
		add_action( 'amp_post_template_footer', array( __CLASS__, 'install_service_worker' ) );
	}

	/**
	 * Install service worker(s).
	 *
	 * @since 1.0
	 * @see wp_print_service_workers()
	 * @link https://github.com/xwp/pwa-wp
	 */
	public static function install_service_worker() {
		if ( ! function_exists( 'wp_service_workers' ) || ! function_exists( 'wp_get_service_worker_url' ) ) {
			return;
		}

		// Get the frontend-scoped service worker scripts.
		$front_handles = array();
		foreach ( wp_service_workers()->registered as $handle => $item ) {
			if ( $item->args['scope'] & WP_Service_Workers::SCOPE_FRONT ) { // Yes, bitwise AND intended.
				$front_handles[] = $handle;
			}
		}

		if ( empty( $front_handles ) ) {
			return; // No service worker scripts are installed.
		}

		$src        = wp_get_service_worker_url( WP_Service_Workers::SCOPE_FRONT );
		$iframe_src = add_query_arg(
			self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR,
			WP_Service_Workers::SCOPE_FRONT,
			home_url( '/', 'https' )
		);
		?>
		<amp-install-serviceworker
			src="<?php echo esc_url( $src ); ?>"
			data-iframe-src="<?php echo esc_url( $iframe_src ); ?>"
			layout="nodisplay"
		>
		</amp-install-serviceworker>
		<?php
	}

	/**
	 * Handle request to install service worker via iframe.
	 *
	 * @see wp_print_service_workers()
	 * @link https://www.ampproject.org/docs/reference/components/amp-install-serviceworker#data-iframe-src-(optional)
	 */
	public static function handle_service_worker_iframe_install() {
		if ( ! isset( $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR ] ) ) {
			return;
		}

		$scope = intval( $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR ] );
		if ( WP_Service_Workers::SCOPE_ADMIN !== $scope && WP_Service_Workers::SCOPE_FRONT !== $scope ) {
			wp_die(
				esc_html__( 'No service workers registered for the requested scope.', 'amp' ),
				esc_html__( 'Service Worker Installation', 'amp' ),
				array( 'response' => 404 )
			);
		}

		$front_scope = home_url( '/', 'relative' );

		?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="utf-8">
				<title><?php esc_html_e( 'Service Worker Installation', 'amp' ); ?></title>
			</head>
			<body>
				<?php esc_html_e( 'Installing service worker...', 'amp' ); ?>
				<?php
				printf(
					'<script>navigator.serviceWorker.register( %s, %s );</script>',
					wp_json_encode( wp_get_service_worker_url( $scope ) ),
					wp_json_encode( array( 'scope' => $front_scope ) )
				);
				?>
			</body>
		</html>
		<?php

		// Die in a way that can be unit tested.
		add_filter( 'wp_die_handler', function() {
			return function() {
				die();
			};
		}, 1 );
		wp_die();
	}
}
