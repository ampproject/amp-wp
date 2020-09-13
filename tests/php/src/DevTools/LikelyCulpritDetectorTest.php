<?php
/**
 * Tests for LikelyCulpritDetector class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\LikelyCulpritDetector;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use RuntimeException;
use WP_UnitTestCase;

/**
 * Tests for LikelyCulpritDetector class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\LikelyCulpritDetector
 */
class LikelyCulpritDetectorTest extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Test instance.
	 *
	 * @var LikelyCulpritDetector
	 */
	private $likely_culprit_detector;

	public function setUp() {
		parent::setUp();

		$this->likely_culprit_detector = Services::get( 'injector' )->make( LikelyCulpritDetector::class );
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_backtrace
	 *
	 * @covers ::analyze_backtrace
	 */
	public function test_analyze_backtrace() {
		$this->markTestIncomplete();
	}

	/**
	 * Data provider that returns the single-step trace data to use for testing.
	 *
	 * @return array[] Single-step trace data to use for testing.
	 */
	public function single_step_trace_data() {
		return [
			'core'         => [
				[ ABSPATH . '/wp-includes/some-file.php' ],
				// Core is skipped as culprit, so source remains empty.
				'',
				'',
			],

			'amp plugin'   => [
				[ __FILE__ ],
				// AMP plugin is skipped as culprit, so source remains empty.
				'',
				'',
			],

			'plugin'       => [
				[ WP_PLUGIN_DIR . '/bad-plugin/bad-plugin.php' ],
				'plugin',
				'bad-plugin',
			],

			'mu-plugin'    => [
				[ WP_CONTENT_DIR . '/mu-plugins/bad-mu-plugin.php' ],
				'mu-plugin',
				// TODO: Is this a correct slug for a single file (MU) plugin?
				'bad-mu-plugin.php',
			],

			'parent theme' => [
				[ get_template_directory() . '/functions.php' ],
				'theme',
				'default',
			],

			'child theme'  => [
				[ get_stylesheet_directory() . '/functions.php' ],
				'theme',
				'default',
			],
		];
	}

	/**
	 * Data provider that returns the multi-step trace data to use for testing.
	 *
	 * @return array[] Multi-step trace data to use for testing.
	 */
	public function multi_step_trace_data() {
		return array_merge(
			$this->single_step_trace_data(),
			[
				'all skipped'  => [
					[
						ABSPATH . '/wp-includes/some-file.php',
						__FILE__,
						ABSPATH . '/wp-includes/another-file.php',
					],
					// Core and AMP plugin are skipped, so no culprit.
					'',
					'',
				],

				'plugin'       => [
					[
						ABSPATH . '/wp-includes/some-file.php', // Core is skipped.
						__FILE__, // AMP plugin is skipped.
						WP_PLUGIN_DIR . '/bad-plugin/bad-plugin.php', // <== Likely culprit.
						WP_CONTENT_DIR . '/mu-plugins/bad-mu-plugin.php',
						get_template_directory() . '/functions.php',
						get_stylesheet_directory() . '/functions.php',
					],
					'plugin',
					'bad-plugin',
				],

				'mu-plugin'    => [
					[
						ABSPATH . '/wp-includes/some-file.php', // Core is skipped.
						__FILE__, // AMP plugin is skipped.
						WP_CONTENT_DIR . '/mu-plugins/bad-mu-plugin.php', // <== Likely culprit.
						WP_PLUGIN_DIR . '/bad-plugin/bad-plugin.php',
						get_template_directory() . '/functions.php',
						get_stylesheet_directory() . '/functions.php',
					],
					'mu-plugin',
					// TODO: Is this a correct slug for a single file (MU) plugin?
					'bad-mu-plugin.php',
				],

				'parent theme' => [
					[
						ABSPATH . '/wp-includes/some-file.php', // Core is skipped.
						__FILE__, // AMP plugin is skipped.
						get_template_directory() . '/functions.php', // <== Likely culprit.
						WP_PLUGIN_DIR . '/bad-plugin/bad-plugin.php',
						WP_CONTENT_DIR . '/mu-plugins/bad-mu-plugin.php',
						get_stylesheet_directory() . '/functions.php',
					],
					'theme',
					'default',
				],

				'child theme'  => [
					[
						ABSPATH . '/wp-includes/some-file.php', // Core is skipped.
						__FILE__, // AMP plugin is skipped.
						get_stylesheet_directory() . '/functions.php', // <== Likely culprit.
						WP_PLUGIN_DIR . '/bad-plugin/bad-plugin.php',
						WP_CONTENT_DIR . '/mu-plugins/bad-mu-plugin.php',
						get_template_directory() . '/functions.php',
					],
					'theme',
					'default',
				],
			]
		);
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_exception
	 *
	 * @dataProvider single_step_trace_data()
	 *
	 * @param string[] $file_stack    Stack of file paths.
	 * @param string   $expected_type Expected source type.
	 * @param string   $expected_name Expected source name.
	 *
	 * @covers ::analyze_exception
	 */
	public function test_analyze_exception( $file_stack, $expected_type, $expected_name ) {
		$trace     = $this->get_trace_from_file_stack( $file_stack );
		$exception = new RuntimeException();
		$this->set_private_property(
			$exception,
			'file',
			array_shift( $trace )['file']
		);

		// The trace of the exception cannot be set as it is defined by a final
		// method and not stored as a property. Therefore, we can only test
		// the first "file" that is encountered.

		$source = $this->likely_culprit_detector->analyze_exception( $exception );

		$this->assertEquals( $expected_type, $source['type'] );
		$this->assertEquals( $expected_name, $source['name'] );
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_trace
	 *
	 * @dataProvider multi_step_trace_data()
	 *
	 * @param string[] $file_stack    Stack of file paths.
	 * @param string   $expected_type Expected source type.
	 * @param string   $expected_name Expected source name.
	 *
	 * @covers ::analyze_trace
	 */
	public function test_analyze_trace( $file_stack, $expected_type, $expected_name ) {
		$source = $this->likely_culprit_detector->analyze_trace(
			$this->get_trace_from_file_stack( $file_stack )
		);

		$this->assertEquals( $expected_type, $source['type'] );
		$this->assertEquals( $expected_name, $source['name'] );
	}

	/**
	 * Convert a file stack into a (simplified) trace.
	 *
	 * @param string[] $file_stack File stack to convert.
	 * @return array[] Associative of (simplified) trace data.
	 */
	private function get_trace_from_file_stack( $file_stack ) {
		return array_map(
			static function ( $file ) {
				return [ 'file' => $file ];
			},
			$file_stack
		);
	}
}
