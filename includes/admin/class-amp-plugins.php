<?php
/**
 * Class AMP_Plugins
 *
 * @package AMP
 * @since   2.2.2
 */

/**
 * Class to add
 */
class AMP_Plugins {

	/**
	 * Registers functionality through WordPress hooks.
	 *
	 * @return void
	 */
	public function init() {

		if ( ! is_admin() ) {
			return;
		}

		add_filter( 'plugin_row_meta', [ $this, 'filter_plugin_row_meta' ], 10, 3 );
	}

	/**
	 * Get list of suppressed plugins.
	 *
	 * @return array List of of suppressed plugins.
	 */
	protected static function get_suppressed_plugins() {

		static $suppressed_plugins = [];

		if ( empty( $suppressed_plugins ) ) {
			$suppressed_plugins = AMP_Options_Manager::get_option( 'suppressed_plugins' );
		}

		return $suppressed_plugins;
	}

	/**
	 * Add meta if plugin is suppressed in AMP page.
	 *
	 * @param array  $plugin_meta An array of the plugin's metadata.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 *
	 * @return array An array of the plugin's metadata
	 */
	public function filter_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data ) {

		$suppressed_plugins = self::get_suppressed_plugins();
		$plugin_slug        = isset( $plugin_data['slug'] ) ? $plugin_data['slug'] : false;

		if ( empty( $plugin_slug ) ) {
			$plugin_slug = strtok( $plugin_file, '/' );
		}

		if ( isset( $suppressed_plugins[ $plugin_slug ] ) ) {
			$plugin_meta[] = sprintf(
				'<a href="%s" aria-label="%s" target="_blank">%s</a>',
				esc_url( admin_url( 'admin.php?page=amp-options' ) ),
				esc_attr__( 'Visit AMP Settings', 'amp' ),
				__( 'Suppressed on AMP Pages', 'amp' )
			);
		}

		return $plugin_meta;
	}
}
