<?php
/**
 * Trait HomeUrlLoopbackRequestMocking.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for to mock HTTP requests to the WordPress.org themes API.
 *
 * @package AmpProject\AmpWP
 */
trait HomeUrlLoopbackRequestMocking {

	/**
	 * Add filter to mock that loopback requests succeed.
	 */
	public function add_home_url_loopback_request_mocking() {
		add_filter(
			'pre_http_request',
			static function ( $pre, $args, $url ) {
				if ( set_url_scheme( untrailingslashit( home_url() ), 'https' ) === set_url_scheme( untrailingslashit( $url ), 'https' ) ) {
					$pre = [
						'body'     => '',
						'headers'  => [],
						'response' => [
							'code'   => 200,
							'status' => 'ok',
						],
					];
				}
				return $pre;
			},
			10,
			3
		);

	}
}
