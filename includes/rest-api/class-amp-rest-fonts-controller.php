<?php
/**
 * Class AMP_REST_Fonts_Controller
 *
 * @package AMP
 */

/**
 * Basic font api for the AMP stories editor.
 *
 * Class AMP_REST_Fonts_Controller
 */
class AMP_REST_Fonts_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'amp/v1';
		$this->rest_base = 'fonts';
	}

	/**
	 * Registers routes for amp fonts.
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Gets a collection of fonts.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$fonts       = AMP_Fonts::get_fonts();
		$total_fonts = count( $fonts );
		$page        = $request['page'];
		$per_page    = $request['per_page'];
		$max_pages   = ceil( $total_fonts / (int) $per_page );

		if ( $page > $max_pages && $total_fonts > 0 ) {
			return new WP_Error( 'rest_post_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'amp' ), [ 'status' => 400 ] );
		}

		$fonts = array_slice( $fonts, ( ( $page - 1 ) * $per_page ), $per_page );

		$formatted_fonts = [];
		foreach ( $fonts as $font ) {
			$data              = $this->prepare_item_for_response( $font, $request );
			$formatted_fonts[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $formatted_fonts );

		$response->header( 'X-WP-Total', (int) $total_fonts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Prepares a single font output for response.
	 *
	 * @param Object          $font Font object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $font, $request ) {
		$fields = $this->get_fields_for_response( $request );
		$schema = $this->get_item_schema();
		$data   = [];

		if ( in_array( 'name', $fields, true ) ) {
			$data['name'] = $font['name'];
		}

		if ( in_array( 'slug', $fields, true ) ) {
			$data['slug'] = $font['slug'];
		}

		if ( in_array( 'handle', $fields, true ) ) {
			$data['handle'] = isset( $font['handle'] ) ? $font['handle'] : '';
		}

		if ( in_array( 'src', $fields, true ) ) {
			$data['src'] = isset( $font['src'] ) ? $font['src'] : $schema['properties']['src']['default'];
		}

		if ( in_array( 'fallbacks', $fields, true ) ) {
			$data['fallbacks'] = isset( $font['fallbacks'] ) ? (array) $font['fallbacks'] : $schema['properties']['fallbacks']['default'];
		}

		if ( in_array( 'weights', $fields, true ) ) {
			$data['weights'] = isset( $font['weights'] ) ? (array) $font['weights'] : $schema['properties']['weights']['default'];
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );
		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters a font returned from the API.
		 *
		 * Allows modification of the font right before it is returned.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param Object $font The original font object.
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_font', $response, $font, $request );
	}


	/**
	 * Checks if a given request has access to get fonts.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Retrieves the font' schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}
		$schema       = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'font',
			'type'       => 'object',
			'properties' => [
				'name'      => [
					'description' => __( 'The title for the font.', 'amp' ),
					'type'        => 'string',
					'context'     => [ 'embed', 'view', 'edit' ],
					'readonly'    => true,
				],
				'slug'      => [
					'description' => __( 'An alphanumeric identifier for the font.', 'amp' ),
					'type'        => 'string',
					'context'     => [ 'embed', 'view', 'edit' ],
					'readonly'    => true,
				],
				'handle'    => [
					'description' => __( 'An alphanumeric identifier for the handle.', 'amp' ),
					'type'        => 'string',
					'context'     => [ 'embed', 'view', 'edit' ],
					'readonly'    => true,
				],
				'fallbacks' => [
					'description' => __( 'Array of fallback fonts', 'amp' ),
					'type'        => 'array',
					'context'     => [ 'embed', 'view', 'edit' ],
					'readonly'    => true,
					'default'     => [],
				],
				'weights'   => [
					'description' => __( 'Array of fallback fonts', 'amp' ),
					'type'        => 'array',
					'context'     => [ 'embed', 'view', 'edit' ],
					'readonly'    => true,
					'default'     => [ 'normal', 'bold', 'bolder', 'lighter' ],
				],
				'src'       => [
					'description' => __( 'URL to enqueue the font', 'amp' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view', 'edit', 'embed' ],
					'readonly'    => true,
					'default'     => '',
				],
			],
		];
		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Override the collected params.
	 *
	 * @return array $query_params Overriden collected params.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context'] = $this->get_context_param( [ 'default' => 'view' ] );

		$query_params['per_page']['maximum'] = 10000;
		$query_params['per_page']['default'] = 1000;

		return $query_params;
	}
}
