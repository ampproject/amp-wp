<?php
/**
 * ScannableURLsRestController class.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validation_Manager;
use WP_Error;
use WP_REST_Controller;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Rest endpoint for fetching the scannable URLs.
 *
 * @since 2.2
 * @internal
 */
final class ScannableURLsRestController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/** @var ScannableURLProvider ScannableURLProvider instance. */
	private $scannable_url_provider;

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
	 * @param ScannableURLProvider $scannable_url_provider Instance of ScannableURLProvider class.
	 */
	public function __construct( ScannableURLProvider $scannable_url_provider ) {
		$this->namespace              = 'amp/v1';
		$this->rest_base              = 'scannable-urls';
		$this->scannable_url_provider = $scannable_url_provider;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'args'                => [],
					'permission_callback' => static function () {
						return current_user_can( \AMP_Validation_Manager::VALIDATE_CAPABILITY );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Retrieves a list of scannable URLs.
	 *
	 * Each item contains a page `type` (e.g. 'home' or 'search') and a
	 * `validate_url` prop for accessing validation data for a given URL.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$nonce = AMP_Validation_Manager::get_amp_validate_nonce();

		return rest_ensure_response(
			array_map(
				static function ( $entry ) use ( $nonce ) {
					$entry['validate_url'] = amp_add_paired_endpoint(
						add_query_arg(
							[
								AMP_Validation_Manager::VALIDATE_QUERY_VAR => $nonce,
							],
							$entry['url']
						)
					);

					return $entry;
				},
				$this->scannable_url_provider->get_urls()
			)
		);
	}

	/**
	 * Retrieves the block type' schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'amp-wp-' . $this->rest_base,
			'type'       => 'object',
			'properties' => [
				'url'          => [
					'description' => __( 'Page URL.', 'amp' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
				'type'         => [
					'description' => __( 'Page type.', 'amp' ),
					'type'        => 'string',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
				'validate_url' => [
					'description' => __( 'URL for accessing validation data for a given page.', 'amp' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
			],
		];
	}
}
