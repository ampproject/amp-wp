<?php
/**
 * Abstract seed base class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use RuntimeException;

final class WpImporterCompat {

	/**
	 * Prepare the compatibility layer for an import.
	 */
	public function prepare_import() {
		$this->assert_importer_plugin_is_available();

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				require $class_wp_importer;
			}
		}

		$wordpress_importer_path = dirname( AMP__DIR__ ) . '/wordpress-importer';

		require_once "{$wordpress_importer_path}/compat.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-simplexml.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-xml.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-regex.php";
		require_once "{$wordpress_importer_path}/class-wp-import.php";
	}

	/**
	 * Prepare the compatibility layer for an export.
	 */
	public function prepare_export() {
		$this->assert_importer_plugin_is_available();

		// @TODO: Add requires as needed.
	}

	/**
	 * Determines whether the WordPress Importer plugin is available.
	 */
	private function assert_importer_plugin_is_available() {
		if ( class_exists( 'WP_Import' ) ) {
			return true;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins            = get_plugins();
		$wordpress_importer = 'wordpress-importer/wordpress-importer.php';

		if ( ! array_key_exists( $wordpress_importer, $plugins ) ) {
			throw new RuntimeException(
				"WordPress Importer needs to be installed. Try 'wp plugin install wordpress-importer'."
			);
		}
	}
}
