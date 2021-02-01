<?php
/**
 * Class StaticMethodCallReflector.
 *
 * This class is based on code from the WordPress/phpdoc-parser project by
 * Ryan McCue, Paul Gibbs, Andrey "Rarst" Savchenko and Contributors,
 * licensed under the GPLv2 or later.
 *
 * @link https://github.com/WordPress/phpdoc-parser
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Parser;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;

/**
 * A reflection of a method call expression.
 */
final class StaticMethodCallReflector extends MethodCallReflector {

	/**
	 * Returns the name for this Reflector instance.
	 *
	 * @return string[] Index 0 is the class name, 1 is the method name.
	 */
	public function getName() {
		$class = $this->node->class;

		// Method is called via a variable classname like $class::method().
		if ( $class instanceof Variable ) {
			return [ $class, $class->name ];
		}

		$prefix = ( $class instanceof FullyQualified ) ? '\\' : '';
		$class  = $prefix . $this->resolveName( implode( '\\', property_exists( $class, 'parts' ) ? $class->parts : [ $class->toString() ] ) );

		return [ $class, $this->getShortName() ];
	}

	/**
	 * @return bool
	 */
	public function isStatic() {
		return true;
	}
}
