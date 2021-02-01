<?php
/**
 * Reference site export widgets.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportWidgets implements ExportStep {

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$exported_widgets = [];

		foreach ( wp_get_sidebars_widgets() as $sidebar => $widgets ) {
			$exported_widgets[ $sidebar ] = [];
			foreach ( $widgets as $widget_instance_id ) {
				// Get id_base (remove -# from end) and instance ID number.
				$id_base        = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance       = preg_replace( '/^' . preg_quote( $id_base, '/' ) . '-/', '', $widget_instance_id );
				$widget_options = get_option( 'widget_' . $id_base, [] );
				$exported_widgets[ $sidebar ][ $widget_instance_id ] = $widget_options[ (int) $instance ];
			}
		}

		$export_result->add_step( 'import_widgets', [ 'widgets' => $exported_widgets ] );

		return $export_result;
	}
}
