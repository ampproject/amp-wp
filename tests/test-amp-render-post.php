<?php

class AMP_Render_Post_Test extends WP_UnitTestCase {
	public function test__invalid_post() {
		// No ob here since it bails early
		$amp_rendered = amp_render_post( PHP_INT_MAX );

		$this->assertNull( $amp_rendered, 'Response was not null' );
		$this->assertEquals( 0, did_action( 'pre_amp_render_post' ), 'pre_amp_render_post action fire when it should not have.' );
	}

	public function test__valid_post() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		$this->assertContains( '<html amp', $amp_rendered, 'Response does not include html tag with amp attribute.' );
		$this->assertEquals( 1, did_action( 'pre_amp_render_post', 'pre_amp_render_post action fire either did not fire or fired too many times.' ) );
	}
}
