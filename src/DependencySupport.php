<?php
/**
 * Class to determine support for AMP plugin features.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Service;

/**
 * DependencySupport class.
 *
 * @internal
 * @package AmpProject\AmpWP
 * @since 2.1.2
 */
class DependencySupport implements Service {

	/**
	 * The minimum version of Gutenberg supported.
	 *
	 * @var string
	 */
	const GB_MIN_VERSION = '9.2.0';

	/**
	 * The minimum version of WordPress supported.
	 *
	 * @var string
	 */
	const WP_MIN_VERSION = '5.6';

	/**
	 * Determines whether core or Gutenberg provides minimal support.
	 *
	 * @return bool
	 */
	public function has_support() {
		return $this->has_support_from_core() || $this->has_support_from_gutenberg_plugin();
	}

	/**
	 * Returns whether the Gutenberg plugin provides minimal support.
	 *
	 * @return bool
	 */
	public function has_support_from_gutenberg_plugin() {
		return defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, self::GB_MIN_VERSION, '>=' );
	}

	/**
	 * Returns whether WP core provides minimum Gutenberg support.
	 *
	 * @return bool
	 */
	public function has_support_from_core() {
		return version_compare( get_bloginfo( 'version' ), self::WP_MIN_VERSION, '>=' );
	}
}
