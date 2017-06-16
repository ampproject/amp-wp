<?php

class AMP_Analytics_Options_Test extends WP_UnitTestCase {

	private $id_one = 'ga-1';
	private $id_two = 'ga-2';
	private $vendor = 'googleanalytics';

	private $config_one = '{
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

	private $config_two = '{
		"requests": {
			"event": "https://amp-publisher-samples-staging.herokuapp.com/amp-analytics/ping?user=amp-pV356ME7W4U6b_ILVWGDfCPCqFv2m4H7mPY0SYwKQPjBFQGoGYlsYcUikw1UiDVl&account=ampbyexample&event=${eventId}"
		},
		"triggers": {
			"trackAnchorClicks": {
				"on": "click",
		          "selector": "a",
		          "request": "event",
		          "vars": {
					"eventId": "clickOnAnyAnchor"
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

	/**
	 * Test that nothing is added if no analytics option defined in the DB
	 */
	function test_no_options() {
		$options = $this->get_options();
		$this->assertFalse( $options );
	}

	/**
	 * Test that exactly one analytics component is inserted into the DB
	 */
	function test_one_option_inserted() {
		/* Delete analytics options, if any */
		delete_option( 'analytics' );

		/* Insert analytics option */
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config
		);
		$options = $this->get_options();

		$this->assertEquals( 1, count($options) );
	}

	/**
	 * Test that two analytics components are inserted into the DB
	 */
	function test_two_options_inserted() {
		/* Delete analytics options, if any */
		delete_option( 'analytics' );

		/* Insert analytics option */
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config_one
		);
		$this->insert_one_option(
			$this->id_two,
			$this->vendor,
			$this->config_two
		);
		$options = $this->get_options();

		$this->assertEquals( 2, count($options) );
	}

	/**
	 * Test that the analytics JS is added to the page
	 */
	function test_analytics_js_added() {
		/* Delete analytics options, if any */
		delete_option( 'analytics' );

		/* Insert analytics option */
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config_one
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		$libxml_previous_state = libxml_use_internal_errors( true );

		// Create a new DOM document
		$dom = new DOMDocument;
		// Load the rendered page into it
		$dom->loadHTML( $amp_rendered );

		$head = $dom->getElementsByTagName( 'head' )[0];
		$scripts = $head->getElementsByTagName( 'script');
		$analytics_js_found = false;
		foreach ( $scripts as $script) {
			if ($script->getAttribute( 'custom-element') == "amp-analytics" ) {
				$analytics_js_found = true;
				break;
			}
		}
		$this->AssertTrue($analytics_js_found);

	}

	/**
	 * Test that exactly one analytics component are added to the page
	 */
	function test_one_analytics_component_added() {
		/* Delete analytics options, if any */
		delete_option( 'analytics' );

		/* Insert analytics option */
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config_one
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		$dom->loadHTML( $amp_rendered );

		$components = $dom->getElementsByTagName( 'amp-analytics' );
		$this->assertEquals(1, $components->length );
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}

	/**
	 * Test that two analytics components are added to the page
	 */
	function test_two_analytics_components_added() {
		$this->insert_one_option(
			$this->id_one,
			$this->vendor,
			$this->config_one
		);

		$this->insert_one_option(
			$this->id_two,
			$this->vendor,
			$this->config_two
		);

		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		$dom->loadHTML( $amp_rendered );

		$components = $dom->getElementsByTagName( 'amp-analytics' );
		$this->assertEquals(2, $components->length );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}
}
