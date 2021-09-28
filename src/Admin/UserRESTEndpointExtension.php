<?php
/**
 * Class UserRESTEndpointExtension
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_User;

/**
 * UserRESTEndpointExtension class.
 *
 * @since 2.2
 * @internal
 */
class UserRESTEndpointExtension implements Service, Registerable, Delayed {

	/**
	 * User meta key that stores a template mode for which the "Review" panel was dismissed.
	 *
	 * @var string
	 */
	const USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE = 'amp_review_panel_dismissed_for_template_mode';

	/**
	 * Get registration action.
	 *
	 * @return string
	 */
	public static function get_registration_action() {
		return 'rest_api_init';
	}

	/**
	 * Adds hooks.
	 */
	public function register() {
		$this->register_rest_field();
	}

	/**
	 * Register REST field for storing a template mode for which the "Review" panel was dismissed.
	 */
	public function register_rest_field() {
		register_rest_field(
			'user',
			self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE,
			[
				'get_callback'    => [ $this, 'get_review_panel_dismissed_for_template_mode' ],
				'update_callback' => [ $this, 'update_review_panel_dismissed_for_template_mode' ],
				'schema'          => [
					'description' => __( 'For which template mode the Review panel on the Settings screen was dismissed by a user', 'amp' ),
					'type'        => 'string',
					'enum'        => [
						null,
						AMP_Theme_Support::READER_MODE_SLUG,
						AMP_Theme_Support::STANDARD_MODE_SLUG,
						AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
					],
				],
			]
		);
	}

	/**
	 * Provides a template mode for which the "Review" panel has been dismissed by a user.
	 *
	 * @param array $user Array of user data prepared for REST.
	 *
	 * @return string|WP_Error Template mode fir which the panel is dismissed, empty string if the option has not been set, or WP_Error if the current user lacks permission.
	 */
	public function get_review_panel_dismissed_for_template_mode( $user ) {
		if ( wp_get_current_user()->ID !== $user['id'] ) {
			return new WP_Error(
				'amp_rest_cannot_get_other_user',
				__( 'Sorry, the current user is not allowed to get this data about other user.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return get_user_meta( $user['id'], self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, true );
	}

	/**
	 * Updates a user's setting determining for which template mode the "Review" panel was dismissed.
	 *
	 * @param string  $template_mode Template mode.
	 * @param WP_User $user          The WP user to update.
	 *
	 * @return bool|WP_Error The result of update_user_meta, or WP_Error if the current user lacks permission.
	 */
	public function update_review_panel_dismissed_for_template_mode( $template_mode, $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return new WP_Error(
				'amp_rest_cannot_edit_user',
				__( 'Sorry, the current user is not allowed to make this change.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( wp_get_current_user()->ID !== $user->ID ) {
			return new WP_Error(
				'amp_rest_cannot_edit_other_user',
				__( 'Sorry, the user is not allowed to make this change for other user.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( empty( $template_mode ) ) {
			return delete_user_meta( $user->ID, self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE );
		}

		return (bool) update_user_meta( $user->ID, self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, $template_mode );
	}
}