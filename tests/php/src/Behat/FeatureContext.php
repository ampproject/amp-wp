<?php
/**
 * Feature tests context class with AmpWP-specific steps.
 *
 * @package AmpProject/AmpWP
 */

namespace AmpProject\AmpWP\Tests\Behat;

use WP_CLI\Tests\Context\FeatureContext as WP_CLI_FeatureContext;
use RuntimeException;

final class FeatureContext extends WP_CLI_FeatureContext {

	/**
	 * @Given a WP install(ation) with the AMP plugin
	 */
	public function given_a_wp_installation_with_the_amp_plugin() {
		$this->install_wp();

		$amp_plugin_dir = $this->variables['RUN_DIR'] . '/wp-content/plugins/amp';

		$this->ensure_dir_exists( $amp_plugin_dir );

		self::copy_dir( realpath( self::get_vendor_dir() . '/../' ), $amp_plugin_dir );

		$this->proc( 'rm -rf vendor/autoload.php vendor/composer/autoload_*', [], $amp_plugin_dir )->run_check();
		$this->proc( 'composer dumpautoload', [], $amp_plugin_dir )->run_check();

		$this->proc( 'wp plugin activate amp' )->run_check();
	}

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
