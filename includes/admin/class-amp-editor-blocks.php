<?php
/**
 * AMP Editor Blocks extending.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Editor_Blocks
 */
class AMP_Editor_Blocks {

	/**
	 * Init.
	 */
	public function init() {
		if ( function_exists( 'gutenberg_init' ) ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		}
	}

	/**
	 * Enqueue editor assets.
	 */
	public function enqueue_block_editor_assets() {

		// Scripts.
		wp_enqueue_script(
			'amp-editor-blocks-build',
			amp_get_asset_url( 'js/amp-blocks-compiled.js' ),
			array( 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
			AMP__VERSION
		);
	}
}
