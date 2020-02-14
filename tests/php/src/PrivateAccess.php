<?php
/**
 * Trait PrivateAccess.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP\Tests;

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
		$property->setValue( $object, $value );
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
		return $property->getValue( $object );
	}

	/**
	 * Get a static private property as if it was public.
	 *
	 * @param string $class         Class string to get the property of.
	 * @param string $property_name Name of the property to get.
	 * @return mixed Return value of the property.
	 * @throws ReflectionException If the class could not be reflected upon.
	 */
	private function get_static_private_property( $class, $property_name ) {
		$properties = ( new ReflectionClass( $class ) )->getStaticProperties();
		return array_key_exists( $property_name, $properties ) ? $properties[ $property_name ] : null;
	}
}
