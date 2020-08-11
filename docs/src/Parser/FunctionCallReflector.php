<?php
/**
 * Class FunctionCallReflector.
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

		$shortName = $this->getShortName();

		if ( $shortName instanceof FullyQualified ) {
			return '\\' . (string) $shortName;
		}

		if ( $shortName instanceof Name ) {
			return (string) $shortName;
		}

		if ( $shortName instanceof ArrayDimFetch ) {
			$var = $shortName->var->name;
			$dim = $shortName->dim->name->parts[0];

			return "\${$var}[{$dim}]";
		}

		if ( $shortName instanceof Variable ) {
			return $shortName->name;
		}

		return (string) $shortName;
	}
}
