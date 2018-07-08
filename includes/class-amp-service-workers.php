<?php
/**
 * AMP Service Workers.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Service_Workers.
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
			$vars[] = self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR;
			return $vars;
		} );

		add_action( 'wp_default_service_workers', array( __CLASS__, 'register_script' ) );
		add_action( 'parse_request', array( __CLASS__, 'handle_service_worker_iframe_install' ) );
		add_action( 'wp', array( __CLASS__, 'add_install_hooks' ) );
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
	 * Register service worker script.
	 *
	 * @param WP_Service_Workers $workers Workers.
	 */
	public static function register_script( WP_Service_Workers $workers ) {
		$workers->register(
			'amp-asset-caching',
			amp_get_asset_url( 'js/amp-service-worker-asset-cache.js' )
		);
	}

	/**
	 * Install service worker(s).
	 *
	 * @since ?
	 * @see wp_print_service_workers()
	 * @link https://github.com/xwp/pwa-wp
	 */
	public static function install_service_worker() {
		if ( ! function_exists( 'wp_service_workers' ) || ! function_exists( 'wp_get_service_worker_url' ) ) {
			return;
		}

		$scopes = wp_service_workers()->get_scopes();
		if ( empty( $scopes ) ) {
			return; // No service worker scripts are installed.
		}

		// Find the scope that has the longest match with the current path.
		$current_url_path  = wp_parse_url( amp_get_current_url(), PHP_URL_PATH );
		$max_matched_scope = '';
		foreach ( $scopes as $scope ) {
			if ( strlen( $scope ) > strlen( $max_matched_scope ) && substr( $current_url_path, 0, strlen( $scope ) ) === $scope ) {
				$max_matched_scope = $scope;
			}
		}

		// None of the registered scripts' scopes are a match for the current URL path.
		if ( empty( $max_matched_scope ) ) {
			return;
		}

		$src        = wp_get_service_worker_url( $max_matched_scope );
		$iframe_src = add_query_arg(
			self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR,
			$max_matched_scope,
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
		if ( empty( $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR ] ) ) {
			return;
		}

		$scope  = $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR ];
		$scopes = wp_service_workers()->get_scopes();
		if ( ! in_array( $scope, $scopes, true ) ) {
			wp_die(
				esc_html__( 'No service workers registered for the requested scope.', 'amp' ),
				esc_html__( 'Service Worker Installation', 'amp' ),
				array( 'response' => 404 )
			);
		}
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="utf-8">
				<title><?php esc_html_e( 'Service Worker Installation', 'amp' ); ?></title>
			</head>
			<body>
				<?php
				printf(
					'<script>navigator.serviceWorker.register( %s, %s );</script>',
					wp_json_encode( wp_get_service_worker_url( $scope ) ),
					wp_json_encode( compact( 'scope' ) )
				)
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
