<?php
/**
 * Class Options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Dom;

use AmpProject\Dom\Document;

interface Options {

	/**
	 * Default options to use for the Dom.
	 *
	 * @var array
	 */
	const DEFAULTS = [
		Document\Option::AMP_BIND_SYNTAX => Document\Option::AMP_BIND_SYNTAX_DATA_ATTRIBUTE,
	];
}
