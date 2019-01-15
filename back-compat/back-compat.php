<?php
/**
 * Functions for managing legacy templates
 *
 * @package AMP
 */

/**
 * Adds hooks to use legacy templates.
 *
 * If you want to use the template that shipped with v0.3 and earlier, you can use this to force that.
 * Note that this may not stick around forever, so use caution and `function_exists`.
 */
function amp_backcompat_use_v03_templates() {
	add_filter( 'amp_customizer_is_enabled', '__return_false' );
	add_filter( 'amp_post_template_dir', '_amp_backcompat_use_v03_templates_callback', 0 ); // Early in case there are other overrides.
}

/**
 * Callback for getting the legacy templates directory.
 *
 * @access private
 *
 * @param string $templates Template directory.
 * @return string Legacy template directory.
 */
function _amp_backcompat_use_v03_templates_callback( $templates ) {
	return AMP__DIR__ . '/back-compat/templates-v0-3';
}
