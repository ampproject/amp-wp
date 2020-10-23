<?php
/**
 * Class LikelyCulpritDetector.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\DevTools;

use AmpProject\AmpWP\Infrastructure\Service;
use Exception;

/**
 * Go through a debug backtrace and detect the extension that is likely to have
 * caused that backtrace.
 *
 * @package AmpProject\AmpWP
 * @since   2.0.2
 * @internal
 */
final class LikelyCulpritDetector implements Service {

	/**
	 * File reflector to use.
	 *
	 * @var FileReflection
	 */
	private $file_reflection;

	/**
	 * LikelyCulpritDetector constructor.
	 *
	 * @param FileReflection $file_reflection File reflector to use.
	 */
	public function __construct( FileReflection $file_reflection ) {
		$this->file_reflection = $file_reflection;
	}

	/**
	 * Detect the themes and plugins responsible for causing the current debug
	 * backtrace.
	 *
	 * @return array {
	 *     Type and name of extension that is the likely culprit.
	 *
	 *     @type string $type Type. Empty if none matched.
	 *     @type string $name Name. Empty if none matched.
	 * }
	 */
	public function analyze_backtrace() {
		return $this->analyze_trace( debug_backtrace( 0 ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
	}

	/**
	 * Detect the themes and plugins responsible for causing the exception.
	 *
	 * @param Exception $exception Exception to analyze.
	 * @return array {
	 *     Type and name of extension that is the likely culprit.
	 *
	 *     @type string $type Type. Empty if none matched.
	 *     @type string $name Name. Empty if none matched.
	 * }
	 */
	public function analyze_exception( Exception $exception ) {
		$trace = $exception->getTrace();
		array_unshift( $trace, [ FileReflection::SOURCE_FILE => $exception->getFile() ] );
		return $this->analyze_trace( $trace );
	}

	/**
	 * Detect the themes and plugins responsible for an issue in a trace.
	 *
	 * @param array $trace Associative array of trace data to analyze.
	 * @return array {
	 *     Type and name of extension that is the likely culprit.
	 *
	 *     @type string $type Type. Empty if none matched.
	 *     @type string $name Name. Empty if none matched.
	 * }
	 */
	public function analyze_trace( $trace ) {
		foreach ( $trace as $call_stack ) {
			if ( empty( $call_stack[ FileReflection::SOURCE_FILE ] ) ) {
				continue;
			}

			$source = $this->file_reflection->get_file_source( $call_stack[ FileReflection::SOURCE_FILE ] );

			if (
				empty( $source )
				||
				FileReflection::TYPE_CORE === $source[ FileReflection::SOURCE_TYPE ]
				||
				(
					FileReflection::TYPE_PLUGIN === $source[ FileReflection::SOURCE_TYPE ]
					&&
					// Per \AmpProject\AmpWP\PluginRegistry::get_plugins(), AMP and Gutenberg are considered core.
					in_array( $source[ FileReflection::SOURCE_NAME ], [ 'amp', 'gutenberg' ], true )
				)
			) {
				// We skip WordPress Core (likely hooks subsystem) and the AMP
				// plugin itself.
				continue;
			}

			return $source;
		}

		return [
			FileReflection::SOURCE_TYPE => '',
			FileReflection::SOURCE_NAME => '',
		];
	}
}
