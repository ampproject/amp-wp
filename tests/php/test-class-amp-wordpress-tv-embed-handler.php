<?php
/**
 * Tests for AMP_WordPress_TV_Embed_Handler.
 *
 * @package AMP
 * @since 1.4
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_WordPress_TV_Embed_Handler.
 *
 * @package AMP
 * @covers AMP_WordPress_TV_Embed_Handler
 */
class Test_AMP_WordPress_TV_Embed_Handler extends TestCase {

	use WithoutBlockPreRendering {
		set_up as public prevent_block_pre_render;
	}

	/**
	 * Set up.
	 */
	public function set_up() {
		$this->prevent_block_pre_render();

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
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $preempt;
		}

		if ( false === strpos( $url, 'wordpress.tv' ) ) {
			return $preempt;
		}
		unset( $r );
		return [
			'body'          => '{"type":"video","version":"1.0","title":null,"width":500,"height":281,"html":"<iframe width=\'500\' height=\'281\' src=\'https:\\/\\/video.wordpress.com\\/embed\\/yFCmLMGL?hd=0\' frameborder=\'0\' allowfullscreen><\\/iframe><script src=\'https:\\/\\/v0.wordpress.com\\/js\\/next\\/videopress-iframe.js?m=1435166243\'></script>"}', // phpcs:ignore
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
	 * @covers AMP_WordPress_TV_Embed_Handler::filter_oembed_html()
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
		$this->assertStringContainsString( '<iframe', $rendered );
		$this->assertStringContainsString( 'video.wordpress.com/embed', $rendered );
		$this->assertStringNotContainsString( '<script', $rendered );
	}

	/**
	 * Gets the test data for test_filter_oembed_html().
	 *
	 * @return array The test data.
	 */
	public function get_filter_oembed_data() {
		$embed_without_script = '<p>Example Embed</p>';
		$embed_with_script    = $embed_without_script . '<script>doThis();</script>';

		return [
			'wrong_embed_url_domain'                => [
				$embed_without_script,
				'https://incorrect.com',
				null,
			],
			'wrong_embed_url_wordpress_com'         => [
				$embed_without_script,
				'https://wordpress.com/123',
				null,
			],
			'wrong_embed_url_no_protocol'           => [
				$embed_without_script,
				'//wordpress.tv/',
				null,
			],
			'correct_embed_url_http'                => [
				$embed_with_script,
				'https://wordpress.tv/123',
				$embed_without_script,
			],
			'correct_embed_url_https'               => [
				$embed_with_script,
				'https://wordpress.tv/123',
				$embed_without_script,
			],
			'correct_embed_url_no_script'           => [
				$embed_without_script,
				'https://wordpress.tv/123',
				null,
			],
			'correct_embed_url_text_script_not_tag' => [
				'This is the script for the play',
				'https://wordpress.tv/123',
				null,
			],
		];
	}

	/**
	 * Test filter_oembed_html
	 *
	 * @dataProvider get_filter_oembed_data
	 * @covers AMP_WordPress_TV_Embed_Handler::filter_oembed_html()
	 *
	 * @param mixed  $cache    The cached markup.
	 * @param string $url      The URL of the embed.
	 * @param string $expected The expected return value.
	 */
	public function test_filter_oembed_html( $cache, $url, $expected ) {
		if ( null === $expected ) {
			$expected = $cache;
		}

		$handler = new AMP_WordPress_TV_Embed_Handler();
		$this->assertEquals(
			$expected,
			$handler->filter_oembed_html( $cache, $url )
		);

	}
}
