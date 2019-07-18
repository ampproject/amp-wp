<?php

/**
 * AMP REST API.
 *
 * @package AMP
 * @since   2.0
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
        if (! class_exists('AMP_REST_API') ) {
            return;
        }

        add_action('rest_api_init', [ __CLASS__, 'rest_api_init' ]);
    }

    /**
     * Register other actions and filters to be used during the REST API initilization.
     *
     * @return void
     */ 
    public static function rest_api_init()
    {
        // Register a rest_prepare_{$post_type} filter for each one of the post types supported
        // by the AMP plugin.
        foreach( AMP_Post_Type_Support::get_builtin_supported_post_types() as $post_type ) {
            $post_type_supported = post_type_supports($post_type, AMP_Post_Type_Support::SLUG);
        
            if ($post_type_supported ) {
                add_filter('rest_prepare_' . $post_type, [ __CLASS__, 'add_content_amp_field' ], 10, 3);
            }
        }
    }

    /**
     * Adds a new field `amp` in the content of the REST API response.
     *
     * @param  WP_REST_Response $response Response object.
     * @param  WP_Post          $post     Post object.
     * @param  WP_REST_Request  $request  Request object.
     * @return WP_REST_Response Response object.
     */
    public static function add_content_amp_field( $response, $post, $request )
    {
        // Check if the query param "_amp" is present.
        $amp = is_string($request->get_param('_amp'));
                                
        // If "_amp" is present and there a content.rendered, output the content.amp field.
        if ($amp && isset($response->data['content']['rendered'])) {
            $response->data['content']['amp'] = 'An AMP valid version of content.rendered.';
        }
        return $response;
    }
}
