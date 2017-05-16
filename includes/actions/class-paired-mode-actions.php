<?php

class PairedModeActions {

	public static function add_actions() {

		if ( is_feed() && ( !get_theme_support('amp') || !is_singular())) {
			return;
		}

		$is_amp_endpoint = is_amp_endpoint();

		if (is_singular()) {
			// Cannot use `get_queried_object` before canonical redirect; see https://core.trac.wordpress.org/ticket/35344
			global $wp_query;
			$post = $wp_query->post;
			$supports = post_supports_amp($post);
			if ( ! $supports ) {
				if ( $is_amp_endpoint ) {
					wp_safe_redirect( get_permalink( $post->ID ) );
					exit;
				}
				return;
			}
		}

		if ( $is_amp_endpoint ) {
			AMPRender::prepare_render();
		} else {
			amp_add_frontend_actions();
		}
	}

	public static function amp_add_post_template_actions() {
		require_once(AMP__DIR__ . '/includes/actions/amp-post-template-actions.php');
		require_once(AMP__DIR__ . '/includes/templates/amp-post-template-functions.php');
	}
}