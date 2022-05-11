<?php
/**
 * Class BlockSources
 *
 * Captures the themes and plugins responsible for dynamically registered editor blocks.
 *
 * @since 2.1
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\DevTools;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\PluginRegistry;

/**
 * BlockSources class.
 *
 * @since 2.1
 * @internal
 */
final class BlockSources implements Conditional, Service, Registerable {

	/**
	 * Key of the cached block source data.
	 *
	 * @var string
	 */
	const CACHE_KEY = 'amp_block_sources';

	/**
	 * The amount of time to store the block source data in cache.
	 *
	 * @var int
	 */
	const CACHE_TIMEOUT = DAY_IN_SECONDS;

	/**
	 * Block source data.
	 *
	 * @var array|null
	 */
	private $block_sources;

	/**
	 * Plugin registry instance.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Likely culprit detector instance.
	 *
	 * @var LikelyCulpritDetector
	 */
	private $likely_culprit_detector;

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		// The register_block_type_args filter, which this feature depends on, was introduced in WP 5.5.
		if ( version_compare( get_bloginfo( 'version' ), '5.5', '<' ) ) {
			return false;
		}

		return is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Class constructor.
	 *
	 * @param PluginRegistry        $plugin_registry Plugin registry instance.
	 * @param LikelyCulpritDetector $likely_culprit_detector Likely culprit detector instance.
	 */
	public function __construct( PluginRegistry $plugin_registry, LikelyCulpritDetector $likely_culprit_detector ) {
		$this->plugin_registry         = $plugin_registry;
		$this->likely_culprit_detector = $likely_culprit_detector;
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		if ( empty( $this->get_block_sources() ) ) {
			add_filter( 'register_block_type_args', [ $this, 'capture_block_type_source' ] );

			// All blocks should be registered well before admin_enqueue_scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'cache_block_sources' ], PHP_INT_MAX );
		}

		add_action( 'activated_plugin', [ $this, 'clear_block_sources_cache' ] );
		add_action( 'after_switch_theme', [ $this, 'clear_block_sources_cache' ] );
		add_action( 'upgrader_process_complete', [ $this, 'clear_block_sources_cache' ] );
	}

	/**
	 * Registers the google font style.
	 *
	 * @param array $args Array of arguments for registering a block type.
	 * @return array Filtered block type args.
	 */
	public function capture_block_type_source( $args ) {
		if ( isset( $this->get_block_sources()[ $args['name'] ] ) ) {
			return $args;
		}

		$likely_culprit = $this->likely_culprit_detector->analyze_backtrace();

		if ( in_array( $likely_culprit[ FileReflection::SOURCE_TYPE ], [ FileReflection::TYPE_PLUGIN, FileReflection::TYPE_MU_PLUGIN ], true ) ) {
			$plugin = $this->plugin_registry->get_plugin_from_slug(
				$likely_culprit[ FileReflection::SOURCE_NAME ],
				FileReflection::TYPE_MU_PLUGIN === $likely_culprit[ FileReflection::SOURCE_TYPE ]
			);

			$likely_culprit['title'] = isset( $plugin['data']['Title'] ) ? $plugin['data']['Title'] : $likely_culprit[ FileReflection::SOURCE_NAME ];
		} elseif ( FileReflection::TYPE_THEME === $likely_culprit[ FileReflection::SOURCE_TYPE ] ) {
			$theme                   = wp_get_theme( $likely_culprit['name'] );
			$likely_culprit['title'] = $theme->get( 'Name' ) ?: $likely_culprit[ FileReflection::SOURCE_NAME ];
		} else {
			$likely_culprit['title'] = __( 'WordPress core', 'amp' );
		}

		$this->block_sources[ $args['name'] ] = $likely_culprit;

		return $args;
	}

	/**
	 * Saves the block source data to cache.
	 */
	public function cache_block_sources() {
		set_transient( __CLASS__ . self::CACHE_KEY, $this->block_sources, self::CACHE_TIMEOUT );
	}

	/**
	 * Clears the cached block source data.
	 */
	public function clear_block_sources_cache() {
		delete_transient( __CLASS__ . self::CACHE_KEY );
	}

	/**
	 * Retrieves block source data from cache.
	 */
	private function set_block_sources_from_cache() {
		$from_cache = get_transient( __CLASS__ . self::CACHE_KEY );

		$this->block_sources = is_array( $from_cache ) ? $from_cache : [];
	}

	/**
	 * Retrieves block source data.
	 *
	 * @return array
	 */
	public function get_block_sources() {
		if ( is_null( $this->block_sources ) ) {
			$this->set_block_sources_from_cache();
		}

		return $this->block_sources;
	}
}
