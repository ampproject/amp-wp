<?php
/**
 * Manage authorization for PXE-related requests.
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

use AmpProject\AmpWP\Infrastructure\Service;
use WP_User;

/**
 * Authorization class.
 *
 * @since 2.3
 * @internal
 */
final class Authorization implements Service {

	/**
	 * Check if a user can run a PXE analysis.
	 *
	 * @param null|WP_User|int $user Optional. ID of the user to check authorization for. Defaults to the current user.
	 * @return bool Whether the user with the requested ID can run a PXE analysis.
	 */
	public function can_user_run_analysis( $user = null ) {
		if ( null === $user ) {
			$user = wp_get_current_user();
		} elseif ( ! $user instanceof WP_User ) {
			$user = new WP_User( $user );
		}

		// @TODO: Add capability checks to decide whether user is authorized to run an analysis.
		// Using 'manage_options' for now.

		return user_can( $user, 'manage_options' );
	}
}
