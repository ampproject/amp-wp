<?php
/**
 * Class AMP_Link_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\MobileRedirection;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;

/**
 * Class AMP_Link_Sanitizer_Test
 */
class AMP_Link_Sanitizer_Test extends DependencyInjectedTestCase {

	/**
	 * Data for test_amp_to_amp_navigation.
	 *
	 * @return array
	 */
	public function get_amp_to_amp_navigation_data() {
		return [
			'Paired AMP' => [ true ],
			'AMP-First'  => [ false ],
		];
	}

	/**
	 * Test adding links.
	 *
	 * @dataProvider get_amp_to_amp_navigation_data
	 * @covers AMP_Link_Sanitizer::process_links()
	 * @covers AMP_Link_Sanitizer::process_element()
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

		$post_link         = get_permalink(
			self::factory()->post->create(
				[
					'post_name'   => 'link-target-post',
					'post_status' => 'publish',
					'post_type'   => 'post',
				]
			)
		);
		$excluded_amp_link = get_permalink( self::factory()->post->create() );
		$excluded_urls     = [ $excluded_amp_link ];

		$links = [
			'home-link'           => [
				'href'         => home_url( '/' ),
				'expected_amp' => true,
				'expected_rel' => null,
			],
			'internal-link'       => [
				'href'         => $post_link,
				'expected_amp' => true,
				'expected_rel' => null,
			],
			'non_amp_to_amp_rel'  => [
				'href'         => $post_link,
				'expected_amp' => false,
				'rel'          => 'noamphtml',
				'expected_rel' => null,
			],
			'two_rel'             => [
				'href'         => $post_link,
				'expected_amp' => false,
				'rel'          => 'help noamphtml',
				'expected_rel' => 'help noamphtml',
			],
			'multiple_rel'        => [
				'href'         => $post_link,
				'expected_amp' => false,
				'rel'          => 'noamphtml nofollow help',
				'expected_rel' => 'noamphtml nofollow help',
			],
			'rel_trailing_space'  => [
				'href'         => $post_link,
				'expected_amp' => false,
				'rel'          => 'noamphtml ',
				'expected_rel' => null,
			],
			'empty_rel'           => [
				'href'         => $post_link,
				'expected_amp' => true,
				'rel'          => '',
				'expected_rel' => null,
			],
			'excluded_amp_link'   => [
				'href'         => $excluded_amp_link,
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'fragment_identifier' => [
				'href'         => $excluded_amp_link . '#heading',
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'ugc-link'            => [
				'rel'          => 'ugc nofollow',
				'href'         => home_url( '/some/user/generated/data/' ),
				'expected_amp' => true,
				'expected_rel' => 'ugc nofollow',
			],
			'page-anchor'         => [
				'href'         => '#top',
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'other-page-anchor'   => [
				'href'         => $post_link . '#top',
				'expected_amp' => true,
				'expected_rel' => null,
			],
			'external-link'       => [
				'href'         => 'https://external.example.com/',
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'non_amp_rel_removed' => [
				'href'         => 'https://external.example.com/',
				'expected_amp' => false,
				'rel'          => 'noamphtml',
				'expected_rel' => null,
			],
			'php-file-link'       => [
				'href'         => site_url( '/wp-login.php' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'feed-link'           => [
				'href'         => get_feed_link(),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'admin-link'          => [
				'href'         => admin_url( 'options-general.php?page=some-plugin' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'image-link'          => [
				'href'         => content_url( '/some-image.jpg' ),
				'expected_amp' => false,
				'expected_rel' => null,
			],
			'mailto-link'         => [
				'href'         => 'mailto:nobody@example.com',
				'expected_amp' => false,
				'expected_rel' => null,
			],
		];

		$admin_bar_link_href = home_url( '/?do_something' );

		$html = sprintf( '<div id="wpadminbar"><a id="admin-bar-link" href="%s"></a></div>', esc_url( $admin_bar_link_href ) );
		foreach ( $links as $id => $link_data ) {
			$html .= sprintf( '<a id="%s" href="%s"', esc_attr( $id ), esc_attr( $link_data['href'] ) );
			if ( ! empty( $link_data['rel'] ) ) {
				$html .= sprintf( ' rel="%s"', esc_attr( $link_data['rel'] ) );
			}
			$html .= '>Link</a>';
		}
		$html .= sprintf( '<form id="internal-search" action="%s" method="get"><input name="s" type="search"></form>', esc_url( home_url( '/' ) ) );
		$html .= sprintf( '<form id="internal-post" action="%s" method="post"><input name="content" type="text"></form>', esc_url( home_url( '/' ) ) );
		$html .= sprintf( '<form id="internal-implied-get" action="%s"><input name="s" type="search"></form>', esc_url( home_url( '/' ) ) );
		$html .= '<form id="external-search" action="https://search.example.com/" method="get"><input name="s" type="search"></form>';
		$html .= '<template type="amp-mustache"><div><a id="template-link" href="{{url}}">Link</a></div></template>';

		$dom = AMP_DOM_Utils::get_dom_from_content( $html );

		$sanitizer = new AMP_Link_Sanitizer( $dom, compact( 'paired', 'excluded_urls' ) );
		$sanitizer->sanitize();

		// Confirm admin bar is unchanged.
		$admin_bar_link = $dom->getElementById( 'admin-bar-link' );
		$this->assertFalse( $admin_bar_link->hasAttribute( 'rel' ) );
		$this->assertEquals( $admin_bar_link_href, $admin_bar_link->getAttribute( 'href' ) );

		// Check content links.
		foreach ( $links as $id => $link_data ) {
			$element = $dom->getElementById( $id );
			$this->assertInstanceOf( 'DOMElement', $element, "ID: $id" );
			$rel = (string) $element->getAttribute( 'rel' );
			if ( empty( $link_data['expected_rel'] ) ) {
				$this->assertDoesNotMatchRegularExpression( '/(^|\s)amphtml(\s|$)/', $rel, "ID: $id" );
			} else {
				$this->assertTrue( $element->hasAttribute( 'rel' ) );
				$this->assertEquals( $link_data['expected_rel'], $element->getAttribute( 'rel' ), "ID: $id" );
			}

			if (
				( isset( $link_data['rel'] ) && empty( $link_data['rel'] ) )
				&&
				( isset( $link_data['expected_rel'] ) && empty( $link_data['expected_rel'] ) )
			) {
				$this->assertFalse( $element->hasAttribute( 'rel' ) );
			}

			if ( $paired && $link_data['expected_amp'] ) {
				$this->assertStringContainsString( '?' . amp_get_slug(), $element->getAttribute( 'href' ), "ID: $id" );
			} elseif ( ! $paired || ! $link_data['expected_amp'] ) {
				$this->assertStringNotContainsString( '?' . amp_get_slug() . '=1', $element->getAttribute( 'href' ), "ID: $id" );
			}
		}

		// Confirm changes to form.
		$this->assertEquals( $paired ? 1 : 0, $dom->xpath->query( '//form[ @id = "internal-search" ]//input[ @name = "amp" ]' )->length );
		$this->assertEquals( 0, $dom->xpath->query( '//form[ @id = "internal-post" ]//input[ @name = "amp" ]' )->length );
		$this->assertEquals( $paired ? 1 : 0, $dom->xpath->query( '//form[ @id = "internal-implied-get" ]//input[ @name = "amp" ]' )->length );
		$this->assertEquals( 0, $dom->xpath->query( '//form[ @id = "external-search" ]//input[ @name = "amp" ]' )->length );

		$this->assertEquals( '{{url}}', $dom->getElementById( 'template-link' )->getAttribute( 'href' ) );
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
		$dom = AMP_DOM_Utils::get_dom_from_content( '<div>Hello</div>' );

		$sanitizer = new AMP_Link_Sanitizer( $dom, $sanitizer_args );
		$sanitizer->sanitize();

		$meta_tag = $dom->xpath->query( "//meta[ @name = 'amp-to-amp-navigation' ]" )->item( 0 );
		$this->assertInstanceOf( 'DOMElement', $meta_tag );
		$this->assertEquals( $expected_meta, $meta_tag->getAttribute( 'content' ) );
	}

	/**
	 * Test disabling mobile redirection if the URL is excluded.
	 */
	public function test_disable_mobile_redirect_for_excluded_url() {
		$mobile_redirection = $this->injector->make( MobileRedirection::class );

		AMP_Options_Manager::update_option( Option::MOBILE_REDIRECT, true );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( amp_add_paired_endpoint( home_url( '/' ) ) );
		$mobile_redirection->redirect();

		$link = home_url( '/' );
		$dom  = AMP_DOM_Utils::get_dom_from_content( "<a id='link' href='{$link}'>Foo</a>" );

		$sanitizer = new AMP_Link_Sanitizer( $dom, [ 'excluded_urls' => [ $link ] ] );
		$sanitizer->sanitize();

		$a_tag = $dom->getElementById( 'link' );
		$this->assertEquals( add_query_arg( QueryVar::NOAMP, QueryVar::NOAMP_MOBILE, $link ), $a_tag->getAttribute( 'href' ) );
	}

	/**
	 * Test disabling mobile redirection if the link has the `noamphtml` relationship.
	 */
	public function test_disable_mobile_redirect_for_url_with_noamphtml_rel() {
		$mobile_redirection = $this->injector->make( MobileRedirection::class );

		AMP_Options_Manager::update_option( Option::MOBILE_REDIRECT, true );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->go_to( amp_add_paired_endpoint( home_url( '/' ) ) );
		$mobile_redirection->redirect();

		$link = home_url( '/' );
		$dom  = AMP_DOM_Utils::get_dom_from_content( "<a id='link' href='{$link}' rel='noamphtml'>Foo</a>" );

		$sanitizer = new AMP_Link_Sanitizer( $dom );
		$sanitizer->sanitize();

		$a_tag = $dom->getElementById( 'link' );
		$this->assertEquals( add_query_arg( QueryVar::NOAMP, QueryVar::NOAMP_MOBILE, $link ), $a_tag->getAttribute( 'href' ) );
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
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_filter( 'amp_to_amp_linking_enabled', $filter );
		$sanitizers = amp_get_content_sanitizers();
		if ( $expected ) {
			$this->assertArrayHasKey( AMP_Link_Sanitizer::class, $sanitizers );
		} else {
			$this->assertArrayNotHasKey( AMP_Link_Sanitizer::class, $sanitizers );
		}
	}
}
