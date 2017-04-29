<?php
class AMPRender {

	public static function prepare_render() {
		error_log("AMPRender::prepare_render()");
		add_action( 'template_redirect', 'AMPRender::render' );
	}

	public static function render() {
		error_log("AMPRender::render()");
		$post_id = get_queried_object_id();
		AMPRender::render_post( $post_id );
		exit;
	}

	public static function render_post($post_id ) {
		error_log("AMPRender::render_post()");
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		AMPUtils::amp_load_classes();

		do_action( 'pre_amp_render_post', $post_id );

		PairedModeActions::amp_add_post_template_actions();
		$template = new AMP_Post_Template( $post_id );
		$template->load();
	}
}