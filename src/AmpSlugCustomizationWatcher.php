<?php
/**
 * Class AmpSlugCustomizationWatcher.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Service for redirecting mobile users to the AMP version of a page.
 *
 * @package AmpProject\AmpWP
 * @internal
 */
final class AmpSlugCustomizationWatcher implements Service, Registerable {

	/**
	 * Action at which a slug can be considered late-defined, which is the ideal case.
	 *
	 * @var string
	 */
	const LATE_DETERMINATION_ACTION = 'after_setup_theme';

	/**
	 * Whether the slug was customized early (at plugins_loaded action, priority 8).
	 *
	 * @var bool
	 */
	protected $is_customized_early = false;

	/**
	 * Whether the slug was customized early (at after_setup_theme action, priority 4).
	 *
	 * @var bool
	 */
	protected $is_customized_late = false;

	/**
	 * Register.
	 */
	public function register() {
		// This is at priority 8 because ReaderThemeLoader::override_theme runs at priority 9, which in turn runs right
		// before _wp_customize_include at priority 10. A slug is customized early if it is customized at priority 8.
		add_action( 'plugins_loaded', [ $this, 'determine_early_customization' ], 8 );
	}

	/**
	 * Whether the slug was customized early (at plugins_loaded action, priority 8).
	 *
	 * @return bool
	 */
	public function did_customize_early() {
		return $this->is_customized_early;
	}

	/**
	 * Whether the slug was customized early (at after_setup_theme action, priority 4).
	 *
	 * @return bool
	 */
	public function did_customize_late() {
		return $this->is_customized_late;
	}

	/**
	 * Determine if the slug was customized early.
	 *
	 * Early customization happens by plugins_loaded action at priority 8; this is required in order for the slug to be
	 * used by `ReaderThemeLoader::override_theme()` which runs at priority 9; this method in turn must run before
	 * before `_wp_customize_include()` which runs at plugins_loaded priority 10. At that point the current theme gets
	 * determined, so for Reader themes to apply the logic in `ReaderThemeLoader` must run beforehand.
	 */
	public function determine_early_customization() {
		if ( QueryVar::AMP !== amp_get_slug( true ) ) {
			$this->is_customized_early = true;
		} else {
			add_action( self::LATE_DETERMINATION_ACTION, [ $this, 'determine_late_customization' ], 4 );
		}
	}

	/**
	 * Determine if the slug was defined late.
	 *
	 * Late slug customization often happens when a theme itself defines `AMP_QUERY_VAR`. This is too late for the plugin
	 * to be able to offer Reader themes which must have `AMP_QUERY_VAR` defined by plugins_loaded priority 9. Also,
	 * defining `AMP_QUERY_VAR` is fundamentally incompatible since loading a Reader theme means preventing the original
	 * theme from ever being loaded, and thus the theme's customized `AMP_QUERY_VAR` will never be read.
	 *
	 * This method must run before `amp_after_setup_theme()` which runs at the after_setup_theme action priority 5. In
	 * this function, the `amp_get_slug()` function is called which will then set the query var for the remainder of the
	 * request.
	 *
	 * @see amp_after_setup_theme()
	 */
	public function determine_late_customization() {
		if ( QueryVar::AMP !== amp_get_slug( true ) ) {
			$this->is_customized_late = true;
		}
	}
}
