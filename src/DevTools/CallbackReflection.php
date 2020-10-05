<?php
/**
 * Class CallbackReflection.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\DevTools;

use AmpProject\AmpWP\Infrastructure\Service;
use Exception;
use ReflectionFunction;
use ReflectionMethod;
use Reflector;

/**
 * Reflect on a file to deduce its type of source (plugin, theme, core).
 *
 * @package AmpProject\AmpWP
 * @since 2.0.2
 * @internal
 */
final class CallbackReflection implements Service {

	/**
	 * File reflection instance to use.
	 *
	 * @var FileReflection
	 */
	private $file_reflection;

	/**
	 * CallbackReflection constructor.
	 *
	 * @param FileReflection $file_reflection File reflector to use.
	 */
	public function __construct( FileReflection $file_reflection ) {
		$this->file_reflection = $file_reflection;
	}

	/**
	 * Gets the plugin or theme of the callback, if one exists.
	 *
	 * @param string|array|callable $callback The callback for which to get the
	 *                                        plugin.
	 * @return array|null {
	 *     The source data.
	 *
	 *     @type string    $type       Source type (core, plugin, mu-plugin, or theme).
	 *     @type string    $name       Source name.
	 *     @type string    $file       Relative file path based on the type.
	 *     @type string    $function   Normalized function name.
	 *     @type Reflector $reflection Reflection.
	 * }
	 */
	public function get_source( $callback ) {
		$reflection = $this->get_reflection( $callback );

		if ( ! $reflection ) {
			return null;
		}

		$source = [ 'reflection' => $reflection ];

		$file   = wp_normalize_path( $reflection->getFileName() );
		$source = array_merge(
			$source,
			$this->file_reflection->get_file_source( $file )
		);

		// If a file was identified, then also supply the line number.
		if ( isset( $source['file'] ) ) {
			$source['line'] = $reflection->getStartLine();
		}

		if ( $reflection instanceof ReflectionMethod ) {
			$source['function'] = $reflection->getDeclaringClass()->getName() . '::' . $reflection->getName();
		} else {
			$source['function'] = $reflection->getName();
		}

		return $source;
	}

	/**
	 * Get the reflection object for the callback.
	 *
	 * @param string|array|callable $callback The callback for which to get the
	 *                                        plugin.
	 * @return ReflectionMethod|ReflectionFunction|null
	 */
	private function get_reflection( $callback ) {
		try {
			if ( is_string( $callback ) && is_callable( $callback ) ) {
				// The $callback is a function or static method.
				$exploded_callback = explode( '::', $callback, 2 );

				if ( 2 !== count( $exploded_callback ) ) {
					return new ReflectionFunction( $callback );
				}

				// Since identified as method, handle as ReflectionMethod below.
				$callback = $exploded_callback;
			}

			if (
				is_array( $callback )
				&&
				isset( $callback[0], $callback[1] )
				&&
				method_exists( $callback[0], $callback[1] )
			) {
				$reflection = new ReflectionMethod( $callback[0], $callback[1] );

				// Handle the special case of the class being a widget, in which
				// case the display_callback method should actually map to the
				// underling widget method. It is the display_callback in the
				// end that is wrapped.
				if (
					'display_callback' === $reflection->getName()
					&&
					'WP_Widget' === $reflection->getDeclaringClass()->getName()
				) {
					return new ReflectionMethod( $callback[0], 'widget' );
				}

				return $reflection;
			}

			if (
				is_object( $callback )
				&&
				'Closure' === get_class( $callback )
			) {
				return new ReflectionFunction( $callback );
			}
		} catch ( Exception $exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Don't let exceptions through here.
		}

		return null;
	}
}
