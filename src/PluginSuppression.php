<?php
/**
 * Class PluginSuppression.
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
		$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
		add_action( 'wp', [ $this, 'suppress_plugins' ], $priority );
	}

	/**
	 * Suppress plugins.
	 */
	public function suppress_plugins() {
		if ( ! is_amp_endpoint() ) {
			return;
		}

		$suppressed = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		if ( empty( $suppressed ) ) {
			return;
		}

		$suppressed_plugin_slugs = array_keys( $suppressed );

		$this->suppress_hooks( $suppressed_plugin_slugs );
		$this->suppress_shortcodes( $suppressed_plugin_slugs );
		$this->suppress_blocks( $suppressed_plugin_slugs );
		$this->suppress_widgets( $suppressed_plugin_slugs );
	}

	/**
	 * Suppress plugin hooks.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global WP_Hook[] $wp_filter
	 */
	private function suppress_hooks( $suppressed_plugins ) {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					if ( $this->is_callback_plugin_suppressed( $callback['function'], $suppressed_plugins ) ) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}

	/**
	 * Suppress plugin shortcodes.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $shortcode_tags
	 */
	private function suppress_shortcodes( $suppressed_plugins ) {
		global $shortcode_tags;

		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			if ( $this->is_callback_plugin_suppressed( $shortcode_tags[ $tag ], $suppressed_plugins ) ) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	}

	/**
	 * Suppress plugin blocks.
	 *
	 * @todo What about static blocks added?
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 */
	private function suppress_blocks( $suppressed_plugins ) {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $block_type ) {
			if ( ! $block_type->is_dynamic() || ! $this->is_callback_plugin_suppressed( $block_type->render_callback, $suppressed_plugins ) ) {
				continue;
			}
			$block_type->script          = null;
			$block_type->style           = null;
			$block_type->render_callback = '__return_empty_string';
		}
	}

	/**
	 * Suppress plugin widgets.
	 *
	 * @see \AMP_Validation_Manager::wrap_widget_callbacks() Which needs to run after this.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $wp_registered_widgets
	 */
	private function suppress_widgets( $suppressed_plugins ) {
		global $wp_registered_widgets;
		foreach ( $wp_registered_widgets as $widget_id => &$registered_widget ) {
			if ( $this->is_callback_plugin_suppressed( $registered_widget['callback'], $suppressed_plugins ) ) {
				$registered_widget['callback'] = '__return_empty_string';
			}
		}
	}

	/**
	 * Determine whether callback is from a suppressed plugin.
	 *
	 * @param callable $callback           Callback.
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @return bool Whether from suppressed plugin.
	 */
	private function is_callback_plugin_suppressed( $callback, $suppressed_plugins ) {
		$source = AMP_Validation_Manager::get_source( $callback );
		return (
			isset( $source['type'], $source['name'] ) &&
			'plugin' === $source['type'] &&
			in_array( $source['name'], $suppressed_plugins, true )
		);
	}
}
