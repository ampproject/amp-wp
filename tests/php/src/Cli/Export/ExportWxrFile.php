<?php
/**
 * Reference site export WXR file.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;
use Exception;
use Export_Command;
use WP_CLI;
use WP_Export_File_Writer;
use WP_Export_Query;
use WP_Export_WXR_Formatter;

final class ExportWxrFile implements ExportStep {

	/**
	 * Name of the reference site.
	 *
	 * @var string
	 */
	private $site_name;

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$this->site_name = $export_result->get_site_name();

		$wxr_file = preg_replace(
			'/\.json$/',
			'.xml',
			$export_result->get_target_path()
		);

		$this->generate_wxr_file( $wxr_file );

		$filename = basename( $wxr_file );
		$export_result->add_step( 'import_wxr_file', compact( 'filename' ) );

		return $export_result;
	}

	/**
	 * Generate the WXR file and store it next to the target path of the site
	 * definition file.
	 *
	 * @param string $target_path Target path of the site definition file.
	 * @throws WP_CLI\ExitException If the export logic throws an exception.
	 */
	private function generate_wxr_file( $target_path ) {
		try {
			Export_Command::load_export_api();

			$export_query = new WP_Export_Query( [] );
			$formatter    = new WP_Export_WXR_Formatter( $export_query );
			$writer       = new WP_Export_File_Writer( $formatter, $target_path );

			add_filter( 'wp_get_attachment_url', [ $this, 'upload_media_file' ], 10, 2 );
			$writer->export();
			remove_filter( 'wp_get_attachment_url', [ $this, 'upload_media_file' ] );
		} catch ( Exception $exception ) {
			WP_CLI::error( $exception->getMessage() );
		}
	}

	/**
	 * Upload media files when retrieving attachment URLs.
	 *
	 * @param string $url           URL for the given attachment.
	 * @return string URL pointing to Google Storage.
	 */
	public function upload_media_file( $url ) {
		if ( 0 === strncmp( $url, 'gs://', 5 ) ) {
			return $url;
		}

		$uploader = new MediaFileUploader();
		return $uploader->upload( $this->site_name, $url );
	}
}
