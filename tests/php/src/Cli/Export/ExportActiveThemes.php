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
		$active_theme = wp_get_theme();
		$child_theme  = $active_theme->get_stylesheet();
		$parent_theme = $active_theme->get_template();

		if ( $parent_theme !== $child_theme ) {
			$export_result->add_step( 'install_theme', [ 'theme' => $parent_theme ] );
		}

		$export_result->add_step( 'activate_theme', [ 'theme' => $child_theme ] );

		return $export_result;
	}
}
