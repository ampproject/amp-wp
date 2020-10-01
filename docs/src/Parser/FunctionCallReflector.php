<?php
/**
 * Class FunctionCallReflector.
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

use phpDocumentor\Reflection\BaseReflector;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;

/**
 * A reflection of a function call expression.
 */
final class FunctionCallReflector extends BaseReflector {

	/**
	 * Returns the name for this Reflector instance.
	 *
	 * @return string
	 */
	public function getName() {
		if ( isset( $this->node->namespacedName ) ) {
			return '\\' . implode( '\\', $this->node->namespacedName->parts );
		}

		$short_name = $this->getShortName();

		if ( $short_name instanceof FullyQualified ) {
			return '\\' . (string) $short_name;
		}

		if ( $short_name instanceof Name ) {
			return (string) $short_name;
		}

		if ( $short_name instanceof ArrayDimFetch ) {
			$var = $short_name->var->name;
			$dim = isset( $short_name->dim->name )
				? $short_name->dim->name->parts[0]
				: false;

			return false === $dim ? "\${$var}" : "\${$var}[{$dim}]";
		}

		if ( $short_name instanceof Variable ) {
			return $short_name->name;
		}

		return (string) $short_name;
	}
}
