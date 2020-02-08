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


	const ENABLE_OPTIMIZER = 'amp_enable_optimizer';
	const ENABLE_SSR       = 'amp_enable_ssr';
	const OPTIMIZER_CONFIG = 'amp_optimizer_config';
}
