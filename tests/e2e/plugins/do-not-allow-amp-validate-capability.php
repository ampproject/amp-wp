<?php
/**
 * Plugin Name: Do Not Allow AMP Validate Capability
 * Plugin URI:  https://github.com/ampproject/amp-wp
 * Description: Demo plugin for revoking AMP Validate capability that can be installed during E2E tests.
 * Author:      AMP Project Contributors
 * Author URI:  https://github.com/ampproject/amp-wp/graphs/contributors
 */

add_filter(
	'map_meta_cap',
	function ( $caps, $cap ) {
		if ( AMP_Validation_Manager::VALIDATE_CAPABILITY === $cap ) {
			$caps[] = 'do_not_allow';
		}
		return $caps;
	},
	10,
	3
);
