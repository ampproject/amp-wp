<?php
/**
 * Media file uploader.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

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
	 * @param string $filepath File path of the media file to upload.
	 * @return string URL of the media file on Google Storage.
	 */
	public function upload( $filepath ) {
		$storage_object = self::$bucket->upload( fopen( $filepath, 'rb' ) );
		return $storage_object->gcsUri();
	}
}
