<?php
/**
 * Reference site import class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use stdClass;
use WP_CLI;
use WP_Import;

final class ReferenceSiteImporter extends WP_Import {

	/**
	 * Performs post-import cleanup of files and the cache
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

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		if ( ! empty( $file ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|svg|gif|png)\b/i', $file, $matches );
			$file_array         = array();
			$file_array['name'] = basename( $matches[0] );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, 0 );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				unlink( $file_array['tmp_name'] );
				return $id;
			}

			// Build the object to return.
			$meta                = wp_get_attachment_metadata( $id );
			$data->attachment_id = $id;
			$data->url           = wp_get_attachment_url( $id );
			$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
			$data->height        = isset( $meta['height'] ) ? $meta['height'] : '';
			$data->width         = isset( $meta['width'] ) ? $meta['width'] : '';
		}

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
		if ( is_string( $string ) ) {

			if ( preg_match( '/\.(jpg|jpeg|svg|png|gif)/i', $string ) ) {
				return true;
			}
		}

		return false;
	}
}
