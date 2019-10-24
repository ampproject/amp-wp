<?php

require_once __DIR__ . '/../../amp-beta-tester.php';

/**
 * Class AMP_Beta_Tester_Test
 *
 * @covers \AMP_Beta_Tester
 */
class AMP_Beta_Tester_Test extends WP_UnitTestCase {

	/**
	 * Allows for a custom GitHub API response to be set.
	 *
	 * @var string
	 */
	public $custom_github_api_response = null;

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
	 * Get data for testing \AMP_Beta_Tester\update_amp_manifest.
	 *
	 * @return array Data.
	 */
	public function get_plugin_updates_data() {
		return [
			'transient does not have no_update property' => [
				$this->generate_github_api_response(),
				$this->generate_plugins_manifest(),
				$this->generate_plugins_manifest(),
			],

			'transient has no_update property'           => [
				$this->generate_github_api_response(),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
			],

			'no pre-release update available'            => [
				$this->generate_github_api_response( null, false ),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
			],

			'has pre-release update without amp.zip'     => [
				$this->generate_github_api_response( null, true, false ),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
					]
				),
			],

			'has pre-release update with amp.zip'        => [
				$this->generate_github_api_response( '999.999.999' ),
				$this->generate_plugins_manifest(
					[
						'no_update' => [
							AMP__PLUGIN__BASENAME => $this->generate_amp_manifest( AMP__VERSION, false ),
						],
					]
				),
				$this->generate_plugins_manifest(
					[
						'no_update' => [],
						'response'  => [
							AMP__PLUGIN__BASENAME => $this->generate_amp_manifest( '999.999.999', true ),
						],
					]
				),
			],
		];
	}

	/**
	 * Test \AMP_Beta_Tester\update_amp_manifest().
	 *
	 * @dataProvider get_plugin_updates_data
	 * @covers \AMP_Beta_Tester\update_amp_manifest()
	 *
	 * @param stdClass $source   Source.
	 * @param string   $expected Expected.
	 */
	public function test__update_amp_manifest( $github_response, $source, $expected ) {
		$this->custom_github_api_response = $github_response;

		add_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ], 10, 3 );

		$modified_manifest = AMP_Beta_Tester\update_amp_manifest( $source );

		$this->assertEquals( $expected, $modified_manifest );

		remove_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ] );
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
					link.href = 'http://example.com';				} );
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
				json_decode( $this->generate_github_api_response() ),
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
		$this->custom_github_api_response = null;

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
			'built pre-release'                   => [
				'1.4.0-beta1-20191023T111320Z-492bfcf2',
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

		AMP_Beta_Tester\show_unstable_reminder( $wp_admin_bar );

		$node = $wp_admin_bar->get_node( 'amp-version-code' );

		$this->assertEquals( sprintf( 'v%s', AMP__VERSION ), $node->title );
		$this->assertEquals( admin_url( 'plugins.php?s=amp&plugin_status=active' ), $node->href );
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

		$body = empty( $this->custom_github_api_response )
			? $this->generate_github_api_response()
			: $this->custom_github_api_response;

		return [
			'body'          => $body,
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
	 * Generate a custom GitHub API response.
	 *
	 * @param string $version         AMP version to use.
	 * @param bool   $is_pre_released Whether to set release as pre-release or not.
	 * @param bool   $with_zip        Whether to include an 'amp.zip' asset or not.
	 * @return string
	 */
	private function generate_github_api_response( $version = '1.0.0-beta', $is_pre_released = true, $with_zip = true ) {
		$response = [
			'url'              => 'https://api.github.com/repos/ampproject/amp-wp/releases/20819569',
			'assets_url'       => 'https://api.github.com/repos/ampproject/amp-wp/releases/20819569/assets',
			'upload_url'       => 'https://uploads.github.com/repos/ampproject/amp-wp/releases/20819569/assets{?name,label}',
			'html_url'         => "https://github.com/ampproject/amp-wp/releases/tag/${version}",
			'id'               => 20819569,
			'node_id'          => 'MDc6UmVsZWFzZTIwODE5NTY5',
			'tag_name'         => "${version}",
			'target_commitish' => 'develop',
			'name'             => "${version}",
			'draft'            => false,
			'author'           => [
				'login'               => 'westonruter',
				'id'                  => 134745,
				'node_id'             => 'MDQ6VXNlcjEzNDc0NQ==',
				'avatar_url'          => 'https://avatars2.githubusercontent.com/u/134745?v=4',
				'gravatar_id'         => '',
				'url'                 => 'https://api.github.com/users/westonruter',
				'html_url'            => 'https://github.com/westonruter',
				'followers_url'       => 'https://api.github.com/users/westonruter/followers',
				'following_url'       => 'https://api.github.com/users/westonruter/following{/other_user}',
				'gists_url'           => 'https://api.github.com/users/westonruter/gists{/gist_id}',
				'starred_url'         => 'https://api.github.com/users/westonruter/starred{/owner}{/repo}',
				'subscriptions_url'   => 'https://api.github.com/users/westonruter/subscriptions',
				'organizations_url'   => 'https://api.github.com/users/westonruter/orgs',
				'repos_url'           => 'https://api.github.com/users/westonruter/repos',
				'events_url'          => 'https://api.github.com/users/westonruter/events{/privacy}',
				'received_events_url' => 'https://api.github.com/users/westonruter/received_events',
				'type'                => 'User',
				'site_admin'          => false,
			],
			'prerelease'       => $is_pre_released,
			'created_at'       => '2019-10-18T23:27:54Z',
			'published_at'     => '2019-10-18T23:37:22Z',
			'tarball_url'      => "https://api.github.com/repos/ampproject/amp-wp/tarball/${version}",
			'zipball_url'      => "https://api.github.com/repos/ampproject/amp-wp/zipball/${version}",
			'body'             => '',
		];

		if ( $with_zip ) {
			$response['assets'] = [
				[
					'url'                  => 'https://api.github.com/repos/ampproject/amp-wp/releases/assets/15579699',
					'id'                   => 15579699,
					'node_id'              => 'MDEyOlJlbGVhc2VBc3NldDE1NTc5Njk5',
					'name'                 => 'amp.zip',
					'label'                => null,
					'uploader'             => [
						'login'               => 'westonruter',
						'id'                  => 134745,
						'node_id'             => 'MDQ6VXNlcjEzNDc0NQ==',
						'avatar_url'          => 'https://avatars2.githubusercontent.com/u/134745?v=4',
						'gravatar_id'         => '',
						'url'                 => 'https://api.github.com/users/westonruter',
						'html_url'            => 'https://github.com/westonruter',
						'followers_url'       => 'https://api.github.com/users/westonruter/followers',
						'following_url'       => 'https://api.github.com/users/westonruter/following{/other_user}',
						'gists_url'           => 'https://api.github.com/users/westonruter/gists{/gist_id}',
						'starred_url'         => 'https://api.github.com/users/westonruter/starred{/owner}{/repo}',
						'subscriptions_url'   => 'https://api.github.com/users/westonruter/subscriptions',
						'organizations_url'   => 'https://api.github.com/users/westonruter/orgs',
						'repos_url'           => 'https://api.github.com/users/westonruter/repos',
						'events_url'          => 'https://api.github.com/users/westonruter/events{/privacy}',
						'received_events_url' => 'https://api.github.com/users/westonruter/received_events',
						'type'                => 'User',
						'site_admin'          => false,
					],
					'content_type'         => 'application/zip',
					'state'                => 'uploaded',
					'size'                 => 1007810,
					'download_count'       => 2,
					'created_at'           => '2019-10-18T23:30:36Z',
					'updated_at'           => '2019-10-18T23:30:43Z',
					'browser_download_url' => "https://github.com/ampproject/amp-wp/releases/download/${version}/amp.zip",
				],
			];
		}

		// phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
		return json_encode( [ $response ] );
	}

	/**
	 * Generate a generic class imitating a WP plugin update transient with custom properties.
	 *
	 * @param array $properties Properties with their associated values.
	 * @return stdClass
	 */
	private function generate_plugins_manifest( $properties = [] ) {
		$plugin_manifest = new stdClass();

		foreach ( $properties as $key => $value ) {
			$plugin_manifest->{$key} = $value;
		}

		return $plugin_manifest;
	}

	/**
	 * Generate a custom AMP plugin update manifest.
	 *
	 * @param string $plugin_version AMP plugin version to use.
	 * @param bool   $from_github To use WP or GitHub's URL.
	 * @return object
	 */
	private function generate_amp_manifest( $plugin_version, $from_github ) {
		$url = $from_github
			? "https://github.com/ampproject/amp-wp/releases/tag/${plugin_version}"
			: 'https://wordpress.org/plugins/amp/';

		$package = $from_github
			? "https://github.com/ampproject/amp-wp/releases/download/${plugin_version}/amp.zip"
			: "https://downloads.wordpress.org/plugin/amp.${plugin_version}.zip";

		return (object) [
			'id'          => 'w.org/plugins/amp',
			'slug'        => 'amp',
			'plugin'      => 'amp/amp.php',
			'new_version' => $plugin_version,
			'url'         => $url,
			'package'     => $package,
			'icons'       => [
				'2x' => 'https://ps.w.org/amp/assets/icon-256x256.png?rev=1987390',
				'1x' => 'https://ps.w.org/amp/assets/icon-128x128.png?rev=1987390',
			],
			'banners'     => [
				'2x' => 'https://ps.w.org/amp/assets/banner-1544x500.png?rev=1987390',
				'1x' => 'https://ps.w.org/amp/assets/banner-772x250.png?rev=1987390',
			],
			'banners_rtl' => [],
		];
	}
}
