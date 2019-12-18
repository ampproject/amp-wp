<?php
/**
 * Class SiteHealth.
 *
 * @package AMP
 */

namespace Amp\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Post_Type_Support;

/**
 * Class SiteHealth
 *
 * Adds tests and debugging information for Site Health.
 *
 * @since 1.5.0
 */
class SiteHealth {

	/**
	 * Adds the filters.
	 */
	public function init() {
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
		add_filter( 'debug_information', [ $this, 'add_debug_information' ] );
		add_filter( 'site_status_test_php_modules', [ $this, 'add_extensions' ] );
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
			/* translators: %s: a type of PHP function */
			'label' => sprintf( esc_html__( '%s functions', 'amp' ), 'curl_multi_*' ),
			'test'  => [ $this, 'curl_multi_functions' ],
		];
		$tests['direct']['amp_icu_version']             = [
			'label' => esc_html__( 'ICU version', 'amp' ),
			'test'  => [ $this, 'icu_version' ],
		];

		return $tests;
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
			'description' => esc_html__( 'The AMP plugin performs at its best when persistent object cache is enabled.', 'amp' ),
			'actions'     => '',
			'test'        => 'amp_persistent_object_cache',
		];

		if ( $is_using_object_cache ) {
			return array_merge(
				$data,
				[
					'status' => 'good',
					'label'  => esc_html__( 'Persistent object caching is enabled', 'amp' ),
				]
			);
		} else {
			return array_merge(
				$data,
				[
					'status'  => 'recommended',
					'label'   => esc_html__( 'Persistent object caching is not enabled', 'amp' ),
					'actions' => sprintf(
						'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
						'https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching',
						esc_html__( 'Learn more about persistent object caching', 'amp' ),
						/* translators: The accessibility text. */
						esc_html__( '(opens in a new tab)', 'amp' )
					),
				]
			);
		}
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
			'description' => esc_html__( 'The AMP plugin performs better when these functions are available.', 'amp' ),
			'actions'     => sprintf(
				'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
				'https://www.php.net/manual/book.curl.php',
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
							'A curl_multi_* function is not defined',
							'Some curl_multi_* functions are not defined',
							count( $undefined_curl_functions ),
							'amp'
						)
					),
					'description' => wp_kses(
						sprintf(
							/* translators: %s: the name(s) of the curl_multi_* PHP function(s) */
							_n(
								'The following curl_multi_* function is not defined: %s. The AMP plugin performs better when this function is available.',
								'The following curl_multi_* functions are not defined: %s. The AMP plugin performs better when these functions are available.',
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
						),
						[ 'code' => [] ]
					),
				]
			);
		} else {
			return array_merge(
				$data,
				[
					'status' => 'good',
					'label'  => esc_html__( 'The curl_multi_* functions are defined.', 'amp' ),
				]
			);
		}
	}

	/**
	 * Gets the test result data for whether the proper ICU version is available.
	 *
	 * @return array The test data.
	 */
	public function icu_version() {
		$icu_version       = defined( 'INTL_ICU_VERSION' ) ? (float) INTL_ICU_VERSION : null;
		$minimum_version   = 65;
		$is_proper_version = $icu_version >= $minimum_version;

		$data = [
			'badge'       => [
				'label' => esc_html__( 'AMP', 'amp' ),
				'color' => $is_proper_version ? 'green' : 'orange',
			],
			'description' => sprintf(
				/* translators: %s: the minimum recommended ICU version */
				esc_html__( 'The version of ICU can affect how the intl extension runs. The minimum recommended version of ICU is %s.', 'amp' ),
				$minimum_version
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
			return array_merge(
				$data,
				[
					'status' => 'recommended',
					/* translators: %s: the constant for the ICU version */
					'label'  => sprintf( esc_html__( 'The ICU version is unknown, as the constant %s is not defined', 'amp' ), 'INTL_ICU_VERSION' ),
				]
			);
		}

		if ( ! $is_proper_version ) {
			return array_merge(
				$data,
				[
					'status' => 'recommended',
					/* translators: %1$s: the ICU version */
					'label'  => sprintf( esc_html__( 'The version of ICU, %1$s, is out of date.', 'amp' ), $icu_version ),
				]
			);
		}

		return array_merge(
			$data,
			[
				'status' => 'good',
				/* translators: %1$s: the ICU version */
				'label'  => sprintf( esc_html__( 'The version of ICU, %1$s, looks good.', 'amp' ), $icu_version ),
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
				'amp' => [
					'label'       => esc_html__( 'AMP', 'amp' ),
					'description' => esc_html__( 'Debugging information for the Official AMP Plugin for WordPress.', 'amp' ),
					'fields'      => [
						'amp_mode_enabled'        => [
							'label'   => 'AMP mode enabled',
							'value'   => AMP_Theme_Support::get_support_mode(),
							'private' => false,
						],
						'amp_experiences_enabled' => [
							'label'   => esc_html__( 'AMP experiences enabled', 'amp' ),
							'value'   => $this->get_experiences_enabled(),
							'private' => false,
						],
						'amp_templates_enabled'   => [
							'label'   => esc_html__( 'Templates enabled', 'amp' ),
							'value'   => $this->get_supported_templates(),
							'private' => false,
						],
					],
				],
			]
		);
	}

	/**
	 * Gets the AMP experiences that are enabled.
	 *
	 * @return string The experiences, in a comma-separated string.
	 */
	public function get_experiences_enabled() {
		$experiences = AMP_Options_Manager::get_option( 'experiences' );
		if ( empty( $experiences ) ) {
			return esc_html__( 'No experience enabled', 'amp' );
		}

		return implode( ', ', $experiences );
	}

	/**
	 * Gets the templates that support AMP.
	 *
	 * @return string The supported template(s), in a comma-separated string.
	 */
	public function get_supported_templates() {
		$possible_post_types = AMP_Options_Manager::get_option( 'supported_post_types' );

		// Get the supported content types, like 'post'.
		$supported_templates = array_filter(
			AMP_Post_Type_Support::get_eligible_post_types(),
			static function( $template ) use ( $possible_post_types ) {
				$post_type = get_post_type_object( $template );
				return (
					post_type_supports( $post_type->name, AMP_Post_Type_Support::SLUG )
					||
					( ! AMP_Options_Manager::is_website_experience_enabled() && in_array( $post_type->name, $possible_post_types, true ) )
				);
			}
		);

		// Add the supported templates, like 'is_author', if not in 'Reader' mode.
		if ( AMP_Theme_Support::READER_MODE_SLUG !== AMP_Theme_Support::get_support_mode() ) {
			$supported_templates = array_merge(
				$supported_templates,
				array_keys(
					array_filter(
						AMP_Theme_Support::get_supportable_templates(),
						static function( $option ) {
							return (
								( empty( $option['immutable'] ) && ! empty( $option['user_supported'] ) )
								||
								! empty( $option['supported'] )
							);
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
	 * Adds suggested PHP extensions to those that Core depends on.
	 *
	 * @param array $extensions The existing extensions from Core.
	 * @return array The extensions, including those for AMP.
	 */
	public function add_extensions( $extensions ) {
		return array_merge(
			$extensions,
			[
				'intl'     => [
					'extension' => 'intl',
					'function'  => 'idn_to_utf8',
					'required'  => false,
				],
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
			]
		);
	}
}
