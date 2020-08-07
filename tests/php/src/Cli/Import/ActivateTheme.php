<?php
/**
 * Reference site activate theme step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class ActivateTheme implements ImportStep {

	/**
	 * Theme slug to activate.
	 *
	 * @var string
	 */
	private $theme;

	/**
	 * ActivateTheme constructor.
	 *
	 * @param string $theme Theme slug to activate.
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
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
				"Installing and activating theme %G'{$this->theme}'%n..."
			)
		);

		$result = WP_CLI::runcommand(
			"theme install {$this->theme} --activate",
			[ 'return' => 'return_code' ]
		);

		return 0 === $result ? 1 : -1;
	}
}
