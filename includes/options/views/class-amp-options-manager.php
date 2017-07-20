<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );

class AMP_Options_Manager {
	public static function get_options() {
		return get_option( 'amp-options' );
	}

	public static function get_option( $option, $default = false ) {
		$amp_options = self::get_options();

		if ( ! isset( $amp_options[ $option ] ) ) {
			return $default;
		}

		return $amp_options[ $option ];
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

		$option_name = 'amp-analytics';

		// Validate JSON configuration is valid
		$is_valid_json = AMP_HTML_Utils::is_valid_json( stripslashes( $data['config'] ) );

		// Check save/delete pre-conditions and proceed if correct
		if ( empty( $data['vendor-type'] ) || empty( $data['config'] ) || ! $is_valid_json ) {
			return false;
		}

		if ( empty( $data['id-value'] ) ) {
			$data['id-value'] = md5( $data['config'] );
		}

		// Prepare the data for the new analytics setting
		$new_analytics_option = array(
			sanitize_key( $data['id-value'] ),
			sanitize_key( $data['vendor-type'] ),
			stripslashes( $data['config'] ),
		);
		// Identifier for analytics option
		$inner_option_name = sanitize_key( $data['vendor-type'] . '-' . $data['id-value'] );

		// Grab the amp_options from the DB
		$amp_options = get_option( 'amp-options' );
		if ( ! $amp_options ) {
			$amp_options = array();
		}

		// Grab the amp-analytics options
		$amp_analytics = isset( $amp_options[ $option_name ] )
			? $amp_options[ $option_name ]
			: array();

		if ( isset( $data['delete'] ) ) {
			unset( $amp_analytics[ $inner_option_name ] );
		} else {
			$amp_analytics[ $inner_option_name ] = $new_analytics_option;
		}
		$amp_options[ $option_name ] = $amp_analytics;

		update_option( 'amp-options', $amp_options, false );

		return true;
	}
}
