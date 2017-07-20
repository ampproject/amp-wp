<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );

class AMP_Options_Manager {
	const OPTION_NAME = 'amp-options';

	public static function get_options() {
		return get_option( self::OPTION_NAME, array() );
	}

	public static function get_option( $option, $default = false ) {
		$amp_options = self::get_options();

		if ( ! isset( $amp_options[ $option ] ) ) {
			return $default;
		}

		return $amp_options[ $option ];
	}

	public static function update_option( $option, $value ) {
		$amp_options = self::get_options();

		$amp_options[ $option ] = $value;

		return update_option( self::OPTION_NAME, $amp_options, false );
	}

	public static function handle_analytics_submit() {
		// Request must come from user with right capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Sorry, you do not have the necessary permissions to perform this action', 'amp' ) );
		}
		// Ensure request is coming from analytics option form
		check_admin_referer( 'analytics-options', 'analytics-options' );

		$status = AMP_Options_Manager::update_analytics_options( $_POST );

		// Redirect to keep the user in the analytics options page
		// Wrap in is_admin() to enable phpunit tests to exercise this code
		wp_safe_redirect( admin_url( 'admin.php?page=amp-analytics-options&valid=' . $status ) );
		exit;
	}

	public static function update_analytics_options( $data ) {
		// Check save/delete pre-conditions and proceed if correct
		if ( empty( $data['vendor-type'] ) || empty( $data['config'] ) ) {
			return false;
		}

		// Validate JSON configuration
		$is_valid_json = AMP_HTML_Utils::is_valid_json( stripslashes( $data['config'] ) );
		if ( ! $is_valid_json ) {
			return false;
		}
		$amp_analytics = self::get_option( 'analytics', array() );

		$entry_vendor_type = sanitize_key( $data['vendor-type'] );
		$entry_config = stripslashes( trim( $data['config'] ) );

		if ( ! empty( $data['id-value'] ) ) {
			$entry_id = sanitize_key( $data['id-value'] );
		} else {
			// Generate a hash string to uniquely identify this entry
			$entry_id = substr( md5( $entry_vendor_type . $entry_config ), 0, 12 );
			// Avoid duplicates
			if ( isset( $amp_analytics[ $entry_id ] ) ) {
				return false;
			}
		}

		if ( isset( $data['delete'] ) ) {
			unset( $amp_analytics[ $entry_id ] );
		} else {
			$amp_analytics[ $entry_id ] = array(
				'type' => $entry_vendor_type,
				'config' => $entry_config,
			);
		}

		return self::update_option( 'analytics', $amp_analytics );
	}
}
