<?php

class Analytics_Options_Serializer {

	private static function valid_json( $data ) {
		if (!empty($data)) {
			@json_decode($data);
			return (json_last_error() === JSON_ERROR_NONE);
		}
		return false;
	}

	public static function save() {

		$option_name = 'amp-analytics';

		if ( ! ( empty( $_POST['vendor-type'] ) || empty( $_POST['config'] ) ) &&
				Analytics_Options_Serializer::valid_json( stripslashes($_POST['config'] ) ) ) {

			if ( empty( $_POST['id-value'] ) ) {
				$_POST['id-value'] = md5( $_POST['config'] );
			}

			// Prepare the data for the new analytics setting
			$new_analytics_option = array(
				$_POST['id-value'],
				$_POST['vendor-type'],
				stripslashes( $_POST['config'] )
			);
			// Identifier for analytics option
			$inner_option_name = $_POST['vendor-type'] . '-' . $_POST['id-value'];

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