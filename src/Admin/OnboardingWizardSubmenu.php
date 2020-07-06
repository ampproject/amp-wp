<?php
/**
 * OnboardingWizardSubmenu class.
 *
 * @package AMP
 * @since 1.6.0
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * AMP onboarding wizard submenu class.
 *
 * @since 1.6.0
 */
final class OnboardingWizardSubmenu implements Delayed, Service, Registerable {
	/**
	 * Setup screen ID.
	 *
	 * @var string
	 */
	const SCREEN_ID = 'amp-onboarding-wizard';

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_menu';
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			__( 'Onboarding Wizard', 'amp' ),
			__( 'Onboarding Wizard', 'amp' ),
			'manage_options',
			self::SCREEN_ID,
			'__return_empty_string'
		);
	}
}
