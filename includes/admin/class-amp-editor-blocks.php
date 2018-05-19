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
			add_action( 'admin_enqueue_scripts', array( $this, 'add_editor_filters' ) );
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_layout_in_wp_kses_allowed_html' ), 10 );
		}
	}

	/**
	 * Whitelist used data-amp-* attributes.
	 *
	 * @param array $context Array of contexts.
	 * @return mixed Modified array.
	 */
	public function whitelist_layout_in_wp_kses_allowed_html( $context ) {
		foreach ( $context as $tag ) {
			$tag['data-amp-layout']    = true;
			$tag['data-amp-noloading'] = true;
		}
		return $context;
	}

	/**
	 * Enqueue filters for extending core blocks attributes.
	 * Has to be loaded before registering the blocks in registerCoreBlocks.
	 */
	public function add_editor_filters() {
		wp_enqueue_script(
			'amp-editor-blocks',
			amp_get_asset_url( 'js/amp-editor-blocks.js' ),
			array( 'amp-runtime', 'underscore', 'wp-hooks', 'wp-i18n' ),
			AMP__VERSION,
			true
		);

		$dynamic_blocks      = array();
		$block_type_registry = WP_Block_Type_Registry::get_instance();
		$block_types         = $block_type_registry->get_all_registered();

		foreach ( $block_types as $block_type ) {
			if ( $block_type->is_dynamic() ) {
				$dynamic_blocks[] = $block_type->name;
			}
		}

		wp_add_inline_script( 'amp-editor-blocks', sprintf( 'ampEditorBlocks.boot( %s );',
			wp_json_encode( array(
				'dynamicBlocks' => $dynamic_blocks,
			) )
		) );
	}
}
