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
		if ( ! function_exists( 'wp_register_service_worker' ) ) {
			return;
		}

		add_filter( 'query_vars', function( $vars ) {
			$vars[] = self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR;
			return $vars;
		} );

		wp_register_service_worker(
			'amp-asset-caching',
			amp_get_asset_url( 'js/amp-service-worker-asset-cache.js' )
		);
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
			if ( 'all' === $item->args['scope'] || 'front' === $item->args['scope'] ) {
				$front_handles[] = $handle;
			}
		}

		if ( empty( $front_handles ) ) {
			return; // No service worker scripts are installed.
		}

		$src        = wp_get_service_worker_url( 'front' );
		$iframe_src = add_query_arg(
			self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR,
			'front',
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

		$scope = $GLOBALS['wp']->query_vars[ self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR ];
		if ( 'front' !== $scope && 'admin' !== $scope ) {
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
				<?php esc_html_e( 'Installing service worker...', 'amp' ); ?>
				<?php
				printf(
					'<script>navigator.serviceWorker.register( %s, %s );</script>',
					wp_json_encode( wp_get_service_worker_url( $scope ) ),
					wp_json_encode( compact( 'scope' ) )
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
