<?php
/**
 * Interface Option.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * An interface to share knowledge about options stored in the AMP Options Manager.
 *
 * @todo Other options used throughout the plugin should use constants in this interface as well.
 *
 * @package AmpProject\AmpWP
 */
interface Option {

	/**
	 * Persist the fact that the transient caching of stylesheets needs to be disabled.
	 *
	 * @var string
	 */
	const DISABLE_CSS_TRANSIENT_CACHING = 'amp_css_transient_monitor_disable_caching';
}
