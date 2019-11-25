<?php
/**
 * Class SiteHealth.
 *
 * @package AMP
 */

namespace Amp\AmpWP\Admin;

/**
 * Class SiteHealth
 *
 * Adds tests to Site Health.
 *
 * @since 1.5.0
 */
class SiteHealth {

	/**
	 * Adds the filter.
	 */
	public function init() {
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
	}

	/**
	 * Adds Site Health tests related to this plugin.
	 *
	 * @param array $tests The Site Health tests.
	 * @return array $tests The filtered tests.
	 */
	public function add_tests( $tests ) {
		$direct_tests = [
			'persistent_object_cache' => esc_html__( 'Persistent object cache', 'amp' ),
			/* translators: %s: a PHP function type */
			'curl_multi_functions'    => sprintf( esc_html__( '%s functions', 'amp' ), 'curl_multi_*' ),
			'amp_mode_enabled'        => esc_html__( 'AMP mode enabled', 'amp' ),
			'amp_experiences_enabled' => esc_html__( 'AMP experiences enabled', 'amp' ),
			'amp_templates_enabled'   => esc_html__( 'AMP templates enabled', 'amp' ),
		];

		foreach ( $direct_tests as $test_name => $test_label ) {
			$tests['direct'][ $test_name ] = [
				'label' => $test_label,
				'test'  => [ $this, $test_name ],
			];
		}

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
			'test'        => 'persistent_object_cache',
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
						esc_url( 'https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching' ),
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
				esc_url( 'https://www.php.net/manual/book.curl.php' ),
				esc_html__( 'Learn more about these functions', 'amp' ),
				/* translators: The accessibility text. */
				esc_html__( '(opens in a new tab)', 'amp' )
			),
			'test'        => 'curl_multi_functions',
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
							_n(
								/* translators: %s: the name(s) of the curl_multi_* PHP function(s) */
								'The following curl_multi_* function is not defined: %s. The AMP plugin performs better when this function is available.',
								'The following curl_multi_* functions are not defined: %s. The AMP plugin performs better when these functions are available.',
								count( $undefined_curl_functions ),
								'amp'
							),
							implode(
								array_map(
									static function( $function_name ) {
										return sprintf( '<code>%s()</code>', $function_name );
									},
									$undefined_curl_functions
								),
								', '
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
}
