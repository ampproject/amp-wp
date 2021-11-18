<?php
/**
 * Class AMP_WordPress_Embed_Handler_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_WordPress_Embed_Handler_Test
 *
 * @coversDefaultClass AMP_WordPress_Embed_Handler
 */
class AMP_WordPress_Embed_Handler_Test extends TestCase {

	use MarkupComparison, WithoutBlockPreRendering;

	/** @var string */
	const WP_59_ALPHA_POST_URL = 'https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/';

	/** @var string */
	const WP_58_STABLE_POST_URL = 'https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/';

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $pre Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r   HTTP request arguments.
	 * @param string $url The request URL.
	 * @return array|false Response data.
	 */
	public function mock_http_request( $pre, /** @noinspection PhpUnusedParameterInspection */ $r, $url ) {
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $pre;
		}

		$query_vars = [];
		parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $query_vars );

		$content_type = null;
		$body         = null;
		if ( self::WP_59_ALPHA_POST_URL === $url ) {
			$content_type = 'text/html';
			$body         = '
				<html>
					<head>
						<link rel="alternate" type="application/json+oembed" href="https://make.wordpress.org/core/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fmake.wordpress.org%2Fcore%2F2021%2F10%2F12%2Fproposal-for-a-performance-team%2F" />
					</head>
					<body></body>
				</html>
			';
		} elseif ( self::WP_58_STABLE_POST_URL === $url ) {
			$content_type = 'text/html';
			$body         = '
				<html>
					<head>
						<link rel="alternate" type="application/json+oembed" href="https://amp-wp.org/wp-json/oembed/1.0/embed?url=https%3A%2F%2Famp-wp.org%2Fintroducing-v2-0-of-the-official-amp-plugin-for-wordpress%2F" />
					</head>
					<body></body>
				</html>
			';
		} elseif (
			strpos( $url, 'https://make.wordpress.org/core/wp-json/oembed/1.0/embed' ) === 0
			&&
			isset( $query_vars['url'] )
			&&
			self::WP_59_ALPHA_POST_URL === $query_vars['url']
		) {
			$content_type = 'application/json';
			$body         = '{"version":"1.0","provider_name":"Make WordPress Core","provider_url":"https:\\/\\/make.wordpress.org\\/core","author_name":"Ari Stathopoulos","author_url":"https:\\/\\/profiles.wordpress.org\\/aristath\\/","title":"Proposal for a Performance team","type":"rich","width":500,"height":282,"html":"<blockquote class=\\"wp-embedded-content\\" data-secret=\\"7SWZvnoOw8\\"><a href=\\"https:\\/\\/make.wordpress.org\\/core\\/2021\\/10\\/12\\/proposal-for-a-performance-team\\/\\">Proposal for a Performance team<\\/a><\\/blockquote><iframe sandbox=\\"allow-scripts\\" security=\\"restricted\\" src=\\"https:\\/\\/make.wordpress.org\\/core\\/2021\\/10\\/12\\/proposal-for-a-performance-team\\/embed\\/#?secret=7SWZvnoOw8\\" width=\\"500\\" height=\\"282\\" title=\\"&#8220;Proposal for a Performance team&#8221; &#8212; Make WordPress Core\\" data-secret=\\"7SWZvnoOw8\\" frameborder=\\"0\\" marginwidth=\\"0\\" marginheight=\\"0\\" scrolling=\\"no\\" class=\\"wp-embedded-content\\"><\\/iframe><script type=\\"text\\/javascript\\">\\n\\/*! This file is auto-generated *\\/\\n!function(c,d){\\"use strict\\";var e=!1,o=!1;if(d.querySelector)if(c.addEventListener)e=!0;if(c.wp=c.wp||{},!c.wp.receiveEmbedMessage)if(c.wp.receiveEmbedMessage=function(e){var t=e.data;if(t)if(t.secret||t.message||t.value)if(!\\/[^a-zA-Z0-9]\\/.test(t.secret)){for(var r,a,i,s=d.querySelectorAll(\'iframe[data-secret=\\"\'+t.secret+\'\\"]\'),n=d.querySelectorAll(\'blockquote[data-secret=\\"\'+t.secret+\'\\"]\'),o=0;o<n.length;o++)n[o].style.display=\\"none\\";for(o=0;o<s.length;o++)if(r=s[o],e.source===r.contentWindow){if(r.removeAttribute(\\"style\\"),\\"height\\"===t.message){if(1e3<(i=parseInt(t.value,10)))i=1e3;else if(~~i<200)i=200;r.height=i}if(\\"link\\"===t.message)if(a=d.createElement(\\"a\\"),i=d.createElement(\\"a\\"),a.href=r.getAttribute(\\"src\\"),i.href=t.value,i.host===a.host)if(d.activeElement===r)c.top.location.href=t.value}}},e)c.addEventListener(\\"message\\",c.wp.receiveEmbedMessage,!1),d.addEventListener(\\"DOMContentLoaded\\",t,!1),c.addEventListener(\\"load\\",t,!1);function t(){if(!o){o=!0;for(var e,t,r,a=-1!==navigator.appVersion.indexOf(\\"MSIE 10\\"),i=!!navigator.userAgent.match(\\/Trident.*rv:11\\\\.\\/),s=d.querySelectorAll(\\"iframe.wp-embedded-content\\"),n=0;n<s.length;n++){if(!(r=(t=s[n]).getAttribute(\\"data-secret\\")))r=Math.random().toString(36).substr(2,10),t.src+=\\"#?secret=\\"+r,t.setAttribute(\\"data-secret\\",r);if(a||i)(e=t.cloneNode(!0)).removeAttribute(\\"security\\"),t.parentNode.replaceChild(e,t);t.contentWindow.postMessage({message:\\"ready\\",secret:r},\\"*\\")}}}}(window,document);\\n<\\/script>\\n"}';
		} elseif (
			strpos( $url, 'https://weston.ruter.net/wp-json/oembed/1.0/embed' ) === 0
			&&
			isset( $query_vars['url'] )
			&&
			self::WP_58_STABLE_POST_URL === $query_vars['url']
		) {
			$content_type = 'application/json';
			$body         = '{"version":"1.0","provider_name":"AMP for WordPress","provider_url":"https:\\/\\/amp-wp.org","author_name":"Alberto Medina","author_url":"https:\\/\\/amp-wp.org\\/author\\/albertomedina\\/","title":"Introducing v2.0 of the official AMP Plugin for WordPress","type":"rich","width":500,"height":282,"html":"<blockquote class=\\"wp-embedded-content\\"><a href=\\"https:\\/\\/amp-wp.org\\/introducing-v2-0-of-the-official-amp-plugin-for-wordpress\\/\\">Introducing v2.0 of the official AMP Plugin for WordPress<\\/a><\\/blockquote>\\n<script type=\'text\\/javascript\'>\\n<!--\\/\\/--><![CDATA[\\/\\/><!--\\n\\t\\t\\/*! This file is auto-generated *\\/\\n\\t\\t!function(c,d){\\"use strict\\";var e=!1,n=!1;if(d.querySelector)if(c.addEventListener)e=!0;if(c.wp=c.wp||{},!c.wp.receiveEmbedMessage)if(c.wp.receiveEmbedMessage=function(e){var t=e.data;if(t)if(t.secret||t.message||t.value)if(!\\/[^a-zA-Z0-9]\\/.test(t.secret)){for(var r,a,i,s=d.querySelectorAll(\'iframe[data-secret=\\"\'+t.secret+\'\\"]\'),n=d.querySelectorAll(\'blockquote[data-secret=\\"\'+t.secret+\'\\"]\'),o=0;o<n.length;o++)n[o].style.display=\\"none\\";for(o=0;o<s.length;o++)if(r=s[o],e.source===r.contentWindow){if(r.removeAttribute(\\"style\\"),\\"height\\"===t.message){if(1e3<(i=parseInt(t.value,10)))i=1e3;else if(~~i<200)i=200;r.height=i}if(\\"link\\"===t.message)if(a=d.createElement(\\"a\\"),i=d.createElement(\\"a\\"),a.href=r.getAttribute(\\"src\\"),i.href=t.value,i.host===a.host)if(d.activeElement===r)c.top.location.href=t.value}}},e)c.addEventListener(\\"message\\",c.wp.receiveEmbedMessage,!1),d.addEventListener(\\"DOMContentLoaded\\",t,!1),c.addEventListener(\\"load\\",t,!1);function t(){if(!n){n=!0;for(var e,t,r=-1!==navigator.appVersion.indexOf(\\"MSIE 10\\"),a=!!navigator.userAgent.match(\\/Trident.*rv:11\\\\.\\/),i=d.querySelectorAll(\\"iframe.wp-embedded-content\\"),s=0;s<i.length;s++){if(!(e=i[s]).getAttribute(\\"data-secret\\"))t=Math.random().toString(36).substr(2,10),e.src+=\\"#?secret=\\"+t,e.setAttribute(\\"data-secret\\",t);if(r||a)(t=e.cloneNode(!0)).removeAttribute(\\"security\\"),e.parentNode.replaceChild(t,e)}}}}(window,document);\\n\\/\\/--><!]]>\\n<\\/script><iframe sandbox=\\"allow-scripts\\" security=\\"restricted\\" src=\\"https:\\/\\/amp-wp.org\\/introducing-v2-0-of-the-official-amp-plugin-for-wordpress\\/embed\\/\\" width=\\"500\\" height=\\"282\\" title=\\"&#8220;Introducing v2.0 of the official AMP Plugin for WordPress&#8221; &#8212; AMP for WordPress\\" frameborder=\\"0\\" marginwidth=\\"0\\" marginheight=\\"0\\" scrolling=\\"no\\" class=\\"wp-embedded-content\\"><\\/iframe>","thumbnail_url":"https:\\/\\/amp-wp.org\\/wp-content\\/uploads\\/2020\\/08\\/amp-wp-banner-logo.png","thumbnail_width":500,"thumbnail_height":250}';
		}

		if ( $content_type && $body ) {
			$pre = [
				'body'     => $body,
				'headers'  => [
					'content-type' => $content_type,
				],
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		}

		return $pre;
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'                      => [
				'Hello world.',
				'<p>Hello world.</p>',
			],
			'wp_trunk_post_url'             => [
				self::WP_59_ALPHA_POST_URL . PHP_EOL,
				'
					<amp-wordpress-embed height="200" layout="fixed-height" title="“Proposal for a Performance team” — Make WordPress Core" data-url="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/embed/">
						<blockquote class="wp-embedded-content" placeholder>
							<p><a href="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/">Proposal for a Performance team</a></p>
						</blockquote>
						<button overflow type="button">See more</button>
					</amp-wordpress-embed>
				',
			],
			'wp_stable_post_url'            => [
				self::WP_58_STABLE_POST_URL . PHP_EOL,
				'
					<amp-wordpress-embed data-url="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/embed/" height="200" layout="fixed-height" title="“Introducing v2.0 of the official AMP Plugin for WordPress” — AMP for WordPress">
						<blockquote class="wp-embedded-content" placeholder>
							<p><a href="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/">Introducing v2.0 of the official AMP Plugin for WordPress</a></p>
						</blockquote>
						<button overflow type="button">See more</button>
					</amp-wordpress-embed>
				',
			],
			'wp_embed_block'                => [
				'
					<!-- wp:embed {"url":"' . self::WP_59_ALPHA_POST_URL . '","type":"wp-embed","providerNameSlug":"make-wordpress-core"} -->
					<figure class="wp-block-embed is-type-wp-embed is-provider-make-wordpress-core wp-block-embed-make-wordpress-core"><div class="wp-block-embed__wrapper">
					' . self::WP_59_ALPHA_POST_URL . '
					</div></figure>
					<!-- /wp:embed -->
				',
				'
					<figure class="wp-block-embed is-type-wp-embed is-provider-make-wordpress-core wp-block-embed-make-wordpress-core">
						<div class="wp-block-embed__wrapper">
							<amp-wordpress-embed height="200" layout="fixed-height" title="“Proposal for a Performance team” — Make WordPress Core" data-url="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/embed/">
								<blockquote class="wp-embedded-content" placeholder>
									<a href="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/">Proposal for a Performance team</a>
								</blockquote>
								<button overflow type="button">See more</button>
							</amp-wordpress-embed>
						</div>
					</figure>
				',
			],
			'two_wp_embed_blocks'           => [
				'
					<!-- wp:embed {"url":"' . self::WP_59_ALPHA_POST_URL . '","type":"wp-embed","providerNameSlug":"make-wordpress-core"} -->
					<figure class="wp-block-embed is-type-wp-embed is-provider-make-wordpress-core wp-block-embed-make-wordpress-core"><div class="wp-block-embed__wrapper">
					' . self::WP_59_ALPHA_POST_URL . '
					</div></figure>
					<!-- /wp:embed -->

					<!-- wp:embed {"url":"' . self::WP_58_STABLE_POST_URL . '","type":"wp-embed","providerNameSlug":"amp-for-wordpress"} -->
					<figure class="wp-block-embed is-type-wp-embed is-provider-amp-for-wordpress wp-block-embed-amp-for-wordpress"><div class="wp-block-embed__wrapper">
					' . self::WP_58_STABLE_POST_URL . '
					</div></figure>
					<!-- /wp:embed -->
				',
				'
					<figure class="wp-block-embed is-type-wp-embed is-provider-make-wordpress-core wp-block-embed-make-wordpress-core">
						<div class="wp-block-embed__wrapper">
							<amp-wordpress-embed height="200" layout="fixed-height" title="“Proposal for a Performance team” — Make WordPress Core" data-url="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/embed/">
								<blockquote class="wp-embedded-content" placeholder>
									<a href="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/">Proposal for a Performance team</a>
								</blockquote>
								<button overflow type="button">See more</button>
							</amp-wordpress-embed>
						</div>
					</figure>
					<figure class="wp-block-embed is-type-wp-embed is-provider-amp-for-wordpress wp-block-embed-amp-for-wordpress">
						<div class="wp-block-embed__wrapper">
							<amp-wordpress-embed height="200" layout="fixed-height" title="“Introducing v2.0 of the official AMP Plugin for WordPress” — AMP for WordPress" data-url="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/embed/">
								<blockquote class="wp-embedded-content" placeholder>
									<a href="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/">Introducing v2.0 of the official AMP Plugin for WordPress</a>
								</blockquote>
								<button overflow type="button">See more</button>
							</amp-wordpress-embed>
						</div>
					</figure>

				',
			],
			'wp_trunk_and_stable_post_urls' => [
				self::WP_59_ALPHA_POST_URL . PHP_EOL . self::WP_58_STABLE_POST_URL . PHP_EOL,
				'
					<amp-wordpress-embed height="200" layout="fixed-height" title="“Proposal for a Performance team” — Make WordPress Core" data-url="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/embed/">
						<blockquote class="wp-embedded-content" placeholder>
							<p><a href="https://make.wordpress.org/core/2021/10/12/proposal-for-a-performance-team/">Proposal for a Performance team</a></p>
						</blockquote>
						<button overflow type="button">See more</button>
					</amp-wordpress-embed>
					<amp-wordpress-embed data-url="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/embed/" height="200" layout="fixed-height" title="“Introducing v2.0 of the official AMP Plugin for WordPress” — AMP for WordPress">
						<blockquote class="wp-embedded-content" placeholder>
							<p><a href="https://amp-wp.org/introducing-v2-0-of-the-official-amp-plugin-for-wordpress/">Introducing v2.0 of the official AMP Plugin for WordPress</a></p>
						</blockquote>
						<button overflow type="button">See more</button>
					</amp-wordpress-embed>
				',
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @covers ::sanitize_raw_embeds()
	 * @covers ::create_amp_wordpress_embed_and_replace_node()
	 * @dataProvider get_conversion_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__conversion( $source, $expected ) {

		// Capture the response bodies to facilitate updating the request mocking.
		$http_response_bodies = [];
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$http_response_bodies ) {
				if ( false !== $pre ) {
					$http_response_bodies[ $url ] = wp_remote_retrieve_body( $pre );
				}
				return $pre;
			},
			1000,
			3
		);
		add_filter(
			'http_response',
			static function ( $response, $args, $url ) use ( &$http_response_bodies ) {
				$http_response_bodies[ $url ] = wp_remote_retrieve_body( $response );
				return $response;
			},
			1000,
			3
		);

		$embed = new AMP_WordPress_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$this->assertSimilarMarkup( $expected, $content, 'Response bodies for HTTP requests: ' . var_export( $http_response_bodies, true ) );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array Scripts data.
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				self::WP_58_STABLE_POST_URL . PHP_EOL,
				[ 'amp-wordpress-embed' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 * @covers ::get_scripts()
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_WordPress_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Test register_embed and unregister_embed.
	 *
	 * @covers ::register_embed()
	 * @covers ::unregister_embed()
	 */
	public function test_register_and_unregister_embed() {
		$embed = new AMP_WordPress_Embed_Handler();

		$this->assertEquals( 10, has_action( 'wp_head', 'wp_oembed_add_host_js' ) );

		$embed->register_embed();
		$this->assertFalse( has_action( 'wp_head', 'wp_oembed_add_host_js' ) );

		$embed->unregister_embed();
		$this->assertEquals( 10, has_action( 'wp_head', 'wp_oembed_add_host_js' ) );
	}
}
