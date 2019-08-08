<?php
/**
 * Tests for AMP_Post_Meta_Box.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Post_Meta_Box.
 */
class Test_AMP_Post_Meta_Box extends WP_UnitTestCase {

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
		unregister_post_type( AMP_Story_Post_Type::POST_TYPE_SLUG );
		parent::tearDown();
	}


	/**
	 * Test init.
	 *
	 * @see AMP_Settings::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_admin_assets' ] ) );
		$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', [ $this->instance, 'enqueue_block_assets' ] ) );
		$this->assertEquals( 10, has_action( 'post_submitbox_misc_actions', [ $this->instance, 'render_status' ] ) );
		$this->assertEquals( 10, has_action( 'save_post', [ $this->instance, 'save_amp_status' ] ) );
	}

	/**
	 * Test enqueue_admin_assets.
	 *
	 * @see AMP_Post_Meta_Box::enqueue_admin_assets()
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
		$this->assertTrue( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$script_data = wp_scripts()->get_data( AMP_Post_Meta_Box::ASSETS_HANDLE, 'after' );

		if ( empty( $script_data ) ) {
			$this->markTestIncomplete( 'Script data could not be found.' );
		}

		// Test inline script boot.
		$this->assertNotSame( false, stripos( wp_json_encode( $script_data ), 'ampPostMetaBox.boot(' ) );
		unset( $GLOBALS['post'], $GLOBALS['current_screen'] );
	}

	/**
	 * Test enqueue_block_assets.
	 *
	 * @covers AMP_Post_Meta_Box::enqueue_block_assets()
	 * @covers AMP_Story_Post_Type::register_story_card_styling()
	 * @covers AMP_Story_Post_Type::export_latest_stories_block_editor_data()
	 */
	public function test_enqueue_block_assets() {
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestSkipped( 'The block editor is not available' );
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
				'wp-server-side-render',
			],
			$block_script->deps
		);
		$this->assertEquals( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE, $block_script->handle );
		$this->assertEquals( amp_get_asset_url( 'js/' . AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE . '.js' ), $block_script->src );
		$this->assertEquals( AMP__VERSION, $block_script->ver );

		/*
		 * Test Stories integration.
		 * The current screen is the AMP Story editor, so the data for the Latest Stories block should not be present, as it's not needed there.
		 */
		register_post_type( AMP_Story_Post_Type::POST_TYPE_SLUG );
		set_current_screen( AMP_Story_Post_Type::POST_TYPE_SLUG );
		AMP_Story_Post_Type::register_story_card_styling( wp_styles() );
		AMP_Story_Post_Type::export_latest_stories_block_editor_data();
		$this->assertFalse( isset( wp_scripts()->registered[ AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ]->extra['before'] ) );

		// The current screen is the editor for a normal post, so the data for the Latest Stories block should be present.
		set_current_screen( 'post.php' );
		AMP_Story_Post_Type::export_latest_stories_block_editor_data();
		$this->assertContains( 'ampLatestStoriesBlockData', implode( '', wp_scripts()->registered[ AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ]->extra['before'] ) );
	}

	/**
	 * Test render_status.
	 *
	 * @see AMP_Settings::render_status()
	 */
	public function test_render_status() {
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

		// This is not in AMP 'canonical mode' but rather reader or transitional mode.
		remove_theme_support( AMP_Theme_Support::SLUG );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertContains( $amp_status_markup, $output );
		$this->assertContains( $checkbox_enabled, $output );

		// This is in AMP-first mode with a template that can be rendered.
		add_theme_support( AMP_Theme_Support::SLUG );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertContains( $amp_status_markup, $output );
		$this->assertContains( $checkbox_enabled, $output );

		// Post type no longer supports AMP, so no status input.
		remove_post_type_support( 'post', AMP_Post_Type_Support::SLUG );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertContains( 'post type does not support it', $output );
		$this->assertNotContains( $checkbox_enabled, $output );
		add_post_type_support( 'post', AMP_Post_Type_Support::SLUG );

		// No template is available to render the post.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		$output = get_echo( [ $this->instance, 'render_status' ], [ $post ] );
		$this->assertContains( 'no supported templates to display this in AMP.', wp_strip_all_tags( $output ) );
		$this->assertNotContains( $checkbox_enabled, $output );

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

	/**
	 * Test get_status_and_errors.
	 *
	 * @see AMP_Post_Meta_Box::get_status_and_errors()
	 */
	public function test_get_status_and_errors() {
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
		add_theme_support( AMP_Theme_Support::SLUG );
		$this->assertEquals(
			$expected_status_and_errors,
			$this->instance->get_status_and_errors( $post )
		);

		// If post type doesn't support AMP, this method should return AMP as being disabled.
		remove_post_type_support( 'post', AMP_Post_Type_Support::SLUG );
		$this->assertEquals(
			[
				'status' => 'disabled',
				'errors' => [ 'post-type-support' ],
			],
			$this->instance->get_status_and_errors( $post )
		);
		add_post_type_support( 'post', AMP_Post_Type_Support::SLUG );

		// There's no template to render this post, so this method should also return AMP as disabled.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		$this->assertEquals(
			[
				'status' => 'disabled',
				'errors' => [ 'no_matching_template' ],
			],
			$this->instance->get_status_and_errors( $post )
		);
	}

	/**
	 * Test get_error_messages.
	 *
	 * @see AMP_Post_Meta_Box::get_error_messages()
	 */
	public function test_get_error_messages() {
		$this->assertEquals(
			[ 'Your site does not allow AMP to be disabled.' ],
			$this->instance->get_error_messages( AMP_Post_Meta_Box::ENABLED_STATUS, [ 'status_immutable' ] )
		);

		$this->assertEquals(
			[ 'Your site does not allow AMP to be enabled.' ],
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'status_immutable' ] )
		);

		$messages = $this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'template_unsupported' ] );
		$this->assertContains( 'There are no', $messages[0] );
		$this->assertContains( 'page=amp-options', $messages[0] );

		$this->assertEquals(
			[ 'AMP cannot be enabled on password protected posts.' ],
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'password-protected' ] )
		);

		$messages = $this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'post-type-support' ] );
		$this->assertContains( 'AMP cannot be enabled because this', $messages[0] );
		$this->assertContains( 'page=amp-options', $messages[0] );

		$this->assertEquals(
			[
				'A plugin or theme has disabled AMP support.',
				'Unavailable for an unknown reason.',
			],
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'skip-post', 'unknown-error' ] )
		);

		$this->assertEquals(
			[ 'Unavailable for an unknown reason.' ],
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, [ 'unknown-error' ] )
		);
	}

	/**
	 * Test save_amp_status.
	 *
	 * @see AMP_Settings::save_amp_status()
	 */
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

	/**
	 * Test preview_post_link.
	 *
	 * @see AMP_Settings::preview_post_link()
	 */
	public function test_preview_post_link() {
		$link = 'https://foo.bar';
		$this->assertEquals( 'https://foo.bar', $this->instance->preview_post_link( $link ) );
		$_POST['amp-preview'] = 'do-preview';
		$this->assertEquals( 'https://foo.bar?' . amp_get_slug() . '=1', $this->instance->preview_post_link( $link ) );
	}

}
