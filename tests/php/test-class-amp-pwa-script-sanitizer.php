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
	 * @covers::sanitize
	 */
	public function test_sanitize() {
		$dom = new Document();
		$dom->loadHTML(
			'<html>
			<head></head>
			<body>
			<script id="wp-navigation-request-properties" type="application/json">{{{WP_NAVIGATION_REQUEST_PROPERTIES}}}</script>
			<script id="wp-offline-page-reload" type="module">
				const shouldRetry = () => {
					if (new URLSearchParams(location.search.substring(1)).has(\'wp_error_template\')) {
						return false;
					}
					const navigationRequestProperties = JSON.parse(document.getElementById(\'wp-navigation-request-properties\').text);
					if (\'GET\' !== navigationRequestProperties.method) {
						return false;
					}
					return true;
				};
				if (shouldRetry()) {
				/**
				 * Listen to changes in the network state, reload when online.
				 * This handles the case when the device is completely offline.
				 */
				window.addEventListener(\'online\', () => {
					window.location.reload();
				});
				// Create a counter to implement exponential backoff.
				let count = 0;
				/**
				 * Check if the server is responding and reload the page if it is.
				 * This handles the case when the device is online, but the server is offline or misbehaving.
				 */
				async function checkNetworkAndReload() {
					try {
					const response = await fetch(location.href, {
						method: \'HEAD\',
					});
					// Verify we get a valid response from the server
					if (response.status >= 200 && response.status < 500) {
						window.location.reload();
						return;
					}
					} catch {
					// Unable to connect so do nothing.
					}
					window.setTimeout(checkNetworkAndReload, Math.pow(2, count++) * 2500);
				}
				checkNetworkAndReload();
				}
			</script>
			</body>
		</html>'
		);
		$sanitizer = new AMP_PWA_Script_Sanitizer( $dom );

		$xpath = '//script[@id="wp-navigation-request-properties" or @id="wp-offline-page-reload"]';

		// If page is not offline or 500 error, no `data-px-verified-tag` should be added in scripts.
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( $xpath );

		$this->assertFalse( is_offline() );
		$this->assertFalse( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}

		// If page is offline, `data-px-verified-tag` should be added in scripts.
		$error_template_url = add_query_arg( 'wp_error_template', 'offline', home_url( '/', 'relative' ) );
		$this->go_to( $error_template_url );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( $xpath );

		$this->assertTrue( is_offline() );
		$this->assertFalse( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}

		// If page is 500 error, `data-px-verified-tag` should be added in scripts.
		$error_template_url = add_query_arg( 'wp_error_template', '500', home_url( '/', 'relative' ) );
		$this->go_to( $error_template_url );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( $xpath );

		$this->assertFalse( is_offline() );
		$this->assertTrue( is_500() );
		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}
}
