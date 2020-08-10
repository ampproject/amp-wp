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

		if ( is_a( $shortName, 'PHPParser_Node_Name_FullyQualified' ) ) {
			return '\\' . (string) $shortName;
		}

		if ( is_a( $shortName, 'PHPParser_Node_Name' ) ) {
			return (string) $shortName;
		}

		/** @var ArrayDimFetch $shortName */
		if ( is_a( $shortName, 'PHPParser_Node_Expr_ArrayDimFetch' ) ) {
			$var = $shortName->var->name;
			$dim = $shortName->dim->name->parts[0];

			return "\${$var}[{$dim}]";
		}

		/** @var Variable $shortName */
		if ( is_a( $shortName, 'PHPParser_Node_Expr_Variable' ) ) {
			return $shortName->name;
		}

		return (string) $shortName;
	}
}
