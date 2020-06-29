<?php
/**
 * Reference site import WXR file step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Step;

use AmpProject\AmpWP\Tests\Cli\Step;
use WP_CLI;

final class ActivateTheme implements Step {

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
		// @TODO: Download theme as needed.
		WP_CLI::log(
			WP_CLI::colorize(
				"Switching active theme to %G'{$this->theme}'%n"
			)
		);

		switch_theme( $this->theme );
	}
}
