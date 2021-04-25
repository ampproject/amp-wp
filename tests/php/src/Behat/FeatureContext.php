<?php
/**
 * Class FeatureContext.
 *
 * Feature tests context class with AmpWP-specific steps.
 *
 * @package AmpProject/AmpWP
 */

namespace AmpProject\AmpWP\Tests\Behat;

use WP_CLI\Tests\Context\FeatureContext as WP_CLI_FeatureContext;
use RuntimeException;

/**
 * Feature tests context class with AmpWP-specific steps.
 *
 * This class extends the one that is provided by the wp-cli/wp-cli-tests package.
 * To see a list of all recognized step definitions, run `vendor/bin/behat -dl`.
 *
 * @package AmpProject\AmpWP
 */
final class FeatureContext extends WP_CLI_FeatureContext {

	/**
	 * @Given a WP install(ation) with the AMP plugin
	 */
	public function given_a_wp_installation_with_the_amp_plugin() {
		$this->install_wp();

		// Symlink the current project folder into the WP folder as a plugin.
		$project_dir = realpath( self::get_vendor_dir() . '/../' );
		$plugin_dir  = $this->variables['RUN_DIR'] . '/wp-content/plugins';
		$this->ensure_dir_exists( $plugin_dir );
		$this->proc( "ln -s {$project_dir} {$plugin_dir}/amp" )->run_check();

		// Activate the previously copied plugin.
		$this->proc( 'wp plugin activate amp' )->run_check();
	}

	/**
	 * Ensure that a requested directory exists and create it recursively as needed.
	 *
	 * @param string $directory Directory to ensure the existence of.
	 */
	private function ensure_dir_exists( $directory ) {
		$parent = dirname( $directory );

		if ( ! empty( $parent ) && ! is_dir( $parent ) ) {
			$this->ensure_dir_exists( $parent );
		}

		if ( ! is_dir( $directory ) && ! mkdir( $directory ) && ! is_dir( $directory ) ) {
			throw new RuntimeException( "Could not create directory '{$directory}'." );
		}
	}
}
