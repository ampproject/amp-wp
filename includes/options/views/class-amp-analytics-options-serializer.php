<?php

class Analytics_Options_Serializer {

	public function init() {
		add_action( 'admin_post_analytics_options', array( $this, 'save' ) );
	}
	
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

		// TODO(@amedina): change this hardwired redirect to use a variable reference
		header('Location: http://localhost:8888/amp-wp-plugin-dev/wp-admin/admin.php?page=amp-analytics-options');
	}
}