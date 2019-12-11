<?php

require_once __DIR__ . '/../../amp-beta-tester.php';

use Amp\AmpWP\Tests\WP_Filesystem_Mock;

/**
 * Class AMP_Beta_Tester_Test
 *
 * @covers \AMP_Beta_Tester
 */
class AMP_Beta_Tester_Test extends WP_UnitTestCase {

	/**
	 * Allows for a custom GitHub API response to be returned.
	 *
	 * @var string
	 */
	public $custom_github_api_response = null;

	/**
	 * Test `\AMP_Beta_Tester\force_plugin_update_check()`.
	 *
	 * @covers \AMP_Beta_Tester\force_plugin_update_check()
	 */
	public function test_force_plugin_update_check() {
		set_site_transient( 'update_plugins', new \stdClass() );
		AMP_Beta_Tester\force_plugin_update_check();

		$this->assertFalse( get_site_transient( 'update_plugins' ) );
	}

	/**
	 * Test `\AMP_Beta_Tester\remove_plugin_data()`.
	 *
	 * @covers \AMP_Beta_Tester\remove_plugin_data()
	 */
	public function test_remove_plugin_data() {
		set_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT, 'foo' );
		set_site_transient( 'update_plugins', 'bar' );

		AMP_Beta_Tester\remove_plugin_data();

		$this->assertFalse( get_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT ) );
		$this->assertFalse( get_site_transient( 'update_plugins' ) );
	}

	/**
	 * Test `\AMP_Beta_Tester\init()`.
	 *
	 * @covers \AMP_Beta_Tester\init()
	 */
	public function test_init() {
		AMP_Beta_Tester\init();

		// These actions are only fired when the AMP plugin is activated.
		$this->assertTrue( defined( 'AMP__VERSION' ) );
		$this->assertEquals( 10, has_action( 'admin_init', 'AMP_Beta_Tester\register_settings' ) );
		$this->assertEquals( 10, has_action( 'admin_menu', 'AMP_Beta_Tester\add_to_setting_pages' ) );

		$this->assertEquals( 10, has_filter( 'plugins_api_result', 'AMP_Beta_Tester\update_amp_plugin_details' ) );
		$this->assertEquals( 10, has_filter( 'pre_set_site_transient_update_plugins', 'AMP_Beta_Tester\update_amp_manifest' ) );
		$this->assertEquals( 10, has_filter( 'upgrader_post_install', 'AMP_Beta_Tester\move_plugin_to_correct_folder' ) );
		$this->assertEquals( 10, has_filter( 'auto_update_plugin', 'AMP_Beta_Tester\can_auto_update_amp_plugin' ) );
	}

	/**
	 * Test `\AMP_Beta_Tester\register_settings()`.
	 *
	 * @covers \AMP_Beta_Tester\register_settings()
	 */
	public function test_register_settings() {
		global $wp_registered_settings;

		$expected = [
			'type'              => 'array',
			'group'             => AMP_Options_Manager::OPTION_NAME,
			'description'       => '',
			'sanitize_callback' => 'AMP_Beta_Tester\validate_settings',
			'show_in_rest'      => false,
			'default'           => [],
		];

		AMP_Beta_Tester\register_settings();

		$this->assertEquals( $expected, $wp_registered_settings[ AMP_BETA_OPTION_NAME ] );
	}

	/**
	 * Test data for `test_validate_settings()`.
	 *
	 * @return array Data.
	 */
	public function data_for_validate_settings() {
		return [
			'no_settings'          => [
				null,
				null,
			],
			'auto_update_enabled'  => [
				[ 'should_auto_update' => 'on' ],
				[ 'should_auto_update' => true ],
			],
			'auto_update_disabled' => [
				[ 'should_auto_update' => '' ],
				[ 'should_auto_update' => false ],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\validate_settings()`.
	 *
	 * @dataProvider data_for_validate_settings
	 * @covers       \AMP_Beta_Tester\validate_settings()
	 *
	 * @param array|null $source   Source.
	 * @param array|null $expected Expected.
	 */
	public function test_validate_settings( $source, $expected ) {
		$actual = AMP_Beta_Tester\validate_settings( $source );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `\AMP_Beta_Tester\add_to_setting_pages()`.
	 *
	 * @covers \AMP_Beta_Tester\add_to_setting_pages()
	 */
	public function test_add_to_setting_pages() {
		global $wp_settings_sections, $wp_settings_fields;

		$expected_section = [
			'id'       => 'beta-tester',
			'title'    => false,
			'callback' => '__return_false',
		];

		$expected_field = [
			'id'       => 'auto_updates',
			'title'    => 'Automatic Updates',
			'callback' => 'AMP_Beta_Tester\render_update_settings',
			'args'     => [],
		];

		AMP_Beta_Tester\add_to_setting_pages();

		$this->assertEquals( $expected_section, $wp_settings_sections[ AMP_Options_Manager::OPTION_NAME ]['beta-tester'] );
		$this->assertEquals( $expected_field, $wp_settings_fields[ AMP_Options_Manager::OPTION_NAME ]['beta-tester']['auto_updates'] );
	}

	/**
	 * Test data for `test_get_rendered_settings()`.
	 *
	 * @return array Data.
	 */
	public function data_for_get_rendered_settings() {
		// Note: HTML markups below are formatted exactly to how they would be outputted.
		return [
			'auto_update_disabled' => [
				[ 'should_auto_update' => false ],
				'	<p>
		<label for="should_auto_update">
			<input id="should_auto_update" type="checkbox" name="amp-beta-options[should_auto_update]" >
			Allow the AMP plugin to be automatically updated.		</label>
	</p>
	<p class="description">
		This will include pre-release updates.	</p>
	',
			],

			'auto_update_enabled'  => [
				[ 'should_auto_update' => true ],
				'	<p>
		<label for="should_auto_update">
			<input id="should_auto_update" type="checkbox" name="amp-beta-options[should_auto_update]"  checked=\'checked\'>
			Allow the AMP plugin to be automatically updated.		</label>
	</p>
	<p class="description">
		This will include pre-release updates.	</p>
	',
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\render_update_settings()`.
	 *
	 * @dataProvider data_for_get_rendered_settings
	 * @covers \AMP_Beta_Tester\render_update_settings()
	 *
	 * @param array  $settings Stored plugin settings.
	 * @param string $expected_html Expected HTML markup.
	 */
	public function test_render_update_settings( $settings, $expected_html ) {
		add_filter(
			'pre_option_amp-beta-options',
			static function () use ( $settings ) {
				return $settings;
			}
		);

		ob_start();
		AMP_Beta_Tester\render_update_settings();
		$actual = ob_get_clean();

		$this->assertEquals( $expected_html, $actual );
	}

	/**
	 * Test data for `test_can_auto_update_amp_plugin()`.
	 *
	 * @return array Data.
	 */
	public function data_for_can_auto_update_amp_plugin() {
		return [
			'wrong_plugin'                 => [
				[ false, (object) [ 'plugin' => 'foo' ] ],
				true,
				false,
			],
			'disabled_auto_update_setting' => [
				[ true, (object) [ 'plugin' => 'amp/amp.php' ] ],
				false,
				false,
			],

			'enabled_auto_update_setting'  => [
				[ false, (object) [ 'plugin' => 'amp/amp.php' ] ],
				true,
				true,
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\can_auto_update_amp_plugin()`.
	 *
	 * @dataProvider data_for_can_auto_update_amp_plugin
	 * @covers \AMP_Beta_Tester\can_auto_update_amp_plugin()
	 *
	 * @param array $params              Parameters for function to test.
	 * @param bool  $should_auto_update  Whether auto update should be enabled or not.
	 * @param bool  $expected            Expected value.
	 */
	public function test_can_auto_update_amp_plugin( $params, $should_auto_update, $expected ) {
		add_filter(
			'pre_option_amp-beta-options',
			static function () use ( $should_auto_update ) {
				return compact( 'should_auto_update' );
			}
		);

		$actual = call_user_func_array( 'AMP_Beta_Tester\can_auto_update_amp_plugin', $params );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test data for `test_update_amp_manifest()`.
	 *
	 * @return array Data.
	 */
	public function data_for_update_amp_manifest() {
		return [
			'transient has no properties'             => [
				$this->generate_github_api_response(),
				(object) [],
				(object) [],
			],

			'transient only has "no_update" property' => [
				$this->generate_github_api_response(),
				(object) [ 'no_update' => [] ],
				(object) [ 'no_update' => [] ],
			],

			'transient only has "response" property'  => [
				$this->generate_github_api_response(),
				(object) [ 'response' => [] ],
				(object) [ 'response' => [] ],
			],

			'plugin is on latest release'             => [
				$this->generate_github_api_response( AMP__VERSION ),
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => [],
					],
					'response'  => [],
				],
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => [],
					],
					'response'  => [],
				],
			],

			'plugin is not on latest release'         => [
				$this->generate_github_api_response( '999.999.999' ),
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => $this->generate_amp_manifest( AMP__VERSION, true ),
					],
					'response'  => [],
				],
				(object) [
					'no_update' => [],
					'response'  => [
						AMP_PLUGIN_BASENAME => $this->generate_amp_manifest( '999.999.999', true ),
					],
				],
			],

			'plugin is not on latest release but no amp.zip' => [
				$this->generate_github_api_response( '999.999.999', false, false ),
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => $this->generate_amp_manifest( AMP__VERSION, true ),
					],
					'response'  => [],
				],
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => $this->generate_amp_manifest( AMP__VERSION, true ),
					],
					'response'  => [],
				],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\update_amp_manifest()`.
	 *
	 * @dataProvider data_for_update_amp_manifest
	 * @covers \AMP_Beta_Tester\update_amp_manifest()
	 *
	 * @param string  $github_response     GitHub response to send when retrieving updates.
	 * @param stdClass $source_transient   Source `update_plugins` transient.
	 * @param stdClass $expected_transient Expected `update_plugins` transient.
	 */
	public function test_update_amp_manifest( $github_response, $source_transient, $expected_transient ) {
		$this->custom_github_api_response = $github_response;
		add_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ], 10, 3 );

		add_filter(
			'pre_site_transient_update_plugins',
			static function () use ( $source_transient ) {
				return $source_transient;
			}
		);

		$actual_transient = AMP_Beta_Tester\update_amp_manifest( $source_transient );

		$this->assertEquals( $expected_transient, $actual_transient );

		remove_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ] );
	}

	/**
	 * Test data for `test_get_download_url_from_amp_release()`.
	 *
	 * @return array Data.
	 */
	public function data_for_update_amp_plugin_details() {
		return [
			'empty values'                       => [
				[ [], '', [] ],
				[],
			],

			'not amp plugin'                     => [
				[ [], 'plugin_information', (object) [ 'slug' => 'not-amp' ] ],
				[],
			],

			'plugin details is not an object'    => [
				[ [], 'plugin_information', (object) [ 'slug' => 'amp' ] ],
				[],
			],

			'only sets amp version'              => [
				[ (object) [], 'plugin_information', (object) [ 'slug' => 'amp' ] ],
				(object) [ 'version' => AMP__VERSION ],
			],

			'sets amp version and download link' => [
				[ (object) [], 'plugin_information', (object) [ 'slug' => 'amp' ] ],
				(object) [
					'version'       => AMP__VERSION,
					'download_link' => 'https://github.com/ampproject/amp-wp/releases/download/999.999.999/amp.zip',
				],
				$this->generate_github_api_response( '999.999.999' ),
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => $this->generate_amp_manifest( AMP__VERSION, true ),
					],
					'response'  => [],
				],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\update_amp_plugin_details()`.
	 *
	 * @dataProvider data_for_update_amp_plugin_details
	 * @covers \AMP_Beta_Tester\update_amp_plugin_details()
	 *
	 * @param array         $params                   Parameters for function to test.
	 * @param object|array  $expected                 Expected value to receive.
	 * @param string        $github_response          Optional. JSON encoded response from GitHub.
	 * @param object        $update_plugins_transient Optional. Value to set for `update_plugins` transient.
	 */
	public function test_update_amp_plugin_details( $params, $expected, $github_response = null, $update_plugins_transient = null ) {
		$this->custom_github_api_response = null === $github_response ? $this->generate_github_api_response() : $github_response;
		add_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ], 10, 3 );

		if ( null !== $update_plugins_transient ) {
			add_filter(
				'pre_site_transient_update_plugins',
				static function () use ( $update_plugins_transient ) {
					return $update_plugins_transient;
				}
			);
		}

		$actual = call_user_func_array( 'AMP_Beta_Tester\update_amp_plugin_details', $params );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `\AMP_Beta_Tester\test_move_plugin_to_correct_folder()`.
	 *
	 * @return array Data.
	 */
	public function data_for_move_plugin_to_correct_folder() {
		return [
			'empty params'                            => [
				[ true, [], [] ],
				true,
			],

			'not amp plugin'                          => [
				[ true, [ 'plugin' => 'foo' ], [] ],
				true,
			],

			'is amp plugin but failed to move folder' => [
				[ true, [ 'plugin' => AMP_PLUGIN_BASENAME ], [ 'destination' => 'foo' ] ],
				function ( $actual ) {
					$this->assertInstanceOf( 'WP_Error', $actual );
				},
				false,
			],

			'moved to correct folder'                 => [
				[ true, [ 'plugin' => AMP_PLUGIN_BASENAME ], [ 'destination' => 'foo' ] ],
				true,
				true,
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\move_plugin_to_correct_folder()`.
	 *
	 * @dataProvider data_for_move_plugin_to_correct_folder
	 * @covers \AMP_Beta_Tester\move_plugin_to_correct_folder()
	 *
	 * @param array        $params                 Parameters for function to test.
	 * @param bool|Closure $expected               Expected value to receive.
	 * @param bool         $folder_moved Optional. Value to return `$wp_filesystem->move` is called.
	 */
	public function test_move_plugin_to_correct_folder( $params, $expected, $folder_moved = false ) {
		global $wp_filesystem;
		$wp_filesystem = new WP_Filesystem_Mock();
		$wp_filesystem->set_returns( $folder_moved );

		$actual = call_user_func_array( 'AMP_Beta_Tester\move_plugin_to_correct_folder', $params );

		if ( $expected instanceof Closure ) {
			$expected( $actual );
		} else {
			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * Test data for `test_get_amp_github_releases()`.
	 *
	 * @return array Data.
	 */
	public function data_for_get_amp_github_releases() {
		$releases_without_names = json_decode( $this->generate_github_api_response() );
		unset( $releases_without_names[0]->name );
		$releases_without_names = json_encode( $releases_without_names ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		return [
			'releases are cached'                         => [
				[ '1.0.0' => $this->generate_github_api_response( '1.0.0' ) ],
				[ '1.0.0' => $this->generate_github_api_response( '1.0.0' ) ],
				true,
			],
			'releases not an array'                       => [
				'foo',
				false,
			],
			'no releases with names'                      => [
				$releases_without_names,
				false,
			],
			'releases keyed by name and zip url attached' => [
				$this->generate_github_api_response( '1.0.0' ),
				[ '1.0.0' => json_decode( $this->attach_zip_url( $this->generate_github_api_response( '1.0.0' ) ) )[0] ],
			],
			'releases are sorted by version'              => [
				json_encode( // phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
					[
						json_decode( $this->generate_github_api_response( '2.0.0-beta' ) )[0],
						json_decode( $this->generate_github_api_response( '2.0.0-alpha' ) )[0],
						json_decode( $this->generate_github_api_response( '1.0.0' ) )[0],
						json_decode( $this->generate_github_api_response( '5.0.0' ) )[0],
					]
				),
				[
					'5.0.0'       => json_decode( $this->attach_zip_url( $this->generate_github_api_response( '5.0.0' ) ) )[0],
					'2.0.0-beta'  => json_decode( $this->attach_zip_url( $this->generate_github_api_response( '2.0.0-beta' ) ) )[0],
					'2.0.0-alpha' => json_decode( $this->attach_zip_url( $this->generate_github_api_response( '2.0.0-alpha' ) ) )[0],
					'1.0.0'       => json_decode( $this->attach_zip_url( $this->generate_github_api_response( '1.0.0' ) ) )[0],
				],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\get_amp_github_releases()`.
	 *
	 * @dataProvider data_for_get_amp_github_releases
	 * @covers       \AMP_Beta_Tester\get_amp_github_releases()
	 *
	 * @param array|string $source   Source.
	 * @param array|bool   $expected Expected.
	 * @param bool         $to_cache Whether to store releases in cache or not.
	 */
	public function test_get_amp_github_releases( $source, $expected, $to_cache = false ) {
		if ( $to_cache ) {
			add_filter(
				'pre_site_transient_' . AMP_BETA_TESTER_RELEASES_TRANSIENT,
				static function () use ( $source ) {
					return $source;
				}
			);
		} else {
			$this->custom_github_api_response = $source;
			add_filter( 'pre_http_request', [ $this, 'mock_github_api_http_request' ], 10, 3 );
		}

		$actual = AMP_Beta_Tester\get_amp_github_releases();

		if ( is_array( $expected ) && is_array( $actual ) ) {
			// Convert all objects into associative arrays so that an identical comparison can be performed.
			foreach ( $actual as $version => $release_data ) {
				$release_data       = is_object( $release_data ) ? json_encode( $release_data ) : $release_data; // phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
				$actual[ $version ] = json_decode( $release_data, true );
			}

			foreach ( $expected as $version => $release_data ) {
				$release_data         = is_object( $release_data ) ? json_encode( $release_data ) : $release_data; // phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
				$expected[ $version ] = json_decode( $release_data, true );
			}

			$this->assertTrue( $expected === $actual );
		}

		$this->assertEquals( $expected, $actual );

		$this->custom_github_api_response = null;
	}

	/**
	 * Test data for `test_get_download_url_from_amp_release()`.
	 *
	 * @return array Data.
	 */
	public function data_for_download_url_from_amp_release() {
		return [
			'invalid param'    => [
				null,
				false,
			],

			'no assets'        => [
				(object) [ 'assets' => [] ],
				false,
			],

			'invalid asset'    => [
				(object) [ 'assets' => [ 'foo' ] ],
				false,
			],

			'no download url'  => [
				(object) [
					'assets' => [
						(object) [ 'name' => 'foo' ],
					],
				],
				false,
			],

			'no amp.zip'       => [
				(object) [
					'assets' => [
						(object) [
							'name'                 => 'foo',
							'browser_download_url' => 'bar',
						],
					],
				],
				false,
			],

			'has download url' => [
				(object) [
					'assets' => [
						(object) [
							'name'                 => 'amp.zip',
							'browser_download_url' => 'https://example.com',
						],
					],
				],
				'https://example.com',
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\get_download_url_from_amp_release()`.
	 *
	 * @dataProvider data_for_download_url_from_amp_release
	 * @covers       \AMP_Beta_Tester\get_download_url_from_amp_release()
	 *
	 * @param object $source  Source.
	 * @param bool   $expected Expected.
	 */
	public function test_get_download_url_from_amp_release( $source, $expected ) {
		$actual = AMP_Beta_Tester\get_download_url_from_amp_release( $source );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test data for `test_generate_amp_update_manifest()`.
	 *
	 * @return array Data.
	 */
	public function data_for_generate_amp_update_manifest() {
		return [
			[ null, false ],
			[ new stdClass(), false ],
			[
				(object) [
					'zip_url' => 'foo',
				],
				false,
			],
			[
				(object) [
					'zip_url' => 'foo',
					'name'    => 'bar',
				],
				false,
			],
			[
				(object) [
					'zip_url'  => 'foo',
					'name'     => 'bar',
					'html_url' => 'baz',
				],
				(object) [
					'package'     => 'foo',
					'new_version' => 'bar',
					'url'         => 'baz',
					'misc'        => 'buzz',
				],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\generate_amp_update_manifest()`.
	 *
	 * @dataProvider data_for_generate_amp_update_manifest
	 * @covers       \AMP_Beta_Tester\generate_amp_update_manifest()
	 *
	 * @param object $source   Source.
	 * @param bool   $expected Expected.
	 */
	public function test_generate_amp_update_manifest( $source, $expected ) {
		add_filter(
			'pre_site_transient_update_plugins',
			static function () {
				return (object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => [
							'misc' => 'buzz',
						],
					],
					'response'  => [],
				];
			}
		);

		$actual = AMP_Beta_Tester\generate_amp_update_manifest( $source );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test data for `test_on_latest_amp_release`.
	 *
	 * @return array Data.
	 */
	public function data_for_on_latest_amp_release() {
		return [
			'old release'   => [ '0.0.1', true ],
			'newer release' => [ '999.999.999', false ],
			'same release'  => [ AMP__VERSION, true ],
		];
	}

	/**
	 * Test for `on_latest_amp_release()`.
	 *
	 * @dataProvider data_for_on_latest_amp_release
	 * @covers       \AMP_Beta_Tester\on_latest_amp_release()
	 *
	 * @param object $plugin_version AMP plugin version.
	 * @param bool   $expected       Expected.
	 */
	public function test_on_latest_amp_release( $plugin_version, $expected ) {
		set_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT, [ "$plugin_version" => [] ] );

		$actual = AMP_Beta_Tester\on_latest_amp_release();
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test data for `test_get_github_amp_update_manifest()`.
	 *
	 * @return array Data.
	 */
	public function data_for_get_github_amp_update_manifest() {
		return [
			'releases not an array'     => [
				'latest',
				false,
				new stdClass(),
			],

			'current release not found' => [
				'1.0.0',
				false,
				[ '2.0.0' => [] ],
			],

			'has newer release'         => [
				'latest',
				(object) [
					'package'     => 'foo',
					'new_version' => 'bar',
					'url'         => 'baz',
					'misc'        => 'buzz',
				],
				[
					'999.999.999' => (object) [
						'zip_url'  => 'foo',
						'name'     => 'bar',
						'html_url' => 'baz',
					],
				],
			],
			'has current version'       => [
				'1.0.0',
				(object) [
					'package'     => 'foo',
					'new_version' => 'bar',
					'url'         => 'baz',
					'misc'        => 'buzz',
				],
				[
					'1.0.0' => (object) [
						'zip_url'  => 'foo',
						'name'     => 'bar',
						'html_url' => 'baz',
					],
				],
			],
		];
	}

	/**
	 * Test for `\AMP_Beta_Tester\get_github_amp_update_manifest()`.
	 *
	 * @dataProvider data_for_get_github_amp_update_manifest
	 * @covers       \AMP_Beta_Tester\data_for_get_github_amp_update_manifest()
	 *
	 * @param string       $version         Version to retrieve manifest for.
	 * @param object|false $expected        Expected value of manifest.
	 * @param array        $github_releases Array representing a list of GitHub releases.
	 */
	public function test_get_github_amp_update_manifest( $version, $expected, $github_releases ) {
		set_site_transient( AMP_BETA_TESTER_RELEASES_TRANSIENT, $github_releases );

		add_filter(
			'pre_site_transient_update_plugins',
			static function () {
				return (object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => [
							'misc' => 'buzz',
						],
					],
					'response'  => [],
				];
			}
		);

		$actual = AMP_Beta_Tester\get_github_amp_update_manifest( $version );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test data for `test_get_amp_update_manifest()`.
	 *
	 * @return array Data.
	 */
	public function data_for_get_amp_update_manifest() {
		return [
			'no transient'                     => [
				null,
				false,
			],

			'only `response` property'         => [
				(object) [ 'response' => [] ],
				false,
			],

			'only `no_update` property'        => [
				(object) [ 'no_update' => [] ],
				false,
			],

			'no manifest for plugin'           => [
				(object) [
					'no_update' => [],
					'response'  => [],
				],
				false,
			],

			'manifest in `no_update` property' => [
				(object) [
					'no_update' => [
						AMP_PLUGIN_BASENAME => 'foo',
					],
					'response'  => [],
				],
				'foo',
			],

			'manifest in `response` property'  => [
				(object) [
					'no_update' => [],
					'response'  => [
						AMP_PLUGIN_BASENAME => 'foo',
					],
				],
				'foo',
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\get_amp_update_manifest()`.
	 *
	 * @dataProvider data_for_get_amp_update_manifest
	 * @covers \AMP_Beta_Tester\get_amp_update_manifest()
	 *
	 * @param object|null   $source  Source.
	 * @param  object|false $expected Expected.
	 */
	public function test_get_amp_update_manifest( $source, $expected ) {
		add_filter(
			'pre_site_transient_update_plugins',
			static function () use ( $source ) {
				return $source;
			}
		);

		$actual = AMP_Beta_Tester\get_amp_update_manifest();

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `\AMP_Beta_Tester\get_amp_version()`.
	 *
	 * @covers \AMP_Beta_Tester\get_amp_version()
	 */
	public function test_get_amp_version() {
		$actual = AMP_Beta_Tester\get_amp_version();

		$this->assertEquals( AMP__VERSION, $actual );
	}

	/**
	 * Test data for `test_get_option()`.
	 *
	 * @return array Data.
	 */
	public function data_for_get_option() {
		return [
			'no option name' => [
				null,
				null,
			],

			'option not set' => [
				'foo',
				null,
			],

			'option set'     => [
				'foo',
				'bar',
				[ 'foo' => 'bar' ],
			],
		];
	}

	/**
	 * Test `\AMP_Beta_Tester\get_option()`.
	 *
	 * @dataProvider data_for_get_option
	 * @covers \AMP_Beta_Tester\get_option()
	 *
	 * @param null|string $source   Source.
	 * @param null|string $expected Expected.
	 * @param array       $option   Option to set for beta plugin.
	 */
	public function test_get_option( $source, $expected, $option = null ) {
		if ( null !== $option ) {
			add_filter(
				'pre_option_' . AMP_BETA_OPTION_NAME,
				static function() use ( $option ) {
					return $option;
				}
			);
		}

		$actual = AMP_Beta_Tester\get_option( $source );

		$this->assertEquals( $expected, $actual );
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
	 * @param bool   $is_pre_release Whether to set release as pre-release or not.
	 * @param bool   $with_zip        Whether to include an 'amp.zip' asset or not.
	 * @return string JSON encoded response body.
	 */
	private function generate_github_api_response( $version = '1.0.0-beta', $is_pre_release = true, $with_zip = true ) {
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
			'prerelease'       => $is_pre_release,
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

		return json_encode( [ $response ] ); // phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	private function attach_zip_url( $github_releases ) {
		$github_releases = json_decode( $github_releases );

		foreach ( $github_releases as $release ) {
			if ( ! isset( $release->assets ) ) {
				continue;
			}

			$release->zip_url = $release->assets[0]->browser_download_url;
		}

		return json_encode( $github_releases ); // phpcs:ignore: WordPress.WP.AlternativeFunctions.json_encode_json_encode
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
