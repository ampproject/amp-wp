<?php
/**
 * Reference site activate plugins step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class ActivatePlugin implements ImportStep {

	/**
	 * Plugin slug to activate.
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * ActivatePlugin constructor.
	 *
	 * @param string $plugin Plugin slug to activate.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		WP_CLI::log(
			WP_CLI::colorize(
				"Installing and activating plugin %G'{$this->plugin}'%n..."
			)
		);

		WP_CLI::runcommand( "plugin install {$this->plugin} --activate" );

		return 1;
	}
}
