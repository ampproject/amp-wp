<?php
/**
 * In addition to handling updates to the AMP cache when a post is created
 * or updated, this class is meant to be a general toolbox for the AMP cache
 * and this has a number of utility functions for use with the AMP Cache.
 * 
 * The original driver for this class was `amp_add_cache_update_actions()` 
 * which adds hooks to ping the AMP cache when a post is updated or deleted.
 *
 * But, this class also includes a number of functions that could be used for other
 * features within the plugin. For example, if there was a need to cache images
 * or other resources (say, fonts), then `get_amp_cache_url_for_resource()`
 * and `get_amp_cache_update_url_for_resource()` can be used to generate
 * the correct URLs for retrieving and caching the resource respectively.
 *
 * This is the reason that not all of the functions included in the class directly
 * relate to updating the AMP cache on a post update/delete.
 *
 * Note: The reason this is an `abstract` class is because all of the 
 * properties and methods are static and thus, the class may be used without
 * being instantiated. `abstract` is just a reminder to call the functions
 * statically and that you don't need to isntantyiate an instance of the
 * class to use it. So, while theres's nothing preventing this class from
 * being extended, that wasn't the primary driver for making it abstract.
 */
abstract class AMP_Cache_Utilities {

	/**
	 * These are the valid types of content that can be cached:
	 * 
	 * 'c' - content (a post)
	 * 'i' - an image
	 * 'r' - a resource (like a font)
	 *
	 * See: https://developers.google.com/amp/cache/overview
	 */
	static $amp_valid_content_types = array( 'c', 'i', 'r' );

	/**
	 * This is the base URL of the AMP cache CDN.
	 */
	static $amp_cache_url_base = 'https://cdn.ampproject.org';

	/**
	 * This is the base URL of the AMP cache ping-update URL
	 */
	static $amp_cache_update_url_base = 'https://cdn.ampproject.org/update-ping';

	/**
	 * Hooks the 'post_updated' and 'before_delete_post' actions to determine if we
	 * need to update the AMP Cache when a post is updated or about to be deleted.
	 */
	public static function amp_add_cache_update_actions() {
		// Hooking this to the post_updated action so this will fire any time a post
		// is updated in any way (including status transitions).
		add_action( 'post_updated',  array( 'AMP_Cache_Utilities', 'post_updated' ), 10, 3 );
		
		// Hooking the call to update the cache *before* the post is updated in case we need
		// to access any metadata for future functionality.
		add_action( 'before_delete_post',  array( 'AMP_Cache_Utilities', 'do_amp_update_ping' ) );
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
	 * @param  int/WP_Post 	$post_id     The post that was being updated
	 * @param  WP_Post 		$post_after  A copy of the post after the update.
	 * @param  WP_Post		$post_before A copy of the post before the update.
	 * @return bool         true is the cache was updated, false otherwise.
	 */
	public static function post_updated( $post_id, $post_after, $post_before ) {
		// if post_status is 'publish' or was 'publish' but now is not, update cache
		if ( ( 'publish' == $post_after->post_status ) ||
			( ( 'publish' != $post_after->post_status ) && ( 'publish' == $post_before->post_status ) ) ) {
			return self::do_amp_update_ping( $post_id );
		}
		return false;
	}

	/**
	 * Given a post_id or WP_Post object, this function will calculate the AMP
	 * cache update URL and ping it.
	 * 
	 * This function will return true if the cache was updated successfully. It returns
	 * false if the cache did not need to be updated *or* if the update failed for any
	 * reason.
	 * 
	 * @param  int/WP_Post	$post 	The post to update in the AMP cache.
	 * @return bool       			true if the cache was updated, false otherwise.
	 */
	public static function do_amp_update_ping( $post ) {
		$update_ping_url = self::get_amp_cache_update_url_for_post( $post );
		if ( ! $update_ping_url ) {
			return false;
		}
		$response = wp_remote_get( $update_ping_url );
		return ( 204 == wp_remote_retrieve_response_code( $response ) );
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
		if ( $amp_cache_resource_path ) {
			return self::$amp_cache_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
		}
		return false;
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
		if ( $amp_cache_resource_path ) {
			return self::$amp_cache_update_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
		}
		return false;
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
		if ( $amp_cache_resource_path ) {
			return self::$amp_cache_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
		}
		return false;
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
		if ( $amp_cache_resource_path ) {
			return self::$amp_cache_update_url_base . '/' . ltrim( $amp_cache_resource_path, '/' );
		}
		return false;
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
	public static function get_amp_cache_path_for_post( $post, $content_type = null, $scheme = null ) {
		$permalink = get_permalink( $post );

		// If permalink couldn't be retrieved, return failure.
		if ( false === $permalink ) {
			return false;
		}

		// determine $content_type, if not specified
		if ( null == $content_type ) {
			$post_type = get_post_type( $post );
			switch ( $post_type ) {
				case 'attachment':
					$content_type = 'i';
					break;
				case 'post':
					$content_type = 'c';
					break;
				default:
					// unhandled post type
					return false;
			}
		}

		// Return the url
		return self::get_amp_cache_path_for_url( $permalink, $content_type, $scheme );
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
		if ( null == $scheme ) {
			if ( isset( $parsed_url['scheme'] ) ) {
				$scheme = $parsed_url['scheme'];
			} else {
				// no scheme
				return false;
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