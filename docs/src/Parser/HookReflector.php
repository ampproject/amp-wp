<?php
/**
 * Class HookReflector.
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
use PhpParser\PrettyPrinter\Standard;

/**
 * Custom reflector for WordPress hooks.
 */
final class HookReflector extends BaseReflector {

	/**
	 * @return string
	 */
	public function getName() {
		$printer = new Standard();
		return $this->cleanupName( $printer->prettyPrintExpr( $this->node->args[0]->value ) );
	}

	/**
	 * Clean up the name.
	 *
	 * @param string $name Name to clean up.
	 *
	 * @return string Cleaned-up name.
	 */
	private function cleanupName( $name ) {
		$matches = [];

		// Quotes on both ends of a string.
		if ( preg_match( '/^[\'"]([^\'"]*)[\'"]$/', $name, $matches ) ) {
			return $matches[1];
		}

		// Two concatenated things, last one of them a variable.
		if ( preg_match(
			'/(?:[\'"]([^\'"]*)[\'"]\s*\.\s*)?' . // First filter name string (optional).
			'(\$[^\s]*)' . // Dynamic variable.
			'(?:\s*\.\s*[\'"]([^\'"]*)[\'"])?/',  // Second filter name string (optional).
			$name,
			$matches
		) ) {

			if ( isset( $matches[3] ) ) {
				return $matches[1] . '{' . $matches[2] . '}' . $matches[3];
			}

			return $matches[1] . '{' . $matches[2] . '}';
		}

		return $name;
	}

	/**
	 * @return string
	 */
	public function getShortName() {
		return $this->getName();
	}

	/**
	 * @return string
	 */
	public function getType() {
		$type = 'filter';
		switch ( (string) $this->node->name ) {
			case 'do_action':
				$type = 'action';
				break;
			case 'do_action_ref_array':
				$type = 'action_reference';
				break;
			case 'do_action_deprecated':
				$type = 'action_deprecated';
				break;
			case 'apply_filters_ref_array':
				$type = 'filter_reference';
				break;
			case 'apply_filters_deprecated':
				$type = 'filter_deprecated';
				break;
		}

		return $type;
	}

	/**
	 * @return array
	 */
	public function getArgs() {
		$printer = new PrettyPrinter();
		$args    = [];
		foreach ( $this->node->args as $arg ) {
			$args[] = $printer->prettyPrintArg( $arg );
		}

		// Skip the filter name.
		array_shift( $args );

		return $args;
	}
}
