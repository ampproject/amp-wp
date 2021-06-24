<?php

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\ErrorPage;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use RuntimeException;

final class ErrorPageTest extends DependencyInjectedTestCase {
	use AssertContainsCompatibility;

	public function test_error_page_output() {
		// Set up temporary capture of error log to test error log output.
		$capture = tmpfile();
		$backup  = ini_set( // phpcs:ignore WordPress.PHP.IniSet.Risky
			'error_log',
			stream_get_meta_data( $capture )['uri']
		);

		$output = $this->injector->make( ErrorPage::class )
			->with_title( 'Error Page Title' )
			->with_message( 'Error Page Message' )
			->with_throwable( new RuntimeException( 'FAILURE', 42 ) )
			->with_response_code( 123 )
			->with_back_link( 'https://back.example.com', 'Go Back' )
			->render();

		// Verify that error log was properly populated.
		$this->assertRegExp(
			'/^\[[^\]]*\] Error Page Message - FAILURE \(42\) \[RuntimeException\].*/',
			stream_get_contents( $capture )
		);

		// Reset error log back to initial settings.
		ini_set( 'error_log', $backup ); // phpcs:ignore WordPress.PHP.IniSet.Risky

		// Test HTML output.
		$this->assertStringContains( '<title>Error Page Title</title>', $output );
		$this->assertStringContains( '<h1>Error Page Title</h1>', $output );
		$this->assertStringContains( '<p>Error Page Message</p>', $output );
		$this->assertStringContains( '<strong>FAILURE</strong> (42) [<em>RuntimeException</em>]', $output );
		$this->assertStringContains( '<!DOCTYPE html>', $output );
		$this->assertStringContains( '<meta name="viewport"', $output );
		$this->assertStringContains( '<body id="error-page">', $output );
		$this->assertStringContains( '<style type="text/css">', $output );
		$this->assertStringContains( 'button button-large', $output );
		$this->assertStringContains( 'https://back.example.com', $output );
		$this->assertStringContains( 'Go Back', $output );
	}
}
