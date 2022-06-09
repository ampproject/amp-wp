<?php
/**
 * Class AMP_PWA_Script_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;
use AmpProject\Html\Tag;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the PWA Plugin Sanitizer class.
 *
 * @coversDefaultClass AMP_PWA_Script_Sanitizer
 */
class AMP_PWA_Script_Sanitizer_Test extends TestCase {

	/**
	 * XPath query to find the scripts element.
	 *
	 * @var string
	 */
	const SCRIPT_XPATH = '//script[ @id = "wp-navigation-request-properties" or ( @type = "module" and contains( text(), "checkNetworkAndReload()" ) ) ]';

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();
		if (
			! ( function_exists( 'is_offline' ) && is_offline() ) &&
			! ( function_exists( 'is_500' ) && is_500() )
		) {
			$this->markTestSkipped( 'PWA plugin not active.' );
		}
	}

	/**
	 * Get offline or 500 page markup.
	 *
	 * @return string
	 */
	public function get_offline_or_500_page_markup() {
		return '<html><head></head><body><script id="wp-navigation-request-properties" type="application/json">{{{WP_NAVIGATION_REQUEST_PROPERTIES}}}</script><script type="module">const shouldRetry=()=>{if (new URLSearchParams(location.search.substring(1)).has(\'wp_error_template\')){return false;}const navigationRequestProperties=JSON.parse(document.getElementById(\'wp-navigation-request-properties\').text);if (\'GET\' !==navigationRequestProperties.method){return false;}return true;};if (shouldRetry()){/** * Listen to changes in the network state, reload when online. * This handles the case when the device is completely offline. */window.addEventListener(\'online\', ()=>{window.location.reload();});// Create a counter to implement exponential backoff.let count=0;/** * Check if the server is responding and reload the page if it is. * This handles the case when the device is online, but the server is offline or misbehaving. */async function checkNetworkAndReload(){try{const response=await fetch(location.href,{method: \'HEAD\',});// Verify we get a valid response from the serverif (response.status >=200 && response.status < 500){window.location.reload();return;}}catch{// Unable to connect so do nothing.}window.setTimeout(checkNetworkAndReload, Math.pow(2, count++) * 2500);}checkNetworkAndReload();}</script></body></html>';
	}

	/**
	 * Do not PX verify script tag if page is not being offline or 500.
	 *
	 * @covers ::sanitize
	 */
	public function test_do_not_px_verify_script_tag() {
		$dom = new Document();
		$dom->loadHTML( $this->get_offline_or_500_page_markup() );
		$sanitizer = new AMP_PWA_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertFalse( is_offline() );
		$this->assertFalse( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * PX verify if page is being offline
	 *
	 * @covers ::sanitize
	 */
	public function test_px_verify_if_page_is_offline() {
		$dom = new Document();
		$dom->loadHTML( $this->get_offline_or_500_page_markup() );
		$sanitizer = new AMP_PWA_Script_Sanitizer( $dom );

		$error_template_url = add_query_arg( 'wp_error_template', 'offline', home_url( '/', 'relative' ) );
		$this->go_to( $error_template_url );

		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertTrue( is_offline() );
		$this->assertFalse( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * PX verify if page is 500
	 *
	 * @covers ::sanitize
	 */
	public function test_px_verify_if_page_is_500() {
		$dom = new Document();
		$dom->loadHTML( $this->get_offline_or_500_page_markup() );
		$sanitizer = new AMP_PWA_Script_Sanitizer( $dom );

		$error_template_url = add_query_arg( 'wp_error_template', '500', home_url( '/', 'relative' ) );
		$this->go_to( $error_template_url );

		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertFalse( is_offline() );
		$this->assertTrue( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}
}
