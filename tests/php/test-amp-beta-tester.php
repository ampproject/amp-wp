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
	 * Get plugin version details data.
	 *
	 * @return array plugin version details data.
	 */
	public function get_view_version_details_data() {
		// Note: scripts below are formatted exactly to how they would be outputted.
		return [
			'no version code'                 => [
				[ 'Version' => '' ],
				'',
			],
			'not a pre-release'               => [
				[ 'Version' => '1.0.0' ],
				'',
			],
			'is a pre-release without an url' => [
				[
					'Version' => '1.0.0-beta1',
				],
				"		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const links = document.querySelectorAll(\"[data-slug='amp'] a.thickbox.open-plugin-details-modal\");

				links.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
									} );
			}, false);
		</script>
		",
			],
			'is a pre-release with url'       => [
				[
					'Version' => '1.0.0-beta1',
					'url'     => 'example.com',
				],
				"		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const links = document.querySelectorAll(\"[data-slug='amp'] a.thickbox.open-plugin-details-modal\");

				links.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
					link.href = 'example.com';				} );
			}, false);
		</script>
		",
			],
		];
	}

	/**
	 * Test replace_view_version_details_link().
	 *
	 * @dataProvider get_view_version_details_data
	 * @covers \AMP_Beta_Tester\replace_view_version_details_link()
	 *
	 * @param array $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__view_version_details( $source, $expected ) {
		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link( null, $source );
		$script = ob_get_clean();

		$this->assertEquals( $expected, $script );
	}

	/**
	 * Get data for test_get_amp_github_releases().
	 *
	 * @return array Data.
	 */
	public function get_amp_releases_data() {
		return [
			'failed http request'     => [
				'mock_http_request_error',
				null,
			],
			'successful http request' => [
				'mock_github_api_http_request',
				json_decode( $this->github_api_response ),
			],
		];
	}

	/**
	 * Test get_amp_github_releases().
	 *
	 * @dataProvider get_amp_releases_data
	 * @covers       \AMP_Beta_Tester\get_amp_github_releases()
	 *
	 * @param string $source Method for filter hook callback.
	 * @param string $expected Expected.
	 */
	public function test__get_amp_github_releases( $source, $expected ) {
		add_filter( 'pre_http_request', [ $this, $source ], 10, 3 );
		$amp_releases = AMP_Beta_Tester\get_amp_github_releases();

		$this->assertEquals( $expected, $amp_releases );

		remove_filter( 'pre_http_request', [ $this, $source ] );
	}

	/**
	 * Get pre-release data.
	 *
	 * @return array pre-release data.
	 */
	public function get_pre_release_data() {
		return [
			'null'                                => [
				null,
				false,
			],
			'empty string'                        => [
				'',
				false,
			],
			'major version only'                  => [
				'1',
				false,
			],
			'version without a patch number'      => [
				'1.0',
				false,
			],
			'stable version'                      => [
				'1.0.0',
				false,
			],
			'beta version without an identifier'  => [
				'1.0.0-beta',
				true,
			],
			'beta version with an identifier'     => [
				'1.0.0-beta1',
				true,
			],
			'alpha version without an identifier' => [
				'1.0.0-alpha',
				true,
			],
			'alpha version with an identifier'    => [
				'1.0.0-alpha1',
				true,
			],
			'RC version without an identifier'    => [
				'1.0.0-RC',
				true,
			],
			'RC version with an identifier'       => [
				'1.0.0-RC1',
				true,
			],
		];
	}

	/**
	 * Test is_pre_release().
	 *
	 * @dataProvider get_pre_release_data
	 * @covers \AMP_Beta_Tester\is_pre_release()
	 */
	public function test__pre_release( $source, $expected ) {
		$is_pre_release = AMP_Beta_Tester\is_pre_release( $source );

		$this->assertEquals( $expected, $is_pre_release );
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
