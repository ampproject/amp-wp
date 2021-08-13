<?php

use AmpProject\AmpWP\Option;
use AmpProject\Dom\Document;
use AmpProject\AmpWP\Tests\TestCase;

class AMP_Analytics_Options_Test extends TestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		AMP_Options_Manager::register_settings();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
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
		return AMP_Options_Manager::get_option( Option::ANALYTICS, [] );
	}

	private function render_post() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create(
			[
				'post_author' => $user_id,
			]
		);

		return get_echo( 'amp_render_post', [ $post_id ] );
	}

	/**
	 * Insert one analytics entry.
	 *
	 * @param string $type   Entry type (vendor).
	 * @param string $config Entry config (JSON).
	 */
	private function insert_one_option( $type, $config ) {
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'abcdefghijkl' => compact( 'type', 'config' ),
			]
		);
	}

	/**
	 * Inserts two analytics entries.
	 *
	 * @param string $type   Entry type (vendor).
	 * @param string $config Entry config (JSON).
	 */
	private function insert_two_options( $type, $config ) {
		AMP_Options_Manager::update_option(
			Option::ANALYTICS,
			[
				'abcdefghijkl' => compact( 'type', 'config' ),
				'mnopqrstuvwx' => [
					'type'   => $type,
					'config' => '{"good": "good"}',
				],
			]
		);
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

		$this->assertCount( 1, $options );
	}

	/**
	 * Test that two analytics components are inserted into the DB
	 */
	public function test_two_options_inserted() {

		$this->insert_two_options(
			$this->vendor,
			$this->config_one
		);

		$options = $this->get_options();

		$this->assertCount( 2, $options );
	}

	/**
	 * Test that exactly one analytics component is added to the page.
	 *
	 * @covers ::amp_print_analytics()
	 */
	public function test_one_analytics_component_added() {

		/* Insert analytics option */
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		ob_start();
		amp_print_analytics( [] );
		$amp_rendered = ob_get_clean();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new Document();
		$dom->loadHTML( $amp_rendered );

		$components = $dom->getElementsByTagName( 'amp-analytics' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		// One amp-analytics component should be in the page
		$this->assertEquals( 1, $components->length );
	}

	/**
	 * Test that two analytics components are added to the page.
	 *
	 * @covers ::amp_print_analytics()
	 */
	public function test_two_analytics_components_added() {

		$this->insert_two_options(
			$this->vendor,
			$this->config_one
		);

		ob_start();
		amp_print_analytics( [] );
		$amp_rendered = ob_get_clean();

		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new Document();
		$dom->loadHTML( $amp_rendered );
		$components = $dom->getElementsByTagName( 'amp-analytics' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		// Two amp-analytics components should be in the page
		$this->assertEquals( 2, $components->length );
	}

	/**
	 * Test amp_get_analytics()
	 *
	 * @covers ::amp_get_analytics()
	 */
	public function test_amp_get_analytics() {
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$analytics = amp_get_analytics();
		$this->assertCount( 1, $analytics );

		$key = key( $analytics );
		$this->assertArrayHasKey( 'type', $analytics[ $key ] );
		$this->assertEquals( 'googleanalytics', $analytics[ $key ]['type'] );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		add_filter(
			'amp_analytics_entries',
			static function( $analytics ) use ( $key ) {
				$analytics[ $key ]['type']                                = 'test';
				$analytics[ $key ]['attributes']['data-include']          = '_till_responded';
				$analytics[ $key ]['attributes']['data-block-on-consent'] = 'credentials';
				return $analytics;
			}
		);
		$analytics = amp_get_analytics();
		$this->assertEquals( 'test', $analytics[ $key ]['type'] );
		$this->assertEquals( '_till_responded', $analytics[ $key ]['attributes']['data-include'] );
		$this->assertEquals( 'credentials', $analytics[ $key ]['attributes']['data-block-on-consent'] );
	}

	/**
	 * Test amp_print_analytics()
	 *
	 * @covers ::amp_print_analytics()
	 */
	public function test_amp_print_analytics() {
		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);

		$analytics = amp_get_analytics();

		$key = key( $analytics );

		$trigger_count = 0;

		$entries_test = function ( $entries ) use ( $analytics, &$trigger_count ) {
			$this->assertEquals( $analytics, $entries );
			$trigger_count++;
		};

		add_action(
			'amp_print_analytics',
			$entries_test
		);

		$output = get_echo( 'amp_print_analytics', [ $analytics ] );

		$this->assertEquals( 1, $trigger_count );

		$this->assertStringStartsWith( '<amp-analytics', $output );
		$this->assertStringContainsString( 'type="googleanalytics"><script type="application/json">{"requests":{"event":', $output );

		remove_action(
			'amp_print_analytics',
			$entries_test
		);

		add_filter(
			'amp_analytics_entries',
			static function( $analytics ) use ( $key ) {
				$analytics[ $key ]['attributes']['data-include'] = '_till_responded';
				return $analytics;
			}
		);

		$analytics = amp_get_analytics();

		$output = get_echo( 'amp_print_analytics', [ $analytics ] );

		$this->assertStringContainsString( 'data-include="_till_responded"', $output );
	}

	/**
	 * Test amp_print_analytics() when empty, called via wp_footer.
	 *
	 * Note that wp_footer action passes empty string to any handlers.
	 * This test asserts that an issue discovered in PHP 7.1 is fixed.
	 *
	 * @see AMP_Theme_Support::add_hooks() Where add_action( 'wp_footer', 'amp_print_analytics' ) is done.
	 * @covers ::amp_print_analytics()
	 */
	public function test_amp_print_analytics_when_empty() {
		$output = get_echo( 'amp_print_analytics', [ '' ] );
		$this->assertEmpty( $output );

		$this->insert_one_option(
			$this->vendor,
			$this->config_one
		);
		$output = get_echo( 'amp_print_analytics', [ '' ] );
		$this->assertStringStartsWith( '<amp-analytics', $output );
		$this->assertStringContainsString( 'type="googleanalytics"><script type="application/json">{"requests":{"event":', $output );
	}
}
