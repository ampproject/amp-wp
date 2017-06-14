<?php

class AMP_Analytics_Options_Test extends WP_UnitTestCase {

	private $id_one = 'ga-1';
	private $id_two = 'ga-2';
	private $vendor = 'googleanalytics';

	private $config = '{
      "requests": {
        "event": "https://amp-publisher-samples-staging.herokuapp.com/amp-analytics/ping?user=amp-pV356ME7W4U6b_ILVWGDfCPCqFv2m4H7mPY0SYwKQPjBFQGoGYlsYcUikw1UiDVl&account=ampbyexample&event=${eventId}"
      },
      "triggers": {
        "trackPageview": {
          "on": "visible",
          "request": "event",
          "visibilitySpec": {
            "selector": "#cat-image-id",
            "visiblePercentageMin": 20,
            "totalTimeMin": 500,
            "continuousTimeMin": 200
          },
          "vars": {
            "eventId": "catview"
          }
        }
      }
	}';
	private $serializer;

	public function setUp() {
		$this->serializer = new Analytics_Options_Serializer();
	}

	public function tearDown() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
	}

	public function get_analytics_component_data() {
		return array(
			'one_component' => array( '', '<amp-analytics id="googleanalytics-ga-1" type="googleanalytics"><script type="application/json">{"requests":{"event":"https:\/\/amp-publisher-samples-staging.herokuapp.com\/amp-analytics\/ping?user=amp-pV356ME7W4U6b_ILVWGDfCPCqFv2m4H7mPY0SYwKQPjBFQGoGYlsYcUikw1UiDVl&account=ampbyexample&event=${eventId}"},"triggers":{"trackPageview":{"on":"visible","request":"event","visibilitySpec":{"selector":"#cat-image-id","visiblePercentageMin":20,"totalTimeMin":500,"continuousTimeMin":200},"vars":{"eventId":"catview"}}}}</script></amp-analytics>')
		);
	}

	private function insert_one_option($id, $vendor, $config) {
		global $_POST;
		$_POST['id'] = $id;
		$_POST['vendor-type'] = $vendor;
		$_POST['config'] = $config;
		$this->serializer->save();
	}

	private function get_options() {
		return get_option('analytics');
	}

	function test_no_options() {
		$options = $this->get_options();
		$this->assertFalse( $options );
	}

	function test_one_option_inserted() {
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config
		);
		$options = $this->get_options();

		$this->assertEquals( 1, count($options) );
	}

	function test_two_options_inserted() {
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config
		);
		$this->insert_one_option(
			$this->id_two,
			$this->vendor,
			$this->config
		);
		$options = $this->get_options();

		$this->assertEquals( 2, count($options) );
	}

	/**
	 * @dataProvider get_analytics_component_data
	 */
	public function test__analytics_component( $source, $expected ) {
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config
		);
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$analytics_components = $dom->getElementsByTagName('amp-analytics');

		$this->assertEquals( 1, count($analytics_components) );
	}
}
