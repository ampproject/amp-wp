<?php
/**
 * Reference site install theme (without activating) step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class InstallTheme implements ImportStep {

	/**
	 * Theme slug to install.
	 *
	 * @var string
	 */
	private $theme;

	/**
	 * InstallTheme constructor.
	 *
	 * @param string $theme Theme slug to install.
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
				"Installing theme %G'{$this->theme}'%n..."
			)
		);

		$result = WP_CLI::runcommand(
			"theme install {$this->theme}",
			[ 'return' => 'return_code' ]
		);

		return 0 === $result ? 1 : -1;
	}
}
