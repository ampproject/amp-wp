<?php
/**
 * Reference site export active plugins.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportActivePlugins implements ExportStep {

	/**
	 * List of plugin slugs to exclude.
	 *
	 * @var string[]
	 */
	const EXCLUDED_PLUGINS = [
		'airplane-mode',
		'amp',
		'hello',
		'query-monitor',
		'redis-cache',
		'wp-rocket',
	];

	/**
	 * Name of the environment variable to use for excluding additional plugins.
	 *
	 * @var string
	 */
	const EXCLUDED_PLUGINS_ENV_VARIABLE = 'AMP_REF_SITE_EXCLUDED_PLUGINS';

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$active_plugins = $this->get_active_plugins();

		$active_plugins = array_filter(
			$active_plugins,
			[ $this, 'skip_excluded_plugins' ]
		);

		foreach ( $active_plugins as $plugin ) {
			$export_result->add_step( 'activate_plugin', compact( 'plugin' ) );
		}

		return $export_result;
	}

	/**
	 * Get the list of currently active plugins.
	 *
	 * @return string[] Array of currently active plugins.
	 */
	private function get_active_plugins() {
		return array_map(
			static function ( $plugin ) {
				$filename = strtok( $plugin, '/' );
				return preg_replace( '/\.php$/', '', $filename );
			},
			get_option( 'active_plugins', [] )
		);
	}

	/**
	 * Skip the plugins that are marked as excluded.
	 *
	 * @param string $active_plugin Active plugin to check.
	 * @return bool Whether to skip the active plugin.
	 */
	private function skip_excluded_plugins( $active_plugin ) {
		static $excluded_plugins = null;

		if ( null === $excluded_plugins ) {
			$excluded_plugins = array_merge(
				self::EXCLUDED_PLUGINS,
				array_filter( (array) getenv( self::EXCLUDED_PLUGINS_ENV_VARIABLE ) )
			);
		}

		return ! in_array( $active_plugin, $excluded_plugins, true );
	}
}
