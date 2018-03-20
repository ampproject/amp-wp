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
	public static function init() {
		if ( function_exists( 'gutenberg_init' ) ) {
			$self = new self();
			add_action( 'admin_enqueue_scripts', array( $self, 'add_editor_filters' ) );
		}
	}

	/**
	 * Enqueue filters for extending core blocks attributes.
	 * Has to be loaded before registering the blocks in registerCoreBlocks.
	 */
	public function add_editor_filters() {
		wp_enqueue_script(
			'amp-editor-blocks',
			amp_get_asset_url( 'js/amp-editor-blocks.js' ),
			array( 'amp-runtime', 'underscore', 'wp-hooks' ),
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
