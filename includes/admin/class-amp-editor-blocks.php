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
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_block_atts_in_wp_kses_allowed_html' ), 10, 2 );
		}
	}

	/**
	 * Whitelist elements and attributes used for AMP.
	 *
	 * This prevents AMP markup from being deleted in
	 *
	 * @param array  $tags    Array of allowed post tags.
	 * @param string $context Context.
	 * @return mixed Modified array.
	 */
	public function whitelist_block_atts_in_wp_kses_allowed_html( $tags, $context ) {
		if ( 'post' !== $context ) {
			return $tags;
		}

		foreach ( $tags as &$tag ) {
			$tag['data-amp-layout']    = true;
			$tag['data-amp-noloading'] = true;
		}

		$amp_blocks = array(
			'amp-mathml',
			'amp-o2-player',
			'amp-ooyala-player',
			'amp-reach-player',
			'amp-springboard-player',
			'amp-jwplayer',
			'amp-brid-player',
			'amp-ima-video',
		);

		foreach ( $amp_blocks as $amp_block ) {
			if ( ! isset( $tags[ $amp_block ] ) ) {
				$tags[ $amp_block ] = array();
			}

			$tags[ $amp_block ] = array_merge(
				array_fill_keys(
					array(
						'layout',
						'width',
						'height',
					),
					true
				),
				$tags[ $amp_block ]
			);

			$amp_tag_specs = AMP_Allowed_Tags_Generated::get_allowed_tag( $amp_block );
			foreach ( $amp_tag_specs as $amp_tag_spec ) {
				if ( ! isset( $amp_tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
					continue;
				}
				$tags[ $amp_block ] = array_merge(
					$tags[ $amp_block ],
					array_fill_keys( array_keys( $amp_tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ), true )
				);
			}
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
