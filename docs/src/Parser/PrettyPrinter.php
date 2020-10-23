<?php
/**
 * Class PrettyPrinter.
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

use PhpParser\Node\Arg;
use PhpParser\PrettyPrinter\Standard;

/**
 * Extends default printer for arguments.
 */
final class PrettyPrinter extends Standard {

	/**
	 * Pretty prints an argument.
	 *
	 * @param Arg $node Expression argument.
	 *
	 * @return string Pretty printed argument.
	 */
	public function prettyPrintArg( Arg $node ) {
		return str_replace(
			"\n" . $this->noIndentToken, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			"\n",
			$this->p( $node )
		);
	}
}
