<?php
/**
 * Class AMP_REST_Stories_Controller
 *
 * @package AMP
 */

/**
 * Override the WP_REST_Posts_Controller class to add `post_content_filtered` to REST request.
 *
 * Class AMP_REST_Stories_Controller
 */
class AMP_REST_Stories_Controller extends WP_REST_Posts_Controller {
	/**
	 * Prepares a single story for create or update. Add post_content_filtered field to save/insert.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return stdClass|WP_Error Post object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_story = parent::prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_story ) ) {
			return $prepared_story;
		}
		// Ensure that content and story_data are updated together.
		if (
			! empty( $request['story_data'] ) && empty( $request['content'] ) ||
			! empty( $request['content'] ) && empty( $request['story_data'] )
		) {
			return new WP_Error( 'rest_empty_content', __( 'content and story_data should always be updated together.', 'amp' ), [ 'status' => 412 ] );
		}

		// If the request is updating the content as well, let's make sure the JSON representation of the story is saved, too.
		if ( isset( $request['story_data'] ) ) {
			$prepared_story->post_content_filtered = wp_json_encode( $request['story_data'] );
		}

		return $prepared_story;
	}

	/**
	 * Prepares a single story output for response. Add post_content_filtered field to output.
	 *
	 * @param WP_Post         $post Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$response = parent::prepare_item_for_response( $post, $request );
		$fields   = $this->get_fields_for_response( $request );
		$data     = $response->get_data();
		$schema   = $this->get_item_schema();

		if ( in_array( 'story_data', $fields, true ) ) {
			$post_story_data    = json_decode( $post->post_content_filtered, true );
			$data['story_data'] = rest_sanitize_value_from_schema( $post_story_data, $schema['properties']['story_data'] );
		}

		if ( in_array( 'featured_media_url', $fields, true ) ) {
			$image                      = get_the_post_thumbnail_url( $post, 'medium' );
			$data['featured_media_url'] = ! empty( $image ) ? $image : $schema['properties']['featured_media_url']['default'];
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->filter_response_by_context( $data, $context );
		$links   = $response->get_links();
		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		foreach ( $links as $rel => $rel_links ) {
			foreach ( $rel_links as $link ) {
				$response->add_link( $rel, $link['href'], $link['attributes'] );
			}
		}

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post $post Post object.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
	}

	/**
	 * Retrieves the attachment's schema, conforming to JSON Schema.
	 *
	 * @return array Item schema as an array.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}
		$schema = parent::get_item_schema();

		$schema['properties']['story_data'] = [
			'description' => __( 'Story data stored as a JSON object. Stored in post_content_filtered field.', 'amp' ),
			'type'        => 'array',
			'items'       => [
				'type'       => 'object',
				'properties' => [
					'id'       => [
						'type' => 'string',
					],
					'type'     => [
						'type'    => 'string',
						'default' => 'page',
					],
					'elements' => [
						'type'    => 'array',
						'default' => [],
					],
					'index'    => [
						'type'    => 'integer',
						'default' => 0,
					],
				],
			],
			'context'     => [ 'edit' ],
			'default'     => [],
		];

		$schema['properties']['featured_media_url'] = [
			'description' => __( 'URL to enqueue the image', 'amp' ),
			'type'        => 'string',
			'format'      => 'uri',
			'context'     => [ 'view', 'edit', 'embed' ],
			'readonly'    => true,
			'default'     => '',
		];

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

}
