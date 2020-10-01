<?php
/**
 * Reference site import class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use stdClass;
use WP_CLI;
use WP_Error;
use WP_Import;

final class ReferenceSiteImporter extends WP_Import {

	/**
	 * Performs post-import cleanup of files and the cache.
	 */
	public function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		WP_CLI::log( '' );
		WP_CLI::success( 'WXR file imported successfully.' );

		do_action( 'import_end' );
	}

	/**
	 * Downloads an image from the specified URL.
	 *
	 * Taken from the core media_sideload_image() function and
	 * modified to return an array of data instead of html.
	 *
	 * @since 1.0.10
	 *
	 * @param string $file The image file path.
	 * @return array An array of image data.
	 */
	public static function sideload_image( $file ) {
		$data = new stdClass();

		if ( empty( $file ) ) {
			WP_CLI::warning(
				WP_CLI::colorize(
					'Provided empty image filename to download, skipping.'
				)
			);

			return $data;
		}

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|svg|gif|png)\b/i', $file, $matches );
		$url_filename           = basename( wp_parse_url( $file, PHP_URL_PATH ) );
		$file_array             = [];
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = wp_tempnam( $url_filename );

		// Download file to temp location.
		$context_options = [
			'ssl' => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		];
		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- Needed for stream wrapper support.
			$file_array['tmp_name'],
			file_get_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Needed for stream wrapper support.
				$file,
				false,
				stream_context_create( $context_options )
			)
		);

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, 0 );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			unlink( $file_array['tmp_name'] );
			WP_CLI::warning(
				WP_CLI::colorize(
					"Could not sideload image %G'{$file}'%n into the media library - {$id->get_error_message()}"
				)
			);

			return $id;
		}

		wp_cache_flush();

		// Build the object to return.
		$meta                = wp_get_attachment_metadata( $id );
		$data->attachment_id = $id;
		$data->url           = wp_get_attachment_url( $id );
		$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
		$data->height        = isset( $meta['height'] ) ? $meta['height'] : '';
		$data->width         = isset( $meta['width'] ) ? $meta['width'] : '';

		return $data;
	}

	/**
	 * Checks to see whether a string is an image url or not.
	 *
	 * @since 1.0.10
	 *
	 * @param string $string The string to check.
	 * @return bool Whether the string is an image url or not.
	 */
	public static function is_image_url( $string = '' ) {
		if ( ! is_string( $string ) ) {
			return false;
		}

		if ( ! preg_match( '/\.(jpg|jpeg|svg|png|gif)$/i', $string ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	public function fetch_remote_file( $url, $post ) {
		if ( ! $this->is_google_storage_url( $url ) ) {
			return parent::fetch_remote_file( $url, $post );
		}

		// Extract the file name from the URL.
		$file_name = basename( wp_parse_url( $url, PHP_URL_PATH ) );

		if ( ! $file_name ) {
			$file_name = md5( $url );
		}

		$tmp_file_name = wp_tempnam( $file_name );
		if ( ! $tmp_file_name ) {
			$message = __( 'Could not create temporary file.', 'amp' );
			WP_CLI::warning( $message );
			return new WP_Error( 'import_no_file', $message );
		}

		$context_options = [
			'ssl' => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		];

		$data = file_get_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Needed for stream wrapper support.
			$url,
			false,
			stream_context_create( $context_options )
		);

		if ( false === $data || empty( $data ) ) {
			$message = __( 'Could not download file from Google Storage: ', 'amp' ) . $url;
			WP_CLI::warning( $message );
			return new WP_Error( 'import_no_gs_download', $message );
		}

		file_put_contents( $tmp_file_name, $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents -- Needed for stream wrapper support.

		// Handle the upload like _wp_handle_upload() does.
		$wp_filetype     = wp_check_filetype_and_ext( $tmp_file_name, $file_name );
		$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
		$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
		$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

		// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect.
		if ( $proper_filename ) {
			$file_name = $proper_filename;
		}

		if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
			return new WP_Error( 'import_file_error', __( 'Sorry, this file type is not permitted for security reasons.', 'amp' ) );
		}

		$uploads = wp_upload_dir( $post['upload_date'] );
		if ( ! ( $uploads && false === $uploads['error'] ) ) {
			return new WP_Error( 'upload_dir_error', $uploads['error'] );
		}

		// Move the file to the uploads dir.
		$file_name     = wp_unique_filename( $uploads['path'], $file_name );
		$new_file      = $uploads['path'] . "/$file_name";
		$move_new_file = copy( $tmp_file_name, $new_file );

		if ( ! $move_new_file ) {
			@unlink( $tmp_file_name ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Quietly clean up left-overs.
			return new WP_Error( 'import_file_error', __( 'The uploaded file could not be moved', 'amp' ) );
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		chmod( $new_file, $perms );

		$upload = [
			'file'  => $new_file,
			'url'   => "{$uploads['url']}/{$file_name}",
			'type'  => $wp_filetype['type'],
			'error' => false,
		];

		// Keep track of the old and new urls so we can substitute them later.
		$this->url_remap[ $url ]          = $upload['url'];
		$this->url_remap[ $post['guid'] ] = $upload['url'];

		return $upload;
	}

	/**
	 * Check whether the provided URL is pointing to Google Storage.
	 *
	 * @param string $url URL to check.
	 * @return bool Whether this is a Google Storage URL.
	 */
	private function is_google_storage_url( $url ) {
		return 0 === strncmp( $url, 'gs://', 5 );
	}
}
