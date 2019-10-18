<?php
/**
 * Tests for AMP_WordPress_TV_Embed_Handler.
 *
 * @package AMP
 * @since 1.4
 */

/**
 * Tests for AMP_WordPress_TV_Embed_Handler.
 *
 * @package AMP
 * @covers AMP_WordPress_TV_Embed_Handler
 */
class Test_AMP_WordPress_TV_Embed_Handler extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestIncomplete( 'Files needed for testing missing.' );
		}
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Missing required render_block filter.' );
		}
		parent::setUp();

		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $preempt, $r, $url ) {
		if ( false === strpos( $url, 'wordpress.tv' ) ) {
			return $preempt;
		}
		unset( $r );
		return [
			'body'          => '{"type":"video","version":"1.0","title":null,"width":500,"height":281,"html":"<iframe width=\'500\' height=\'281\' src=\'https:\\/\\/videopress.com\\/embed\\/yFCmLMGL?hd=0\' frameborder=\'0\' allowfullscreen><\\/iframe><script src=\'https:\\/\\/v0.wordpress.com\\/js\\/next\\/videopress-iframe.js?m=1435166243\'></script>"}', // phpcs:ignore
			'headers'       => [],
			'response'      => [
				'code'    => 200,
				'message' => 'ok',
			],
			'cookies'       => [],
			'http_response' => null,
		];
	}

	/**
	 * Test that the script tag that VideoPress adds is removed by the sanitizer.
	 *
	 * @covers AMP_WordPress_TV_Embed_Handler::filter_rendered_block()
	 */
	public function test_script_removal() {
		$handler = new AMP_WordPress_TV_Embed_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.

		$wordpress_tv_block = '
			<!-- wp:core-embed/wordpress-tv {"url":"https://wordpress.tv/2019/10/08/the-history-of-wordpress-in-four-minutes/","type":"video","providerNameSlug":"","className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
				<figure class="wp-block-embed-wordpress-tv wp-block-embed is-type-video wp-embed-aspect-16-9 wp-has-aspect-ratio">
					<div class="wp-block-embed__wrapper">
						https://wordpress.tv/2019/10/08/the-history-of-wordpress-in-four-minutes/
					</div>
				</figure>
			<!-- /wp:core-embed/wordpress-tv -->
		';

		$handler->register_embed();
		$rendered = apply_filters( 'the_content', $wordpress_tv_block );
		$this->assertContains( '<iframe', $rendered );
		$this->assertContains( 'videopress.com/embed', $rendered );
		$this->assertNotContains( '<script', $rendered );
	}
}
