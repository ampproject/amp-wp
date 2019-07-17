<?php

/**
 * AMP REST API.
 *
 * @package AMP
 * @since 2.0
 */

/**
 * Class AMP_REST_API.
 */
class AMP_REST_API
{

  /**
   * Init.
   */
  public static function init()
  {
    if (!class_exists('AMP_REST_API')) {
      return;
    }

    // Register one rest_prepare filter for each custom post type.
    add_action('registered_post_type', array(__CLASS__, 'add_rest_prepare_filter'));
  }

  /**
   * Register a rest_prepare filter for the $post_type.
   *
   * @param string $post_type The post type.
   * @return void
   */
  public static function add_rest_prepare_filter($post_type)
  {
    add_filter('rest_prepare_' . $post_type, array(__CLASS__, 'add_content_amp_field'), 10, 3);
  }

  /**
   * Adds a new field `amp` in the content of the REST API response.
   *
   * @param WP_REST_Response $response Response object.
   * @param WP_Post $post Post object.
   * @param WP_REST_Request $request Request object.
   * @return WP_REST_Response Response object.
   */
  public static function add_content_amp_field($response, $post, $request)
  {
    if (isset($response->data['content']['rendered']))
      $response->data['content']['amp'] = "An AMP valid HTML generated with the AMP Plugin.";
    return $response;
  }
}
