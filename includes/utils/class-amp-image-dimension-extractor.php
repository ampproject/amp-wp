<?php

class AMP_Image_Dimension_Extractor {
	static public function extract( $url ) {
		$dimensions = self::extract_from_filename( $url );
		if ( $dimensions ) {
			return $dimensions;
		}

		$dimensions = self::extract_from_attachment_metadata( $url );
		if ( $dimensions ) {
			return $dimensions;
		}

		return false;
	}

	public static function extract_from_filename( $url ) {
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

	public static function extract_from_attachment_metadata( $url ) {
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
