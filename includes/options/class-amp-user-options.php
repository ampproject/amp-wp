<?php
/**
 * Class AMP_User_Options.
 *
 * @package AMP
 */

/**
 * Class AMP_User_Options
 */
class AMP_User_Options {

	/**
	 * Key for a user meta field storing preferences related to the plugin.
	 *
	 * @var string
	 */
	const USER_OPTIONS_KEY = 'amp_user_options';

	/**
	 * Key for the user option (stored in amp_features user meta) enabling or disabling developer tools.
	 *
	 * @var string
	 */
	const OPTION_DEVELOPER_TOOLS = 'developer_tools';

	/**
	 * Sets up hooks.
	 */
	public static function init() {
		add_filter( 'amp_setup_wizard_data', [ __CLASS__, 'inject_setup_wizard_data' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'register_user_meta' ] );
	}

	/**
	 * Registers user meta related to validation management.
	 *
	 * @since 1.6.0
	 */
	public static function register_user_meta() {
		register_meta(
			'user',
			self::USER_OPTIONS_KEY,
			[
				'show_in_rest' => [
					'schema' => [
						'default'    => [
							self::OPTION_DEVELOPER_TOOLS => 'unset',
						],
						'type'       => 'object',
						'properties' => [
							self::OPTION_DEVELOPER_TOOLS => [
								'type' => 'string',
								'enum' => [
									'disabled',
									'enabled',
									'unset',
								],
							],
						],
					],
				],
				'single'       => true,
				'type'         => 'object',
			]
		);
	}

	/**
	 * Add fields relevant to user options to the data passed to the setup wizard app.
	 *
	 * @param array $data Associative array of data provided to the app.
	 * @return array Filtered array.
	 */
	public static function inject_setup_wizard_data( $data ) {
		$data['USER_OPTIONS_KEY']            = self::USER_OPTIONS_KEY;
		$data['USER_OPTION_DEVELOPER_TOOLS'] = self::OPTION_DEVELOPER_TOOLS;
		$data['USER_REST_ENDPOINT']          = rest_url( 'wp/v2/users/me' );

		return $data;
	}
}
