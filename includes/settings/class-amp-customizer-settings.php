<?php
/**
 * Class AMP_Customizer_Settings
 *
 * @package AMP
 */

/**
 * Class AMP_Customizer_Settings
 *
 * @internal
 */
class AMP_Customizer_Settings {

	/**
	 * Gets the AMP Customizer settings directly from the option.
	 *
	 * @since 0.6
	 *
	 * @return array Associative array of $setting => $value pairs.
	 */
	private static function get_stored_options() {
		return get_option( 'amp_customizer', [] );
	}

	/**
	 * Gets the AMP Customizer settings.
	 *
	 * @since 0.6
	 *
	 * @return array Associative array of $setting => $value pairs.
	 */
	public static function get_settings() {
		$settings = self::get_stored_options();

		/**
		 * Filters the AMP Customizer settings.
		 *
		 * @since 0.6
		 *
		 * @param array $settings Associative array of $setting => $value pairs.
		 */
		return apply_filters( 'amp_customizer_get_settings', $settings );
	}
}
