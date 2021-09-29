<?php
/**
 * Class UserRESTEndpointExtension
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_User;

/**
 * Service which registers additional REST API fields for the user endpoint.
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
	 * Register.
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
					'description' => __( 'The template mode for which the Review panel on the Settings screen was dismissed by a user.', 'amp' ),
					'type'        => 'string',
					'enum'        => [
						'',
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
	 * @return string Template mode for which the panel is dismissed, empty string if the option has not been set.
	 */
	public function get_review_panel_dismissed_for_template_mode( $user ) {
		return get_user_meta( $user['id'], self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, true );
	}

	/**
	 * Updates a user's setting determining for which template mode the "Review" panel was dismissed.
	 *
	 * @param string  $template_mode Template mode or empty string.
	 * @param WP_User $user          The WP user to update.
	 *
	 * @return bool The result of updating or deleting the user meta.
	 */
	public function update_review_panel_dismissed_for_template_mode( $template_mode, WP_User $user ) {
		if ( empty( $template_mode ) ) {
			return delete_user_meta( $user->ID, self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE );
		}

		return (bool) update_user_meta( $user->ID, self::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE, $template_mode );
	}
}
