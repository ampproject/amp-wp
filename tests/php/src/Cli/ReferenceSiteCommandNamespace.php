<?php
/**
 * Abstract seed base class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use WP_CLI\Dispatcher\CommandNamespace;

/**
 * Manage the AMP reference site used for testing and measuring the impact of the AMP plugin.
 *
 * @package AmpProject\AmpWP\Tests\Cli
 */
final class ReferenceSiteCommandNamespace extends CommandNamespace {

	/**
	 * Root folder to use for the reference site definition files.
	 *
	 * @var string
	 */
	const REFERENCE_SITES_ROOT = AMP__DIR__ . '/tests/reference-sites/';

}
