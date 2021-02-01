<?php
/**
 * Interface ConfigurationArgument.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Constants for the options that the AmpWP plugin supports.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
interface ConfigurationArgument {

	const ENABLE_OPTIMIZER = 'enable_optimizer';
	const ENABLE_SSR       = 'enable_ssr';
}
