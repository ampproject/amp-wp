<?php
/**
 * Reference site export active themes.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportActiveThemes implements ExportStep {

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$active_themes = $this->get_active_theme();

		foreach ( $active_themes as $plugin ) {
			$export_result->add_step( 'activate_theme', compact( 'theme' ) );
		}

		return $export_result;
	}

	/**
	 * Get the list of currently active themes.
	 *
	 * @return string[] Array of currently active themes.
	 */
	private function get_active_themes() {
		return array_map( static function ( $plugin ) {
			$filename = basename( $plugin );
			return preg_replace( '/\.php$/', '', $filename );
		}, get_option( 'active_themes', [] ) );
	}

	/**
	 * Skip the themes that are marked as excluded.
	 *
	 * @param string $active_plugin Active plugin to check.
	 * @return bool Whether to skip the active plugin.
	 */
	private function skip_excluded_themes( $active_plugin ) {
		return ! in_array( $active_plugin, self::EXCLUDED_PLUGINS, true );
	}
}
