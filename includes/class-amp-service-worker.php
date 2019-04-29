<?php
/**
 * AMP Service Workers.
 *
 * @package AMP
 * @since 1.1
 */

/**
 * Class AMP_Service_Worker.
 */
class AMP_Service_Worker {

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

		// Shim support for service worker installation from PWA feature plugin.
		add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ) );
		add_action( 'parse_request', array( __CLASS__, 'handle_service_worker_iframe_install' ) );
		add_action( 'wp', array( __CLASS__, 'add_install_hooks' ) );

		$theme_support = AMP_Theme_Support::get_theme_support_args();
		if ( isset( $theme_support['service_worker'] ) && false === $theme_support['service_worker'] ) {
			return;
		}

		/*
		 * The default-enabled options reflect which features are not commented-out in the AMP-by-Example service worker.
		 * See <https://github.com/ampproject/amp-by-example/blob/e093edb401b1617859b5365e80b639d81b06f058/boilerplate-generator/templates/files/serviceworkerJs.js>.
		 */
		$enabled_options = array(
			'cdn_script_caching'   => true,
			'image_caching'        => false,
			'google_fonts_caching' => false,
		);
		if ( isset( $theme_support['service_worker'] ) && is_array( $theme_support['service_worker'] ) ) {
			$enabled_options = array_merge(
				$enabled_options,
				$theme_support['service_worker']
			);
		}

		if ( $enabled_options['cdn_script_caching'] ) {
			add_action( 'wp_front_service_worker', array( __CLASS__, 'add_cdn_script_caching' ) );
		}
		if ( $enabled_options['image_caching'] ) {
			add_action( 'wp_front_service_worker', array( __CLASS__, 'add_image_caching' ) );
		}
		if ( $enabled_options['google_fonts_caching'] ) {
			add_action( 'wp_front_service_worker', array( __CLASS__, 'add_google_fonts_caching' ) );
		}
	}

	/**
	 * Add query var for iframe service worker request.
	 *
	 * @param array $vars Query vars.
	 * @return array Amended query vars.
	 */
	public static function add_query_var( $vars ) {
		$vars[] = self::INSTALL_SERVICE_WORKER_IFRAME_QUERY_VAR;
		return $vars;
	}

	/**
	 * Add runtime caching for scripts loaded from the AMP CDN with a stale-while-revalidate strategy.
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/4593af61609898043302a101826ddafe7206bfd9/boilerplate-generator/templates/files/serviceworkerJs.js
	 *
	 * @param WP_Service_Worker_Scripts $service_workers Service worker registry.
	 */
	public static function add_cdn_script_caching( $service_workers ) {
		if ( ! ( $service_workers instanceof WP_Service_Worker_Scripts ) ) {
			/* translators: %s: WP_Service_Worker_Cache_Registry. */
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Please update to PWA v0.2. Expected argument to be %s.', 'amp' ), 'WP_Service_Worker_Cache_Registry' ), '1.1' );
			return;
		}

		// Add AMP scripts to runtime cache which will then get stale-while-revalidate strategy.
		$service_workers->register(
			'amp-cdn-runtime-caching',
			function() {
				$urls = AMP_Service_Worker::get_precached_script_cdn_urls();
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
			}
		);

		// Serve the AMP Runtime from cache and check for an updated version in the background. See <https://github.com/ampproject/amp-by-example/blob/4593af61609898043302a101826ddafe7206bfd9/boilerplate-generator/templates/files/serviceworkerJs.js#L54-L58>.
		$service_workers->caching_routes()->register(
			'^https:\/\/cdn\.ampproject\.org\/.*',
			array(
				'strategy' => WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE,
			)
		);
	}

	/**
	 * Add runtime image caching from the origin with a cache-first strategy.
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/4593af61609898043302a101826ddafe7206bfd9/boilerplate-generator/templates/files/serviceworkerJs.js#L60-L74
	 *
	 * @param WP_Service_Worker_Scripts $service_workers Service workers.
	 */
	public static function add_image_caching( $service_workers ) {
		if ( ! ( $service_workers instanceof WP_Service_Worker_Scripts ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Please update to PWA v0.2. Expected argument to be WP_Service_Worker_Scripts.', 'amp' ), '1.1' );
			return;
		}

		$service_workers->caching_routes()->register(
			'^' . preg_quote( set_url_scheme( content_url( '/' ), 'https' ), '/' ) . '[^\?]+?\.(?:png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
			array(
				'strategy'  => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
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
	 * Add runtime caching of Google Fonts with stale-while-revalidate strategy for stylesheets and cache-first strategy for webfont files.
	 *
	 * @link https://developers.google.com/web/tools/workbox/guides/common-recipes#google_fonts
	 * @link https://github.com/ampproject/amp-by-example/blob/4593af61609898043302a101826ddafe7206bfd9/boilerplate-generator/templates/files/serviceworkerJs.js#L76-L103
	 * @link https://github.com/xwp/pwa-wp/blob/master/integrations/class-wp-service-worker-fonts-integration.php
	 *
	 * @param WP_Service_Worker_Scripts $service_workers Service workers.
	 */
	public static function add_google_fonts_caching( $service_workers ) {
		if ( ! ( $service_workers instanceof WP_Service_Worker_Scripts ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Please update to PWA v0.2. Expected argument to be WP_Service_Worker_Scripts.', 'amp' ), '1.1' );
			return;
		}

		// The PWA plugin also automatically adds runtime caching for Google Fonts when WP_SERVICE_WORKER_INTEGRATIONS_ENABLED is set.
		if ( class_exists( 'WP_Service_Worker_Fonts_Integration' ) ) {
			return;
		}

		// Cache the Google Fonts stylesheets with a stale while revalidate strategy.
		$service_workers->caching_routes()->register(
			'^https:\/\/fonts\.googleapis\.com',
			array(
				'strategy'  => WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE,
				'cacheName' => 'google-fonts-stylesheets',
			)
		);

		// Cache the Google Fonts webfont files with a cache first strategy for 1 year.
		$service_workers->caching_routes()->register(
			'^https:\/\/fonts\.gstatic\.com',
			array(
				'strategy'  => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
				'cacheName' => 'google-fonts-webfonts',
				'plugins'   => array(
					'cacheableResponse' => array(
						'statuses' => array( 0, 200 ),
					),
					'expiration'        => array(
						'maxAgeSeconds' => YEAR_IN_SECONDS,
						'maxEntries'    => 30,
					),
				),
			)
		);
	}

	/**
	 * Register URLs that will be precached in the runtime cache. (Yes, this sounds somewhat strange.)
	 *
	 * Note that the PWA plugin handles the precaching of custom logo, custom header,
	 * and custom background. The PWA plugin also handles precaching & serving of the
	 * offline/500 error pages and enabling navigation preload.
	 *
	 * @link https://github.com/ampproject/amp-by-example/blob/4593af61609898043302a101826ddafe7206bfd9/boilerplate-generator/templates/files/serviceworkerJs.js#L9-L22
	 * @see AMP_Service_Worker::add_cdn_script_caching()
	 *
	 * @return array Runtime pre-cached URLs.
	 */
	public static function get_precached_script_cdn_urls() {

		// List of AMP scripts that we know will be used in WordPress always.
		$precached_handles = array(
			'amp-runtime',
			'amp-bind', // Used by comments.
			'amp-form', // Used by comments.
			'amp-install-serviceworker',
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

		// Reader mode integration.
		add_action( 'amp_post_template_footer', array( __CLASS__, 'install_service_worker' ) );
		add_filter(
			'amp_post_template_data',
			function ( $data ) {
				$data['amp_component_scripts']['amp-install-serviceworker'] = true;
				return $data;
			}
		);
	}

	/**
	 * Install service worker(s).
	 *
	 * @since 1.1
	 * @see wp_print_service_workers()
	 * @link https://github.com/xwp/pwa-wp
	 */
	public static function install_service_worker() {
		if ( ! function_exists( 'wp_service_workers' ) || ! function_exists( 'wp_get_service_worker_url' ) ) {
			return;
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
		add_filter(
			'wp_die_handler',
			function() {
				return function() {
					die();
				};
			},
			1
		);
		wp_die();
	}
}
