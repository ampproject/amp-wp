<?php
/**
 * Interface Filter.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP;

/**
 * Constants for the filters that the AmpWP plugin supports.
 *
 * @package Amp\AmpWP
 */
interface Filter {

	/**
	 * Filter whether the generated HTML output should be run through the AMP Optimizer or not.
	 *
	 * @since 1.5.0
	 */
	const ENABLE_OPTIMIZER = 'amp_enable_optimizer';

	/**
	 * Filter whether the AMP Optimizer should use server-side rendering or not.
	 *
	 * @since 1.5.0
	 */
	const ENABLE_SSR = 'amp_enable_ssr';

	/**
	 * Filter the configuration to be used for the AMP Optimizer.
	 *
	 * @since 1.5.0
	 */
	const OPTIMIZER_CONFIG = 'amp_optimizer_config';
}
