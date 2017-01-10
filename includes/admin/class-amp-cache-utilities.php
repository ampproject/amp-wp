<?php
/**
 * In addition to handling updates to the AMP cache when a post is created
 * updated, or deleted this class is meant to be a general toolbox for the AMP cache
 * and this has a number of utility functions for use with the AMP Cache.
 *
 */
class AMP_Cache_Utilities {

	/**
	 * These are the valid types of content that can be cached:
	 * 
	 * 'c' - content (a post)
	 * 'i' - an image
	 * 'r' - a resource (like a font)
	 *
	 * See: https://developers.google.com/amp/cache/overview
	 */
	public static $amp_valid_content_types = array( 'c', 'i', 'r' );

	/**
	 * This is the base URL of the AMP cache CDN.
	 */
	public static $amp_cache_url_base = 'https://cdn.ampproject.org';

	/**
	 * This is the base URL of the AMP cache ping-update URL
	 */
	public static $amp_cache_update_url_base = 'https://cdn.ampproject.org/update-ping';

	/**
	 * This keeps track of any posts that were about to be deleted so we
	 * can actually run the ping *after* they were deleted.
	 */
	private $deferred_permalinks_to_update = array();

	/**
	 * Hooks the 'post_updated' and 'before_delete_post' actions to determine if we
	 * need to update the AMP Cache when a post is updated or about to be deleted.
	 */
	public function amp_add_cache_update_actions() {
		// Skip AMP Cache updates when importing.
		if ( defined( 'WP_IMPORTING' ) && ( true === WP_IMPORTING ) ) {
			return;
		}

		// Hooking this to the post_updated action so this will fire any time a post
		// is updated in any way (including status transitions).
		add_action( 'post_updated',  array( $this, 'post_updated' ), 10, 3 );
		
		// Hooking the call to update the cache *before* the post is updated in case we need
		// to access any metadata for future functionality.
		add_action( 'before_delete_post',  array( $this, 'before_delete_post' ) );
	}

	/**
	 * Called from the 'post_updated' hook when a post is updated. This hook is also fired when
	 * a post's status changes. If the post is currently published or was not published and now is, 
	 * then we need to update the cache.
	 * 
	 * This function will return true if the cache was updated successfully. It returns
	 * false if the cache did not need to be updated *or* if the update failed for any
	 * reason.
	 * 
	 * @param  int/WP_Post 	$post The post that was being updated
	 * @param  WP_Post 		$post_after  A copy of the post after the update.
	 * @param  WP_Post		$post_before A copy of the post before the update.
	 * @return bool         true if the cache was updated, false otherwise.
	 */
	public function post_updated( $post, $post_after, $post_before ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		// don't ping cache if amp isn't supported on this post
		if ( ! post_supports_amp( $post ) ) {
			return false;
		}

		// Don't update cache if post was not previously published
		// and is still not published
		if ( ( 'publish' != $post_before->post_status ) &&
			( 'publish' != $post_after->post_status ) ) {
			return false;
		}

		// Don't update cache if post *was* previously published
		// and is still published. (ex. to fix a typo)
		if ( ( 'publish' == $post_before->post_status ) &&
			( 'publish' == $post_after->post_status ) ) {
			return false;
		}

		// Don't update cache if post was not previously published
		// but is now published
		if ( ( 'publish' != $post_before->post_status ) &&
			( 'publish' == $post_after->post_status ) ) {
			return false;
		}

		// This leaves us with the case where the post was previously
		// published and is now not published.
		
		// Ping the AMP Cache for this post.
		return self::do_amp_update_ping_for_post( $post );
	}

	/**
	 * Called form the 'before_delete_post' hook when a post is about to be deleted. If the
	 * post is currentrly published, then we will add this post's ping URL to a list of 
	 * urls to publish once they are actually deleted.
	 * 
	 * @param  int/WP_Post 	$post The post that was being deleted
	 * @return bool         true if this post was added to the $deferred_updates list, false otherwise.
	 */
	public function before_delete_post( $post ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		// don't ping cache if amp isn't supported on this post
		if ( ! post_supports_amp( $post ) ) {
			return false;
		}

		// don't ping if post is not published
		if ( 'publish' != $post->post_status ) {
			return false;
		}

		$permalink = get_permalink( $post );
		if ( $permalink ) {
			$this->deferred_permalinks_to_update[ $post->ID ] = get_permalink( $post );
			add_action( 'deleted_post', array( $this, 'do_deferred_update' ) );
		}
	}

	/**
	 * This is hooked from `deleted_post` so that the ping to the AMP Cache is made only
	 * after the post is actually deleted. Since the permalink no longer exists at this point,
	 * the permalink was added to the `deferred_permalinks_to_update` property on the
	 * `bewfore_delete_post` hook.
	 * 
	 * @param  int $post_id ID of the post that was deleted.
	 */
	public function do_deferred_update( $post_id ) {
		if ( isset( $this->deferred_permalinks_to_update[ $post_id ] ) ) {
			self::do_amp_update_ping_for_permalink( $this->deferred_permalinks_to_update[ $post_id ] );
			unset( $this->deferred_permalinks_to_update[ $post_id ] );
		}
	}

	/**
	 * Given a post_id or WP_Post object, this function will calculate the AMP
	 * cache update URL and ping it.
	 * 
	 * This function will return true if the cache was updated successfully. It returns
	 * false if the update failed for any reason.
	 *
	 * Note: This function assumes that $post supports AMP.
	 * 
	 * @param  int/WP_Post	$post 	The post to update in the AMP cache.
	 * @return bool       			true if the cache was updated, false otherwise.
	 */
	public static function do_amp_update_ping_for_post( $post ) {
		$post = get_post( $post );
		
		if ( ! $post ) {
			return false;
		}
				
		$update_ping_url = self::get_amp_cache_update_url_for_post( $post );
		if ( ! $update_ping_url ) {
			return false;
		}
		return self::do_amp_cache_update( $update_ping_url );
	}

	/**
	 * Given a permalink URL, this function will calculate the AMP
	 * cache update URL and ping it.
	 * 
	 * This function will return true if the cache was updated successfully. It returns
	 * false if the update failed for any reason.
	 *
	 * Note: This function assumes that $post supports AMP.
	 * 
	 * @param  int/WP_Post	$post 	The post to update in the AMP cache.
	 * @return bool       			true if the cache was updated, false otherwise.
	 */
	public static function do_amp_update_ping_for_permalink( $permalink ) {
		$update_ping_url = self::get_amp_cache_update_url_for_resource( $permalink, 'c' );
		if ( ! $update_ping_url ) {
			return false;
		}
		return self::do_amp_cache_update( $update_ping_url );
	}

	/**
	 * Perform an HTTP GET on the URL.
	 * @param  string $url 	The URL to ping.
	 * @return bool     	true if the wp_remote_get doesn't return an error
	 */
	private static function do_amp_cache_update( $url ) {
		$args = array(
			'timeout' => 1,
			'blocking' => false,
		);
		$response = wp_remote_get( $url, $args );
		return ( !is_wp_error( $response ) );
	}

	/**
	 * Given a post_id or WP_Post object, calculste the AMP cache URL based on
	 * the post's permalink.
	 *
	 * See: https://developers.google.com/amp/cache/overview
	 * 
	 * @param  int/WP_Post	$post 	The post to get rhe AMP cache url for.
	 * @return string/bool       	The AMP cache URL on success, false on failure.
	 */
	public static function get_amp_cache_url_for_post( $post ) {
		$amp_cache_resource_path = self::get_amp_cache_path_for_post( $post );
		if ( ! $amp_cache_resource_path ) {
			return false;
		}
		
		return self::$amp_cache_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
	}

	/**
	 * Given a post_id or WP_Post object, calculate the AMP cache update URL based
	 * on the post's permalink.
	 *
	 * See: https://developers.google.com/amp/cache/update-ping
	 *
	 * @param  int/WP_Post	$post 	The post to get rhe AMP cache update url for.
	 * @return string/bool       	The AMP cache URL on success, false on failure.
	 */
	public static function get_amp_cache_update_url_for_post( $post ) {
		$amp_cache_resource_path = self::get_amp_cache_path_for_post( $post );
		if ( ! $amp_cache_resource_path ) {
			return false;
		}
		
		return self::$amp_cache_update_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
	}

	/**
	 * Given any URL and content type, calculate the AMP cache URL for this resource.
	 * 
	 * @param  string 	$url     		The url to calculate the AMP cache URL from.
	 * @param  string 	$content_type 	Currently only supports 'c' for posts and 'i' for images.
	 * @param  string 	$scheme       	Optional. 'http' or 'https' If provided, this overrides the
	 *                                	scheme in the URL. If null, scheme is taken from the URL.
	 * @return string/bool              The AMP cache URL on success, false on failure.
	 */
	public static function get_amp_cache_url_for_resource( $url, $content_type, $scheme = null ) {
		$amp_cache_resource_path = self::get_amp_cache_path_for_url( $url, $content_type );
		if ( ! $amp_cache_resource_path ) {
			return false;
		}
		
		return self::$amp_cache_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
	}

	/**
	 * Given any URL and content type, calculate the AMP cache update URL for this resource.
	 * 
	 * @param  string 	$url     		The url to calculate the AMP cache update URL from.
	 * @param  string 	$content_type 	Currently only supports 'c' for posts and 'i' for images.
	 * @param  string 	$scheme       	Optional. 'http' or 'https' If provided, this overrides the
	 *                                	scheme in the URL. If null, scheme is taken from the URL.
	 * @return string/bool              The AMP cache udpate URL on success, false on failure.
	 */
	public static function get_amp_cache_update_url_for_resource( $url, $content_type, $scheme = null ) {
		$amp_cache_resource_path = self::get_amp_cache_path_for_url( $url, $content_type );
		if ( ! $amp_cache_resource_path ) {
			return false;
		}

		return self::$amp_cache_update_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
	}

	/**
	 * Given a post_id or WP_Post onbject, calculate the AMP cache path part (the part after 
	 * https://cdn.ampproject.org) for this post.
	 * 
	 * @param  int/WP_Post	$post 			The post to get rhe AMP cache path for.
	 * @param  string 		$content_type 	Currently only supports 'c' for posts and 'i' for images.
	 *                                 		Optional, if provided, this overrides the actual content
	 *                                 		type of the post. If null, the content type is determined
	 *                                 		by post_type.
	 * @param  string 	$scheme       		Optional. 'http' or 'https' If provided, this overrides the
	 *                                		scheme in the URL. If null, scheme is taken from the URL.
	 * @return string/bool              	The AMP cache path part on success, false on failure.
	 */
	public static function get_amp_cache_path_for_post( $post, $scheme = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( ! post_supports_amp( $post ) ) {
			return false;
		}

		// If permalink couldn't be retrieved, return failure.
		$permalink = get_permalink( $post );
		if ( false === $permalink ) {
			return false;
		}

		// assume that all supported posts types are 'c' content_type and
		// return the url
		return self::get_amp_cache_path_for_url( $permalink, 'c', $scheme );
	}

	/**
	 * Given a URL and content_type, calculate the AMP cache path part (the part after
	 * https://cdn.ampproject.org) for this url/content_type
	 * .
	 * @param  string 	$url     		The url to calculate the AMP cache path from.
	 * @param  string 	$content_type 	Currently only supports 'c' for posts and 'i' for images.
	 * @param  string 	$scheme       	Optional. 'http' or 'https' If provided, this overrides the
	 *                                	scheme in the URL. If null, scheme is taken from the URL.
	 * @return string/bool              The AMP cache path part on success, false on failure.
	 */
	public static function get_amp_cache_path_for_url( $url, $content_type , $scheme = null ) {
		$parsed_url = wp_parse_url( $url );
		// If permalink couldn't be parsed, then return failure.
		if ( false === $parsed_url ) {
			return false;
		}

		// If there is no host part to this URL, return failure.
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		// If there is no scheme specified in the parameter list and this is a protocol
		// relative URL, then we can't figure out whether this should be https or http.
		// We are assuming that a protocol relative URL will support
		if ( null == $scheme ) {
			if ( isset( $parsed_url['scheme'] ) ) {
				$scheme = $parsed_url['scheme'];
			} else {
				$scheme = 'http';
			}
		}
		switch ( $scheme ) {
			case 'http':
				$scheme_code = '';
				break;
			case 'https':
				$scheme_code = 's';
				break;
			default:
				// invalid scheme
				return false;
		}

		// validate $content_type
		if ( ! in_array( $content_type, self::$amp_valid_content_types ) ) {
			return false;
		}

		// Start building the amp cache url
		$amp_cache_url = '/' . $content_type;

		if ( ! empty( $scheme_code ) ) {
			$amp_cache_url .= '/' . $scheme_code;
		}

		$amp_cache_url .= '/' . $parsed_url['host'];

		if ( isset( $parsed_url['port'] ) ) {
			$amp_cache_url .= ':' . strval( $parsed_url['port'] );
		}

		if ( isset( $parsed_url['path'] ) ) {
			$amp_cache_url .= $parsed_url['path'];
		}

		if ( isset( $parsed_url['query'] ) ) {
			$amp_cache_url .= '?' . $parsed_url['query'];
		}

		if ( isset( $parsed_url['fragment'] ) ) {
			$amp_cache_url .= '#' . $parsed_url['fragment'];
		}

		return $amp_cache_url;
	}
}
?>