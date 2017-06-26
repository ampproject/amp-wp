<?php

class AMP_Analytics_Options_Test extends WP_UnitTestCase {

	private $vendor = 'googleanalytics';

	private $config_one = '{
      "requests": {
        "event": "https://example.com/..."
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
			"event": "https://example.com/..."
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

	private function get_options() {
		$analytics_options = false;
		$amp_options = get_option('amp-options');
		if ( $amp_options ) {
			$analytics_options = $amp_options[ 'amp-analytics'];
		}

		return $analytics_options;
	}

	private function render_post() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		return $amp_rendered;
	}

	private function insert_one_option($vendor, $config) {
		global $_POST;
		$_POST['id-value'] = '';
		$_POST['vendor-type'] = $vendor;
		$_POST['config'] = $config;
		$this->serializer->save();
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

		// Delete options, if any
		delete_option( 'amp-options' );

		/* Insert analytics option */
		$this->insert_one_option(
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

		// Delete options, if any
		delete_option( 'amp-options' );

		/* Insert analytics option one */
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		/* Insert analytics option two */
		$this->insert_one_option(
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
			$this->vendor,
			$this->config_one
		);

		$amp_rendered = $this->render_post();

		$libxml_previous_state = libxml_use_internal_errors( true );

		// Create a new DOM document
		$dom = new DOMDocument;
		// Load the rendered page into it
		$dom->loadHTML( $amp_rendered );

		$head = $dom->getElementsByTagName( 'head' )->item(0);
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

		// Delete options, if any
		delete_option( 'amp-options' );

		/* Insert analytics option */
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		// Render AMP post
		$amp_rendered = $this->render_post();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		$dom->loadHTML( $amp_rendered );

		$components = $dom->getElementsByTagName( 'amp-analytics' );

		// One amp-analytics component should be in the page
		$this->assertEquals(1, $components->length );
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}

	/**
	 * Test that two analytics components are added to the page
	 */
	function test_two_analytics_components_added() {

		// Delete options, if any
		delete_option( 'amp-options' );

		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$this->insert_one_option(
			$this->vendor,
			$this->config_two
		);

		$amp_rendered = $this->render_post();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		$dom->loadHTML( $amp_rendered );
		$components = $dom->getElementsByTagName( 'amp-analytics' );
		// Two amp-analytics components should be in the page
		$this->assertEquals(2, $components->length );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}
}
