<?php
/**
 * Reference site import class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use AmpProject\AmpWP\Tests\Cli\Export;
use AmpProject\AmpWP\Tests\Cli\Export\ExportResult;
use Exception;
use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Utils;

final class ReferenceSiteExportCommand extends WP_CLI_Command {

	/**
	 * List of export steps to process.
	 *
	 * @var string
	 */
	const EXPORT_STEPS = [
		Export\ExportActiveThemes::class,
		Export\ExportActivePlugins::class,
		Export\ExportWxrFile::class,
		Export\ExportOptions::class,
		Export\ExportThemeMods::class,
		Export\ExportCustomizerSettings::class,
		Export\ExportWidgets::class,
	];

	/**
	 * Exports content from a current site into a reference site definition.
	 *
	 * Uses the WordPress Importer plugin behind the scenes for performing data
	 * migrations.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the reference site definition to export into.
	 *
	 * [--force]
	 * : Whether to force writing into the provided site definition file.
	 *
	 * ## EXAMPLES
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		try {
			( new WpImporterCompat() )->prepare_export();
		} catch ( Exception $exception ) {
			WP_CLI::error( 'Unable to export reference site: ' . $exception->getMessage() );
		}

		list( $site_definition_file ) = $args;
		$force                        = Utils\get_flag_value( $assoc_args, 'force', false );

		if ( 0 !== substr_compare( $site_definition_file, '.json', -5 ) ) {
			$site_definition_file .= '.json';
		}

		if ( ! path_is_absolute( $site_definition_file ) ) {
			$site_definition_file = ReferenceSiteCommandNamespace::REFERENCE_SITES_ROOT . $site_definition_file;
		}

		if ( ! $force && file_exists( $site_definition_file ) ) {
			WP_CLI::error( "The provided site definition file '{$site_definition_file}' already exists. Use --force if you want to overwrite it." );
		}

		WP_CLI::log( 'Starting the reference site export process...' );

		$this->export_site( $site_definition_file );
	}

	/**
	 * Export the reference site into the provided site definition file.
	 *
	 * @param string $site_definition_file Site definition file to export into.
	 */
	private function export_site( $site_definition_file ) {
		$export_result = new ExportResult( $site_definition_file );

		foreach ( self::EXPORT_STEPS as $export_step_class ) {
			/** @var ExportStep $export_step */
			$export_step   = new $export_step_class();
			$export_result = $export_step->process( $export_result );
		}

		file_put_contents( $site_definition_file, $export_result->to_json() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- Needed for stream wrapper support.
	}
}
