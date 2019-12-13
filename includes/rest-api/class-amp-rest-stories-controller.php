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

		if ( isset( $request['content_filtered'] ) ) {
			$prepared_story->post_content_filtered = $request['content_filtered'];
			if ( isset( $request['content_filtered']['raw'] ) ) {
				$prepared_story->post_content_filtered = $request['content_filtered']['raw'];
			}

			$prepared_story->post_content_filtered = wp_json_encode( $prepared_story->post_content_filtered );
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

		if ( in_array( 'content_filtered', $fields, true ) ) {
			$post_content_filtered    = json_decode( $post->post_content_filtered );
			$data['content_filtered'] = [
				'raw' => rest_sanitize_value_from_schema( $post_content_filtered, $schema['properties']['content_filtered']['properties']['raw'] ),
			];
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

		$schema['properties']['content_filtered'] = [
			'description' => __( 'The post content filtered for the object.', 'amp' ),
			'type'        => 'object',
			'context'     => [ 'view', 'edit', 'embed' ],
			'properties'  => [
				'raw' => [
					'description' => __( 'Content filtered for the object, as it exists in the database.', 'amp' ),
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
				],
			],
		];

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

}
