<?php
/**
 * Class DevToolsUserAccess.
 *
 * @since 1.6.0
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_User;

/**
 * Class DevToolsUserAccess
 *
 * @since 1.6.0
 */
final class DevToolsUserAccess implements Service, Registerable {

	/**
	 * User meta key enabling or disabling developer tools.
	 *
	 * @var string
	 */
	const USER_FIELD_DEVELOPER_TOOLS_ENABLED = 'amp_dev_tools_enabled';

	/**
	 * Sets up hooks.
	 */
	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_rest_field' ] );
	}

	/**
	 * Registers a rest field corresponding to the dev tools enabled user meta field.
	 *
	 * @since 1.6.0
	 */
	public function register_rest_field() {
		register_rest_field(
			'user',
			self::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			[
				'get_callback'    => [ $this, 'rest_get_dev_tools_enabled' ],
				'update_callback' => [ $this, 'rest_update_dev_tools_enabled' ],
				'schema'          => [
					'description' => __( 'Whether AMP development tools are available to the user', 'amp' ),
					'type'        => 'boolean',
				],
			]
		);
	}

	/**
	 * Provides the user's dev tools enabled setting.
	 *
	 * @param array $user Array of user data prepared for REST.
	 * @return null|boolean Whether tools are enabled for the user, or null if the option has not been set.
	 */
	public function rest_get_dev_tools_enabled( $user ) {
		$meta = get_user_meta( $user['id'] );

		if ( is_array( $meta ) && array_key_exists( self::USER_FIELD_DEVELOPER_TOOLS_ENABLED, $meta ) ) {
			return boolval(
				is_array( $meta[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] ) && ! empty( $meta[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] )
					? reset( $meta[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] )
					: $meta[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ]
			);
		}

		// If the field is not yet set, don't make a default selection in the setup wizard.
		return null;
	}

	/**
	 * Updates a user's dev tools enabled setting.
	 *
	 * @param bool    $new_value New setting for whether dev tools are enabled for the user.
	 * @param WP_User $user      The WP user to update.
	 * @return int|bool|WP_Error The result of update_user_meta, or WP_Error if the current user lacks permission.
	 */
	public function rest_update_dev_tools_enabled( $new_value, WP_User $user ) {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return new WP_Error(
				'amp_rest_cannot_edit_user',
				__( 'Sorry, the current user is not allowed to make this change.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return update_user_meta( $user->ID, self::USER_FIELD_DEVELOPER_TOOLS_ENABLED, boolval( $new_value ) );
	}
}
