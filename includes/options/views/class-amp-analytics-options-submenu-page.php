<?php

require_once( AMP__DIR__ . '/includes/options/views/class-amp-analytics-options-serializer.php' );


class AMP_Analytics_Options_Submenu_Page {

	private $serializer;

	public function __construct() {
		$this->serializer = new Analytics_Options_Serializer();
		$this->serializer->init();
	}

	public static function render() {
		include_once( AMP__DIR__ . '/includes/options/views/analytics-options.php');
	}
}