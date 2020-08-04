<?php
/**
 * Reference site export options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportCustomizerSettings implements ExportStep {

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$settings = [];

		$astra_settings = get_option( 'astra-settings' );
		if ( ! empty( $astra_settings ) ) {
			$settings['astra-settings'] = $astra_settings;
		}

		$custom_css = wp_get_custom_css();
		if ( ! empty( $custom_css ) ) {
			$settings['custom-css'] = $custom_css;
		}

		$export_result->add_step( 'import_customizer_settings', compact( 'settings' ) );

		return $export_result;
	}
}
