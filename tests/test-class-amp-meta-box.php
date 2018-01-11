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
	}

	/**
	 * Test render_status.
	 *
	 * @see AMP_Settings::render_status()
	 */
	public function test_render_status() {
		$post = $this->factory->post->create_and_get();
		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );
		$amp_status_markup = '<div class="misc-pub-section misc-amp-status"';

		// This is in AMP 'canonical mode,' so it shouldn't have the AMP status.
		add_theme_support( 'amp' );
		ob_start();
		$this->instance->render_status( $post );
		$this->assertNotContains( $amp_status_markup, ob_get_clean() );

		// This is not in AMP 'canonical mode'.
		remove_theme_support( 'amp' );
		ob_start();
		$this->instance->render_status( $post );
		$this->assertContains( $amp_status_markup, ob_get_clean() );

		remove_post_type_support( 'post', AMP_QUERY_VAR );

		ob_start();
		$this->instance->render_status( $post );
		$this->assertEmpty( ob_get_clean() );

		add_post_type_support( 'post', AMP_QUERY_VAR );
		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'subscriber',
		) ) );

		ob_start();
		$this->instance->render_status( $post );
		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Test save_amp_status.
	 *
	 * @see AMP_Settings::save_amp_status()
	 */
	public function test_save_amp_status() {
		// Test failure.
		$post_id = $this->factory->post->create();
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true ) );

		// Setup for success.
		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );
		$_POST[ AMP_Post_Meta_Box::NONCE_NAME ]        = wp_create_nonce( AMP_Post_Meta_Box::NONCE_ACTION );
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'disabled';

		// Test revision bail.
		$post_id = $this->factory->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY );
		wp_save_post_revision( $post_id );
		$this->assertEmpty( get_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true ) );

		// Test post update success to disable.
		$post_id = $this->factory->post->create();
		delete_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => 'updated',
		) );
		$this->assertTrue( (bool) get_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true ) );

		// Test post update success to enable.
		$_POST[ AMP_Post_Meta_Box::STATUS_INPUT_NAME ] = 'enabled';
		delete_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => 'updated',
		) );
		$this->assertFalse( (bool) get_post_meta( $post_id, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true ) );
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
		$this->assertEquals( 'https://foo.bar?' . AMP_QUERY_VAR . '=1', $this->instance->preview_post_link( $link ) );
	}

}
