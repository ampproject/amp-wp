<?php
/**
 * Class AMP_Autoloader
 *
 * @package AMP
 */

/**
 * Autoload the classes used by the AMP plugin.
 *
 * Class AMP_Autoloader
 *
 * @deprecated Use Composer autoloading instead.
 * @internal
 */
class AMP_Autoloader {
	/**
	 * Is registered.
	 *
	 * @var bool
	 */
	public static $is_registered = false;

	/**
	 * Registers this autoloader to PHP.
	 *
	 * @since 0.6
	 *
	 * Called at the end of this file; calling a second time has no effect.
	 */
	public static function register() {
		_deprecated_function( 'AMP_Autoloader::register', '1.5', 'Autoloading is done through Composer.' );
	}

	/**
	 * Allows an extensions plugin to register a class and its file for autoloading
	 *
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @since 0.6
	 *
	 * @deprecated Autoloading works via Composer. Extensions need to use their own mechanism.
	 *
	 * @param string $class_name Full classname (include namespace if applicable).
	 * @param string $filepath   Absolute filepath to class file, including .php extension.
	 */
	public static function register_autoload_class( $class_name, $filepath ) {
		_deprecated_function( 'AMP_Autoloader::register_autoload_class', '1.5', 'Use Composer or custom autoloader in extensions.' );
	}
}
