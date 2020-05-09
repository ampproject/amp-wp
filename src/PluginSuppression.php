<?php
/**
 * Abstract class PluginSuppression.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Validation_Manager;
use WP_Hook;

/**
 * Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.
 *
 * @package AmpProject\AmpWP
 */
final class PluginSuppression implements Service {

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp', [ $this, 'suppress_plugins' ] );
	}

	/**
	 * Suppress plugins.
	 *
	 * @global WP_Hook[] $wp_filter
	 */
	public function suppress_plugins() {
		global $wp_filter;
		$suppressed = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		if ( empty( $suppressed ) ) {
			return;
		}

		// @todo We need to also remove shortcodes.
		// @todo We need to also remove widgets.
		// @todo We need to also remove blocks?
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					$source = AMP_Validation_Manager::get_source( $callback['function'] );
					if (
						isset( $source['type'], $source['name'] ) &&
						'plugin' === $source['type'] &&
						array_key_exists( $source['name'], $suppressed )
					) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}
}
