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

		// Copy the current source files into the WordPress installation as a plugin.
		$amp_plugin_dir = $this->variables['RUN_DIR'] . '/wp-content/plugins/amp';
		$this->ensure_dir_exists( $amp_plugin_dir );
		self::copy_dir( realpath( self::get_vendor_dir() . '/../' ), $amp_plugin_dir );

		// Regenerate the autoloader so that it is created with a new hash in the class name.
		// This is needed to avoid a fatal error on class redeclaration, as the Composer
		// autoloader that is copied is the same one that was already loaded into the process
		// by Behat to find its files.
		$this->proc( 'rm -rf vendor/autoload.php vendor/composer/autoload_*', [], $amp_plugin_dir )->run_check();
		$this->proc( 'composer dumpautoload', [], $amp_plugin_dir )->run_check();

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
