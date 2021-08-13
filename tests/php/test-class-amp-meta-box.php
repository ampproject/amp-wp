<?php
/**
 * Tests for AMP_Post_Meta_Box.
 *
 * @package AMP
 */

use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\Helpers\AssertRestApiField;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for AMP_Post_Meta_Box.
 *
 * @coversDefaultClass AMP_Post_Meta_Box
 */
class Test_AMP_Post_Meta_Box extends TestCase {

	use AssertRestApiField;

	/**
	 * Instance of AMP_Post_Meta_Box
	 *
	 * @var AMP_Post_Meta_Box
	 */
	public $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		global $wp_scripts, $wp_styles;
		$wp_scripts     = null;
		$wp_styles      = null;
		$this->instance = new AMP_Post_Meta_Box();
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;
		parent::tearDown();
	}

	/**
	 * Test init.
	 *
	 * @covers ::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_admin_assets' ] ) );
		$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', [ $this->instance, 'enqueue_block_assets' ] ) );
		$this->assertEquals( 10, has_action( 'post_submitbox_misc_actions', [ $this->instance, 'render_status' ] ) );
		$this->assertEquals( 10, has_action( 'save_post', [ $this->instance, 'save_amp_status' ] ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->instance, 'add_rest_api_fields' ] ) );
		$this->assertEquals( 10, has_filter( 'preview_post_link', [ $this->instance, 'preview_post_link' ] ) );
	}

	/**
	 * Test enqueue_admin_assets.
	 *
	 * @covers ::enqueue_admin_assets()
	 */
	public function test_enqueue_admin_assets() {
		// Test enqueue outside of a post with AMP support.
		$this->assertFalse( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );

		$this->instance->enqueue_admin_assets( 'foo-bar.php' );

		$this->assertFalse( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );

		// Test enqueue on a post with AMP support.
		$post            = self::factory()->post->create_and_get();
		$GLOBALS['post'] = $post;

		set_current_screen( 'post.php' );
		get_current_screen()->is_block_editor = true;
		$this->instance->enqueue_admin_assets();

		$this->assertFalse( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );

		set_current_screen( 'post.php' );
		get_current_screen()->is_block_editor = false;
		$this->instance->enqueue_admin_assets();

		$this->assertTrue( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );

		if ( Services::get( 'dependency_support' )->has_support_from_core() ) {
			$this->assertTrue( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
			$script_data = wp_scripts()->get_data( AMP_Post_Meta_Box::ASSETS_HANDLE, 'after' );

			if ( empty( $script_data ) ) {
				$this->markTestIncomplete( 'Script data could not be found.' );
			}

			// Test inline script boot.
			$this->assertNotSame( false, stripos( wp_json_encode( $script_data ), 'ampPostMetaBox.boot(' ) );
			unset( $GLOBALS['post'], $GLOBALS['current_screen'] );
		} else {
			$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		}
	}

	/**
	 * Test enqueue_block_assets.
	 *
	 * @covers ::enqueue_block_assets()
	 */
	public function test_enqueue_block_assets() {
		set_current_screen( 'post.php' );
		get_current_screen()->is_block_editor = true;

		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestSkipped( 'The block editor is not available' );
		}

		if (
			defined( 'GUTENBERG_VERSION' )
			&&
			version_compare( GUTENBERG_VERSION, DependencySupport::GB_MIN_VERSION, '<' )
		) {
			$this->markTestSkipped( 'The version of Gutenberg installed is not compatible with the plugin.' );
		}

		// If a post type doesn't have AMP enabled, the script shouldn't be enqueued.
		register_post_type(
			'secret',
			[ 'public' => false ]
		);
		$GLOBALS['post'] = self::factory()->post->create_and_get(
			[
				'post_type' => 'secret',
			]
		);
		$this->instance->enqueue_block_assets();
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ) );

		// If a post type has AMP enabled, the script should be enqueued.
		$GLOBALS['post'] = self::factory()->post->create_and_get();
		$this->instance->enqueue_block_assets();
		$this->assertTrue( wp_script_is( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ) );

		$block_script = wp_scripts()->registered[ AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ];
		$this->assertEqualSets(
			[
				'lodash',
				'moment',
				'react',
				'wp-block-editor',
				'wp-blocks',
				'wp-components',
				'wp-compose',
				'wp-data',
				'wp-edit-post',
				'wp-element',
				'wp-hooks',
				'wp-i18n',
				'wp-plugins',
				'wp-polyfill',
			],
			$block_script->deps
		);
		$this->assertEquals( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE, $block_script->handle );
		$this->assertEquals( amp_get_asset_url( 'js/' . AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE . '.js' ), $block_script->src );

		/**
		 * @since 2.0.9
		 * Values are now loaded using wp_inline_script()
		 */
		$before = implode( '', $block_script->extra['before'] );
		$this->assertStringContainsString( 'ampBlockEditor', $before );
		$expected_localized_values = [
			'ampUrl',
			'ampPreviewLink',
			'errorMessages',
			'hasThemeSupport',
			'isStandardMode',
			'featuredImageMinimumHeight',
			'featuredImageMinimumWidth',
		];

		foreach ( $expected_localized_values as $localized_value ) {
			$this->assertStringContainsString( $localized_value, $before );
		}
		unset( $GLOBALS['post'], $GLOBALS['current_screen'] );
	}

	/** @covers ::get_featured_image_dimensions() */
	public function test_featured_image_dimensions() {
		list( $width, $height ) = AMP_Post_Meta_Box::get_featured_image_dimensions();
		$this->assertEquals( 1200, $width );
		$this->assertEquals( 675, $height );
	}

	/** @covers ::get_featured_image_dimensions() */
	public function test_when_height_and_width_are_valid_should_return_filter_values() {
		add_filter(
			'amp_featured_image_minimum_height',
			static function () {
				return 1200;
			}
		);
		add_filter(
			'amp_featured_image_minimum_width',
			static function () {
				return 1300;
			}
		);
		list( $width, $height ) = AMP_Post_Meta_Box::get_featured_image_dimensions();
		remove_all_filters( 'amp_featured_image_minimum_height' );
		remove_all_filters( 'amp_featured_image_minimum_width' );
		$this->assertEquals( 1300, $width );
		$this->assertEquals( 1200, $height );
	}

	/** @covers ::get_featured_image_dimensions() */
	public function test_when_height_and_width_are_filtered_to_disable_minimums() {
		add_filter(
			'amp_featured_image_minimum_height',
			static function () {
				return -1;
			}
		);
		add_filter(
			'amp_featured_image_minimum_width',
			static function () {
				return 0;
			}
		);
		list( $width, $height ) = AMP_Post_Meta_Box::get_featured_image_dimensions();
		remove_all_filters( 'amp_featured_image_minimum_height' );
		remove_all_filters( 'amp_featured_image_minimum_width' );
		$this->assertEquals( 0, $width );
		$this->assertEquals( 0, $height );
	}

	/** @covers ::render_status() */
	public function test_render_status() {
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$post = self::factory()->post->create_and_get();
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);
		add_post_type_support( 'post', AMP_Post_Type_Support::SLUG );
		$amp_status_markup = '<div class="misc-pub-section misc-amp-status"';
		$checkbox_enabled  = '<input id="amp-status-enabled" type="radio" name="amp_status" value="enabled"  checked=\'checked\'>';
		$no_support_notice = '<div class="notice notice-info notice-alt inline">';

		// This is not in AMP 'canonical mode' but rather reader or transitional mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertStringContainsString( $amp_status_markup, $output );
		if ( Services::get( 'dependency_support' )->has_support_from_core() ) {
			$this->assertStringContainsString( $checkbox_enabled, $output );
		} else {
			$this->assertStringContainsString( $no_support_notice, $output );
		}

		// This is in AMP-first mode with a template that can be rendered.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertStringContainsString( $amp_status_markup, $output );
		if ( Services::get( 'dependency_support' )->has_support_from_core() ) {
			$this->assertStringContainsString( $checkbox_enabled, $output );
		} else {
			$this->assertStringContainsString( $no_support_notice, $output );
		}

		// Post type no longer supports AMP, so no status input.
		$supported_post_types = array_diff( AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ), [ 'post' ] );
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		if ( Services::get( 'dependency_support' )->has_support_from_core() ) {
			$this->assertStringContainsString( 'This post type is not', $output );
			$this->assertStringNotContainsString( $checkbox_enabled, $output );
		} else {
			$this->assertStringContainsString( $no_support_notice, $output );
		}
		$supported_post_types[] = 'post';
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );

		// No template is available to render the post.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		if ( Services::get( 'dependency_support' )->has_support_from_core() ) {
			$this->assertStringContainsString( 'There are no supported templates.', wp_strip_all_tags( $output ) );
			$this->assertStringNotContainsString( $checkbox_enabled, $output );
		} else {
			$this->assertStringContainsString( $no_support_notice, $output );
		}

		// User doesn't have the capability to display the metabox.
		add_post_type_support( 'post', AMP_Post_Type_Support::SLUG );
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'subscriber',
				]
			)
		);

		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertEmpty( $output );
	}

	/** @covers ::get_status_and_errors() */
	public function test_get_status_and_errors() {
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$expected_status_and_errors = [
			'status' => 'enabled',
			'errors' => [],
		];

		// A post of type post shouldn't have errors, and AMP should be enabled.
		$post = self::factory()->post->create_and_get();
		$this->assertEquals(
			$expected_status_and_errors,
			$this->instance->get_status_and_errors( $post )
		);

		// In AMP-first, there also shouldn't be errors.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertEquals(
			$expected_status_and_errors,
			$this->instance->get_status_and_errors( $post )
		);

		// If post type doesn't support AMP, this method should return AMP as being disabled.
		$supported_post_types = array_diff( AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ), [ 'post' ] );
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		remove_post_type_support( 'post', AMP_Post_Type_Support::SLUG );
		$this->assertEquals(
			[
				'status' => 'disabled',
				'errors' => [ 'post-type-support' ],
			],
			$this->instance->get_status_and_errors( $post )
		);
		$supported_post_types[] = 'post';
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );

		// There's no template to render this post, so this method should also return AMP as disabled.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$this->assertEquals(
			[
				'status' => 'disabled',
				'errors' => [ 'no_matching_template' ],
			],
			$this->instance->get_status_and_errors( $post )
		);
	}

	/** @covers ::get_error_messages() */
	public function test_get_error_messages() {
		$messages = $this->instance->get_error_messages( [ 'template_unsupported' ] );
		$this->assertStringContainsString( 'There are no', $messages[0] );
		$this->assertStringContainsString( 'page=amp-options', $messages[0] );

		$messages = $this->instance->get_error_messages( [ 'post-type-support' ] );
		$this->assertStringContainsString( 'This post type is not', $messages[0] );
		$this->assertStringContainsString( 'page=amp-options', $messages[0] );

		$this->assertEquals(
			[
				'A plugin or theme has disabled AMP support.',
				'Unavailable for an unknown reason.',
			],
			$this->instance->get_error_messages( [ 'skip-post', 'unknown-error' ] )
		);

		$this->assertEquals(
			[ 'Unavailable for an unknown reason.' ],
			$this->instance->get_error_messages( [ 'unknown-error' ] )
		);

		$this->assertEquals(
			[ 'The post data could not be successfully retrieved.' ],
			$this->instance->get_error_messages( [ 'invalid-post' ] )
		);
	}

	/** @covers ::save_amp_status() */
	public function test_save_amp_status() {
		// Test failure.
		$post_id = self::factory()->post->create();
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Setup for success.
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);
		$_POST[ AMP_Post_Meta_Box::NONCE_NAME ]        = wp_create_nonce( AMP_Post_Meta_Box::NONCE_ACTION );
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'disabled';

		// Test revision bail.
		$post_id = self::factory()->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_save_post_revision( $post_id );
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Test post update success to disable.
		$post_id = self::factory()->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_update_post(
			[
				'ID'         => $post_id,
				'post_title' => 'updated',
			]
		);
		$this->assertTrue( (bool) get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Test post update success to enable.
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'enabled';
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_update_post(
			[
				'ID'         => $post_id,
				'post_title' => 'updated',
			]
		);
		$this->assertEquals( AMP_Post_Meta_Box::ENABLED_STATUS, get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );
	}

	/** @covers ::preview_post_link() */
	public function test_preview_post_link() {
		$link = 'https://foo.bar';
		$this->assertEquals( 'https://foo.bar', $this->instance->preview_post_link( $link ) );
		$_POST['amp-preview'] = 'do-preview';
		$this->assertEquals( 'https://foo.bar?' . amp_get_slug() . '=1', $this->instance->preview_post_link( $link ) );
	}

	/**
	 * Test data for test_add_rest_api_fields().
	 *
	 * @return array[] Test data.
	 */
	public function get_theme_support_data() {
		return [
			'transitional' => [ AMP_Theme_Support::SLUG, [ AMP_Theme_Support::PAIRED_FLAG => true ] ],
			'canonical'    => [ AMP_Theme_Support::SLUG, [] ],
		];
	}

	/**
	 * Test add_rest_api_fields.
	 *
	 * @dataProvider get_theme_support_data
	 * @covers ::add_rest_api_fields()
	 *
	 * @param string $theme_feature Theme feature being added.
	 * @param array  $support_args Theme support arguments.
	 */
	public function test_add_rest_api_fields( $theme_feature, $support_args ) {
		add_theme_support( $theme_feature, $support_args );
		$this->instance->add_rest_api_fields();
		$this->assertRestApiFieldPresent(
			AMP_Post_Type_Support::get_post_types_for_rest_api(),
			AMP_Post_Meta_Box::REST_ATTRIBUTE_NAME,
			[
				'get_callback'    => [ $this->instance, 'get_amp_enabled_rest_field' ],
				'update_callback' => [ $this->instance, 'update_amp_enabled_rest_field' ],
				'schema'          => [
					'description' => __( 'AMP enabled', 'amp' ),
					'type'        => 'boolean',
				],
			]
		);
	}

	/** @covers ::get_amp_enabled_rest_field() */
	public function test_get_amp_enabled_rest_field() {
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );

		// AMP status should be disabled if AMP is not supported for the `post` post type.
		$supported_post_types = array_diff( AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ), [ 'post' ] );
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		$id = self::factory()->post->create();
		$this->assertFalse(
			$this->instance->get_amp_enabled_rest_field( compact( 'id' ) )
		);

		// AMP status should be enabled if AMP is supported for the `post` post type.
		$supported_post_types[] = 'post';
		AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		$id = self::factory()->post->create();
		$this->assertTrue(
			$this->instance->get_amp_enabled_rest_field( compact( 'id' ) )
		);

		// AMP status should be enabled if the `amp_status` post meta equals 'enabled'.
		$id = self::factory()->post->create();
		add_metadata( 'post', $id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, AMP_Post_Meta_Box::ENABLED_STATUS );
		$this->assertTrue(
			$this->instance->get_amp_enabled_rest_field( compact( 'id' ) )
		);

		// AMP status should be disabled if the `amp_status` post meta equals 'disabled'.
		$id = self::factory()->post->create();
		add_metadata( 'post', $id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, AMP_Post_Meta_Box::DISABLED_STATUS );
		$this->assertFalse(
			$this->instance->get_amp_enabled_rest_field( compact( 'id' ) )
		);
	}

	/** @covers ::update_amp_enabled_rest_field() */
	public function test_update_amp_enabled_rest_field() {
		// User should not be able to update AMP status if they do not have the `edit_post` capability.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$post = self::factory()->post->create_and_get();
		add_metadata( 'post', $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, AMP_Post_Meta_Box::ENABLED_STATUS );
		$result = $this->instance->update_amp_enabled_rest_field( false, $post );

		$this->assertEquals( AMP_Post_Meta_Box::ENABLED_STATUS, get_post_meta( $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'rest_insufficient_permission', $result->get_error_code() );

		// User should be able to update AMP status if they have the sufficient capabilities.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = self::factory()->post->create_and_get();
		add_metadata( 'post', $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, AMP_Post_Meta_Box::ENABLED_STATUS );
		$this->assertNull( $this->instance->update_amp_enabled_rest_field( false, $post ) );

		$this->assertEquals( AMP_Post_Meta_Box::DISABLED_STATUS, get_post_meta( $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );
	}

	/** @covers ::get_amp_blocks_in_use() */
	public function test_get_amp_blocks_in_use() {
		global $post;

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped();
		}

		$post = self::factory()->post->create_and_get(
			[
				'post_content' => '
					<!-- wp:amp/amp-mathml -->
					<amp-mathml data-formula="$$ \cos(θ+φ)=\cos(θ)\cos(φ)−\sin(θ)\sin(φ) $$" layout="container" class="wp-block-amp-amp-mathml"></amp-mathml>
					<!-- /wp:amp/amp-mathml -->

					<!-- wp:amp/amp-timeago -->
					<amp-timeago layout="fixed-height" class="wp-block-amp-amp-timeago alignnone" datetime="2020-11-02T15:43:00+00:00" locale="en" height="20">Monday 2 November 15:43</amp-timeago>
					<!-- /wp:amp/amp-timeago -->
				',
			]
		);

		$result = $this->instance->get_amp_blocks_in_use();
		$this->assertEquals( [ 'amp/amp-mathml', 'amp/amp-timeago' ], $result );
	}
}
