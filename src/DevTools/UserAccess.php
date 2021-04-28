<?php
/**
 * Class UserAccess.
 *
 * @since 2.0
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\DevTools;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validation_Manager;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use WP_Error;
use WP_User;

/**
 * Class UserAccess
 *
 * @since 2.0
 * @internal
 */
final class UserAccess implements Service, Registerable {

	/**
	 * User meta key enabling or disabling developer tools.
	 *
	 * @var string
	 */
	const USER_FIELD_DEVELOPER_TOOLS_ENABLED = 'amp_dev_tools_enabled';

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_rest_field' ] );
		add_action( 'personal_options', [ $this, 'print_personal_options' ] );
		add_action( 'personal_options_update', [ $this, 'update_user_setting' ] );
		add_action( 'edit_user_profile_update', [ $this, 'update_user_setting' ] );
	}

	/**
	 * Determine whether developer tools are enabled for the a user and whether they can access them.
	 *
	 * @param null|WP_User|int $user User. Defaults to the current user.
	 * @return bool Whether developer tools are enabled for the user.
	 */
	public function is_user_enabled( $user = null ) {
		if ( null === $user ) {
			$user = wp_get_current_user();
		} elseif ( ! $user instanceof WP_User ) {
			$user = new WP_User( $user );
		}

		if ( ! AMP_Validation_Manager::has_cap( $user ) ) {
			return false;
		}

		return $this->get_user_enabled( $user );
	}

	/**
	 * Get user enabled (regardless of whether they have the required capability).
	 *
	 * @param int|WP_User $user User.
	 * @return bool Whether dev tools is enabled.
	 */
	public function get_user_enabled( $user ) {
		if ( ! $user instanceof WP_User ) {
			$user = new WP_User( $user );
		}
		$enabled = $user->get( self::USER_FIELD_DEVELOPER_TOOLS_ENABLED );
		if ( '' === $enabled ) {
			// Disable Developer Tools by default when in Reader mode.
			$enabled = AMP_Theme_Support::READER_MODE_SLUG !== AMP_Options_Manager::get_option( Option::THEME_SUPPORT );

			/**
			 * Filters whether Developer Tools is enabled by default for a user.
			 *
			 * When Reader mode is active, Developer Tools is currently disabled by default.
			 *
			 * @since 2.0.1
			 *
			 * @param bool $enabled DevTools enabled.
			 * @param int  $user_id User ID.
			 */
			$enabled = (bool) apply_filters( 'amp_dev_tools_user_default_enabled', $enabled, $user->ID );
		}
		return rest_sanitize_boolean( $enabled );
	}

	/**
	 * Set user enabled.
	 *
	 * @param int|WP_User $user    User.
	 * @param bool        $enabled Whether enabled.
	 * @return bool Whether update was successful.
	 */
	public function set_user_enabled( $user, $enabled ) {
		if ( $user instanceof WP_User ) {
			$user = $user->ID;
		}
		return (bool) update_user_meta( (int) $user, self::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( (bool) $enabled ) );
	}

	/**
	 * Register REST field.
	 */
	public function register_rest_field() {
		register_rest_field(
			'user',
			self::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			[
				'get_callback'    => [ $this, 'rest_get_dev_tools_enabled' ],
				'update_callback' => [ $this, 'rest_update_dev_tools_enabled' ],
				'schema'          => [
					'description' => __( 'Whether the user has enabled dev tools.', 'amp' ),
					'type'        => 'boolean',
				],
			]
		);
	}

	/**
	 * Add the developer tools checkbox to the user edit screen.
	 *
	 * @param WP_User $profile_user Current user being edited.
	 */
	public function print_personal_options( $profile_user ) {
		if ( ! current_user_can( 'edit_user', $profile_user->ID ) || ! AMP_Validation_Manager::has_cap( $profile_user ) ) {
			return;
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'AMP Developer Tools', 'amp' ); ?></th>
			<td>
				<label for="amp_dev_tools_enabled">
					<input name="<?php echo esc_attr( self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ); ?>" type="checkbox" id="amp_dev_tools_enabled" value="true" <?php checked( $this->is_user_enabled( $profile_user ) ); ?> />
					<?php esc_html_e( 'Enable AMP developer tools to surface validation errors when editing posts and viewing the site.', 'amp' ); ?>
				</label>

				<p class="description"><?php esc_html_e( 'This presumes you have some experience coding with HTML, CSS, JS, and PHP.', 'amp' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Update the user setting from the edit user screen).
	 *
	 * @param int $user_id User being edited.
	 * @return bool Whether update was successful.
	 */
	public function update_user_setting( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! AMP_Validation_Manager::has_cap( $user_id ) ) {
			return false;
		}
		$enabled = isset( $_POST[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] ) && rest_sanitize_boolean( wp_unslash( $_POST[ self::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce handled by user-edit.php; sanitization used is sanitized.
		return $this->set_user_enabled( $user_id, $enabled );
	}

	/**
	 * Provides the user's dev tools enabled setting.
	 *
	 * @param array $user Array of user data prepared for REST.
	 * @return null|boolean Whether tools are enabled for the user, or null if the option has not been set.
	 */
	public function rest_get_dev_tools_enabled( $user ) {
		return $this->is_user_enabled( $user['id'] );
	}

	/**
	 * Updates a user's dev tools enabled setting.
	 *
	 * @param bool    $new_value New setting for whether dev tools are enabled for the user.
	 * @param WP_User $user      The WP user to update.
	 * @return bool|WP_Error The result of update_user_meta, or WP_Error if the current user lacks permission.
	 */
	public function rest_update_dev_tools_enabled( $new_value, WP_User $user ) {
		if ( ! AMP_Validation_Manager::has_cap( $user ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return new WP_Error(
				'amp_rest_cannot_edit_user',
				__( 'Sorry, the current user is not allowed to make this change.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return $this->set_user_enabled( $user->ID, $new_value );
	}
}
