<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-wp-utils.php' );

function amp_get_permalink( $post_id ) {
	return AMP_WP_Utils::amp_get_permalink ( $post_id );
}
function post_supports_amp( $post ) {
	return AMP_WP_Utils::post_supports_amp( $post );
}

function is_amp_endpoint() {
	return AMP_WP_Utils::is_amp_endpoint();
}
function amp_get_asset_url( $file ) {
	return AMP_WP_Utils::amp_get_asset_url ( $file );
}