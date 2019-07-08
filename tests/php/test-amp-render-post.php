<?php

class AMP_Render_Post_Test extends WP_UnitTestCase {
	public function test__invalid_post() {
		// No ob here since it bails early
		$amp_rendered = amp_render_post( PHP_INT_MAX );

		$this->assertNull( $amp_rendered, 'Response was not null' );
		$this->assertEquals( 0, did_action( 'pre_amp_render_post' ), 'pre_amp_render_post action fire when it should not have.' );
	}

	public function test__valid_post() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create( [ 'post_author' => $user_id ] );

		$output = get_echo( 'amp_render_post', [ $post_id ] );

		$this->assertContains( '<html amp', $output, 'Response does not include html tag with amp attribute.' );
		$this->assertEquals( 1, did_action( 'pre_amp_render_post', 'pre_amp_render_post action fire either did not fire or fired too many times.' ) );
	}

	/**
	 * Stored result of is_amp_endpoint() when calling amp_render_post().
	 *
	 * @var bool
	 */
	protected $was_amp_endpoint;

	/**
	 * Test is_amp_endpoint.
	 *
	 * @covers ::is_amp_endpoint()
	 */
	public function test__is_amp_endpoint() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create(
			[
				'post_author' => $user_id,
			]
		);

		$before_is_amp_endpoint = is_amp_endpoint();

		add_action( 'pre_amp_render_post', [ $this, 'check_is_amp_endpoint' ] );
		$this->was_amp_endpoint = false;

		$output = get_echo( 'amp_render_post', [ $post_id ] );
		$this->assertContains( '<html amp', $output );

		$after_is_amp_endpoint = is_amp_endpoint();

		$this->assertFalse( $before_is_amp_endpoint, 'is_amp_endpoint was not defaulting to false before amp_render_post' );
		$this->assertTrue( $this->was_amp_endpoint, 'is_amp_endpoint was not forced to true during amp_render_post' );
		$this->assertFalse( $after_is_amp_endpoint, 'is_amp_endpoint was not reset after amp_render_post' );

		add_theme_support( AMP_Theme_Support::SLUG );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_amp_endpoint() );

		// Make is_admin() true, as requests for an admin page aren't for AMP endpoints.
		set_current_screen( 'edit.php' );
		$this->assertFalse( is_amp_endpoint() );
		unset( $GLOBALS['current_screen'] );

		$GLOBALS['wp_query']->is_feed = true;
		$this->assertFalse( is_amp_endpoint() );
		$GLOBALS['wp_query']->is_feed = false;
	}

	/**
	 * Store whether it currently is_amp_endpoint().
	 */
	public function check_is_amp_endpoint() {
		$this->was_amp_endpoint = is_amp_endpoint();
	}
}
