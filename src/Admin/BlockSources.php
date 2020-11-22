<?php
/**
 * Class GoogleFonts.
 *
 * Registers Google fonts for admin screens.
 *
 * @since 2.0
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Enqueue Google Fonts stylesheet.
 *
 * @since 2.0
 * @internal
 */
final class BlockSources implements Conditional, Service, Registerable {

	const SOURCE_CORE    = 'core';
	const SOURCE_UNKNOWN = 'unknown';
	const SOURCE_THEME   = 'theme';
	const SOURCE_PLUGIN  = 'plugin';

	const CACHE_KEY = 'amp_block_sources';

	const CACHE_TIMEOUT = DAY_IN_SECONDS;

	/**
	 * Block source data.
	 *
	 * @var array
	 */
	private $block_sources;

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		if ( true || is_null( $this->block_sources ) ) {
			add_filter( 'register_block_type_args', [ $this, 'capture_block_type_source' ] );

			// All blocks should be registered before admin_enqueue_scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'cache_block_sources' ], PHP_INT_MAX );
		}

		add_action( 'activated_plugin', [ $this, 'clear_block_sources_cache' ] );
		add_action( 'after_switch_theme', [ $this, 'clear_block_sources_cache' ] );
	}

	/**
	 * Registers the google font style.
	 *
	 * @param array $args Array of arguments for registering a block type.
	 */
	public function capture_block_type_source( $args ) {
		if ( isset( $this->get_block_sources()[ $args['name'] ] ) ) {
			return $this->get_block_sources()[ $args['name'] ];
		}

		if ( 0 === strpos( $args['name'], 'core/' ) ) {
			$this->block_sources[ $args['name'] ] = [
				'source' => self::SOURCE_CORE,
				'name'   => null,
			];
			return $args;
		}

		// PHPCS ignore reason: debug_backtrace is being used for user-facing AMP debugging tools.
		$backtrace = debug_backtrace(); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace, PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		$files     = array_map(
			function( $entry ) {
				return $entry['file'];
			},
			$backtrace
		);

		// Reverse the file list because the earliest plugin or theme listed is likely to be where the block is registered.
		array_reverse( $files );

		$plugins_directory = trailingslashit( dirname( AMP__DIR__ ) );
		$plugins           = get_plugins();
		$theme_directory   = get_stylesheet_directory();

		foreach ( $files as $file ) {
			if ( 0 === strpos( $file, $theme_directory ) ) {
				$this->block_sources[ $args['name'] ] = [
					'source' => self::SOURCE_THEME,
					'name'   => wp_get_theme()->Name,
				];
				return $args;
			}

			if ( 0 === strpos( $file, $plugins_directory ) ) {
				$plugin_file      = str_replace( $plugins_directory, '', $file );
				$plugin_directory = explode( '/', $plugin_file )[0];

				foreach ( $plugins as $possibly_matching_plugin_file => $plugin ) {
					$possibly_matching_plugin_directory = explode( '/', $possibly_matching_plugin_file )[0];

					if ( $possibly_matching_plugin_directory === $plugin_directory ) {
						$this->block_sources[ $args['name'] ] = [
							'source' => self::SOURCE_PLUGIN,
							'name'   => $plugin['Name'],
						];

						return $args;
					}
				}

				return $args;
			}
		}

		$this->block_sources[ $args['name'] ] = [
			'source' => self::SOURCE_UNKNOWN,
			'name'   => null,
		];

		return $args;
	}


	/**
	 * Saves the block source data to cache.
	 */
	public function cache_block_sources() {
		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( self::CACHE_KEY, $this->block_sources, __CLASS__, self::CACHE_TIMEOUT );
		} else {
			set_transient( __CLASS__ . self::CACHE_KEY, $this->block_sources, self::CACHE_TIMEOUT );
		}
	}

	/**
	 * Clears the cached block source data.
	 */
	public function clear_block_sources_cache() {
		if ( wp_using_ext_object_cache() ) {
			wp_cache_delete( self::CACHE_KEY, __CLASS__ );
		} else {
			delete_transient( __CLASS__ . self::CACHE_KEY );
		}
	}

	/**
	 * Retrieves block source data from cache.
	 *
	 * @return array
	 */
	private function get_block_sources_from_cache() {
		if ( wp_using_ext_object_cache() ) {
			$from_cache = wp_cache_get( self::CACHE_KEY, __CLASS__ );
		} else {
			$from_cache = get_transient( __CLASS__ . self::CACHE_KEY );
		}

		return is_array( $from_cache ) ? $from_cache : [];
	}

	/**
	 * Retrieves block source data.
	 *
	 * @return array
	 */
	public function get_block_sources() {
		if ( is_null( $this->block_sources ) ) {
			$this->block_sources = $this->get_block_sources_from_cache();
		}

		return $this->block_sources;
	}
}
