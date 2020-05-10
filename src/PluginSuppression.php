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
	 * Suppressed plugin slugs.
	 *
	 * @var string[]
	 */
	private $suppressed_plugin_slugs = [];

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

		$this->suppressed_plugin_slugs = array_keys( $suppressed );

		$this->suppress_hooks();
		$this->suppress_shortcodes();
		$this->suppress_blocks();

		// @todo We need to also remove widgets.
	}

	/**
	 * Suppress plugin hooks.
	 *
	 * @global WP_Hook[] $wp_filter
	 */
	private function suppress_hooks() {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					if ( $this->is_callback_plugin_suppressed( $callback['function'] ) ) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}

	/**
	 * Suppress plugin shortcodes.
	 *
	 * @global array $shortcode_tags
	 */
	private function suppress_shortcodes() {
		global $shortcode_tags;

		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			if ( $this->is_callback_plugin_suppressed( $shortcode_tags[ $tag ] ) ) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	}

	/**
	 * Suppress plugin blocks.
	 *
	 * @todo What about static blocks added?
	 */
	private function suppress_blocks() {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $block_type ) {
			if ( ! $block_type->is_dynamic() || ! $this->is_callback_plugin_suppressed( $block_type->render_callback ) ) {
				continue;
			}
			$block_type->script          = null;
			$block_type->style           = null;
			$block_type->render_callback = '__return_empty_string';
		}
	}

	/**
	 * Determine whether callback is from a suppressed plugin.
	 *
	 * @param callable $callback Callback.
	 * @return bool Whether from suppressed plugin.
	 */
	private function is_callback_plugin_suppressed( $callback ) {
		$source = AMP_Validation_Manager::get_source( $callback );
		return (
			isset( $source['type'], $source['name'] ) &&
			'plugin' === $source['type'] &&
			in_array( $source['name'], $this->suppressed_plugin_slugs, true )
		);
	}
}
