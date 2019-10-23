<?php

/**
 * Class AMP_Beta_Tester_Test
 *
 * @covers \AMP_Beta_Tester
 */
class AMP_Beta_Tester_Test extends WP_UnitTestCase {

	protected $github_api_response = '[{ "url": "https://api.github.com/repos/ampproject/amp-wp/releases/20819569", "assets_url": "https://api.github.com/repos/ampproject/amp-wp/releases/20819569/assets", "upload_url": "https://uploads.github.com/repos/ampproject/amp-wp/releases/20819569/assets{?name,label}", "html_url": "https://github.com/ampproject/amp-wp/releases/tag/1.4.0-beta1", "id": 20819569, "node_id": "MDc6UmVsZWFzZTIwODE5NTY5", "tag_name": "1.4.0-beta1", "target_commitish": "develop", "name": "1.4.0-beta1", "draft": false, "author": { "login": "westonruter", "id": 134745, "node_id": "MDQ6VXNlcjEzNDc0NQ==", "avatar_url": "https://avatars2.githubusercontent.com/u/134745?v=4", "gravatar_id": "", "url": "https://api.github.com/users/westonruter", "html_url": "https://github.com/westonruter", "followers_url": "https://api.github.com/users/westonruter/followers", "following_url": "https://api.github.com/users/westonruter/following{/other_user}", "gists_url": "https://api.github.com/users/westonruter/gists{/gist_id}", "starred_url": "https://api.github.com/users/westonruter/starred{/owner}{/repo}", "subscriptions_url": "https://api.github.com/users/westonruter/subscriptions", "organizations_url": "https://api.github.com/users/westonruter/orgs", "repos_url": "https://api.github.com/users/westonruter/repos", "events_url": "https://api.github.com/users/westonruter/events{/privacy}", "received_events_url": "https://api.github.com/users/westonruter/received_events", "type": "User", "site_admin": false }]';

	/**
	 * Runs the routine before setting up all tests.
	 */
	public static function setupBeforeClass() {
		require_once __DIR__ . '/../../amp-beta-tester.php';

		parent::setUpBeforeClass();
	}

	/**
	 * Test force_plugin_update_check().
	 *
	 * @covers \AMP_Beta_Tester\force_plugin_update_check()
	 */
	public function test_force_plugin_update_check() {
		set_site_transient( 'update_plugins', new \stdClass() );
		AMP_Beta_Tester\force_plugin_update_check();

		$this->assertFalse( get_site_transient( 'update_plugins' ) );
	}

	/**
	 * Test init().
	 *
	 * @covers \AMP_Beta_Tester\init()
	 */
	public function test_init() {
		AMP_Beta_Tester\init();

		$this->assertEquals( 10, has_filter( 'admin_bar_menu', 'AMP_Beta_Tester\show_unstable_reminder' ) );
		$this->assertEquals( 10, has_filter( 'pre_set_site_transient_update_plugins', 'AMP_Beta_Tester\update_amp_manifest' ) );
		$this->assertEquals( 10, has_action( 'after_plugin_row_amp/amp.php', 'AMP_Beta_Tester\replace_view_version_details_link' ) );
	}

	/**
	 * Test replace_view_version_details_link().
	 *
	 * @covers \AMP_Beta_Tester\replace_view_version_details_link()
	 */
	public function test_replace_view_version_details_link() {
		// It should not output anything if its not a pre-release.
		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link( null, [ 'Version' => '' ] );
		$actual = ob_get_clean();

		$this->assertEquals( '', $actual );

		// It should output a script if its a pre-release.
		ob_start();
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const links = document.querySelectorAll("[data-slug='amp'] a.thickbox.open-plugin-details-modal");

				links.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
					link.href = 'example.com';				} );
			}, false);
		</script>
		<?php
		$expected = ob_get_clean();

		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link(
			null,
			[
				'Version' => '1.0.0-beta',
				'url'     => 'example.com',
			]
		);
		$actual = ob_get_clean();

		$this->assertEquals( $expected, $actual );

		// It should not output a script if its a pre-release and no URL provided.
		ob_start();
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const links = document.querySelectorAll("[data-slug='amp'] a.thickbox.open-plugin-details-modal");

				links.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
									} );
			}, false);
		</script>
		<?php
		$expected = ob_get_clean();

		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link(
			null,
			[
				'Version' => '1.0.0-beta',
			]
		);
		$actual = ob_get_clean();

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test get_amp_github_releases().
	 *
	 * @covers \AMP_Beta_Tester\get_amp_github_releases()
	 */
	public function test_get_amp_github_releases() {
		// It returns `null` on a failed HTTP request.
		add_filter( 'pre_http_request', [ $this, 'mock_http_request_error' ], 10, 3 );
		$amp_releases = AMP_Beta_Tester\get_amp_github_releases();

		$this->assertEquals( null, $amp_releases );

		remove_filter( 'pre_http_request', [ $this, 'mock_http_request_error' ] );

		// It returns a JSON array on a successful HTTP request.
		add_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ], 10, 3 );
		$amp_releases = AMP_Beta_Tester\get_amp_github_releases();

		$this->assertEquals( json_decode( $this->github_api_response ), $amp_releases );

		remove_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ] );
	}

	/**
	 * Test is_pre_release().
	 *
	 * @covers \AMP_Beta_Tester\is_pre_release()
	 */
	public function test_is_pre_release() {
		$actual = AMP_Beta_Tester\is_pre_release( '' );
		$this->assertFalse( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0' );
		$this->assertFalse( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-beta' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-beta1' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-alpha' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-RC1' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-RC' );
		$this->assertTrue( $actual );
	}

	/**
	 * Test show_unstable_reminder().
	 *
	 * @covers \AMP_Beta_Tester\show_unstable_reminder()
	 */
	public function test_show_unstable_reminder() {
		if ( ! \AMP_Beta_Tester\is_pre_release( AMP__VERSION ) ) {
			$this->markTestSkipped( 'Unstable reminder is only shown on non-stable versions.' );
		}

		global $show_admin_bar, $wp_admin_bar;

		$show_admin_bar = true;

		_wp_admin_bar_init();

		ob_start();
		AMP_Beta_Tester\show_unstable_reminder();
		$styles = ob_get_clean();

		$this->assertEquals( $styles, '<style>#wpadminbar #wp-admin-bar-amp-beta-tester-admin-bar { background: #0075C2; }</style>' );

		$node_amp_beta_tester = $wp_admin_bar->get_node( 'amp-beta-tester-admin-bar' );

		$this->assertEquals( 'AMP v' . AMP__VERSION, $node_amp_beta_tester->title );
		$this->assertEquals( admin_url( 'admin.php?page=amp-options' ), $node_amp_beta_tester->href );
	}

	/**
	 * Mock a failed HTTP request to GitHub's API.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return WP_Error Simulated HTTP error.
	 */
	public function mock_http_request_error( $preempt, $r, $url ) {
		if ( false === strpos( $url, 'api.github.com' ) ) {
			return $preempt;
		}

		return new WP_Error( 'failed_http_error' );
	}

	/**
	 * Mock a successful HTTP request to GitHub's API.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_github_api_http_request( $preempt, $r, $url ) {
		if ( false === strpos( $url, 'api.github.com' ) ) {
			return $preempt;
		}

		return [
			'body'          => $this->github_api_response,
			'headers'       => [],
			'response'      => [
				'code'    => 200,
				'message' => 'ok',
			],
			'cookies'       => [],
			'http_response' => null,
		];
	}
}
