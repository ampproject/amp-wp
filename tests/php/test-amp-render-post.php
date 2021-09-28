<?php

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

class AMP_Render_Post_Test extends TestCase {

	/**
	 * @expectedDeprecated amp_render_post
	 */
	public function test__invalid_post() {
		// No ob here since it bails early
		$amp_rendered = amp_render_post( PHP_INT_MAX );

		$this->assertNull( $amp_rendered, 'Response was not null' );
		$this->assertEquals( 0, did_action( 'pre_amp_render_post' ), 'pre_amp_render_post action fire when it should not have.' );
	}

	/**
	 * @expectedDeprecated amp_render_post
	 * @expectedDeprecated amp_add_post_template_actions
	 */
	public function test__valid_post() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create( [ 'post_author' => $user_id ] );
		$this->go_to( get_permalink( $post_id ) );

		$output = get_echo( 'amp_render_post', [ $post_id ] );

		$this->assertStringContainsString( '<html amp', $output, 'Response does not include html tag with amp attribute.' );
		$this->assertEquals( 1, did_action( 'pre_amp_render_post', 'pre_amp_render_post action fire either did not fire or fired too many times.' ) );
	}

	/**
	 * Stored result of amp_is_request() when calling amp_render_post().
	 *
	 * @var bool
	 */
	protected $was_amp_endpoint;

	/**
	 * Test amp_is_request.
	 *
	 * @covers ::amp_is_request()
	 * @expectedDeprecated amp_render_post
	 * @expectedDeprecated amp_add_post_template_actions
	 */
	public function test__amp_is_request() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create(
			[
				'post_author' => $user_id,
			]
		);

		$this->go_to( get_permalink( $post_id ) );

		$before_amp_is_request = amp_is_request();

		add_action( 'pre_amp_render_post', [ $this, 'check_amp_is_request' ] );
		$this->was_amp_endpoint = false;

		$output = get_echo( 'amp_render_post', [ $post_id ] );
		$this->assertStringContainsString( '<html amp', $output );

		$after_amp_is_request = amp_is_request();

		$this->assertFalse( $before_amp_is_request, 'amp_is_request was not defaulting to false before amp_render_post' );
		$this->assertTrue( $this->was_amp_endpoint, 'amp_is_request was not forced to true during amp_render_post' );
		$this->assertFalse( $after_amp_is_request, 'amp_is_request was not reset after amp_render_post' );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( amp_is_request() );

		// Make is_admin() true, as requests for an admin page aren't for AMP endpoints.
		set_current_screen( 'edit.php' );
		$this->assertFalse( amp_is_request() );
		unset( $GLOBALS['current_screen'] );

		$GLOBALS['wp_query']->is_feed = true;
		$this->assertFalse( amp_is_request() );
		$GLOBALS['wp_query']->is_feed = false;
	}

	/**
	 * Store whether it currently amp_is_request().
	 */
	public function check_amp_is_request() {
		$this->was_amp_endpoint = amp_is_request();
	}
}
