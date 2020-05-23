<?php

namespace AmpProject\AmpWP_QA_Tester;

/**
 * Main class for the plugin.
 *
 * @since 1.0.0
 * @package AmpProject\AmpWP_QA_Tester
 */
class Plugin {

	const DOWNLOAD_BASE   = 'https://raw.githubusercontent.com/wiki/ampproject/amp-wp/refs/{PR}/merge/amp-wp';
	const PLUGIN_SLUG     = 'amp';
	const URL_STORAGE_KEY = 'amp_qa_tester_url';
	const REPO_BASE       = 'https://api.github.com/repos/ampproject/amp-wp/';

	/**
	 * Main instance of the plugin.
	 *
	 * @since 1.0.0
	 * @var Plugin|null
	 */
	protected static $instance;

	/**
	 * Admin Bar.
	 *
	 * @since 1.0.0
	 * @var Admin_Bar|null
	 */
	protected $admin_bar;

	/**
	 * Rest Route.
	 *
	 * @since 1.0.0
	 * @var Rest_Route|null
	 */
	protected $rest_route = null;

	/**
	 * Absolute path to the plugin main file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $main_file;

	/**
	 * Sets the plugin main file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( $main_file ) {
		$this->main_file  = $main_file;
		$this->admin_bar  = new Admin_Bar();
		$this->rest_route = new Rest_Route();
	}

	/**
	 * Registers the plugin with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->admin_bar->register();
		$this->rest_route->register();
	}

	/**
	 * Get asset URL.
	 *
	 * @param string $file Relative path to file in assets directory.
	 * @return string URL.
	 */
	public static function get_asset_url( $file ) {
		if ( null === static::$instance ) {
			return null;
		}

		return plugins_url( 'assets/' . $file, static::$instance->main_file );
	}

	/**
	 * Gets the absolute path for a path relative to the plugin directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $relative_path Optional. Relative path. Default '/'.
	 * @return string Absolute path.
	 */
	public static function get_path( $relative_path = '/' ) {
		return plugin_dir_path( static::$instance->main_file ) . ltrim( $relative_path, '/' );
	}

	/**
	 * Loads the plugin main instance and initializes it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 * @return bool True if the plugin main instance could be loaded, false otherwise.
	 */
	public static function load( $main_file ) {
		if ( null !== static::$instance ) {
			return false;
		}

		static::$instance = new static( $main_file );
		static::$instance->register();

		return true;
	}
}
