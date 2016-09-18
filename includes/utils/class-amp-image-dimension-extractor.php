<?php

class AMP_Image_Dimension_Extractor {
	static $callbacks_registered = false;
    const STATUS_FAILED_LAST_ATTEMPT = 'failed';
    const STATUS_IMAGE_EXTRACTION_FAILED= 'failed';

    static public function extract( $urls ) {
		if ( ! self::$callbacks_registered ) {
			self::register_callbacks();
		}

		foreach ($urls as &$url) {
            $url = self::normalize_url($url);
        }

		$dimensions = apply_filters( 'amp_extract_image_dimensions', $urls );

        if ( has_filter('amp_extract_image_dimensions') ) {
            return $dimensions;
        } else {
            return self::modify_return_value_for_tests_that_disable_extraction_filter($dimensions);
        }
	}

    /**
     * Account for the possibility of the amp_extract_image_dimensions filter being removed (see AMP_Img_Sanitizer_Test)
     *
     * Not surprisingly, the functions invoked by the amp_extract_image_dimensions filter modify the value returned by
     * AMP_Image_Dimension_Extractor::extract. For that reason, the return value has to be massaged before being returned
     * when the amp_extract_image_dimensions filter is removed via tests.
     *
     * @param array $urls A list or urls that would have had dimensions fetched had amp_extract_image_dimensions filter not been removed
     */
	private static function modify_return_value_for_tests_that_disable_extraction_filter($urls) {
	    $dimensions = array();
        foreach ( $urls as $url ) {
            $dimensions[$url] = false;
        }
        return $dimensions;
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

		$parsed = parse_url( $url );
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

		add_filter( 'amp_extract_image_dimensions', array( __CLASS__, 'extract_by_downloading_images' ), 999, 1 );

		do_action( 'amp_extract_image_dimensions_callbacks_registered' );
	}

    /**
     * Extract dimensions from downloaded images (or transient/cached dimensions from downloaded images)
     *
     * @param array $urls Image urls
     * @return array Dimensions mapped to image urls, or false if they could not be retrieved
     */
	public static function extract_by_downloading_images($urls, $mode = 'concurrent') {
        $transient_expiration = 30 * DAY_IN_SECONDS;

        $urls_to_fetch = array();
        $all_dimensions = array();
        $images = array();

        self::determine_which_images_to_fetch($urls, $urls_to_fetch, $all_dimensions);
        self::fetch_images($urls_to_fetch, $images, $mode);
        self::process_fetched_images($urls_to_fetch, $images, $all_dimensions, $transient_expiration);

        return $all_dimensions;
	}

    /**
     * Determine which images to fetch by checking for dimensions in transient/cache.
     * Creates a short lived transient that acts as a semaphore so that another visitor
     * doesn't trigger a remote fetch for the same image at the same time.
     *
     * @param array $urls Urls of image src
     * @param array $urls_to_fetch Urls of images to fetch because dimensions are not in transient/cache
     * @param array $all_dimensions "Master" list of img url to dimension mappings - used here to track which dimensions couldn't be retrieved
     */
	private static function determine_which_images_to_fetch(&$urls, &$urls_to_fetch, &$all_dimensions) {
        foreach ( $urls as $url ) {
            $url_hash = md5( $url );
            $transient_name = sprintf( 'amp_img_%s', $url_hash );
            $dimensions = get_transient( $transient_name );

            if ( is_array( $dimensions ) ) {
                $all_dimensions[$url] = array(
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                );
            } else {
                if ( self::STATUS_FAILED_LAST_ATTEMPT === $dimensions ) {
                    // If we couldn't get dimensions for this image the last time we tried, don't try again until the transient expires
                    $all_dimensions[$url] = false;
                    continue;
                } else {
                    // Ensure we're the only one trying to extract dimensions for this image at the moment so we don't duplicate requests
                    $transient_lock_name = sprintf( 'amp_lock_%s', $url_hash );
                    if ( false !== get_transient( $transient_lock_name ) ) {
                        $all_dimensions[$url] = false;
                        continue;
                    } else {
                        $urls_to_fetch[$url]['url'] = $url;
                        $urls_to_fetch[$url]['transient_name'] = $transient_name;
                        $urls_to_fetch[$url]['transient_lock_name'] = $transient_lock_name;
                        set_transient($transient_lock_name, 1, MINUTE_IN_SECONDS);
                    }
                }
            }
        }
    }

    /**
     * Fetch dimensions of remote images
     *
     * @param array $urls_to_fetch Image src urls to fetch
     * @param array $images Array to populate with results of image/dimension inspection
     */
    private static function fetch_images($urls_to_fetch, &$images, $mode) {
        //User Faster Image when PHP version supports it (it contains a closure that could not be ported to 5.2)
        if ( $mode === 'synchronous' || strnatcmp( phpversion(), '5.3.0' ) < 0 ) {
            self::fetch_images_via_fast_image( $urls_to_fetch, $images );
        } else {
            self::fetch_images_via_faster_image( $urls_to_fetch, $images );
        }
    }

    /**
     * Fetch images via FastImage library
     *
     * @param array $urls_to_fetch Image src urls to fetch
     * @param array $images Array to populate with results of image/dimension inspection
     */
    private static function fetch_images_via_fast_image( $urls_to_fetch, &$images ) {
        require_once( AMP__DIR__ . '/includes/lib/class-fastimage.php' );
        $image = new FastImage();
        $urls = array();
        // array_column doesn't exist in PHP 5.2
        foreach ( $urls_to_fetch as $key => $value) {
            $urls[] = $key;
        }
        foreach ( $urls as $url ) {
            $result = $image->load($url);
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
     * @param array $urls_to_fetch Image src urls to fetch
     * @param array $images Array to populate with results of image/dimension inspection
     */
    private static function fetch_images_via_faster_image( $urls_to_fetch, &$images ) {
        if ( ! class_exists( 'Faster_Image_B52f1a8_Faster_Image' ) ) {
            require_once( AMP__DIR__ . '/includes/lib/class-faster-image-b52f1a8-faster-image.php' );
        }
        $client = new Faster_Image_B52f1a8_Faster_Image();
        $images = $client->batch( array_column( $urls_to_fetch, 'url' ) );
    }

    /**
     * Determine success or failure of remote fetch, integrate fetched dimensions into url to dimension mapping,
     * cache fetched dimensions via transient and release/delete semaphore transient
     *
     * @param array $urls_to_fetch List of image urls that were fetched and transient names corresponding to each (for unlocking semaphore, setting "real" transient)
     * @param array $images Results of remote fetch mapping fetched image url to dimensions
     * @param array $all_dimensions "Master" map of image url to dimensions to be updated with results of remote fetch
     * @param int $transient_expiration Duration image dimensions should exist in transient/cache
     */
    private static function process_fetched_images($urls_to_fetch, $images, &$all_dimensions, $transient_expiration) {
        foreach ( $urls_to_fetch as $url_data) {
            $image_data = $images[$url_data['url']];
            if ( self::STATUS_IMAGE_EXTRACTION_FAILED === $image_data['size'] ) {
                $all_dimensions[$url_data['url']] = false;
                set_transient( $url_data['transient_name'], self::STATUS_FAILED_LAST_ATTEMPT, $transient_expiration );
            } else {
                $all_dimensions[$url_data['url']] = array(
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
}
