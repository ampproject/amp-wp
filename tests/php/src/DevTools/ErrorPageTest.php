<?php

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\ErrorPage;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use RuntimeException;

final class ErrorPageTest extends DependencyInjectedTestCase {

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
		$this->assertMatchesRegularExpression(
			'/^\[[^\]]*\] Error Page Message - FAILURE \(42\) \[RuntimeException\].*/',
			stream_get_contents( $capture )
		);

		// Reset error log back to initial settings.
		ini_set( 'error_log', $backup ); // phpcs:ignore WordPress.PHP.IniSet.Risky

		// Test HTML output.
		$this->assertStringContainsString( '<title>Error Page Title</title>', $output );
		$this->assertStringContainsString( '<h1>Error Page Title</h1>', $output );
		$this->assertStringContainsString( '<p>Error Page Message</p>', $output );
		$this->assertStringContainsString( '<strong>FAILURE</strong> (42) [<em>RuntimeException</em>]', $output );
		$this->assertStringContainsString( '<!DOCTYPE html>', $output );
		$this->assertStringContainsString( '<meta name="viewport"', $output );
		$this->assertStringContainsString( '<body id="error-page">', $output );
		$this->assertStringContainsString( '<style type="text/css">', $output );
		$this->assertStringContainsString( 'If you get stuck', $output );
		$this->assertStringContainsString( 'button button-large', $output );
		$this->assertStringContainsString( 'https://back.example.com', $output );
		$this->assertStringContainsString( 'Go Back', $output );
	}
}
