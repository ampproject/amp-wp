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
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$fonts       = AMP_Fonts::get_fonts();
		$total_posts = count( $fonts );
		$page        = $request['page'];
		$per_page    = $request['per_page'];
		$max_pages   = ceil( $total_posts / (int) $per_page );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error( 'rest_post_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'amp' ), [ 'status' => 400 ] );
		}

		$fonts = array_slice( $fonts, ( ( $page - 1 ) * $per_page ), $per_page );

		$response = rest_ensure_response( $fonts );

		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Checks if a given request has access to get fonts.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Override the collected params.
	 *
	 * @return array $query_params Overriden collected params.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['per_page']['maximum'] = 10000;
		$query_params['per_page']['default'] = 1000;

		return $query_params;
	}
}
