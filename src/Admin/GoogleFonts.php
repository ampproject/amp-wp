<?php
/**
 * Class GoogleFonts.
 *
 * Registers Google fonts for admin screens.
 *
 * @since 1.6.0
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Enqueue Google Fonts stylesheet.
 *
 * @since 1.6.0
 */
final class GoogleFonts implements Delayed, Service, Registerable {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_enqueue_scripts';
	}

	/**
	 * Provides the asset handle.
	 *
	 * @return string
	 */
	public function get_handle() {
		return 'amp-admin-google-fonts';
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		// PHPCS ignore reason: WP will strip multiple `family` args from the Google fonts URL while adding the version string,
		// so we need to avoid specifying a version at all.
		wp_register_style( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			$this->get_handle(),
			'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&family=Poppins:wght@400;700&display=swap',
			[],
			null
		);
	}
}
