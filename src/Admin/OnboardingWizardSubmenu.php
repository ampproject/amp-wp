<?php
/**
 * OnboardingWizardSubmenu class.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Icon;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * AMP onboarding wizard submenu class.
 *
 * @since 2.0
 */
final class OnboardingWizardSubmenu implements Conditional, Delayed, Service, Registerable {
	/**
	 * Setup screen ID.
	 *
	 * @var string
	 */
	const SCREEN_ID = 'amp-onboarding-wizard';

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return amp_should_use_new_onboarding();
	}

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
			'',
			__( 'AMP Onboarding Wizard', 'amp' ),
			__( 'AMP Onboarding Wizard', 'amp' ),
			'manage_options',
			self::SCREEN_ID,
			'__return_empty_string',
			99
		);

		if ( amp_is_legacy() && ReaderThemes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME ) ) {
			$selected_theme = AMP_Options_Manager::get_option( Option::READER_THEME );

			/* translators: placeholder is the name of the Reader theme. */
			$error_message = sprintf( __( 'The Reader theme "%s" cannot be found. Your site has been updated to use the AMP Legacy Reader theme.', 'amp' ), $selected_theme );

			add_settings_error(
				AMP_Options_Manager::OPTION_NAME,
				'unavailable_reader_theme',
				wp_kses_post( Icon::warning()->to_html() . $error_message ),
				'warning'
			);
		}
	}
}
