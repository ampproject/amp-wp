<?php

class AMP_Image_Dimension_Extractor {
	static $callbacks_registered = false;

	static public function extract( $url ) {
		if ( ! self::$callbacks_registered ) {
			self::register_callbacks();
		}

		return apply_filters( 'amp_extract_image_dimensions', false, $url );
	}

	private static function register_callbacks() {
		self::$callbacks_registered = true;

		add_filter( 'amp_extract_image_dimensions', array( __CLASS__, 'extract_from_filename' ), 10, 2 );
		add_filter( 'amp_extract_image_dimensions', array( __CLASS__, 'extract_from_attachment_metadata' ), 10, 2 );
	}

	public static function extract_from_filename( $dimensions, $url ) {
		if ( $dimensions ) {
			return $dimensions;
		}

		$path = parse_url( $url, PHP_URL_PATH );
		$filename = basename( $path );
		if ( ! $filename ) {
			return false;
		}

		$result = preg_match( '~-(\d+)x(\d+)\.(jpg|jpeg|png)$~i', $filename, $matches );
		if ( ! $result ) {
			return false;
		}

		return array( $matches[1], $matches[2] );
	}

	public static function extract_from_attachment_metadata( $dimensions, $url ) {
		if ( $dimensions ) {
			return $dimensions;
		}

		$url = strtok( $url, '?' );
		$attachment_id = attachment_url_to_postid( $url );
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! $metadata ) {
			return false;
		}

		return array( $metadata['width'], $metadata['height'] );
	}
}
