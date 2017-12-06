<?php
/**
 * AMP_Options_Manager class.
 *
 * @todo This file is located in the wrong directory.
 * @package AMP
 */

require_once AMP__DIR__ . '/includes/utils/class-amp-html-utils.php';

/**
 * Class AMP_Options_Manager
 */
class AMP_Options_Manager {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'amp-options';

	/**
	 * Register settings.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_NAME,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'validate_options' ),
			)
		);

		add_action( 'update_option_' . self::OPTION_NAME, 'flush_rewrite_rules' );
	}

	/**
	 * Get plugin options.
	 *
	 * @return array Options.
	 */
	public static function get_options() {
		return get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $option  Plugin option name.
	 * @param bool   $default Default value.
	 *
	 * @return mixed Option value.
	 */
	public static function get_option( $option, $default = false ) {
		$amp_options = self::get_options();

		if ( ! isset( $amp_options[ $option ] ) ) {
			return $default;
		}

		return $amp_options[ $option ];
	}

	/**
	 * Validate options.
	 *
	 * @param array $new_options Plugin options.
	 * @return array Options.
	 */
	public static function validate_options( $new_options ) {
		$defaults = array(
			'supported_post_types' => array(),
			'analytics'            => array(),
		);

		$options = array_merge(
			$defaults,
			self::get_options()
		);

		// Validate post type support.
		if ( isset( $new_options['supported_post_types'] ) ) {
			$options['supported_post_types'] = array();
			foreach ( $new_options['supported_post_types'] as $post_type => $enabled ) {
				$options['supported_post_types'][ $post_type ] = (bool) $enabled;
			}
		}

		// Validate analytics.
		if ( isset( $new_options['analytics'] ) ) {
			foreach ( $new_options['analytics'] as $id => $data ) {

				// Check save/delete pre-conditions and proceed if correct.
				if ( empty( $data['type'] ) || empty( $data['config'] ) ) {
					add_settings_error( self::OPTION_NAME, 'missing_analytics_vendor_or_config', __( 'Missing vendor type or config.', 'amp' ) );
					continue;
				}

				// Validate JSON configuration.
				$is_valid_json = AMP_HTML_Utils::is_valid_json( $data['config'] );
				if ( ! $is_valid_json ) {
					add_settings_error( self::OPTION_NAME, 'invalid_analytics_config_json', __( 'Invalid analytics config JSON.', 'amp' ) );
					continue;
				}

				$entry_vendor_type = sanitize_key( $data['type'] );
				$entry_config      = trim( $data['config'] );

				if ( ! empty( $data['id'] ) && '__new__' !== $data['id'] ) {
					$entry_id = sanitize_key( $data['id'] );
				} else {

					// Generate a hash string to uniquely identify this entry.
					$entry_id = substr( md5( $entry_vendor_type . $entry_config ), 0, 12 );

					// Avoid duplicates.
					if ( isset( $options['analytics'][ $entry_id ] ) ) {
						add_settings_error( self::OPTION_NAME, 'duplicate_analytics_entry', __( 'Duplicate analytics entry found.', 'amp' ) );
						continue;
					}
				}

				if ( isset( $data['delete'] ) ) {
					unset( $options['analytics'][ $entry_id ] );
				} else {
					$options['analytics'][ $entry_id ] = array(
						'type'   => $entry_vendor_type,
						'config' => $entry_config,
					);
				}
			}
		}

		return $options;
	}

	/**
	 * Update plugin option.
	 *
	 * @param string $option Plugin option name.
	 * @param mixed  $value  Plugin option value.
	 *
	 * @return bool Whether update succeeded.
	 */
	public static function update_option( $option, $value ) {
		$amp_options = self::get_options();

		$amp_options[ $option ] = $value;

		return update_option( self::OPTION_NAME, $amp_options, false );
	}

	/**
	 * Handle analytics submission.
	 */
	public static function handle_analytics_submit() {

		// Request must come from user with right capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you do not have the necessary permissions to perform this action', 'amp' ) );
		}

		// Ensure request is coming from analytics option form.
		check_admin_referer( 'analytics-options', 'analytics-options' );

		if ( isset( $_POST['amp-options']['analytics'] ) ) {
			self::update_option( 'analytics', wp_unslash( $_POST['amp-options']['analytics'] ) );

			$errors = get_settings_errors( self::OPTION_NAME );
			if ( empty( $errors ) ) {
				add_settings_error( self::OPTION_NAME, 'settings_updated', __( 'The analytics entry was successfully saved!', 'amp' ), 'updated' );
				$errors = get_settings_errors( self::OPTION_NAME );
			}
			set_transient( 'settings_errors', $errors );
		}

		// Redirect to keep the user in the analytics options page.
		// Wrap in is_admin() to enable phpunit tests to exercise this code.
		wp_safe_redirect( admin_url( 'admin.php?page=amp-analytics-options&settings-updated=1' ) );
		exit;
	}

	/**
	 * Update analytics options.
	 *
	 * @codeCoverageIgnore
	 * @deprecated
	 * @param array $data Unsanitized unslashed data.
	 * @return bool Whether options were updated.
	 */
	public static function update_analytics_options( $data ) {
		_deprecated_function( __METHOD__, '0.6.0', __CLASS__ . '::update_option' );
		return self::update_option( 'analytics', wp_unslash( $data ) );
	}
}
