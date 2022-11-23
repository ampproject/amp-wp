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
	 */
	public function mock_admin_user() {
		if ( is_multisite() ) {
			$user_id = self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			);

			grant_super_admin( $user_id );
			wp_set_current_user( $user_id );
		} else {
			wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		}
	}
}
