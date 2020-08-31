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
use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * Class SiteHealth
 *
 * Adds tests and debugging information for Site Health.
 *
 * @since 1.5.0
 * @internal
 */
final class SiteHealth implements Service, Registerable, Delayed, Conditional {

	/**
	 * Service that monitors and controls the CSS transient caching.
	 *
	 * @var MonitorCssTransientCaching
	 */
	private $css_transient_caching;

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return is_admin() && ! wp_doing_ajax();
	}

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
	 * @param MonitorCssTransientCaching $css_transient_caching CSS transient caching monitoring service.
	 */
	public function __construct( MonitorCssTransientCaching $css_transient_caching ) {
		$this->css_transient_caching = $css_transient_caching;
	}

	/**
	 * Adds the filters.
	 */
	public function register() {
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
		add_filter( 'debug_information', [ $this, 'add_debug_information' ] );
		add_filter( 'site_status_test_result', [ $this, 'modify_test_result' ] );
		add_filter( 'site_status_test_php_modules', [ $this, 'add_extensions' ] );
		add_action( 'admin_print_styles-site-health.php', [ $this, 'add_styles' ] );
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
		$tests['direct']['amp_curl_multi_functions']    = [
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
			esc_url( 'https://make.wordpress.org/hosting/handbook/handbook/performance/#object-cache' ),
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
		$data                  = [
			'badge'       => [
				'label' => esc_html__( 'AMP', 'amp' ),
				'color' => $is_using_object_cache ? 'green' : 'orange',
			],
			'description' => esc_html__( 'The AMP plugin performs at its best when persistent object cache is enabled. Object caching is used to more effectively store image dimensions and parsed CSS.', 'amp' ),
			'actions'     => $this->get_persistent_object_cache_learn_more_action(),
			'test'        => 'amp_persistent_object_cache',
		];

		$status = $is_using_object_cache ? 'good' : 'recommended';
		$label  = $is_using_object_cache
			? __( 'Persistent object caching is enabled', 'amp' )
			: __( 'Persistent object caching is not enabled', 'amp' );

		return array_merge(
			$data,
			[
				'status' => $status,
				'label'  => esc_html( $label ),
			]
		);
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
				'label' => esc_html__( 'AMP', 'amp' ),
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
						_n(
							'A cURL multi function is not defined',
							'Some cURL multi functions are not defined',
							count( $undefined_curl_functions ),
							'amp'
						)
					),
					'description' => wp_kses(
						sprintf(
							/* translators: %s: the name(s) of the cURL multi PHP function(s) */
							_n(
								'The following cURL multi function is not defined: %s.',
								'The following cURL multi functions are not defined: %s.',
								count( $undefined_curl_functions ),
								'amp'
							),
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
				'label' => esc_html__( 'AMP', 'amp' ),
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
				'label' => esc_html__( 'AMP', 'amp' ),
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
					'label' => esc_html__( 'AMP', 'amp' ),
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
					'label'       => esc_html__( 'AMP', 'amp' ),
					'description' => esc_html__( 'Debugging information for the Official AMP Plugin for WordPress.', 'amp' ),
					'fields'      => [
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
