<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\ErrorPage;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use RuntimeException;
use WP_UnitTestCase;

final class ErrorPageTest extends WP_UnitTestCase {
	use AssertContainsCompatibility;

	public function test_error_page_output() {
		// Set up temporary capture of error log to test error log output.
		$capture = tmpfile();
		$backup  = ini_set(
			'error_log',
			stream_get_meta_data( $capture )['uri']
		);

		$output = ( new ErrorPage() )
			->with_title( 'Error Page Title' )
			->with_message( 'Error Page Message' )
			->with_exception( new RuntimeException( 'FAILURE', 42 ) )
			->with_response_code( 123 )
			->render();

		// Verify that error log was properly populated.
		$this->assertRegExp(
			'/^\[[^\]]*\] Error Page Message - FAILURE \(42\) \[RuntimeException\].*/',
			stream_get_contents( $capture )
		);

		// Reset error log back to initial settings.
		ini_set( 'error_log', $backup );

		// Test HTML output.
		$this->assertStringContains( '<title>Error Page Title</title>', $output );
		$this->assertStringContains( '<h1>Error Page Title</h1>', $output );
		$this->assertStringContains( '<p>Error Page Message</p>', $output );
		$this->assertStringContains( '<strong>FAILURE</strong> (42) [<em>RuntimeException</em>]', $output );
		$this->assertStringContains( '<!DOCTYPE html>', $output );
		$this->assertStringContains( '<meta name="viewport"', $output );
		$this->assertStringContains( '<body id="error-page">', $output );
		$this->assertStringContains( '<style type="text/css">', $output );
	}
}
