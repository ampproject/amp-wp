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
			static function( $post ) {
				return remove_query_arg( 'amp', AMP_Validated_URL_Post_Type::get_url_from_post( $post ) );
			},
			$query->posts
		);
	}

	/**
	 * Construct a WP HTTP response for a validation request.
	 *
	 * @return array The response.
	 */
	public function get_validate_response() {
		$mock_validation = [
			'results' => [
				[
					'error'     => [
						'code' => 'foo',
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
