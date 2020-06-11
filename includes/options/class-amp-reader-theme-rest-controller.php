<?php
/**
 * Reader theme controller.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * AMP reader theme REST controller.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Theme_REST_Controller extends WP_REST_Controller {

	/**
	 * Reader themes provider class.
	 *
	 * @var AMP_Reader_Themes
	 */
	private $reader_themes;

	/**
	 * Constructor.
	 *
	 * @param AMP_Reader_Themes $reader_themes AMP_Reader_Themes instance to provide theme data.
	 */
	public function __construct( $reader_themes ) {
		$this->reader_themes = $reader_themes;
		$this->namespace     = 'amp/v1';
		$this->rest_base     = 'reader-themes';
	}

	/**
	 * Registers routes for the controller.
	 *
	 * @since 1.6.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'  => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_items' ],
					'args'     => [],
				],
			]
		);
	}

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$themes = $this->reader_themes->get_themes();

		return rest_ensure_response( $themes );
	}
}
