<?php

class Analytics_Options_Serializer {

	public function init() {
		add_action( 'admin_post_analytics_options', array( $this, 'save' ) );
	}
	
	public static function save() {
		$analytics_options = array(
			$_POST['id'],
			$_POST['vendor-type'],
			$_POST['config']
		);
		add_option('analytics', $analytics_options);
	}
}