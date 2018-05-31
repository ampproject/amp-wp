<?php

class AMP_Analytics_Options_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		AMP_Options_Manager::register_settings();
	}

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

	private function get_options() {
		return AMP_Options_Manager::get_option( 'analytics', array() );
	}

	private function render_post() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array(
			'post_author' => $user_id,
		) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		return $amp_rendered;
	}

	/**
	 * Insert one analytics entry.
	 *
	 * @param string $type   Entry type (vendor).
	 * @param string $config Entry config (JSON).
	 */
	private function insert_one_option( $type, $config ) {
		AMP_Options_Manager::update_option( 'analytics', array(
			'__new__' => compact( 'type', 'config' ),
		) );
	}

	/**
	 * Test that nothing is added if no analytics option defined in the DB
	 */
	public function test_no_options() {
		$options = $this->get_options();
		$this->assertEmpty( $options );
	}

	/**
	 * Test that exactly one analytics component is inserted into the DB
	 */
	public function test_one_option_inserted() {
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);
		$options = $this->get_options();

		$this->assertEquals( 1, count( $options ) );
	}

	/**
	 * Test that two analytics components are inserted into the DB
	 */
	public function test_two_options_inserted() {

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

		$this->assertEquals( 2, count( $options ) );
	}

	/**
	 * Test that the analytics JS is added to the page
	 */
	public function test_analytics_js_added() {

		/* Insert analytics option */
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$amp_rendered = $this->render_post();

		$libxml_previous_state = libxml_use_internal_errors( true );

		// Create a new DOM document
		$dom = new DOMDocument();
		// Load the rendered page into it
		$dom->loadHTML( $amp_rendered );

		$head = $dom->getElementsByTagName( 'head' )->item( 0 );

		$scripts = $head->getElementsByTagName( 'script' );
		$analytics_js_found = false;
		foreach ( $scripts as $script ) {
			if ( 'amp-analytics' === $script->getAttribute( 'custom-element' ) ) {
				$analytics_js_found = true;
				break;
			}
		}
		$this->AssertTrue( $analytics_js_found );

	}

	/**
	 * Test that exactly one analytics component are added to the page
	 */
	public function test_one_analytics_component_added() {

		/* Insert analytics option */
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		// Render AMP post
		$amp_rendered = $this->render_post();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadHTML( $amp_rendered );

		$components = $dom->getElementsByTagName( 'amp-analytics' );

		// One amp-analytics component should be in the page
		$this->assertEquals( 1, $components->length );
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}

	/**
	 * Test that two analytics components are added to the page
	 */
	public function test_two_analytics_components_added() {

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

		$dom = new DOMDocument();
		$dom->loadHTML( $amp_rendered );
		$components = $dom->getElementsByTagName( 'amp-analytics' );
		// Two amp-analytics components should be in the page
		$this->assertEquals( 2, $components->length );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

	}

	/**
	 * Test amp_get_analytics()
	 *
	 * @covers \amp_get_analytics()
	 */
	public function test_amp_get_analytics() {
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$analytics = amp_get_analytics();
		$this->assertEquals( 1, count( $analytics ) );

		$key = key( $analytics );
		$this->assertArrayHasKey( 'type', $analytics[ $key ] );
		$this->assertEquals( 'googleanalytics', $analytics[ $key ]['type'] );

		add_theme_support( 'amp' );
		add_filter( 'amp_analytics_entries', function( $analytics ) use ( $key ) {
			$analytics[ $key ]['type'] = 'test';
			return $analytics;
		} );
		$analytics = amp_get_analytics();
		$this->assertEquals( 'test', $analytics[ $key ]['type'] );
	}

	/**
	 * Test amp_print_analytics()
	 *
	 * @covers \amp_print_analytics()
	 */
	public function test_amp_print_analytics() {
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$analytics = amp_get_analytics();

		ob_start();
		amp_print_analytics( $analytics );
		$output = ob_get_clean();

		$this->assertStringStartsWith( '<amp-analytics', $output );
		$this->assertContains( 'type="googleanalytics"><script type="application/json">{"requests":{"event":', $output );
	}

	/**
	 * Test amp_print_analytics() when empty, called via wp_footer.
	 *
	 * Note that wp_footer action passes empty string to any handlers.
	 * This test asserts that an issue discovered in PHP 7.1 is fixed.
	 *
	 * @see AMP_Theme_Support::add_hooks() Where add_action( 'wp_footer', 'amp_print_analytics' ) is done.
	 * @covers \amp_print_analytics()
	 */
	public function test_amp_print_analytics_when_empty() {

		ob_start();
		amp_print_analytics( '' );
		$this->assertEmpty( ob_get_clean() );

		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);
		ob_start();
		amp_print_analytics( '' );
		$output = ob_get_clean();
		$this->assertStringStartsWith( '<amp-analytics', $output );
		$this->assertContains( 'type="googleanalytics"><script type="application/json">{"requests":{"event":', $output );
	}

}
