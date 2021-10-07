<?php
/**
 * ScannableURLsRestController class.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_Post;
use WP_REST_Controller;
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
	 * Besides the page URL, each item contains a page `type` (e.g. 'home' or
	 * 'search') and a URL to a corresponding AMP page (`amp_url`).
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return rest_ensure_response(
			array_map(
				static function ( $entry ) {
					$entry['amp_url'] = amp_add_paired_endpoint( $entry['url'] );

					$validated_url_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( $entry['url'] );
					if ( $validated_url_post instanceof WP_Post ) {
						$entry['validation_errors'] = [];

						$data = json_decode( $validated_url_post->post_content, true );
						if ( is_array( $data ) ) {
							$entry['validation_errors'] = wp_list_pluck( $data, 'data' );
						}

						$entry['validated_url_post'] = [
							'id'        => $validated_url_post->ID,
							'edit_link' => get_edit_post_link( $validated_url_post->ID, 'raw' ),
						];

						$entry['stale'] = ( count( AMP_Validated_URL_Post_Type::get_post_staleness( $validated_url_post ) ) > 0 );
					}

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
				'url'     => [
					'description' => __( 'Page URL.', 'amp' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
				'amp_url' => [
					'description' => __( 'AMP URL.', 'amp' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
				'type'    => [
					'description' => __( 'Page type.', 'amp' ),
					'type'        => 'string',
					'readonly'    => true,
					'context'     => [ 'view' ],
				],
			],
		];
	}
}
