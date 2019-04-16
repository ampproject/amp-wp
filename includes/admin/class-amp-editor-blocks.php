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
	 * List of AMP scripts that need to be printed when AMP components are used in non-AMP document context ("dirty AMP").
	 *
	 * @var array
	 */
	public $content_required_amp_scripts = array();

	/**
	 * AMP components that have blocks.
	 *
	 * @var array
	 */
	public $amp_blocks = array(
		'amp-mathml',
		'amp-timeago',
		'amp-o2-player',
		'amp-ooyala-player',
		'amp-reach-player',
		'amp-springboard-player',
		'amp-jwplayer',
		'amp-brid-player',
		'amp-ima-video',
		'amp-fit-text',
	);

	/**
	 * Init.
	 */
	public function init() {
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_block_atts_in_wp_kses_allowed_html' ), 10, 2 );

			/*
			 * Dirty AMP is required when a site is in native mode but not all templates are being served
			 * as AMP. In particular, if a single post is using AMP-specific Gutenberg Blocks which make
			 * use of AMP components, and the singular template is served as AMP but the blog page is not,
			 * then the non-AMP blog page need to load the AMP runtime scripts so that the AMP components
			 * in the posts displayed there will be rendered properly. This is only relevant on native AMP
			 * sites because the AMP Gutenberg blocks are only made available in that mode; they are not
			 * presented in the Gutenberg inserter in transitional mode. In general, using AMP components in
			 * non-AMP documents is still not officially supported, so it's occurrence is being minimized
			 * as much as possible. For more, see <https://github.com/ampproject/amp-wp/issues/1192>.
			 */
			if ( amp_is_canonical() ) {
				add_filter( 'the_content', array( $this, 'tally_content_requiring_amp_scripts' ) );
				add_action( 'wp_print_footer_scripts', array( $this, 'print_dirty_amp_scripts' ) );
			}
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
			if ( ! is_array( $tag ) ) {
				continue;
			}
			$tag['data-amp-layout']              = true;
			$tag['data-amp-noloading']           = true;
			$tag['data-amp-lightbox']            = true;
			$tag['data-close-button-aria-label'] = true;
		}

		foreach ( $this->amp_blocks as $amp_block ) {
			if ( ! isset( $tags[ $amp_block ] ) ) {
				$tags[ $amp_block ] = array();
			}

			// @todo The global attributes included here should be matched up with what is actually used by each block.
			$tags[ $amp_block ] = array_merge(
				array_fill_keys(
					array(
						'layout',
						'width',
						'height',
						'class',
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

		// Enqueue script and style for AMP-specific blocks.
		if ( amp_is_canonical() ) {
			wp_enqueue_style(
				'amp-editor-blocks-style',
				amp_get_asset_url( 'css/amp-editor-blocks.css' ),
				array(),
				AMP__VERSION
			);

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_enqueue_script(
				'amp-editor-blocks-build',
				amp_get_asset_url( 'js/amp-blocks-compiled.js' ),
				array( 'wp-editor', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
				AMP__VERSION
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'amp-editor-blocks-build', 'amp' );
			}
		}

		wp_enqueue_script(
			'amp-editor-blocks',
			amp_get_asset_url( 'js/amp-editor-blocks.js' ),
			array( 'underscore', 'wp-hooks', 'wp-i18n', 'wp-components' ),
			AMP__VERSION,
			true
		);

		wp_add_inline_script(
			'amp-editor-blocks',
			sprintf(
				'ampEditorBlocks.boot( %s );',
				wp_json_encode(
					array(
						'hasThemeSupport' => current_theme_supports( AMP_Theme_Support::SLUG ),
					)
				)
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'amp-editor-blocks', 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			wp_add_inline_script(
				'wp-i18n',
				'wp.i18n.setLocaleData( ' . wp_json_encode( $locale_data ) . ', "amp" );',
				'after'
			);
		}
	}

	/**
	 * Tally the AMP component scripts that are needed in a dirty AMP document.
	 *
	 * @param string $content Content.
	 * @return string Content (unmodified).
	 */
	public function tally_content_requiring_amp_scripts( $content ) {
		if ( ! is_amp_endpoint() ) {
			$pattern = sprintf( '/<(%s)\b.*?>/s', join( '|', $this->amp_blocks ) );
			if ( preg_match_all( $pattern, $content, $matches ) ) {
				$this->content_required_amp_scripts = array_merge(
					$this->content_required_amp_scripts,
					$matches[1]
				);
			}
		}
		return $content;
	}

	/**
	 * Print AMP scripts required for AMP components used in a non-AMP document (dirty AMP).
	 */
	public function print_dirty_amp_scripts() {
		if ( ! is_amp_endpoint() && ! empty( $this->content_required_amp_scripts ) ) {
			wp_scripts()->do_items( $this->content_required_amp_scripts );
		}
	}
}
