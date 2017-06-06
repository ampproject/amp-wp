<?php

// Add shortcode to list of tags to not be texturized.
// Needed to maintain the JSON string in the tag content properly
// formatted
add_filter( 'no_texturize_shortcodes', 'amp_shortcodes_exempt_from_wptexturize' );
function amp_shortcodes_exempt_from_wptexturize( $shortcodes ) {
	$shortcodes = array(
		'amp-analytics'
	);
	return $shortcodes;
}

// Change the order of the wpautop filter to prevent
// the JSON string to be "polluted" with "extraneous" characters
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 12);

// Primary handler
function amp_analytics( $atts, $content ) {

	// Encode shortcode content into GA config object
	$ga_config = json_decode($content);
	
	$analytics = array();
	foreach ($ga_config as $config) {
		$type = $config->{'type'};
		$analytics[$type] = array();
		$analytics[$type]['type'] = $config->{'type'};
		$analytics[$type]['attributes'] = get_object_vars($config->{'attributes'});
		$analytics[$type]['config_data'] = get_object_vars($config->{'config_data'});
	}

	function add_analytics_script($data) {
		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
		return $data;
	}

	// Define a closure to capturing the state in $analytics
	// Pass it to the add_action for the amp_post_template_footer
	// When the action is executed, the function will receive the
	// $template and will add the $analytics script and data
	$add_analytics_data = function($template) use ($analytics) {
		error_log( "Adding analytics data!" );

		foreach ( $analytics as $id => $analytics_entry ) {
			if ( ! isset( $analytics_entry['type'], $analytics_entry['attributes'], $analytics_entry['config_data'] ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'Analytics entry for %s is missing one of the following keys: `type`, `attributes`, or `config_data` (array keys: %s)', 'amp' ), esc_html( $id ), esc_html( implode( ', ', array_keys( $analytics_entry ) ) ) ), '0.3.2' );
				continue;
			}

			$script_element = AMP_HTML_Utils::build_tag( 'script', array(
				'type' => 'application/json',
			), wp_json_encode( $analytics_entry['config_data'] ) );

			$amp_analytics_attr = array_merge( array(
				'id'   => $id,
				'type' => $analytics_entry['type'],
			), $analytics_entry['attributes'] );

			echo AMP_HTML_Utils::build_tag( 'amp-analytics', $amp_analytics_attr, $script_element );
		}
	};

	if ( ! empty($analytics) ) {
		add_action( 'amp_post_template_data', 'add_analytics_script' );
		add_action( 'amp_post_template_footer', $add_analytics_data);
	}
}

