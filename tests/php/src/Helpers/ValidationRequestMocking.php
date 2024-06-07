<?php

namespace AmpProject\AmpWP\Tests\Helpers;

use AMP_Validated_URL_Post_Type;
use WP_Query;

/**
 * Trait ValidationRequestMocking
 *
 * Helpers for validation tests.
 */
trait ValidationRequestMocking {

	/**
	 * Gets all of the validated URLs.
	 *
	 * @return string[] $urls The validated URLs.
	 */
	public function get_validated_urls() {
		$query = new WP_Query(
			[
				'post_type'      => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'posts_per_page' => 100,
				'fields'         => 'ids',
			]
		);

		return array_map(
			static function ( $post ) {
				return remove_query_arg( 'amp', AMP_Validated_URL_Post_Type::get_url_from_post( $post ) );
			},
			$query->posts
		);
	}

	/**
	 * Add filter to mock validate responses.
	 */
	public function add_validate_response_mocking_filter() {
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ], 10, 3 );
	}

	/**
	 * Construct a WP HTTP response for a validation request.
	 *
	 * @return array The response.
	 */
	public function get_validate_response( $r, /** @noinspection PhpUnusedParameterInspection */ $args, $url ) {
		if ( false === strpos( $url, 'amp_validate' ) ) {
			return $r;
		}

		$mock_validation = [
			'results' => [
				[
					'error'     => [
						'code'    => 'foo',
						'sources' => [
							[
								'type' => 'plugin',
								'name' => 'foo',
								'file' => 'foo/foo.php',
							],
						],
					],
					'sanitized' => false,
				],
			],
			'url'     => home_url( '/' ),
		];

		return [
			'body'     => wp_json_encode( $mock_validation ),
			'response' => [
				'code'    => 200,
				'message' => 'ok',
			],
		];
	}
}
