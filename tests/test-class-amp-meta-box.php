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
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Post_Meta_Box();
	}

	/**
	 * Test init.
	 *
	 * @see AMP_Settings::init()
	 */
	public function test_init() {
		$this->instance->init();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( $this->instance, 'enqueue_admin_assets' ) ) );
		$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', array( $this->instance, 'enqueue_block_assets' ) ) );
		$this->assertEquals( 10, has_action( 'post_submitbox_misc_actions', array( $this->instance, 'render_status' ) ) );
		$this->assertEquals( 10, has_action( 'save_post', array( $this->instance, 'save_amp_status' ) ) );
	}

	/**
	 * Test enqueue_admin_assets.
	 *
	 * @see AMP_Settings::enqueue_admin_assets()
	 */
	public function test_enqueue_admin_assets() {
		// Test enqueue outside of a post with AMP support.
		$this->assertFalse( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->instance->enqueue_admin_assets( 'foo-bar.php' );
		$this->assertFalse( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );

		// Test enqueue on a post with AMP support.
		$post            = self::factory()->post->create_and_get();
		$GLOBALS['post'] = $post;
		set_current_screen( 'post.php' );
		$this->instance->enqueue_admin_assets();
		$this->assertTrue( wp_style_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$this->assertTrue( wp_script_is( AMP_Post_Meta_Box::ASSETS_HANDLE ) );
		$script_data = wp_scripts()->get_data( AMP_Post_Meta_Box::ASSETS_HANDLE, 'after' );

		if ( empty( $script_data ) ) {
			$this->markTestIncomplete( 'Script data could not be found.' );
		}

		// Test inline script boot.
		$this->assertTrue( false !== stripos( wp_json_encode( $script_data ), 'ampPostMetaBox.boot(' ) );
		unset( $GLOBALS['post'] );
		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test enqueue_block_assets.
	 *
	 * @see AMP_Post_Meta_Box::enqueue_block_assets()
	 */
	public function test_enqueue_block_assets() {
		if ( ! function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$this->markTestSkipped( 'Gutenberg is not available' );
		}

		// If a post type doesn't have AMP enabled, the script shouldn't be enqueued.
		$GLOBALS['post'] = self::factory()->post->create_and_get( array(
			'post_type' => 'draft',
		) );
		$this->instance->enqueue_block_assets();
		$this->assertFalse( wp_script_is( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ) );

		// If a post type has AMP enabled, the script should be enqueued.
		$GLOBALS['post'] = self::factory()->post->create_and_get();
		$this->instance->enqueue_block_assets();
		$this->assertTrue( wp_script_is( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ) );

		$block_script = wp_scripts()->registered[ AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ];
		$this->assertEquals(
			array(
				'wp-hooks',
				'wp-i18n',
				'wp-components',
			),
			$block_script->deps
		);
		$this->assertEquals( AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE, $block_script->handle );
		$this->assertEquals( amp_get_asset_url( 'js/' . AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE . '.js' ), $block_script->src );
		$this->assertEquals( AMP__VERSION, $block_script->ver );
		$this->assertInternalType( 'array', $block_script->extra['before'] );

		$matches = preg_grep( '/wpAmpEditor/', $block_script->extra['before'] );
		$this->assertCount( 1, $matches );
		$this->assertContains( AMP_Post_Meta_Box::ENABLED_STATUS, array_shift( $matches ) );
	}

	/**
	 * Test render_status.
	 *
	 * @see AMP_Settings::render_status()
	 */
	public function test_render_status() {
		$post = $this->factory()->post->create_and_get();
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );
		add_post_type_support( 'post', amp_get_slug() );
		$amp_status_markup = '<div class="misc-pub-section misc-amp-status"';
		$checkbox_enabled  = '<input id="amp-status-enabled" type="radio" name="amp_status" value="enabled"  checked=\'checked\'>';

		// This is not in AMP 'canonical mode' but rather classic paired mode.
		remove_theme_support( 'amp' );
		ob_start();
		$this->instance->render_status( $post );
		$output = ob_get_clean();
		$this->assertContains( $amp_status_markup, $output );
		$this->assertContains( $checkbox_enabled, $output );

		// This is in AMP native mode with a template that can be rendered.
		add_theme_support( 'amp' );
		ob_start();
		$this->instance->render_status( $post );
		$output = ob_get_clean();
		$this->assertContains( $amp_status_markup, $output );
		$this->assertContains( $checkbox_enabled, $output );

		// Post type no longer supports AMP, so no status input.
		remove_post_type_support( 'post', amp_get_slug() );
		ob_start();
		$this->instance->render_status( $post );
		$output = ob_get_clean();
		$this->assertContains( 'post type does not support it', $output );
		$this->assertNotContains( $checkbox_enabled, $output );
		add_post_type_support( 'post', amp_get_slug() );

		// No template is available to render the post.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		ob_start();
		$this->instance->render_status( $post );
		$output = ob_get_clean();
		$this->assertContains( 'no supported templates to display this in AMP.', wp_strip_all_tags( $output ) );
		$this->assertNotContains( $checkbox_enabled, $output );

		// User doesn't have the capability to display the metabox.
		add_post_type_support( 'post', amp_get_slug() );
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'subscriber',
		) ) );

		ob_start();
		$this->instance->render_status( $post );
		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Test get_status_and_errors.
	 *
	 * @see AMP_Post_Meta_Box::get_status_and_errors()
	 */
	public function test_get_status_and_errors() {
		$expected_status_and_errors = array(
			'status' => 'enabled',
			'errors' => array(),
		);

		// A post of type post shouldn't have errors, and AMP should be enabled.
		$post = $this->factory()->post->create_and_get();
		$this->assertEquals(
			$expected_status_and_errors,
			$this->instance->get_status_and_errors( $post )
		);

		// In Native AMP, there also shouldn't be errors.
		add_theme_support( 'amp' );
		$this->assertEquals(
			$expected_status_and_errors,
			$this->instance->get_status_and_errors( $post )
		);

		// If post type doesn't support AMP, this method should return AMP as being disabled.
		remove_post_type_support( 'post', amp_get_slug() );
		$this->assertEquals(
			array(
				'status' => 'disabled',
				'errors' => array( 'post-type-support' ),
			),
			$this->instance->get_status_and_errors( $post )
		);
		add_post_type_support( 'post', amp_get_slug() );

		// There's no template to render this post, so this method should also return AMP as disabled.
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		$this->assertEquals(
			array(
				'status' => 'disabled',
				'errors' => array( 'no_matching_template' ),
			),
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
			array( 'Your site does not allow AMP to be disabled.' ),
			$this->instance->get_error_messages( AMP_Post_Meta_Box::ENABLED_STATUS, array( 'status_immutable' ) )
		);

		$this->assertEquals(
			array( 'Your site does not allow AMP to be enabled.' ),
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'status_immutable' ) )
		);

		$messages = $this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'template_unsupported' ) );
		$this->assertContains( 'There are no', $messages[0] );
		$this->assertContains( 'page=amp-options', $messages[0] );

		$this->assertEquals(
			array( 'AMP cannot be enabled on password protected posts.' ),
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'password-protected' ) )
		);

		$messages = $this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'post-type-support' ) );
		$this->assertContains( 'AMP cannot be enabled because this', $messages[0] );
		$this->assertContains( 'page=amp-options', $messages[0] );

		$this->assertEquals(
			array(
				'A plugin or theme has disabled AMP support.',
				'Unavailable for an unknown reason.',
			),
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'skip-post', 'unknown-error' ) )
		);

		$this->assertEquals(
			array( 'Unavailable for an unknown reason.' ),
			$this->instance->get_error_messages( AMP_Post_Meta_Box::DISABLED_STATUS, array( 'unknown-error' ) )
		);
	}

	/**
	 * Test save_amp_status.
	 *
	 * @see AMP_Settings::save_amp_status()
	 */
	public function test_save_amp_status() {
		// Test failure.
		$post_id = $this->factory->post->create();
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Setup for success.
		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );
		$_POST[ AMP_Post_Meta_Box::NONCE_NAME ]        = wp_create_nonce( AMP_Post_Meta_Box::NONCE_ACTION );
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'disabled';

		// Test revision bail.
		$post_id = $this->factory->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_save_post_revision( $post_id );
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Test post update success to disable.
		$post_id = $this->factory->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => 'updated',
		) );
		$this->assertTrue( (bool) get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) );

		// Test post update success to enable.
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'enabled';
		delete_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => 'updated',
		) );
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
