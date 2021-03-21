<?php
/**
 * Exception FailedToMakeInstance.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use AmpProject\AmpWP\Infrastructure\Injector\InjectionChain;
use RuntimeException;

/**
 * Exception thrown when the injector couldn't instantiate a given class or
 * interface.
 *
 * @since 2.0
 * @internal
 */
final class FailedToMakeInstance
	extends RuntimeException
	implements AmpWpException {

	// These constants are public so you can use them to find out what exactly
	// happened when you catch a "FailedToMakeInstance" exception.
	const CIRCULAR_REFERENCE             = 100;
	const UNRESOLVED_INTERFACE           = 200;
	const UNREFLECTABLE_CLASS            = 300;
	const UNRESOLVED_ARGUMENT            = 400;
	const UNINSTANTIATED_SHARED_INSTANCE = 500;
	const INVALID_DELEGATE               = 600;

	/**
	 * Create a new instance of the exception for an interface or class that
	 * created a circular reference.
	 *
	 * @param string         $interface_or_class         Interface or class name that
	 *                                                   generated the circular
	 *                                                   reference.
	 * @param InjectionChain $injection_chain    Injection chain that led to the
	 *                                           circular reference.
	 * @return self
	 */
	public static function for_circular_reference(
		$interface_or_class,
		InjectionChain $injection_chain
	) {
		$message = \sprintf(
			'Circular reference detected while trying to resolve the interface or class "%s".',
			$interface_or_class
		);

		$message .= "\nInjection chain:\n";
		foreach ( $injection_chain->get_chain() as $link ) {
			$message .= "{$link}\n";
		}

		return new self( $message, self::CIRCULAR_REFERENCE );
	}

	/**
	 * Create a new instance of the exception for an interface that could not
	 * be resolved to an instantiable class.
	 *
	 * @param string $interface Interface that was left unresolved.
	 *
	 * @return self
	 */
	public static function for_unresolved_interface( $interface ) {
		$message = \sprintf(
			'Could not resolve the interface "%s" to an instantiable class, probably forgot to bind an implementation.',
			$interface
		);

		return new self( $message, self::UNRESOLVED_INTERFACE );
	}

	/**
	 * Create a new instance of the exception for an interface or class that
	 * could not be reflected upon.
	 *
	 * @param string $interface_or_class Interface or class that could not be
	 *                                   reflected upon.
	 *
	 * @return self
	 */
	public static function for_unreflectable_class( $interface_or_class ) {
		$message = \sprintf(
			'Could not reflect on the interface or class "%s", probably not a valid FQCN.',
			$interface_or_class
		);

		return new self( $message, self::UNREFLECTABLE_CLASS );
	}

	/**
	 * Create a new instance of the exception for an argument that could not be
	 * resolved.
	 *
	 * @param string $argument_name Name of the argument that could not be
	 *                              resolved.
	 * @param string $class         Class that had the argument in its
	 *                              constructor.
	 * @return self
	 */
	public static function for_unresolved_argument( $argument_name, $class ) {
		$message = \sprintf(
			'Could not resolve the argument "%s" while trying to instantiate the class "%s".',
			$argument_name,
			$class
		);

		return new self( $message, self::UNRESOLVED_ARGUMENT );
	}

	/**
	 * Create a new instance of the exception for a class that was meant to be
	 * reused but was not yet instantiated.
	 *
	 * @param string $class Class that was not yet instantiated.
	 *
	 * @return self
	 */
	public static function for_uninstantiated_shared_instance( $class ) {
		$message = \sprintf(
			'Could not retrieve the shared instance for "%s" as it was not instantiated yet.',
			$class
		);

		return new self( $message, self::UNINSTANTIATED_SHARED_INSTANCE );
	}

	/**
	 * Create a new instance of the exception for a delegate that was requested
	 * for a class that doesn't have one.
	 *
	 * @param string $class Class for which there is no delegate.
	 *
	 * @return self
	 */
	public static function for_invalid_delegate( $class ) {
		$message = \sprintf(
			'Could not retrieve a delegate for "%s", none was defined.',
			$class
		);

		return new self( $message, self::INVALID_DELEGATE );
	}
}
