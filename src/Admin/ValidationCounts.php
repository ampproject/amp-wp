<?php
/**
 * Class ValidatedUrlCounts.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
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
final class ValidationCounts implements Service, Registerable, Conditional, Delayed {

	/**
	 * Assets handle.
	 *
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-validation-counts';

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_init';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		$dev_tools_user_access = Services::get( 'dev_tools.user_access' );
		return $dev_tools_user_access->is_user_enabled();
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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
			false
		);

		wp_enqueue_style(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'css/' . self::ASSETS_HANDLE . '.css' ),
			false,
			$version
		);
	}
}
