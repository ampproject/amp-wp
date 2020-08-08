<?php
/**
 * Trait LoadsCoreThemes.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for loading core themes.
 *
 * @package AmpProject\AmpWP
 */
trait LoadsCoreThemes {

	private $original_theme_directories;

	/**
	 * Register the core theme directory.
	 */
	private function register_core_themes() {
		global $wp_theme_directories;

		$this->original_theme_directories = $wp_theme_directories;
		register_theme_directory( ABSPATH . 'wp-content/themes' );
		delete_site_transient( 'theme_roots' );
	}

	/**
	 * Restore the original theme directories.
	 */
	private function restore_theme_directories() {
		global $wp_theme_directories;

		$wp_theme_directories = $this->original_theme_directories;
		delete_site_transient( 'theme_roots' );
	}
}
