<?php

class AMP_REST_Stories_Controller extends WP_REST_Posts_Controller {
	protected function prepare_item_for_database( $request ) {
		$prepared_story = parent::prepare_item_for_database( $request );

		if ( isset( $request['content_filtered'] ) ) {
			if ( is_string( $request['content_filtered'] ) ) {
				$prepared_story->post_content_filtered = $request['content_filtered'];
			} elseif ( isset( $request['content_filtered']['raw'] ) ) {
				$prepared_story->post_content_filtered = $request['content_filtered']['raw'];
			}
		}

		return $prepared_story;
	}

	public function prepare_item_for_response( $post, $request ) {
		$response = parent::prepare_item_for_response( $post, $request );
		$fields   = $this->get_fields_for_response( $request );
		$data     = $response->get_data();

		if ( in_array( 'content_filtered', $fields, true ) ) {
			$data['content_filtered'] = array(
				'raw'      => $post->post_content_filtered,
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );
		$links = $response->get_links();
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
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}
		$schema = parent::get_item_schema();

		$schema['properties']['content_filtered'] = array(
			'description' => __( 'The post content filtered for the object.' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit', 'embed' ),
			'arg_options' => array(
				'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
				'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database()
			),
			'properties'  => array(
				'raw'      => array(
					'description' => __( 'Content filtered for the object, as it exists in the database.' ),
					'type'        => 'object',
					'context'     => array( 'edit' ),
				),
			),
		);

		$this->schema = $schema;
		return $this->add_additional_fields_schema( $this->schema );
	}

}
