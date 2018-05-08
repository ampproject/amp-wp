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
			add_action( 'enqueue_block_editor_assets', array( $this, 'add_editor_filters' ) );
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_block_atts_in_wp_kses_allowed_html' ), 10 );
		}
	}

	/**
	 * Whenever a post type is registered, hook into saving the post.
	 *
	 * @param string $post_type The newly registered post type.
	 * @return string That same post type.
	 */
	public static function register_post_pre_insert_functions( $post_type ) {
		add_filter( "rest_pre_insert_{$post_type}", 'AMP_Editor_Blocks::filter_rest_pre_insert_post', 11, 1 );
		return $post_type;
	}

	/**
	 * Filter pre_insert_post to ensure className is saved to post_content, too.
	 *
	 * @param WP_Post $prepared_post Prepared post.
	 * @return mixed Prepared post.
	 */
	public static function filter_rest_pre_insert_post( $prepared_post ) {
		if ( ! class_exists( 'Gutenberg_PEG_Parser' ) ) {
			return $prepared_post;
		}
		$parser = new Gutenberg_PEG_Parser();
		$blocks = $parser->parse( _gutenberg_utf8_split( $prepared_post->post_content ) );
		foreach ( $blocks as $block ) {
			if ( isset( $block['innerHTML'] ) ) {

				// Get the class of the figure.
				preg_match( "'<figure class=\"(.*?)\"'si", $block['innerHTML'], $match );

				// Lets check for gallery block class.
				if ( empty( $match ) ) {
					preg_match( "'<ul class=\"(.*?)\"'si", $block['innerHTML'], $match );
				}

				if ( ! empty( $match ) ) {
					continue;
				}
				$class_names = explode( ' ', $match[1] );
				$class_attr  = array();
				if ( isset( $block['attrs']['className'] ) ) {
					$class_attr[] = $block['attrs']['className'];
				}

				// Take everything with amp-*.
				foreach ( $class_names as $class_name ) {
					if ( false !== strpos( $class_name, 'amp-' ) ) {
						$class_attr[] = $class_name;
					}
				}

				if ( empty( $class_attr ) ) {
					continue;
				}
				$new_attributes = wp_json_encode( array_merge(
					$block['attrs'],
					array(
						'className' => implode( ' ', $class_attr ),
					)
				), 64 /* JSON_UNESCAPED_SLASHES */ );
				$to_replace     = wp_json_encode( $block['attrs'], 64 /* JSON_UNESCAPED_SLASHES */ );

				// Replace the classname attribute with the modified one.
				$content                     = str_replace( $to_replace, $new_attributes, $prepared_post->post_content );
				$prepared_post->post_content = $content;
			}
		}
		return $prepared_post;
	}

	/**
	 * Whitelist used data-amp-* attributes.
	 *
	 * @param array $context Array of contexts.
	 * @return mixed Modified array.
	 */
	public function whitelist_block_atts_in_wp_kses_allowed_html( $context ) {
		foreach ( $context as $tag ) {
			$tag['data-amp-layout']              = true;
			$tag['data-amp-noloading']           = true;
			$tag['data-close-button-aria-label'] = true;
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
			array( 'amp-runtime', 'underscore', 'wp-hooks', 'wp-components' ),
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
