const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InnerBlocks
} = wp.editor;

const ALLOWED_BLOCKS = [
	'core/button',
	'core/code',
	'core/embed',
	'core/image',
	'core/list',
	'core/paragraph',
	'core/preformatted',
	'core/pullquote',
	'core/quote',
	'core/table',
	'core/verse',
	'core/video'
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-cta-layer',
	{
		title: __( 'AMP Story CTA Layer' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],

		// @todo Allow only once and only as the last element.
		/*
		 * <amp-story-cta-layer>:
		 *   mandatory_ancestor: "AMP-STORY-PAGE"
		 *   descendant_tag_list: "amp-story-cta-layer-allowed-descendants"
		 *
		 * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L172-L188
		 */

		edit() {
			return (
				<InnerBlocks key='contents' allowedBlocks={ ALLOWED_BLOCKS } />
			);
		},

		save( { attributes } ) {
			return (
				<amp-story-cta-layer template={ attributes.template }>
					<InnerBlocks.Content />
				</amp-story-cta-layer>
			);
		}
	}
);
