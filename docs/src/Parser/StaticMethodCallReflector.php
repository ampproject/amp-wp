<?php
/**
 * Class StaticMethodCallReflector.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Parser;

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
		$class  = $this->node->class;
		$prefix = ( $class instanceof FullyQualified ) ? '\\' : '';
		$class  = $prefix . $this->_resolveName( implode( '\\', $class->parts ) );

		return [ $class, $this->getShortName() ];
	}

	/**
	 * @return bool
	 */
	public function isStatic() {
		return true;
	}
}
