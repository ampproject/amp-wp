<?php
/**
 * Trait MockAdminUser.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for mocking an user:
 * - Admin user in single site.
 * - Super admin user in multisite.
 *
 * @package AmpProject\AmpWP
 */
trait MockAdminUser {

	/**
	 * Mock an admin or super admin user.
	 *
	 * @return WP_User
	 */
	public function mock_admin_user() {
		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );

		if ( is_multisite() ) {
			grant_super_admin( $admin_user->ID );
		}

		wp_set_current_user( $admin_user->ID );

		return $admin_user;
	}
}
