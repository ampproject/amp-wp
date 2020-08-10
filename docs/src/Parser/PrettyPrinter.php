<?php
/**
 * Class PrettyPrinter.
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
	 * @param Arg $node Expression argument
	 *
	 * @return string Pretty printed argument
	 */
	public function prettyPrintArg( Arg $node ) {
		return str_replace(
			"\n" . $this->noIndentToken,
			"\n",
			$this->p( $node )
		);
	}
}
