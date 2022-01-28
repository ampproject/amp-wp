<?php
/**
 * Class SiteHealth.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Post_Type_Support;
use AmpProject\AmpWP\AmpSlugCustomizationWatcher;
use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use WP_Error;
use WP_REST_Server;

/**
 * Class SiteHealth
 *
 * Adds tests and debugging information for Site Health.
 *
 * @since 1.5.0
 * @internal
 */
final class SiteHealth implements Service, Registerable, Delayed {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const REST_API_NAMESPACE = 'amp/v1';

	/**
	 * Constant to store the results of the latest check for page caching.
	 *
	 * @var string
	 */
	const HAS_PAGE_CACHING_TRANSIENT_KEY = 'amp_has_page_caching';

	/**
	 * REST API endpoint for page cache test.
	 *
	 * @var string
	 */
	const REST_API_PAGE_CACHE_ENDPOINT = '/test/page-cache';

	/**
	 * Test slug for testing page caching.
	 *
	 * @var string
	 */
	const TEST_PAGE_CACHING = 'amp_page_cache';

	/**
	 * Service that monitors and controls the CSS transient caching.
	 *
	 * @var MonitorCssTransientCaching
	 */
	private $css_transient_caching;

	/**
	 * Service that checks when the AMP slug was defined.
	 *
	 * @var AmpSlugCustomizationWatcher
	 */
	private $amp_slug_customization_watcher;

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp_loaded';
	}

	/**
	 * SiteHealth constructor.
	 *
	 * @param MonitorCssTransientCaching  $css_transient_caching          CSS transient caching monitoring service.
	 * @param AmpSlugCustomizationWatcher $amp_slug_customization_watcher AMP slug customization watcher.
	 */
	public function __construct( MonitorCssTransientCaching $css_transient_caching, AmpSlugCustomizationWatcher $amp_slug_customization_watcher ) {
		$this->css_transient_caching          = $css_transient_caching;
		$this->amp_slug_customization_watcher = $amp_slug_customization_watcher;
	}

	/**
	 * Adds the filters.
	 */
	public function register() {
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
		add_action( 'rest_api_init', [ $this, 'register_async_test_endpoints' ] );
		add_filter( 'debug_information', [ $this, 'add_debug_information' ] );
		add_filter( 'site_status_test_result', [ $this, 'modify_test_result' ] );
		add_filter( 'site_status_test_php_modules', [ $this, 'add_extensions' ] );

		add_action( 'admin_print_styles-tools_page_health-check', [ $this, 'add_styles' ] );
		add_action( 'admin_print_styles-site-health.php', [ $this, 'add_styles' ] );
	}

	/**
	 * Detect whether async tests can be used.
	 *
	 * Returns true if on WP 5.6+ and *not* on version of Health Check plugin which doesn't support REST async tests.
	 *
	 * @param array $tests Tests.
	 * @return bool
	 */
	private function supports_async_rest_tests( $tests ) {
		if ( version_compare( get_bloginfo( 'version' ), '5.6', '<' ) ) {
			return false;
		}

		if ( defined( 'HEALTH_CHECK_PLUGIN_VERSION' ) ) {
			$core_async_tests = [
				'dotorg_communication',
				'background_updates',
				'loopback_requests',
				'https_status',
				'authorization_header',
			];
			foreach ( $core_async_tests as $core_async_test ) {
				if (
					array_key_exists( 'async', $tests )
					&&
					isset( $tests['async'][ $core_async_test ] )
					&&
					! isset( $tests['async'][ $core_async_test ]['has_rest'] )
				) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get badge label.
	 *
	 * @return string AMP.
	 */
	private function get_badge_label() {
		return esc_html__( 'AMP', 'amp' );
	}

	/**
	 * Register async test endpoints.
	 *
	 * This is only done in WP 5.6+.
	 */
	public function register_async_test_endpoints() {
		register_rest_route(
			self::REST_API_NAMESPACE,
			self::REST_API_PAGE_CACHE_ENDPOINT,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'page_cache' ],
					'permission_callback' => static function() {
						return current_user_can( 'view_site_health_checks' );
					},
				],
			]
		);
	}

	/**
	 * Adds Site Health tests related to this plugin.
	 *
	 * @param array $tests The Site Health tests.
	 * @return array $tests The filtered tests, with tests for AMP.
	 */
	public function add_tests( $tests ) {
		$tests['direct']['amp_persistent_object_cache'] = [
			'label' => esc_html__( 'Persistent object cache', 'amp' ),
			'test'  => [ $this, 'persistent_object_cache' ],
		];

		if ( ! amp_is_canonical() && QueryVar::AMP !== amp_get_slug() ) {
			$tests['direct']['amp_slug_definition_timing'] = [
				'label' => esc_html__( 'AMP slug (query var) definition timing', 'amp' ),
				'test'  => [ $this, 'slug_definition_timing' ],
			];
		}
		$tests['direct']['amp_curl_multi_functions'] = [
			'label' => esc_html__( 'cURL multi functions', 'amp' ),
			'test'  => [ $this, 'curl_multi_functions' ],
		];

		if ( $this->is_intl_extension_needed() ) {
			$tests['direct']['amp_icu_version'] = [
				'label' => esc_html__( 'ICU version', 'amp' ),
				'test'  => [ $this, 'icu_version' ],
			];
		}

		$tests['direct']['amp_css_transient_caching'] = [
			'label' => esc_html__( 'Transient caching of stylesheets', 'amp' ),
			'test'  => [ $this, 'css_transient_caching' ],
		];
		$tests['direct']['amp_xdebug_extension']      = [
			'label' => esc_html__( 'Xdebug extension', 'amp' ),
			'test'  => [ $this, 'xdebug_extension' ],
		];

		if ( $this->supports_async_rest_tests( $tests ) ) {
			$tests['async'][ self::TEST_PAGE_CACHING ] = [
				'label'             => esc_html__( 'Page caching', 'amp' ),
				'test'              => rest_url( self::REST_API_NAMESPACE . self::REST_API_PAGE_CACHE_ENDPOINT ),
				'has_rest'          => true,
				'async_direct_test' => [ $this, 'page_cache' ],
			];
		}

		return $tests;
	}

	/**
	 * Get action HTML for the link to learn more about persistent object caching.
	 *
	 * @return string HTML.
	 */
	private function get_persistent_object_cache_learn_more_action() {
		return sprintf(
			'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( 'https://amp-wp.org/documentation/getting-started/amp-site-setup/persistent-object-caching/' ),
			esc_html__( 'Learn more about persistent object caching', 'amp' ),
			/* translators: The accessibility text. */
			esc_html__( '(opens in a new tab)', 'amp' )
		);
	}

	/**
	 * Gets the test result data for whether there is a persistent object cache.
	 *
	 * @return array The test data.
	 */
	public function persistent_object_cache() {
		$is_using_object_cache = wp_using_ext_object_cache();
		$page_cache_detail     = $this->get_page_cache_detail( true );
		$has_page_caching      = ( is_array( $page_cache_detail ) && 'good' === $page_cache_detail['status'] );

		$description = '<p>' . esc_html__( 'The AMP plugin performs at its best when persistent object cache is enabled. Persistent object caching is used to more effectively store image dimensions and parsed CSS using a caching backend rather than using the options table in the database.', 'amp' ) . '</p>';

		if ( ! $is_using_object_cache ) {
			if ( $has_page_caching ) {
				$description .= '<p>' . esc_html__( 'Since page caching was detected, the need for persistent object caching is lessened. However, it still remains a best practice.', 'amp' ) . '</p>';
			}

			$services = $this->get_persistent_object_cache_availability();

			$available_services = array_filter(
				$services,
				static function ( $service ) {
					return $service['available'];
				}
			);

			$description .= '<p>';
			if ( count( $available_services ) > 0 ) {

				$description .= _n(
					'During the test, we found the following object caching service may be available on your server:',
					'During the test, we found the following object caching services may be available on your server:',
					count( $available_services ),
					'amp'
				);

				$description .= ' ' . implode(
					', ',
					array_map(
						static function ( $available_service ) {
							return sprintf(
								'<a href="%s">%s</a>',
								esc_url( $available_service['url'] ),
								esc_html( $available_service['name'] )
							);
						},
						$available_services
					)
				);

				$description .= ' ' . _n(
					'(link goes to Add Plugins screen).',
					'(links go to Add Plugins screen).',
					count( $available_services ),
					'amp'
				);

				$description .= ' ';
			}

			$description .= __( 'Please check with your host for what persistent caching services are available.', 'amp' );
			$description .= '</p>';
		}

		if ( $is_using_object_cache ) {
			$status = 'good';
			$color  = 'green';
			$label  = __( 'Persistent object caching is enabled', 'amp' );
		} elseif ( $has_page_caching ) {
			$status = 'good';
			$color  = 'blue';
			$label  = __( 'Persistent object caching is not enabled, but page caching was detected', 'amp' );
		} else {
			$status = 'recommended';
			$color  = 'orange';
			$label  = __( 'Persistent object caching is not enabled', 'amp' );
		}

		return [
			'badge'       => [
				'label' => $this->get_badge_label(),
				'color' => $color,
			],
			'description' => wp_kses_post( $description ),
			'actions'     => $this->get_persistent_object_cache_learn_more_action(),
			'test'        => 'amp_persistent_object_cache',
			'status'      => $status,
			'label'       => $label,
		];
	}

	/**
	 * Get the threshold below which a response time is considered good.
	 *
	 * @since 2.2.1
	 *
	 * @return int Threshold.
	 */
	public function get_good_response_time_threshold() {
		/**
		 * Filters the threshold below which a response time is considered good.
		 *
		 * @since 2.2.1
		 * @param int $threshold Threshold in milliseconds.
		 */
		return (int) apply_filters( 'amp_page_cache_good_response_time_threshold', 600 );
	}

	/**
	 * Get the test result data for whether there is page caching or not.
	 *
	 * @return array
	 */
	public function page_cache() {
		$page_cache_detail = $this->get_page_cache_detail();

		$description = '<p>' . esc_html__( 'The AMP plugin performs at its best when page caching is enabled. This is because the additional optimizations performed require additional server processing time, and page caching ensures that responses are served quickly.', 'amp' ) . '</p>';

		$description .= '<p>' . esc_html__( 'Page caching is detected by looking for an active page caching plugin as well as making three requests to the homepage and looking for one or more of the following HTTP client caching response headers:', 'amp' )
			. ' <code>' . implode( '</code>, <code>', array_keys( self::get_page_cache_headers() ) ) . '.</code>';

		if ( is_wp_error( $page_cache_detail ) ) {
			$badge_color = 'red';
			$status      = 'critical';
			$label       = __( 'Unable to detect the presence of page caching', 'amp' );

			$error_info = sprintf(
				/* translators: 1 is error message, 2 is error code */
				__( 'Unable to detect page caching due to possible loopback request problem. Please verify that the loopback request test is passing. Error: %1$s (Code: %2$s)', 'amp' ),
				$page_cache_detail->get_error_message(),
				$page_cache_detail->get_error_code()
			);

			$description = "<p>$error_info</p>" . $description;
		} elseif ( 'recommended' === $page_cache_detail['status'] ) {
			$badge_color = 'orange';
			$status      = $page_cache_detail['status'];
			$label       = __( 'Page caching is not detected but the server response time is OK', 'amp' );
		} elseif ( 'good' === $page_cache_detail['status'] ) {
			$badge_color = 'green';
			$status      = $page_cache_detail['status'];
			$label       = __( 'Page caching is detected and the server response time is good', 'amp' );
		} else {
			$badge_color = 'red';
			$status      = $page_cache_detail['status'];
			if ( empty( $page_cache_detail['headers'] ) && ! $page_cache_detail['advanced_cache_present'] ) {
				$label = __( 'Page caching is not detected and the server response time is slow', 'amp' );
			} else {
				$label = __( 'Page caching is detected but the server response time is still slow', 'amp' );
			}
		}

		if ( ! is_wp_error( $page_cache_detail ) ) {
			$page_cache_test_summary = [];

			if ( empty( $page_cache_detail['response_time'] ) ) {
				$page_cache_test_summary[] = '<span class="dashicons dashicons-dismiss"></span> ' . __( 'Server response time could not be determined. Verify that loopback requests are working.', 'amp' );
			} else {

				$threshold = $this->get_good_response_time_threshold();
				if ( $page_cache_detail['response_time'] < $threshold ) {
					$page_cache_test_summary[] = '<span class="dashicons dashicons-yes-alt"></span> ' . sprintf(
						/* translators: %d is the response time in milliseconds */
						__( 'Median server response time was %1$s milliseconds. This is less than the %2$s millisecond threshold.', 'amp' ),
						number_format_i18n( $page_cache_detail['response_time'] ),
						number_format_i18n( $threshold )
					);
				} else {
					$page_cache_test_summary[] = '<span class="dashicons dashicons-warning"></span> ' . sprintf(
						/* translators: %d is the response time in milliseconds */
						__( 'Median server response time was %1$s milliseconds. It should be less than %2$s milliseconds.', 'amp' ),
						number_format_i18n( $page_cache_detail['response_time'] ),
						number_format_i18n( $threshold )
					);
				}

				if ( empty( $page_cache_detail['headers'] ) ) {
					$page_cache_test_summary[] = '<span class="dashicons dashicons-warning"></span> ' . __( 'No client caching response headers were detected.', 'amp' );
				} else {
					$page_cache_test_summary[] = '<span class="dashicons dashicons-yes-alt"></span> ' .
						sprintf(
							/* translators: Placeholder is number of caching headers */
							_n(
								'There was %d client caching response header detected:',
								'There were %d client caching response headers detected:',
								count( $page_cache_detail['headers'] ),
								'amp'
							),
							count( $page_cache_detail['headers'] )
						) .
						' <code>' . implode( '</code>, <code>', $page_cache_detail['headers'] ) . '</code>.';
				}
			}

			if ( $page_cache_detail['advanced_cache_present'] ) {
				$page_cache_test_summary[] = '<span class="dashicons dashicons-yes-alt"></span> ' . __( 'A page caching plugin was detected.', 'amp' );
			} elseif ( ! ( is_array( $page_cache_detail ) && ! empty( $page_cache_detail['headers'] ) ) ) {
				// Note: This message is not shown if client caching response headers were present since an external caching layer may be employed.
				$page_cache_test_summary[] = '<span class="dashicons dashicons-warning"></span> ' . __( 'A page caching plugin was not detected.', 'amp' );
			}

			$description .= '<ul><li>' . implode( '</li><li>', $page_cache_test_summary ) . '</li></ul>';
		}

		return [
			'badge'       => [
				'label' => $this->get_badge_label(),
				'color' => $badge_color,
			],
			'description' => wp_kses_post( $description ),
			'test'        => self::TEST_PAGE_CACHING,
			'status'      => $status,
			'label'       => esc_html( $label ),
			'actions'     => sprintf(
				'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				esc_url( 'https://amp-wp.org/documentation/getting-started/amp-site-setup/page-caching-with-amp-and-wordpress/' ),
				esc_html__( 'Learn more about page caching', 'amp' ),
				/* translators: The accessibility text. */
				esc_html__( '(opens in a new tab)', 'amp' )
			),
		];
	}

	/**
	 * Get page caching result from cache.
	 *
	 * @param bool $use_previous_result Whether to use previous result or not.
	 *
	 * @return WP_Error|array {
	 *    Page cache detail or else a WP_Error if unable to determine.
	 *
	 *    @type string   $status                 Page cache status. Good, Recommended or Critical.
	 *    @type bool     $advanced_cache_present Whether page cache plugin is available or not.
	 *    @type string[] $headers                Client caching response headers detected.
	 *    @type float    $response_time          Response time of site.
	 * }
	 */
	public function get_page_cache_detail( $use_previous_result = false ) {

		if ( $use_previous_result ) {
			$page_cache_detail = get_transient( self::HAS_PAGE_CACHING_TRANSIENT_KEY );

			// Disregard cached legacy value. Instead of a string, now an array or a WP_Error are stored.
			if ( is_string( $page_cache_detail ) ) {
				$page_cache_detail = null;
			}
		}

		if ( ! $use_previous_result || empty( $page_cache_detail ) ) {
			$page_cache_detail = $this->check_for_page_caching();
			if ( is_wp_error( $page_cache_detail ) ) {
				set_transient( self::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_detail, DAY_IN_SECONDS );
			} else {
				set_transient( self::HAS_PAGE_CACHING_TRANSIENT_KEY, $page_cache_detail, MONTH_IN_SECONDS );
			}
		}

		if ( is_wp_error( $page_cache_detail ) ) {
			return $page_cache_detail;
		}

		// Use the median server response time.
		$response_timings = $page_cache_detail['response_timing'];
		rsort( $response_timings );
		$page_speed = $response_timings[ floor( count( $response_timings ) / 2 ) ];

		// Obtain unique set of all client caching response headers.
		$headers = [];
		foreach ( $page_cache_detail['page_caching_response_headers'] as $page_caching_response_headers ) {
			$headers = array_merge( $headers, array_keys( $page_caching_response_headers ) );
		}
		$headers = array_unique( $headers );

		// Page caching is detected if there are response headers or a page caching plugin is present.
		$has_page_caching = ( count( $headers ) > 0 || $page_cache_detail['advanced_cache_present'] );

		if ( $page_speed && $page_speed < $this->get_good_response_time_threshold() ) {
			$result = $has_page_caching ? 'good' : 'recommended';
		} else {
			$result = 'critical';
		}

		return [
			'status'                 => $result,
			'advanced_cache_present' => $page_cache_detail['advanced_cache_present'],
			'headers'                => $headers,
			'response_time'          => $page_speed,
		];
	}

	/**
	 * List of header and it's verification callback to verify if page cache is enabled or not.
	 *
	 * Note: key is header name and value could be callable function to verify header value.
	 * Empty value mean existence of header detect page cache is enable.
	 *
	 * @return array List of client caching headers and their (optional) verification callbacks.
	 */
	protected static function get_page_cache_headers() {

		$cache_hit_callback = static function ( $header_value ) {
			return false !== strpos( strtolower( $header_value ), 'hit' );
		};

		return [
			'cache-control'          => static function ( $header_value ) {
				return (bool) preg_match( '/max-age=[1-9]/', $header_value );
			},
			'expires'                => static function ( $header_value ) {
				return strtotime( $header_value ) > time();
			},
			'age'                    => static function ( $header_value ) {
				return is_numeric( $header_value ) && $header_value > 0;
			},
			'last-modified'          => '',
			'etag'                   => '',
			'x-cache'                => $cache_hit_callback,
			'x-proxy-cache'          => $cache_hit_callback,
			'cf-cache-status'        => $cache_hit_callback,
			'x-kinsta-cache'         => $cache_hit_callback,
			'x-cache-enabled'        => static function ( $header_value ) {
				return 'true' === strtolower( $header_value );
			},
			'x-cache-disabled'       => static function ( $header_value ) {
				return ( 'on' !== strtolower( $header_value ) );
			},
			'cf-apo-via'             => static function ( $header_value ) {
				return false !== strpos( strtolower( $header_value ), 'tcache' );
			},
			'x-srcache-store-status' => $cache_hit_callback,
			'x-srcache-fetch-status' => $cache_hit_callback,
			'cf-edge-cache'          => static function ( $header_value ) {
				return false !== strpos( strtolower( $header_value ), 'cache' );
			},
		];
	}

	/**
	 * Check if site has page cache enable or not.
	 *
	 * @return WP_Error|array {
	 *     Page caching detection details or else error information.
	 *
	 *     @type bool    $advanced_cache_present        Whether a page caching plugin is present.
	 *     @type array[] $page_caching_response_headers Sets of client caching headers for the responses.
	 *     @type float[] $response_timing               Response timings.
	 * }
	 */
	public function check_for_page_caching() {

		/** This filter is documented in wp-includes/class-wp-http-streams.php */
		$sslverify = apply_filters( 'https_local_ssl_verify', false );

		$headers = [];

		// Include basic auth in loopback requests. Note that this will only pass along basic auth when user is
		// initiating the test. If a site requires basic auth, the test will fail when it runs in WP Cron as part of
		// wp_site_health_scheduled_check. This logic is copied from WP_Site_Health::can_perform_loopback() in core.
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) { // phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.BasicAuthentication
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPressVIPMinimum.Variables.ServerVariables.BasicAuthentication
			$headers['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
		}

		$caching_headers               = self::get_page_cache_headers();
		$page_caching_response_headers = [];
		$response_timing               = [];

		for ( $i = 1; $i <= 3; $i++ ) {
			$start_time    = microtime( true );
			$http_response = wp_remote_get( home_url( '/' ), compact( 'sslverify', 'headers' ) );
			$end_time      = microtime( true );

			if ( is_wp_error( $http_response ) ) {
				return $http_response;
			}
			if ( wp_remote_retrieve_response_code( $http_response ) !== 200 ) {
				return new WP_Error(
					'http_' . wp_remote_retrieve_response_code( $http_response ),
					wp_remote_retrieve_response_message( $http_response )
				);
			}

			$response_headers = [];

			foreach ( $caching_headers as $header => $callback ) {
				$header_value = wp_remote_retrieve_header( $http_response, $header );
				if (
					$header_value
					&&
					(
						empty( $callback )
						||
						( is_callable( $callback ) && true === $callback( $header_value ) )
					)
				) {
					$response_headers[ $header ] = $header_value;
				}
			}

			$page_caching_response_headers[] = $response_headers;
			$response_timing[]               = ( $end_time - $start_time ) * 1000;
		}

		return [
			'advanced_cache_present'        => (
				file_exists( WP_CONTENT_DIR . '/advanced-cache.php' )
				&&
				( defined( 'WP_CACHE' ) && WP_CACHE )
				&&
				/** This filter is documented in wp-settings.php */
				apply_filters( 'enable_loading_advanced_cache_dropin', true )
			),
			'page_caching_response_headers' => $page_caching_response_headers,
			'response_timing'               => $response_timing,
		];
	}

	/**
	 * Return list of available object cache mechanism.
	 *
	 * @return array
	 */
	public function get_persistent_object_cache_availability() {
		return [
			'redis'     => [
				'available' => class_exists( 'Redis' ),
				'name'      => _x( 'Redis', 'persistent object cache service', 'amp' ),
				'url'       => admin_url( 'plugin-install.php?s=redis%20object%20cache&tab=search&type=term' ),
			],
			'memcached' => [
				'available' => ( class_exists( 'Memcache' ) || class_exists( 'Memcached' ) ),
				'name'      => _x( 'Memcached', 'persistent object cache service', 'amp' ),
				'url'       => admin_url( 'plugin-install.php?s=memcached%20object%20cache&tab=search&type=term' ),
			],
			'apcu'      => [
				'available' => (
					extension_loaded( 'apcu' ) ||
					function_exists( 'apc_store' ) ||
					function_exists( 'apcu_store' )
				),
				'name'      => _x( 'APCu', 'persistent object cache service', 'amp' ),
				'url'       => admin_url( 'plugin-install.php?s=apcu%20object%20cache&tab=search&type=term' ),
			],
		];
	}

	/**
	 * Gets the test result data for whether the AMP slug (query var) was defined late.
	 *
	 * @return array The test data.
	 */
	public function slug_definition_timing() {
		$is_defined_late = $this->amp_slug_customization_watcher->did_customize_late();

		$data = [];

		$data['test']  = 'amp_slug_definition_timing';
		$data['badge'] = [
			'label' => $this->get_badge_label(),
			'color' => $is_defined_late ? 'orange' : 'green',
		];

		$data['status'] = $is_defined_late ? 'recommended' : 'good';

		$data['label'] = $is_defined_late
			? esc_html__( 'The AMP slug (query var) was defined late', 'amp' )
			: esc_html__( 'The AMP slug (query var) was defined early', 'amp' );

		$data['description'] = sprintf(
			/* translators: %s is 'plugins_loaded'  */
			esc_html__( 'For best results, the AMP slug (query var) should be available early in WordPress\'s execution flow, specifically before the %s action occurs (at priority 4). This slug is used to construct the paired AMP URLs.', 'amp' ),
			'<code>plugins_loaded</code>'
		);
		$data['description'] .= ' ';

		if ( $is_defined_late ) {
			$data['description'] .= sprintf(
				/* translators: %1$s is the value of the slug, %2$s is the constant name, %3$s is the filter name */
				esc_html__( 'Your AMP slug (%1$s) is defined late, most likely in the theme via the %2$s filter or %3$s constant. Make sure this is being defined in a plugin at the top level so it happens before %4$s. While a late-defined AMP slug will still work, it requires extra work to make the slug available earlier in WordPress execution by storing it in an option.', 'amp' ),
				sprintf( '<code>%s</code>', esc_html( amp_get_slug() ) ),
				'<code>amp_query_var</code>',
				'<code>AMP_QUERY_VAR</code>',
				'<code>plugins_loaded</code>'
			);
		} else {
			$data['description'] .= sprintf(
				/* translators: %1$s is the value of the slug */
				esc_html__( 'Your AMP slug (%s) is defined early so you\'re good to go!', 'amp' ),
				sprintf( '<code>%s</code>', esc_html( amp_get_slug() ) )
			);
		}

		return $data;
	}

	/**
	 * Gets the test result data for whether the curl_multi_* functions exist.
	 *
	 * @return array The test data.
	 */
	public function curl_multi_functions() {
		$undefined_curl_functions = array_filter(
			[
				'curl_multi_add_handle',
				'curl_multi_exec',
				'curl_multi_init',
			],
			static function( $function_name ) {
				return ! function_exists( $function_name );
			}
		);

		$data = [
			'badge'       => [
				'label' => $this->get_badge_label(),
				'color' => $undefined_curl_functions ? 'orange' : 'green',
			],
			'description' => esc_html__( 'The AMP plugin is able to more efficiently determine the dimensions of images lacking width or height by making parallel requests via cURL multi.', 'amp' ),
			'actions'     => sprintf(
				'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				esc_url( __( 'https://www.php.net/manual/en/book.curl.php', 'amp' ) ),
				esc_html__( 'Learn more about these functions', 'amp' ),
				/* translators: The accessibility text. */
				esc_html__( '(opens in a new tab)', 'amp' )
			),
			'test'        => 'amp_curl_multi_functions',
		];

		if ( $undefined_curl_functions ) {
			return array_merge(
				$data,
				[
					'status'      => 'recommended',
					'label'       => esc_html(
						sprintf(
							/* translators: %s is count of functions */
							_n(
								'There is %s undefined cURL multi function',
								'There are %s undefined cURL multi functions',
								count( $undefined_curl_functions ),
								'amp'
							),
							number_format_i18n( count( $undefined_curl_functions ) )
						)
					),
					'description' => wp_kses(
						sprintf(
							/* translators: %1$s: the count of functions, %2$s: the name(s) of the cURL multi PHP function(s) */
							_n(
								'The following %1$s cURL multi function is not defined: %2$s.',
								'The following %1$s cURL multi functions are not defined: %2$s.',
								count( $undefined_curl_functions ),
								'amp'
							),
							number_format_i18n( count( $undefined_curl_functions ) ),
							implode(
								', ',
								array_map(
									static function( $function_name ) {
										return sprintf( '<code>%s()</code>', $function_name );
									},
									$undefined_curl_functions
								)
							)
						) . ' ' . $data['description'],
						[ 'code' => [] ]
					),
				]
			);
		}

		return array_merge(
			$data,
			[
				'status' => 'good',
				'label'  => esc_html__( 'The cURL multi functions are defined', 'amp' ),
			]
		);
	}

	/**
	 * Gets the test result data for whether the proper ICU version is available.
	 *
	 * @return array The test data.
	 */
	public function icu_version() {
		$icu_version       = defined( 'INTL_ICU_VERSION' ) ? INTL_ICU_VERSION : null;
		$minimum_version   = '4.6';
		$is_proper_version = version_compare( $icu_version, $minimum_version, '>=' );

		$data = [
			'badge'       => [
				'label' => $this->get_badge_label(),
				'color' => $is_proper_version ? 'green' : 'orange',
			],
			'description' => esc_html(
				sprintf(
					/* translators: %s: the minimum recommended ICU version */
					__( 'The version of ICU can affect how the intl extension runs. This extension is used to derive AMP Cache URLs for internationalized domain names (IDNs). The minimum recommended version of ICU is v%s.', 'amp' ),
					$minimum_version
				)
			),
			'actions'     => sprintf(
				'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				'http://site.icu-project.org/',
				esc_html__( 'Learn more about ICU', 'amp' ),
				/* translators: The accessibility text. */
				esc_html__( '(opens in a new tab)', 'amp' )
			),
			'test'        => 'amp_icu_version',
		];

		if ( ! defined( 'INTL_ICU_VERSION' ) ) {
			$status = 'recommended';
			/* translators: %s: the constant for the ICU version */
			$label = sprintf( __( 'The ICU version is unknown, as the constant %s is not defined', 'amp' ), 'INTL_ICU_VERSION' );
		} elseif ( ! $is_proper_version ) {
			$status = 'recommended';
			/* translators: %s: the ICU version */
			$label = sprintf( __( 'The version of ICU (v%s) is out of date', 'amp' ), $icu_version );
		} else {
			$status = 'good';
			/* translators: %s: the ICU version */
			$label = sprintf( __( 'The version of ICU (v%s) looks good', 'amp' ), $icu_version );
		}

		return array_merge(
			$data,
			[
				'status' => $status,
				'label'  => esc_html( $label ),
			]
		);
	}

	/**
	 * Gets the test result data for whether transient caching for stylesheets was disabled.
	 *
	 * @return array The test data.
	 */
	public function css_transient_caching() {
		$disabled = AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );

		if ( wp_using_ext_object_cache() ) {
			$status = 'good';
			$color  = 'blue';
			$label  = __( 'Transient caching of parsed stylesheets is not used due to external object cache', 'amp' );
		} elseif ( $disabled ) {
			$status = 'recommended';
			$color  = 'orange';
			$label  = __( 'Transient caching of parsed stylesheets is disabled', 'amp' );
		} else {
			$status = 'good';
			$color  = 'green';
			$label  = __( 'Transient caching of parsed stylesheets is enabled', 'amp' );
		}

		$data = [
			'badge'       => [
				'label' => $this->get_badge_label(),
				'color' => $color,
			],
			'description' => wp_kses(
				sprintf(
					/* translators: %1$s is wp_options table, %2$s is the amp_css_transient_monitoring_threshold filter, and %3$s is the amp_css_transient_monitoring_sampling_range filter */
					__( 'On sites which have highly variable CSS and are not using a persistent object cache, the transient caching of parsed stylesheets may be automatically disabled in order to prevent a site from filling up its <code>%1$s</code> table with too many transients. Examples of highly variable CSS include dynamically-generated style rules with selectors referring to user IDs or elements being given randomized background colors. There are two filters which may be used to configure the CSS transient monitoring: <code>%2$s</code> and <code>%3$s</code>.', 'amp' ),
					'wp_options',
					'amp_css_transient_monitoring_threshold',
					'amp_css_transient_monitoring_sampling_range'
				),
				[
					'code' => [],
				]
			),
			'test'        => 'amp_css_transient_caching',
			'status'      => $status,
			'label'       => esc_html( $label ),
		];

		if ( $disabled ) {
			$data['description'] .= ' ' . esc_html__( 'If you have identified and eliminated the cause of the variable CSS, please re-enable transient caching to reduce the amount of CSS processing required to generate AMP pages.', 'amp' );
			$data['actions']      = sprintf(
				'<p><a class="button reenable-css-transient-caching" href="#">%s</a><span class="dashicons dashicons-yes success-icon"></span><span class="dashicons dashicons-no failure-icon"></span><span class="success-text">%s</span><span class="failure-text">%s</span></p>',
				esc_html__( 'Re-enable transient caching', 'amp' ),
				esc_html__( 'Reload the page to refresh the diagnostic check.', 'amp' ),
				esc_html__( 'The operation failed, please reload the page and try again.', 'amp' )
			);
			$data['actions']     .= $this->get_persistent_object_cache_learn_more_action();
		}

		return $data;
	}

	/**
	 * Gets the test result data for whether the Xdebug extension is loaded.
	 *
	 * @since 1.5
	 *
	 * @return array The test data.
	 */
	public function xdebug_extension() {
		$status      = 'good';
		$color       = 'green';
		$description = esc_html__( 'The Xdebug extension can cause some of the AMP plugin&#8217;s processes to time out depending on your system resources and configuration. It should not be enabled on a live site (production environment).', 'amp' );

		if ( extension_loaded( 'xdebug' ) ) {
			$label = esc_html__( 'Your server currently has the Xdebug PHP extension loaded', 'amp' );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				/* translators: %s: the WP_DEBUG constant */
				$description .= ' ' . sprintf( esc_html__( 'Nevertheless, %s is enabled which suggests that this site is currently under development or is undergoing debugging.', 'amp' ), '<code>WP_DEBUG</code>' );
			} else {
				$status = 'recommended';
				$color  = 'orange';
				/* translators: %s: the WP_DEBUG constant */
				$description .= ' ' . sprintf( esc_html__( 'Please deactivate Xdebug to improve performance. Otherwise, you may enable %s to indicate that this site is currently under development or is undergoing debugging.', 'amp' ), '<code>WP_DEBUG</code>' );
			}
		} else {
			$label = esc_html__( 'Your server currently does not have the Xdebug PHP extension loaded', 'amp' );
		}

		return array_merge(
			compact( 'status', 'label', 'description' ),
			[
				'badge' => [
					'label' => $this->get_badge_label(),
					'color' => $color,
				],
				'test'  => 'amp_xdebug_extension',
			]
		);
	}

	/**
	 * Adds debug information for AMP.
	 *
	 * @param array $debugging_information The debugging information from Core.
	 * @return array The debugging information, with added information for AMP.
	 */
	public function add_debug_information( $debugging_information ) {
		return array_merge(
			$debugging_information,
			[
				'amp_wp' => [
					'label'       => $this->get_badge_label(),
					'description' => esc_html__( 'Debugging information for the Official AMP Plugin for WordPress.', 'amp' ),
					'fields'      => [
						'amp_slug_query_var'      => [
							'label'   => 'AMP slug query var',
							'value'   => amp_get_slug(),
							'private' => false,
						],
						'amp_slug_defined_late'   => [
							'label'   => 'AMP slug defined late',
							'value'   => $this->amp_slug_customization_watcher->did_customize_late() ? 'true' : 'false',
							'private' => false,
						],
						'amp_mode_enabled'        => [
							'label'   => 'AMP mode enabled',
							'value'   => AMP_Options_Manager::get_option( Option::THEME_SUPPORT ),
							'private' => false,
						],
						'amp_reader_theme'        => [
							'label'   => 'AMP Reader theme',
							'value'   => AMP_Options_Manager::get_option( Option::READER_THEME ),
							'private' => false,
						],
						'amp_templates_enabled'   => [
							'label'   => esc_html__( 'Templates enabled', 'amp' ),
							'value'   => $this->get_supported_templates(),
							'private' => false,
						],
						'amp_serve_all_templates' => [
							'label'   => esc_html__( 'Serve all templates as AMP?', 'amp' ),
							'value'   => $this->get_serve_all_templates(),
							'private' => false,
						],
						'amp_css_transient_caching_disabled' => [
							'label'   => esc_html__( 'Transient caching for stylesheets disabled', 'amp' ),
							'value'   => $this->get_css_transient_caching_disabled(),
							'private' => false,
						],
						'amp_css_transient_caching_threshold' => [
							'label'   => esc_html__( 'Threshold for monitoring stylesheet caching', 'amp' ),
							'value'   => $this->get_css_transient_caching_threshold(),
							'private' => false,
						],
						'amp_css_transient_caching_sampling_range' => [
							'label'   => esc_html__( 'Sampling range for monitoring stylesheet caching', 'amp' ),
							'value'   => $this->get_css_transient_caching_sampling_range(),
							'private' => false,
						],
						'amp_css_transient_caching_transient_count' => [
							'label'   => esc_html__( 'Number of stylesheet transient cache entries', 'amp' ),
							'value'   => $this->css_transient_caching->query_css_transient_count(),
							'private' => false,
						],
						'amp_css_transient_caching_time_series' => [
							'label'   => esc_html__( 'Calculated time series for monitoring the stylesheet caching', 'amp' ),
							'value'   => $this->css_transient_caching->get_time_series(),
							'private' => false,
						],
						'amp_libxml_version'      => [
							'label'   => 'libxml Version',
							'value'   => LIBXML_DOTTED_VERSION,
							'private' => false,
						],
					],
				],
			]
		);
	}

	/**
	 * Modify test results.
	 *
	 * @param array $test_result Site Health test result.
	 *
	 * @return array Modified test result.
	 */
	public function modify_test_result( $test_result ) {
		// Set the `https_status` test status to critical if its current status is recommended, along with adding to the
		// description for why its required for AMP.
		if (
			isset( $test_result['test'], $test_result['status'], $test_result['description'] )
			&& 'https_status' === $test_result['test']
			&& 'recommended' === $test_result['status']
		) {
			$test_result['status']       = 'critical';
			$test_result['description'] .= '<p>' . __( 'Additionally, AMP requires HTTPS for most components to work properly, including iframes and videos.', 'amp' ) . '</p>';
		}

		return $test_result;
	}

	/**
	 * Gets the templates that support AMP.
	 *
	 * @return string The supported template(s), in a comma-separated string.
	 */
	private function get_supported_templates() {

		// Get the supported content types, like 'post'.
		$supported_templates = AMP_Post_Type_Support::get_supported_post_types();

		// Add the supported templates, like 'is_author', if not in 'Reader' mode.
		if ( ! amp_is_legacy() ) {
			$supported_templates = array_merge(
				$supported_templates,
				array_keys(
					array_filter(
						AMP_Theme_Support::get_supportable_templates(),
						static function( $option ) {
							return ! empty( $option['supported'] );
						}
					)
				)
			);
		}

		if ( empty( $supported_templates ) ) {
			return esc_html__( 'No template supported', 'amp' );
		}

		return implode( ', ', $supported_templates );
	}

	/**
	 * Gets whether the option to serve all templates is selected.
	 *
	 * @return string The value of the option to serve all templates.
	 */
	private function get_serve_all_templates() {
		if ( amp_is_legacy() ) {
			return esc_html__( 'This option does not apply to Reader mode.', 'amp' );
		}

		// Not translated, as this is debugging information, and it could be confusing getting this from different languages.
		return AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED ) ? 'true' : 'false';
	}

	/**
	 * Gets whether the transient caching of stylesheets was disabled.
	 *
	 * @return string Whether the transient caching of stylesheets was disabled.
	 */
	private function get_css_transient_caching_disabled() {
		if ( wp_using_ext_object_cache() ) {
			return 'n/a';
		}

		$disabled = AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );

		return $disabled ? 'true' : 'false';
	}

	/**
	 * Gets the threshold being used to when monitoring the transient caching of stylesheets.
	 *
	 * @return string Threshold for the transient caching of stylesheets.
	 */
	private function get_css_transient_caching_threshold() {
		/** This filter is documented in src/BackgroundTask/MonitorCssTransientCaching.php */
		$threshold = (float) apply_filters(
			'amp_css_transient_monitoring_threshold',
			$this->css_transient_caching->get_default_threshold()
		);

		return "{$threshold} transients per day";
	}

	/**
	 * Gets the sampling range being used to when monitoring the transient caching of stylesheets.
	 *
	 * @return string Sampling range for the transient caching of stylesheets.
	 */
	private function get_css_transient_caching_sampling_range() {
		/** This filter is documented in src/BackgroundTask/MonitorCssTransientCaching.php */
		$sampling_range = (float) apply_filters(
			'amp_css_transient_monitoring_sampling_range',
			$this->css_transient_caching->get_default_sampling_range()
		);

		return "{$sampling_range} days";
	}

	/**
	 * Adds suggested PHP extensions to those that Core depends on.
	 *
	 * @param array $core_extensions The existing extensions from Core.
	 * @return array The extensions, including those for AMP.
	 */
	public function add_extensions( $core_extensions ) {
		$extensions = [
			'json'     => [
				'extension' => 'json',
				'function'  => 'json_encode',
				'required'  => false,
			],
			'mbstring' => [
				'extension' => 'mbstring',
				'required'  => false,
			],
			'zip'      => [
				'extension' => 'zip',
				'required'  => false,
			],
		];

		if ( $this->is_intl_extension_needed() ) {
			$extensions['intl'] = [
				'extension' => 'intl',
				'function'  => 'idn_to_utf8',
				'required'  => false,
			];
		}

		return array_merge( $core_extensions, $extensions );
	}

	/**
	 * Add needed styles for the Site Health integration.
	 */
	public function add_styles() {
		echo '
			<style>
				.health-check-accordion-panel > p:first-child {
					/* Note this is essentially a core fix. */
					margin-top: 0;
				}

				.wp-core-ui .button.reenable-css-transient-caching ~ .success-icon,
				.wp-core-ui .button.reenable-css-transient-caching ~ .success-text,
				.wp-core-ui .button.reenable-css-transient-caching ~ .failure-icon,
				.wp-core-ui .button.reenable-css-transient-caching ~ .failure-text {
					display: none;
				}

				.wp-core-ui .button.reenable-css-transient-caching ~ .success-icon,
				.wp-core-ui .button.reenable-css-transient-caching ~ .failure-icon {
					font-size: xx-large;
					padding-right: 1rem;
				}

				.wp-core-ui .button.reenable-css-transient-caching.ajax-success ~ .success-icon,
				.wp-core-ui .button.reenable-css-transient-caching.ajax-success ~ .success-text,
				.wp-core-ui .button.reenable-css-transient-caching.ajax-failure ~ .failure-icon,
				.wp-core-ui .button.reenable-css-transient-caching.ajax-failure ~ .failure-text {
					display: inline-block;
				}

				.wp-core-ui .button.reenable-css-transient-caching.ajax-success ~ .success-icon {
					color: #46b450;
				}

				.wp-core-ui .button.reenable-css-transient-caching.ajax-failure ~ .failure-icon {
					color: #dc3232;
				}

				#health-check-accordion-block-amp_page_cache .dashicons-yes-alt {
					color: #46b450;
				}
				#health-check-accordion-block-amp_page_cache .dashicons-dismiss {
					color: #dc3232;
				}
				#health-check-accordion-block-amp_page_cache .dashicons-warning {
					color: #dba617;
				}
			</style>
		';
	}

	/**
	 * Determine if the `intl` extension is needed.
	 *
	 * @return bool True if the `intl` extension is needed, otherwise false.
	 */
	private function is_intl_extension_needed() {
		// Publisher's own origins.
		$domains = array_unique(
			[
				wp_parse_url( site_url(), PHP_URL_HOST ),
				wp_parse_url( home_url(), PHP_URL_HOST ),
			]
		);

		foreach ( $domains as $domain ) {
			if ( preg_match( '/(^|\.)xn--/', $domain ) ) {
				return true;
			}
		}

		return false;
	}
}
