<?php
/**
 * Trait PrivateAccess.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

use ReflectionClass;
use ReflectionException;

/**
 * Trait PrivateAccess.
 *
 * Allows accessing private methods and properties for testing.
 *
 * @internal
 * @since 1.5.0
 */
trait PrivateAccess {

	/**
	 * Call a private method as if it was public.
	 *
	 * @param object|string $object      Object instance or class string to call the method of.
	 * @param string        $method_name Name of the method to call.
	 * @param array         $args        Optional. Array of arguments to pass to the method.
	 * @return mixed Return value of the method call.
	 * @throws ReflectionException If the object could not be reflected upon.
	 */
	private function call_private_method( $object, $method_name, $args = [] ) {
		$method = ( new ReflectionClass( $object ) )->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( $object, $args );
	}

	/**
	 * Call a private static method as if it was public.
	 *
	 * @param string $class       Class string to call the method of.
	 * @param string $method_name Name of the method to call.
	 * @param array  $args        Optional. Array of arguments to pass to the method.
	 * @return mixed Return value of the method call.
	 * @throws ReflectionException If the class could not be reflected upon.
	 */
	private function call_private_static_method( $class, $method_name, $args = [] ) {
		$method = ( new ReflectionClass( $class ) )->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( null, $args );
	}

	/**
	 * Set a private property as if it was public.
	 *
	 * @param object|string $object        Object instance or class string to set the property of.
	 * @param string        $property_name Name of the property to set.
	 * @param mixed         $value         Value to set the property to.
	 * @throws ReflectionException If the object could not be reflected upon.
	 */
	private function set_private_property( $object, $property_name, $value ) {
		$property = ( new ReflectionClass( $object ) )->getProperty( $property_name );
		$property->setAccessible( true );

		// Note: In PHP 8, `ReflectionProperty::getValue()` now requires that an object be supplied if it's a
		// non-static property.
		$property->isStatic() ? $property->setValue( $value ) : $property->setValue( $object, $value );
	}

	/**
	 * Get a private property as if it was public.
	 *
	 * @param object|string $object        Object instance or class string to get the property of.
	 * @param string        $property_name Name of the property to get.
	 * @return mixed Return value of the property.
	 * @throws ReflectionException If the object could not be reflected upon.
	 */
	private function get_private_property( $object, $property_name ) {
		$property = ( new ReflectionClass( $object ) )->getProperty( $property_name );
		$property->setAccessible( true );

		// Note: In PHP 8, `ReflectionProperty::getValue()` now requires that an object be supplied if it's a
		// non-static property.
		return $property->isStatic() ? $property->getValue() : $property->getValue( $object );
	}
}
