<?php
/**
 * Abstract class PluginSuppression.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Validation_Manager;
use WP_Block_Type_Registry;
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
	 */
	public function suppress_plugins() {

		$suppressed = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		if ( empty( $suppressed ) ) {
			return;
		}

		$plugin_slugs = array_keys( $suppressed );

		$this->suppress_hooks( $plugin_slugs );
		$this->suppress_shortcodes( $plugin_slugs );
		$this->suppress_blocks( $plugin_slugs );

		// @todo We need to also remove widgets.
	}

	/**
	 * Suppress plugin hooks.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugin slugs.
	 * @global WP_Hook[] $wp_filter
	 */
	public function suppress_hooks( $suppressed_plugins ) {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					$source = AMP_Validation_Manager::get_source( $callback['function'] );
					if (
						isset( $source['type'], $source['name'] ) &&
						'plugin' === $source['type'] &&
						in_array( $source['name'], $suppressed_plugins, true )
					) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}

	/**
	 * Suppress plugin shortcodes.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugin slugs.
	 * @global array $shortcode_tags
	 */
	public function suppress_shortcodes( $suppressed_plugins ) {
		global $shortcode_tags;

		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			$source = AMP_Validation_Manager::get_source( $shortcode_tags[ $tag ] );
			if (
				isset( $source['type'], $source['name'] ) &&
				'plugin' === $source['type'] &&
				in_array( $source['name'], $suppressed_plugins, true )
			) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	}

	/**
	 * Suppress plugin blocks.
	 *
	 * @todo What about static blocks added?
	 * @param string[] $suppressed_plugins Suppressed plugin slugs.
	 */
	public function suppress_blocks( $suppressed_plugins ) {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $block_type ) {
			if ( ! $block_type->is_dynamic() ) {
				continue;
			}
			$source = AMP_Validation_Manager::get_source( $block_type->render_callback );
			if (
				isset( $source['type'], $source['name'] ) &&
				'plugin' === $source['type'] &&
				in_array( $source['name'], $suppressed_plugins, true )
			) {
				$block_type->script          = null;
				$block_type->style           = null;
				$block_type->render_callback = '__return_empty_string';
			}
		}
	}
}
