<?php
/**
 * Media file uploader.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use Exception;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use WP_CLI;

final class MediaFileUploader {

	/**
	 * Name of the Google Storage bucket to upload to.
	 *
	 * @var string
	 */
	const MEDIA_FILES_BUCKET = 'ampwp_reference_sites_media_files';

	/**
	 * Google Storage client instance to use.
	 *
	 * @var StorageClient
	 */
	private static $storage;

	/**
	 * Google Storage bucket to use.
	 *
	 * @var Bucket
	 */
	private static $bucket;

	/**
	 * Initialize the media uploader.
	 */
	public function init() {
		if ( null !== self::$storage ) {
			return;
		}

		self::$storage = new StorageClient();
		self::$bucket  = self::$storage->bucket( self::MEDIA_FILES_BUCKET );
	}

	/**
	 * Upload a media file to Google Storage.
	 *
	 * @param string $site_name Name of the reference site to upload the file for.
	 * @param string $url       URL of the media file to upload.
	 * @return string URL of the media file on Google Storage.
	 */
	public function upload( $site_name, $url ) {
		$this->init();

		$context_options = [
			'ssl' => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		];

		try {
			$old_url = $url;

			$media_file = file_get_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Needed for stream wrapper support.
				$old_url,
				false,
				stream_context_create( $context_options )
			);

			$storage_object = self::$bucket->upload(
				$media_file,
				[ 'name' => $site_name . '/' . basename( $url ) ]
			);

			$url = $storage_object->gcsUri();
			WP_CLI::log( "Uploaded: {$old_url} => {$url}" );

		} catch ( Exception $exception ) {
			WP_CLI::error( "Failed to upload media file '{$old_url}' to Google Storage: {$exception->getMessage()}" );
		}

		return $url;
	}
}
