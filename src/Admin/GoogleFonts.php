<?php
/**
 * Class GoogleFonts.
 *
 * Registers Google fonts for admin screens.
 *
 * @since 2.0
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Styles;

/**
 * Enqueue Google Fonts stylesheet.
 *
 * @since 2.0
 * @internal
 */
final class GoogleFonts implements Conditional, Delayed, Service, Registerable {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'plugins_loaded';
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
		add_action( 'wp_default_styles', [ $this, 'register_style' ] );
	}

	/**
	 * Registers the google font style.
	 *
	 * @param WP_Styles $wp_styles WP_Styles instance.
	 */
	public function register_style( WP_Styles $wp_styles ) {
		// PHPCS ignore reason: WP will strip multiple `family` args from the Google fonts URL while adding the version string,
		// so we need to avoid specifying a version at all.
		$wp_styles->add( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			$this->get_handle(),
			'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&family=Poppins:wght@400;700&display=swap',
			[],
			null
		);
	}
}
