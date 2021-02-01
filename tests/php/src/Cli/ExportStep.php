<?php
/**
 * ExportStep interface.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use AmpProject\AmpWP\Tests\Cli\Export\ExportResult;

interface ExportStep {

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result );
}
