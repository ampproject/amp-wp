<?php

class Analytics_Options_Serializer {

	public static function save() {

		$option_name = 'analytics';

		if ( empty( $_POST['id'] ) ||
		     empty( $_POST['vendor-type'] ) ||
		     empty( $_POST['config'] ) ) {
			return;
		}

		$new_analytics_options = array(
			$_POST['id'],
			$_POST['vendor-type'],
			stripslashes($_POST['config'])
		);

		$inner_option_name = $_POST['vendor-type'] . '-' . $_POST['id'];
		$analytics_options = get_option($option_name);
		if ( ! $analytics_options ) {
			$analytics_options = array();
		}
		if ( isset($_POST['delete']) ) {
			unset($analytics_options[$inner_option_name]);
		} else {
			$analytics_options[$inner_option_name] = $new_analytics_options;
		}
		update_option( $option_name, $analytics_options, false);
		// [Redirect] Keep the user in the analytics options page
		header('Location: ' . admin_url('admin.php?page=amp-analytics-options'));
	}
}