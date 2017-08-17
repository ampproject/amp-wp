<?php

class AMP_Image_Dimension_Extractor {
	static $callbacks_registered = false;
	const STATUS_FAILED_LAST_ATTEMPT = 'failed';
	const STATUS_IMAGE_EXTRACTION_FAILED = 'failed';

	static public function extract( $urls ) {
		if ( ! self::$callbacks_registered ) {
			self::register_callbacks();
		}

		$return_dimensions = array();

		// Normalize URLs and also track a map of normalized-to-original as we'll need it to reformat things when returning the data.
		$url_map = array();
		$normalized_urls = array();
		foreach ( $urls as $original_url ) {
			$normalized_url = self::normalize_url( $original_url );
			if ( false !== $normalized_url ) {
				$url_map[ $normalized_url ] = $original_url;
				$normalized_urls[] = $normalized_url;
			} else {
				// This is not a URL we can extract dimensions from, so default to false.
				$return_dimensions[ $original_url ] = false;
			}
		}

		$extracted_dimensions = array_fill_keys( $normalized_urls, false );
		$extracted_dimensions = apply_filters( 'amp_extract_image_dimensions_batch', $extracted_dimensions );

		// We need to return a map with the original (un-normalized URL) as we that to match nodes that need dimensions.
		foreach ( $extracted_dimensions as $normalized_url => $dimension ) {
			$original_url = $url_map[ $normalized_url ];
			$return_dimensions[ $original_url ] = $dimension;
		}

		return $return_dimensions;
	}

	public static function normalize_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		if ( 0 === strpos( $url, 'data:' ) ) {
			return false;
		}

		if ( 0 === strpos( $url, '//' ) ) {
			return set_url_scheme( $url, 'http' );
		}

		$parsed = AMP_WP_Utils::parse_url( $url );
		if ( ! isset( $parsed['host'] ) ) {
			$path = '';
			if ( isset( $parsed['path'] ) ) {
				$path .= $parsed['path'];
			}
			if ( isset( $parsed['query'] ) ) {
				$path .= '?' . $parsed['query'];
			}
			$url = site_url( $path );
		}

		return $url;
	}

	private static function register_callbacks() {
		self::$callbacks_registered = true;

		add_filter( 'amp_extract_image_dimensions_batch', array( __CLASS__, 'extract_by_downloading_images' ), 999, 1 );

		do_action( 'amp_extract_image_dimensions_batch_callbacks_registered' );
	}

	/**
	 * Extract dimensions from downloaded images (or transient/cached dimensions from downloaded images)
	 *
	 * @param array  $dimensions Image urls mapped to dimensions.
	 * @param string $mode Whether image dimensions should be extracted concurrently or synchronously.
	 * @return array Dimensions mapped to image urls, or false if they could not be retrieved
	 */
	public static function extract_by_downloading_images( $dimensions, $mode = 'concurrent' ) {
		$transient_expiration = 30 * DAY_IN_SECONDS;

		$urls_to_fetch = array();
		$images = array();

		self::determine_which_images_to_fetch( $dimensions, $urls_to_fetch );
		self::fetch_images( $urls_to_fetch, $images, $mode );
		self::process_fetched_images( $urls_to_fetch, $images, $dimensions, $transient_expiration );

		return $dimensions;
	}

	/**
	 * Determine which images to fetch by checking for dimensions in transient/cache.
	 * Creates a short lived transient that acts as a semaphore so that another visitor
	 * doesn't trigger a remote fetch for the same image at the same time.
	 *
	 * @param array $dimensions Image urls mapped to dimensions.
	 * @param array $urls_to_fetch Urls of images to fetch because dimensions are not in transient/cache.
	 */
	private static function determine_which_images_to_fetch( &$dimensions, &$urls_to_fetch ) {
		foreach ( $dimensions as $url => $value ) {

			// Check whether some other callback attached to the filter already provided dimensions for this image.
			if ( is_array( $value ) ) {
				continue;
			}

			$url_hash = md5( $url );
			$transient_name = sprintf( 'amp_img_%s', $url_hash );
			$cached_dimensions = get_transient( $transient_name );

			// If we're able to retrieve the dimensions from a transient, set them and move on.
			if ( is_array( $cached_dimensions ) ) {
				$dimensions[ $url ] = array(
					'width' => $cached_dimensions[0],
					'height' => $cached_dimensions[1],
				);
				continue;
			}

			// If the value in the transient reflects we couldn't get dimensions for this image the last time we tried, move on.
			if ( self::STATUS_FAILED_LAST_ATTEMPT === $cached_dimensions ) {
				$dimensions[ $url ] = false;
				continue;
			}

			$transient_lock_name = sprintf( 'amp_lock_%s', $url_hash );

			// If somebody is already trying to extract dimensions for this transient right now, move on.
			if ( false !== get_transient( $transient_lock_name ) ) {
				$dimensions[ $url ] = false;
				continue;
			}

			// Include the image as a url to fetch.
			$urls_to_fetch[ $url ] = array();
			$urls_to_fetch[ $url ]['url'] = $url;
			$urls_to_fetch[ $url ]['transient_name'] = $transient_name;
			$urls_to_fetch[ $url ]['transient_lock_name'] = $transient_lock_name;
			set_transient( $transient_lock_name, 1, MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Fetch dimensions of remote images
	 *
	 * @param array  $urls_to_fetch Image src urls to fetch.
	 * @param array  $images Array to populate with results of image/dimension inspection.
	 * @param string $mode Whether image dimensions should be extracted concurrently or synchronously.
	 */
	private static function fetch_images( $urls_to_fetch, &$images, $mode ) {
		// Use FasterImage when for compatible PHP versions
		if ( 'synchronous' === $mode ||
			false === function_exists( 'curl_multi_exec' ) ||
			version_compare( PHP_VERSION, '5.4.0' ) < 0
		) {
			self::fetch_images_via_fast_image( $urls_to_fetch, $images );
		} else {
			self::fetch_images_via_faster_image( $urls_to_fetch, $images );
		}
	}

	/**
	 * Fetch images via FastImage library
	 *
	 * @param array $urls_to_fetch Image src urls to fetch.
	 * @param array $images Array to populate with results of image/dimension inspection.
	 */
	private static function fetch_images_via_fast_image( $urls_to_fetch, &$images ) {
		if ( ! class_exists( 'FastImage' ) ) {
			require_once( AMP__DIR__ . '/includes/lib/fastimage/class-fastimage.php' );
		}

		$image = new FastImage();
		$urls = array_keys( $urls_to_fetch );

		foreach ( $urls as $url ) {
			$result = $image->load( $url );
			if ( false === $result ) {
				$images[ $url ]['size'] = self::STATUS_IMAGE_EXTRACTION_FAILED;
			} else {
				$size = $image->getSize();
				$images[ $url ]['size'] = $size;
			}
		}
	}

	/**
	 * Fetch images via FasterImage library
	 *
	 * @param array $urls_to_fetch Image src urls to fetch.
	 * @param array $images Array to populate with results of image/dimension inspection.
	 */
	private static function fetch_images_via_faster_image( $urls_to_fetch, &$images ) {
		$urls = array_keys( $urls_to_fetch );

		if ( ! function_exists( 'amp_get_fasterimage_client' ) ) {
			require_once( AMP__DIR__ . '/includes/lib/fasterimage/amp-fasterimage.php' );
		}

		$user_agent = apply_filters( 'amp_extract_image_dimensions_get_user_agent', self::get_default_user_agent() );
		$client = amp_get_fasterimage_client( $user_agent );
		$images = $client->batch( $urls );
	}

	/**
	 * Determine success or failure of remote fetch, integrate fetched dimensions into url to dimension mapping,
	 * cache fetched dimensions via transient and release/delete semaphore transient
	 *
	 * @param array $urls_to_fetch List of image urls that were fetched and transient names corresponding to each (for unlocking semaphore, setting "real" transient).
	 * @param array $images Results of remote fetch mapping fetched image url to dimensions.
	 * @param array $dimensions Map of image url to dimensions to be updated with results of remote fetch.
	 * @param int   $transient_expiration Duration image dimensions should exist in transient/cache.
	 */
	private static function process_fetched_images( $urls_to_fetch, $images, &$dimensions, $transient_expiration ) {
		foreach ( $urls_to_fetch as $url_data ) {
			$image_data = $images[ $url_data['url'] ];
			if ( self::STATUS_IMAGE_EXTRACTION_FAILED === $image_data['size'] ) {
				$dimensions[ $url_data['url'] ] = false;
				set_transient( $url_data['transient_name'], self::STATUS_FAILED_LAST_ATTEMPT, $transient_expiration );
			} else {
				$dimensions[ $url_data['url'] ] = array(
					'width' => $image_data['size'][0],
					'height' => $image_data['size'][1],
				);
				set_transient(
					$url_data['transient_name'],
					array(
						$image_data['size'][0],
						$image_data['size'][1],
					),
					$transient_expiration
				);
			}
			delete_transient( $url_data['transient_lock_name'] );
		}
	}


	/**
	 * Get default user agent
	 *
	 * @return string
	 */
	public static function get_default_user_agent() {
		return 'amp-wp, v' . AMP__VERSION . ', ' . get_site_url();
	}
}
