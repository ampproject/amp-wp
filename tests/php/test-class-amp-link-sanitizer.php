<?php
/**
 * Class AMP_Link_Sanitizer_Test.
 *
 * @package AMP
 */

/**
 * Class AMP_Link_Sanitizer_Test
 */
class AMP_Link_Sanitizer_Test extends WP_UnitTestCase {

	public function get_amp_to_amp_navigation_data() {
		return [
			[
				[ 'add_query_vars' => true ],
				[ true, true, false, false, false, false, false ],
			],
			[
				[ 'add_query_vars' => false ],
				[ false, false, false, false, false, false, false ],
			],
		];
	}

	/**
	 * @dataProvider get_amp_to_amp_navigation_data
	 */
	public function test_amp_to_amp_navigation( $args_array, $expected_array ) {
		// Enable pretty permalinks to keep the AMP slug as the only query var.
		global $wp_rewrite;
		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->use_trailing_slashes = true;
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();

		$post_to_link_to = self::factory()->post->create(
			[
				'post_name'   => 'link-target-post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		$post = self::factory()->post->create_and_get(
			[
				'post_name'    => 'some-post',
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_content' => sprintf(
					'
						<a id="home-link" href="%s">Home Link</a>
						<a id="internal-link" href="%s">Internal Link</a>
						<a id="external-link" href="%s">External Link</a>
						<a id="php-file-link" href="%s">PHP File Link</a>
						<a id="feed-link" href="%s">Feed Link</a>
						<a id="admin-link" href="%s">Admin Link</a>
						<a id="image-link" href="%s">Image Link</a>
					',
					home_url(),
					get_permalink( $post_to_link_to ),
					'https://example.com',
					site_url( '/wp-login.php' ),
					get_feed_link(),
					admin_url( 'options-general.php?page=some-plugin' ),
					content_url( '/some-image.jpg' )
				),
			]
		);

		$amp_slug = amp_get_slug();
		$dom      = AMP_DOM_Utils::get_dom_from_content( $post->post_content );
		$xpath    = new DOMXPath( $dom );

		$sanitizer = new AMP_Link_Sanitizer( $dom, $args_array );
		$sanitizer->sanitize();

		foreach ( [ 'home-link', 'internal-link', 'external-link', 'php-file-link', 'feed-link', 'admin-link', 'image-link' ] as $id ) {
			$link      = $xpath->query( "//*[ @id = '{$id}' ]" )->item( 0 );
			$url       = $link->getAttribute( 'href' );
			$expected  = array_shift( $expected_array );
			$assertion = $expected ? 'assertStringEndsWith' : 'assertStringEndsNotWith';
			$this->$assertion( "?{$amp_slug}", $url );
		}
	}

	public function get_amp_to_amp_meta_tag_data() {
		return [
			[
				[
					'has_theme_support' => false,
					'add_a2a_meta'      => AMP_Link_Sanitizer::DEFAULT_A2A_META_CONTENT,
				],
				false,
			],
			[
				[
					'has_theme_support' => true,
					'add_a2a_meta'      => AMP_Link_Sanitizer::DEFAULT_A2A_META_CONTENT,
				],
				true,
			],
			[
				[
					'has_theme_support' => false,
					'add_a2a_meta'      => false,
				],
				false,
			],
			[
				[
					'has_theme_support' => true,
					'add_a2a_meta'      => false,
				],
				false,
			],
		];
	}

	/**
	 * @dataProvider get_amp_to_amp_meta_tag_data
	 */
	public function test_amp_to_amp_meta_tag( $args_array, $expected ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( '<div>Hello</div>' );
		$xpath = new DOMXPath( $dom );

		$sanitizer = new AMP_Link_Sanitizer( $dom, $args_array );
		$sanitizer->sanitize();

		$meta_tag = $xpath->query( "//meta[ @name = 'amp-to-amp-navigation' ]" )->item( 0 );
		$this->assertEquals( $expected, $meta_tag instanceof DOMElement );
	}

	public function get_amp_to_amp_rel_attribute_data() {
		return [
			[
				[
					'add_amphtml_rel' => true,
					'add_query_vars'  => true,
				],
				true,
				true,
			],
			[
				[
					'add_amphtml_rel' => false,
					'add_query_vars'  => true,
				],
				true,
				false,
			],
			[
				[
					'add_amphtml_rel' => true,
					'add_query_vars'  => false,
				],
				false,
				true,
			],
			[
				[
					'add_amphtml_rel' => false,
					'add_query_vars'  => false,
				],
				false,
				false,
			],
		];
	}

	/**
	 * @dataProvider get_amp_to_amp_rel_attribute_data
	 */
	public function test_amp_to_amp_rel_attribute( $args_array, $expected_slug, $expected_rel ) {
		$content = sprintf( '<a id="home-link" href="%s">Home Link</a>', home_url() );

		$amp_slug = amp_get_slug();
		$dom      = AMP_DOM_Utils::get_dom_from_content( $content );
		$xpath    = new DOMXPath( $dom );

		$sanitizer = new AMP_Link_Sanitizer( $dom, $args_array );
		$sanitizer->sanitize();

		$link      = $xpath->query( "//*[ @id = 'home-link' ]" )->item( 0 );
		$url       = $link->getAttribute( 'href' );
		$assertion = $expected_slug ? 'assertStringEndsWith' : 'assertStringEndsNotWith';
		$this->$assertion( "?{$amp_slug}", $url );

		$this->assertEquals( $expected_rel, $link->hasAttribute( 'rel' ) );
		if ( $expected_rel ) {
			$this->assertStringEndsWith( 'amphtml', $link->getAttribute( 'rel' ) );
		}
	}

	public function get_test_amp_to_amp_linking_enabled() {
		return [
			[ '__return_true', true ],
			[ '__return_false', false ],
		];
	}

	/**
	 * @dataProvider get_test_amp_to_amp_linking_enabled
	 */
	public function test_amp_to_amp_linking_enabled_true( $filter, $expected ) {
		add_filter( 'amp_to_amp_linking_enabled', $filter );
		$sanitizers = amp_get_content_sanitizers();
		$this->assertArrayHasKey( 'AMP_Link_Sanitizer', $sanitizers );
		$this->assertArrayHasKey( 'add_query_vars', $sanitizers['AMP_Link_Sanitizer'] );
		$this->assertEquals( $expected, $sanitizers['AMP_Link_Sanitizer']['add_query_vars'] );
	}
}
