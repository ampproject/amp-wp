<?php
/**
 * Class ValidationCounts.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;

/**
 * Loads assets necessary to retrieve and show the unreviewed counts for validated URLs and validation errors in
 * the AMP admin menu.
 *
 * @since 2.1
 * @internal
 */
final class ValidationCounts implements Service, Registerable, Conditional, Delayed, HasRequirements {

	/**
	 * Assets handle.
	 *
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-validation-counts';

	/**
	 * RESTPreloader instance.
	 *
	 * @var RESTPreloader
	 */
	private $rest_preloader;

	/**
	 * ValidationCounts constructor.
	 *
	 * @param RESTPreloader $rest_preloader An instance of the RESTPreloader class.
	 */
	public function __construct( RESTPreloader $rest_preloader ) {
		$this->rest_preloader = $rest_preloader;
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_enqueue_scripts';
	}

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return string[] List of required services.
	 */
	public static function get_requirements() {
		return [
			'dependency_support',
			'dev_tools.user_access',
		];
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return Services::get( 'dependency_support' )->has_support() && Services::get( 'dev_tools.user_access' )->is_user_enabled();
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_scripts() {
		$asset_file   = AMP__DIR__ . '/assets/js/' . self::ASSETS_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'js/' . self::ASSETS_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'css/' . self::ASSETS_HANDLE . '.css' ),
			false,
			$version
		);

		$this->maybe_add_preload_rest_paths();
	}

	/**
	 * Adds REST paths to preload.
	 *
	 * Preload validation counts data on an admin screen that has the AMP Options page as a parent or on any admin
	 * screen related to `amp_validation_error` post type (which includes the `amp_validation_error` taxonomy).
	 */
	protected function maybe_add_preload_rest_paths() {
		if ( $this->is_dedicated_plugin_screen() ) {
			$this->rest_preloader->add_preloaded_path( '/amp/v1/unreviewed-validation-counts' );
		}
	}

	/**
	 * Whether the current screen is pages inside the AMP Options menu.
	 */
	public function is_dedicated_plugin_screen() {
		return (
			AMP_Options_Manager::OPTION_NAME === get_admin_page_parent()
			||
			AMP_Validated_URL_Post_Type::POST_TYPE_SLUG === get_current_screen()->post_type
		);
	}
}
