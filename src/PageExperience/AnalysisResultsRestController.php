<?php
/**
 * REST API endpoint providing access to the Page Experience Engine (PXE).
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use Exception;
use PageExperience\Engine\Analysis\Status;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * PageExperienceEngineRestController class.
 *
 * @since 2.3
 * @internal
 */
final class AnalysisResultsRestController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * Analyzer instance to use.
	 *
	 * @var Analyzer
	 */
	private $analyzer;

	/**
	 * Authorization instance.
	 *
	 * @var Authorization
	 */
	private $pxe_authorization;

	/**
	 * Response schema.
	 *
	 * @var array
	 */
	protected $schema;

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'rest_api_init';
	}

	/**
	 * Constructor.
	 *
	 * @param Analyzer      $analyzer      Analyzer instance to use.
	 * @param Authorization $authorization Authorization instance.
	 */
	public function __construct( Analyzer $analyzer, Authorization $authorization ) {
		// @TODO: Should we use `px/v1` right away here as the namespace?
		$this->namespace         = 'amp/v1';
		$this->rest_base         = 'analysis-results';
		$this->analyzer          = $analyzer;
		$this->pxe_authorization = $authorization;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}",
			[
				[
					// @TODO: Should retrieving an analysis be a GET or a POST request?
					// Given that we create new data (outside of caching), I'd say a POST request.
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => [
						'url' => [
							'description'       => __( 'URL of the page to analyze.', 'amp' ),
							'required'          => true,
							'type'              => 'string',
							// @TODO: URL needs to be validated and sanitized.
						]
					],
				],
			]
		);
	}

	/**
	 * Checks whether the current user can view AMP validation results.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! $this->pxe_authorization->can_user_run_analysis() ) {
			return new WP_Error(
				'amp_rest_no_page_experience_analysis',
				__( 'Sorry, you do not have access to run a page experience analysis with the AMP plugin for WordPress.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @since 4.7.0
	 *
	 */
	public function create_item( $request ) {
		$request_args = $request->get_body_params();
		$request_args = ( ! empty( $request_args ) && is_array( $request_args ) ) ? $request_args : [];

		if ( ! array_key_exists( 'url', $request_args ) ) {
			return new WP_Error( 'rest_property_required', __( 'url is a required argument.', 'amp' ) );
		}

		try {
			$analysis = $this->analyzer->analyze( $request_args[ 'url' ] );
			$response = [
				'success' => $analysis->getStatus()->equals( Status::SUCCESS() ),
				'data'    => $analysis->jsonSerialize(),
			];
		} catch ( Exception $exception ) {
			$response = new WP_Error(
				'fail_to_send_data',
				__( 'Failed to retrieve page experience analysis results. Please try again later.', 'amp' )
				.  " ({$exception->getMessage()})",
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Retrieves the schema for plugin options provided by the endpoint.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'pxe-analysis-result',
			'type'       => 'object',
			'properties' => [
				'status'    => [
					'description' => __( 'Status of the analysis run.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
				'timestamp' => [
					'description' => __( 'Timestamp of the analysis run.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
				'scope'     => [
					'description' => __( 'Scope of the analysis run.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
				'ruleset'   => [
					'description' => __( 'Ruleset of the analysis run.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
				'results'   => [
					'description' => __( 'Analysis results for the URL.', 'amp' ),
					'readonly'    => true,
					'type'        => 'array',
					// @TODO: Define full schema.
				],
			],
		];

		return $this->schema;
	}
}
