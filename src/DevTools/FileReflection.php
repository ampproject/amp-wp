<?php
/**
 * Class FileReflection.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\DevTools;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\PluginRegistry;

/**
 * Reflect on a file to deduce its type of source (plugin, theme, core).
 *
 * @package AmpProject\AmpWP
 * @since 2.0.2
 * @internal
 */
final class FileReflection implements Service, Registerable {

	// Fields to include in the source array.
	const SOURCE_FILE = 'file';
	const SOURCE_NAME = 'name';
	const SOURCE_TYPE = 'type';

	// Source types.
	const TYPE_CORE      = 'core';
	const TYPE_MU_PLUGIN = 'mu-plugin';
	const TYPE_THEME     = 'theme';
	const TYPE_PLUGIN    = 'plugin';

	// Regular expression patterns to use.
	const SLUG_PATTERN         = '(?<slug>[^/]+)';
	const FILE_PATTERN         = '(?<file>.*$)';
	const SLASHED_FILE_PATTERN = '(/(?<file>.*$))?';
	const CORE_FILE_PATTERN    = '(?<slug>wp-admin|wp-includes)/(?<file>.*$)';

	/**
	 * Plugin registry instance to use.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Plugin file pattern to use.
	 *
	 * Used as cache to not recreate the pattern with each file lookup.
	 *
	 * @var string
	 */
	private $plugin_file_pattern;

	/**
	 * Parent theme pattern to use.
	 *
	 * Used as cache to not recreate the pattern with each file lookup.
	 *
	 * @var string|null
	 */
	private $parent_theme_pattern;

	/**
	 * Child theme pattern to use.
	 *
	 * Used as cache to not recreate the pattern with each file lookup.
	 *
	 * @var string|null
	 */
	private $child_theme_pattern;

	/**
	 * Must-use plugin file pattern to use.
	 *
	 * Used as cache to not recreate the pattern with each file lookup.
	 *
	 * @var string
	 */
	private $mu_plugin_file_pattern;

	/**
	 * WordPress Core file pattern to use.
	 *
	 * Used as cache to not recreate the pattern with each file lookup.
	 *
	 * @var string
	 */
	private $core_file_pattern;

	/**
	 * Template directory.
	 *
	 * Used as cache to avoid running a filtered getter with each file lookup.
	 *
	 * @var string|null
	 */
	private $template_directory;

	/**
	 * Template slug.
	 *
	 * Used as cache to avoid running a filtered getter with each file lookup.
	 *
	 * @var string|null
	 */
	private $template_slug;

	/**
	 * Stylesheet directory.
	 *
	 * Used as cache to avoid running a filtered getter with each file lookup.
	 *
	 * @var string|null
	 */
	private $stylesheet_directory;

	/**
	 * Stylesheet slug.
	 *
	 * Used as cache to avoid running a filtered getter with each file lookup.
	 *
	 * @var string|null
	 */
	private $stylesheet_slug;

	/**
	 * FileReflection constructor.
	 *
	 * @param PluginRegistry $plugin_registry Plugin registry to use.
	 */
	public function __construct( PluginRegistry $plugin_registry ) {
		$this->plugin_registry = $plugin_registry;
	}

	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'setup_theme', [ $this, 'reset_theme_variables' ], ~PHP_INT_MAX );
	}

	/**
	 * Reset the cached theme variables.
	 *
	 * This is needed in case a plugin has dynamically changed the theme.
	 */
	public function reset_theme_variables() {
		$this->template_directory   = null;
		$this->template_slug        = null;
		$this->parent_theme_pattern = null;
		$this->stylesheet_directory = null;
		$this->stylesheet_slug      = null;
		$this->child_theme_pattern  = null;
	}

	/**
	 * Identify the type, name, and relative path for a file.
	 *
	 * @param string $file File.
	 * @return array {
	 *     @type string $type Source type (core, plugin, mu-plugin, or theme). Not set if no match.
	 *     @type string $name Source name. Not set if no match.
	 *     @type string $file Relative file path based on the type. Not set if no match.
	 * }
	 */
	public function get_file_source( $file ) {
		static $recursion_protection = false;

		if ( $recursion_protection ) {
			return [];
		}

		$recursion_protection = true;

		$matches = [];

		if ( $this->is_parent_theme_file( $file, $matches ) ) {
			$recursion_protection = false;
			return $this->get_file_source_array(
				self::TYPE_THEME,
				$this->get_template_slug(),
				$matches['file']
			);
		}

		if ( $this->is_child_theme_file( $file, $matches ) ) {
			$recursion_protection = false;
			return $this->get_file_source_array(
				self::TYPE_THEME,
				$this->get_stylesheet_slug(),
				$matches['file']
			);
		}

		if ( $this->is_plugin_file( $file, $matches ) ) {
			$recursion_protection = false;
			return $this->get_file_source_array(
				self::TYPE_PLUGIN,
				$matches['slug'],
				isset( $matches['file'] ) ? $matches['file'] : $matches['slug']
			);
		}

		if ( $this->is_mu_plugin_file( $file, $matches ) ) {
			$recursion_protection = false;
			return $this->get_file_source_array(
				self::TYPE_MU_PLUGIN,
				$matches['slug'],
				isset( $matches['file'] ) ? $matches['file'] : $matches['slug']
			);
		}

		if ( $this->is_core_file( $file, $matches ) ) {
			$recursion_protection = false;
			return $this->get_file_source_array(
				self::TYPE_CORE,
				$matches['slug'],
				$matches['file']
			);
		}

		$recursion_protection = false;
		return [];
	}

	/**
	 * Get the template directory.
	 *
	 * @return string Template directory.
	 */
	private function get_template_directory() {
		if ( null === $this->template_directory ) {
			$this->template_directory = wp_normalize_path(
				get_template_directory()
			);
		}

		return $this->template_directory;
	}

	/**
	 * Get the template slug.
	 *
	 * @return string Template slug.
	 */
	private function get_template_slug() {
		if ( null === $this->template_slug ) {
			$this->template_slug = get_template();
		}

		return $this->template_slug;
	}

	/**
	 * Get the stylesheet directory.
	 *
	 * @return string Stylesheet directory.
	 */
	private function get_stylesheet_directory() {
		if ( null === $this->stylesheet_directory ) {
			$this->stylesheet_directory = wp_normalize_path(
				get_stylesheet_directory()
			);
		}

		return $this->stylesheet_directory;
	}

	/**
	 * Get the stylesheet slug.
	 *
	 * @return string Stylesheet slug.
	 */
	private function get_stylesheet_slug() {
		if ( null === $this->stylesheet_slug ) {
			$this->stylesheet_slug = get_stylesheet();
		}

		return $this->stylesheet_slug;
	}

	/**
	 * Check whether the given file belongs to a plugin.
	 *
	 * @param string $file    File to check.
	 * @param array  $matches Associative array of matches, passed by reference.
	 * @return false|int Number of found matches, or false if an error occurred.
	 */
	private function is_plugin_file( $file, &$matches ) {
		if ( null === $this->plugin_file_pattern ) {
			$this->plugin_file_pattern = sprintf(
				':%s%s%s:s',
				preg_quote(
					trailingslashit(
						wp_normalize_path(
							$this->plugin_registry->get_plugin_dir()
						)
					),
					':'
				),
				self::SLUG_PATTERN,
				self::SLASHED_FILE_PATTERN
			);
		}

		return preg_match( $this->plugin_file_pattern, $file, $matches );
	}

	/**
	 * Check whether the given file belongs to a parent theme.
	 *
	 * @param string $file    File to check.
	 * @param array  $matches Associative array of matches, passed by reference.
	 * @return false|int Number of found matches, or false if an error occurred.
	 */
	private function is_parent_theme_file( $file, &$matches ) {
		$template_directory = $this->get_template_directory();

		if ( empty( $template_directory ) ) {
			return false;
		}

		if ( null === $this->parent_theme_pattern ) {
			$this->parent_theme_pattern = sprintf(
				':%s%s:s',
				preg_quote( trailingslashit( $template_directory ), ':' ),
				self::FILE_PATTERN
			);
		}

		return preg_match( $this->parent_theme_pattern, $file, $matches );
	}

	/**
	 * Check whether the given file belongs to a child theme.
	 *
	 * @param string $file    File to check.
	 * @param array  $matches Associative array of matches, passed by reference.
	 * @return false|int Number of found matches, or false if an error occurred.
	 */
	private function is_child_theme_file( $file, &$matches ) {
		$stylesheet_directory = $this->get_stylesheet_directory();

		if ( empty( $stylesheet_directory ) ) {
			return false;
		}

		if ( null === $this->child_theme_pattern ) {
			$this->child_theme_pattern = sprintf(
				':%s%s:s',
				preg_quote( trailingslashit( $stylesheet_directory ), ':' ),
				self::FILE_PATTERN
			);
		}

		return preg_match( $this->child_theme_pattern, $file, $matches );
	}

	/**
	 * Check whether the given file belongs to a must-use plugin.
	 *
	 * @param string $file    File to check.
	 * @param array  $matches Associative array of matches, passed by reference.
	 * @return false|int Number of found matches, or false if an error occurred.
	 */
	private function is_mu_plugin_file( $file, &$matches ) {
		if ( null === $this->mu_plugin_file_pattern ) {
			$this->mu_plugin_file_pattern = sprintf(
				':%s%s%s:s',
				preg_quote(
					trailingslashit(
						wp_normalize_path( WPMU_PLUGIN_DIR )
					),
					':'
				),
				self::SLUG_PATTERN,
				self::SLASHED_FILE_PATTERN
			);
		}

		return preg_match( $this->mu_plugin_file_pattern, $file, $matches );
	}

	/**
	 * Check whether the given file belongs to WordPress Core.
	 *
	 * @param string $file    File to check.
	 * @param array  $matches Associative array of matches, passed by reference.
	 * @return false|int Number of found matches, or false if an error occurred.
	 */
	private function is_core_file( $file, &$matches ) {
		if ( null === $this->core_file_pattern ) {
			$this->core_file_pattern = sprintf(
				':%s%s:s',
				preg_quote(
					trailingslashit(
						wp_normalize_path( ABSPATH )
					),
					':'
				),
				self::CORE_FILE_PATTERN
			);
		}

		return preg_match( $this->core_file_pattern, $file, $matches );
	}

	/**
	 * Get a new file source array.
	 *
	 * @param string $type Type of the file source.
	 * @param string $name Name of the file source.
	 * @param string $file File reference for the file source.
	 *
	 * @return string[] File source array.
	 */
	private function get_file_source_array( $type, $name, $file ) {
		return [
			self::SOURCE_TYPE => $type,
			self::SOURCE_NAME => $name,
			self::SOURCE_FILE => $file,
		];
	}
}
