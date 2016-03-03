<?php

add_filter( 'amp_content_embed_handlers', 'wpcom_amp_add_custom_embeds', 10, 2 );

function wpcom_amp_add_custom_embeds( $embed_handler_classes, $post ) {
	require_once( dirname( __FILE__ ) . '/class-amp-polldaddy-embed.php' );
	$embed_handler_classes[ 'WPCOM_AMP_Polldaddy_Embed' ] = array();

	return $embed_handler_classes;
}
