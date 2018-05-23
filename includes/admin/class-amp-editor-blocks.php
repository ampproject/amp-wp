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
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_layout_in_wp_kses_allowed_html' ), 10 );
		}
	}

	/**
	 * Whitelist used data-amp-* attributes.
	 *
	 * @param array $tags Array of allowed post tags.
	 * @return mixed Modified array.
	 */
	public function whitelist_layout_in_wp_kses_allowed_html( $tags ) {
		foreach ( $tags as &$tag ) {
			$tag['data-amp-layout']    = true;
			$tag['data-amp-noloading'] = true;
		}
		return $tags;
	}

	/**
	 * Enqueue filters for extending core blocks attributes.
	 * Has to be loaded before registering the blocks in registerCoreBlocks.
	 */
	public function enqueue_block_editor_assets() {

		// Scripts.
		wp_enqueue_script(
			'amp-editor-blocks-build',
			amp_get_asset_url( 'js/amp-blocks-compiled.js' ),
			array( 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
			AMP__VERSION
		);

		wp_enqueue_script(
			'amp-editor-blocks',
			amp_get_asset_url( 'js/amp-editor-blocks.js' ),
			array( 'underscore', 'wp-hooks', 'wp-i18n' ),
			AMP__VERSION,
			true
		);

		wp_add_inline_script( 'amp-editor-blocks', sprintf( 'ampEditorBlocks.boot();' ) );
	}
}
