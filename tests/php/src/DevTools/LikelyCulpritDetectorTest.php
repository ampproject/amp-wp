<?php
/**
 * Tests for LikelyCulpritDetector class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\LikelyCulpritDetector;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use RuntimeException;

/**
 * Tests for LikelyCulpritDetector class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\LikelyCulpritDetector
 */
class LikelyCulpritDetectorTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Test instance.
	 *
	 * @var LikelyCulpritDetector
	 */
	private $likely_culprit_detector;

	public function setUp() {
		parent::setUp();

		$this->likely_culprit_detector = $this->injector->make( LikelyCulpritDetector::class );
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_backtrace
	 *
	 * This only tests a single scenario as it is non-trivial to shape the
	 * debug_backtrace(). The rest of the cases are already covered by the tests
	 * for analyze_trace().
	 *
	 * @covers ::analyze_backtrace
	 */
	public function test_analyze_backtrace() {
		$source = null;

		// We need to provide a way to trigger the culprit detection after the
		// code has passed through a theme or plugin that is not seen as being
		// part of the AMP plugin. We therefore use a theme, as these are
		// detected by active parent or child theme name instead of file
		// location (which would still be a subfolder of the AMP plugin).

		$analyze_trace_callback = function () use ( &$source ) {
			$source = $this->likely_culprit_detector->analyze_backtrace();
		};

		$theme_root = dirname( dirname( __DIR__ ) ) . '/data/themes';

		$set_theme_root_callback = static function () use ( $theme_root ) {
			return $theme_root;
		};

		add_action( 'execute_from_within_theme', $analyze_trace_callback );
		add_filter( 'theme_root', $set_theme_root_callback );
		register_theme_directory( $theme_root );

		$previous_theme = get_stylesheet();
		switch_theme( 'custom' );
		require get_template_directory() . '/functions.php';

		// Refresh internal reflection caches.
		do_action( 'setup_theme' );

		do_action( 'trigger_action_to_execute' );

		$this->assertInternalType( 'array', $source, 'Expected the action to be triggered.' );

		$this->assertArrayHasKey( 'type', $source );
		$this->assertArrayHasKey( 'name', $source );

		$this->assertEquals( 'theme', $source['type'] );
		$this->assertEquals( 'custom', $source['name'] );

		switch_theme( $previous_theme );

		remove_filter( 'theme_root', $set_theme_root_callback );
		remove_action( 'execute_from_within_theme', $analyze_trace_callback );

		// Refresh internal reflection caches.
		do_action( 'setup_theme' );
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
	 * Tests LikelyCulpritDetector::analyze_throwable
	 *
	 * @dataProvider single_step_trace_data()
	 *
	 * @param string[] $file_stack    Stack of file paths.
	 * @param string   $expected_type Expected source type.
	 * @param string   $expected_name Expected source name.
	 *
	 * @covers ::analyze_throwable
	 */
	public function test_analyze_throwable( $file_stack, $expected_type, $expected_name ) {
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

		$source = $this->likely_culprit_detector->analyze_throwable( $exception );

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
