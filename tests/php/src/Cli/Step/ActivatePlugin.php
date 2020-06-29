<?php
/**
 * Reference site import WXR file step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Step;

use AmpProject\AmpWP\Tests\Cli\Step;
use WP_CLI;

final class ActivatePlugin implements Step {

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
		// @TODO: Download plugin as needed.
		WP_CLI::log(
			WP_CLI::colorize(
				"Activating plugin %G'{$this->plugin}'%n"
			)
		);

		$active_plugins = (array) get_option( 'active_plugins' );

		$active_plugins[] = $this->plugin;
		$active_plugins = array_unique( $active_plugins );

		update_option( 'active_plugins', $active_plugins );
	}
}
