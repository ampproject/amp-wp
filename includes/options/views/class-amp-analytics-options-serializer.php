<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );

class Analytics_Options_Serializer {

	public static function save() {

		if ( is_admin() ) {
			// Request must come from user with right capabilities
			if ( ! current_user_can( 'administrator' ) ) {
				wp_die( 'Sorry, you do not have the necessary permissions to perform this action' );
			}
			// Ensure request is coming from analytics option form
			check_admin_referer( 'analytics-options', 'analytics-options' );
		}

		$option_name = 'amp-analytics';

		// Check save/delete pre-conditions and proceed if correct
		if ( ! ( empty( $_POST['vendor-type'] ) || empty( $_POST['config'] ) ) &&
				AMP_HTML_Utils::valid_json( stripslashes($_POST['config'] ) ) ) {

			if ( empty( $_POST['id-value'] ) ) {
				$_POST['id-value'] = md5( $_POST['config'] );
			}

			// Prepare the data for the new analytics setting
			$new_analytics_option = array(
				$_POST['id-value'],
				sanitize_key($_POST['vendor-type']),
				stripslashes( $_POST['config'] )
			);
			// Identifier for analytics option
			$inner_option_name = sanitize_key($_POST['vendor-type'] . '-' . $_POST['id-value'] );

			// Grab the amp_options from the DB
			$amp_options = get_option( 'amp-options' );
			if ( ! $amp_options ) {
				$amp_options = array();
			}

			// Grab the amp-analytics options
			$amp_analytics = isset($amp_options[ $option_name ])
				? $amp_options[ $option_name ]
				: array();

			if ( isset( $_POST['delete'] ) ) {
				unset( $amp_analytics[ $inner_option_name ] );
			} else {
				$amp_analytics[ $inner_option_name ] = $new_analytics_option;
			}
			$amp_options[ $option_name ] = $amp_analytics;
			update_option( 'amp-options' , $amp_options, false );
		}
		// [Redirect] Keep the user in the analytics options page
		// Wrap with is_admin() to enable phpunit tests to exercise this code
		if ( is_admin() ) {
			wp_redirect( admin_url( 'admin.php?page=amp-analytics-options' ) );
		}
	}
}