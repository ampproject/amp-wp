<?php

// Add shortcode to list of tags to not be texturized.
// Needed to maintain the JSON string in the tag content properly
// formatted
add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );
function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
	$shortcodes[] = 'amp-analytics';
	return $shortcodes;
}

// Change the order of the wpautop filter to prevent
// the JSON string to be "polluted" with "extraneous" characters
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 12);

// Primary handler
function amp_analytics( $atts, $content ) {
	$ga = json_decode($content);

	// TODO (@amedina): Define here the logic to add the amp-analytics
	// component to the footer section

	return '';
}
add_shortcode('amp-analytics', 'amp_analytics');
