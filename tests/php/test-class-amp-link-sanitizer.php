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

	/**
	 * Data for test_amp_to_amp_navigation.
	 *
	 * @return array
	 */
	public function get_amp_to_amp_navigation_data() {
		return [
			[ true ],
			[ false ],
		];
	}

	/**
	 * Test adding links.
	 *
	 * @dataProvider get_amp_to_amp_navigation_data
	 * @covers AMP_Link_Sanitizer::process_links()
	 *
	 * @param bool $paired Paired.
	 */
	public function test_amp_to_amp_navigation( $paired ) {
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

		$links = [
			'home-link'         => [
				'href'         => home_url( '/' ),
				'expected_amp' => true,
				'expected_rel' => 'amphtml',
			],
			'internal-link'     => [
				'href'         => get_permalink( $post_to_link_to ),
				'expected_amp' => true,
				'expected_rel' => 'amphtml',
			],
			'ugc-link'          => [
				'rel'          => 'ugc',
				'href'         => home_url( '/some/user/generated/data/' ),
				'expected_amp' => true,
				'expected_rel' => 'ugc amphtml',
			],
			'page-anchor'       => [
				'href'         => '#top',
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'other-page-anchor' => [
				'href'         => get_permalink( $post_to_link_to ) . '#top',
				'expected_amp' => true,
				'expected_rel' => 'amphtml',
			],
			'external-link'     => [
				'href'         => 'https://external.example.com/',
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'php-file-link'     => [
				'href'         => site_url( '/wp-login.php' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'feed-link'         => [
				'href'         => get_feed_link(),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'admin-link'        => [
				'href'         => admin_url( 'options-general.php?page=some-plugin' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'image-link'        => [
				'href'         => content_url( '/some-image.jpg' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
		];

		$html = '';
		foreach ( $links as $id => $link_data ) {
			$html .= sprintf( '<a id="%s" href="%s"', esc_attr( $id ), esc_attr( $link_data['href'] ) );
			if ( isset( $link_data['rel'] ) ) {
				$html .= sprintf( ' rel="%s"', esc_attr( $link_data['rel'] ) );
			}
			$html .= '>Link</a>';
		}

		$post = self::factory()->post->create_and_get(
			[
				'post_name'    => 'some-post',
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_content' => $html,
			]
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $post->post_content );

		$sanitizer = new AMP_Link_Sanitizer( $dom, compact( 'paired' ) );
		$sanitizer->sanitize();

		foreach ( $links as $id => $link_data ) {
			$element = $dom->getElementById( $id );
			$this->assertInstanceOf( 'DOMElement', $element, "ID: $id" );
			if ( empty( $link_data['expected_rel'] ) ) {
				$this->assertFalse( $element->hasAttribute( 'rel' ) );
			} else {
				$this->assertEquals( $link_data['expected_rel'], $element->getAttribute( 'rel' ) );
			}

			if ( $paired && $link_data['expected_amp'] ) {
				$this->assertContains( '?' . amp_get_slug(), $element->getAttribute( 'href' ) );
			} elseif ( ! $paired || ! $link_data['expected_amp'] ) {
				$this->assertNotContains( '?' . amp_get_slug(), $element->getAttribute( 'href' ) );
			}
		}
	}

	/**
	 * Get data for test_amp_to_amp_meta_tag.
	 *
	 * @return array
	 */
	public function get_test_amp_to_amp_meta_tag_data() {
		return [
			'default' => [
				[],
				AMP_Link_Sanitizer::DEFAULT_META_CONTENT,
			],
			'custom'  => [
				[
					'meta_content' => 'AMP-Redirect-To',
				],
				'AMP-Redirect-To',
			],
		];
	}

	/**
	 * Test adding amp-to-amp-navigation meta tag.
	 *
	 * @dataProvider get_test_amp_to_amp_meta_tag_data
	 * @param array  $sanitizer_args Sanitizer args.
	 * @param string $expected_meta Expected meta content.
	 */
	public function test_amp_to_amp_meta_tag( $sanitizer_args, $expected_meta ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( '<div>Hello</div>' );
		$xpath = new DOMXPath( $dom );

		$sanitizer = new AMP_Link_Sanitizer( $dom, $sanitizer_args );
		$sanitizer->sanitize();

		$meta_tag = $xpath->query( "//meta[ @name = 'amp-to-amp-navigation' ]" )->item( 0 );
		$this->assertInstanceOf( 'DOMElement', $meta_tag );
		$this->assertEquals( $expected_meta, $meta_tag->getAttribute( 'content' ) );
	}

	/**
	 * Get data for test_amp_to_amp_linking_enabled.
	 *
	 * @return array
	 */
	public function get_test_amp_to_amp_linking_enabled() {
		return [
			[ '__return_true', true ],
			[ '__return_false', false ],
		];
	}

	/**
	 * Test the 'amp_to_amp_linking_enabled filter.
	 *
	 * @dataProvider get_test_amp_to_amp_linking_enabled
	 * @covers ::amp_get_content_sanitizers()
	 *
	 * @param callable $filter   Filter.
	 * @param bool     $expected Whether to expect the sanitizer to be present.
	 */
	public function test_amp_to_amp_linking_enabled( $filter, $expected ) {
		add_filter( 'amp_to_amp_linking_enabled', $filter );
		$sanitizers = amp_get_content_sanitizers();
		if ( $expected ) {
			$this->assertArrayHasKey( 'AMP_Link_Sanitizer', $sanitizers );
		} else {
			$this->assertArrayNotHasKey( 'AMP_Link_Sanitizer', $sanitizers );
		}
	}
}
