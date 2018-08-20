import memoize from 'memize';
import uuid from 'uuid/v4';

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InnerBlocks
} = wp.editor;
const { select } = wp.data;
const { getBlock } = select( 'core/editor' );

const ALLOWED_BLOCKS = [
	'amp/amp-story-grid-layer',
	'amp/amp-story-cta-layer'
];

/**
 * Returns the amp-story-page's configuration for a given number of amp-story-grid-layer and if added, amp-story-cta-layer.
 *
 * @param {number} grids Number of grids.
 * @param {bool}   hasCTA If has amp-story-cta-layer.
 *
 * @return {Object[]} Story page's layout configuration.
 */
const getStoryPageTemplate = memoize( ( grids, hasCTA ) => {
	let template = _.times( grids, () => [
		'amp/amp-story-grid-layer',
		[],
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Add content to layer.', 'amp' )
				}
			]
		]
	] );
	if ( hasCTA ) {
		template.push(
			[ 'amp/amp-story-cta-layer' ]
		);
	}
	return template;
} );

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-page',
	{
		title: __( 'AMP Story Page', 'amp' ),
		category: 'layout',
		icon: 'admin-page',

		// @todo Enforce that the amp-story-page can only be a root-level block; Using `parent: []` does not work, and it causes the inserter to be disabled entirely.
		attributes: {
			id: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-story-page',
				attribute: 'id'
			}
		},

		/*
		 * <amp-story-page>:
		 *   mandatory_parent: "AMP-STORY"
		 *   mandatory_min_num_child_tags: 1
		 *   child_tag_name_oneof: "AMP-ANALYTICS"
		 *   child_tag_name_oneof: "AMP-PIXEL"
		 *   child_tag_name_oneof: "AMP-STORY-CTA-LAYER"
		 *   child_tag_name_oneof: "AMP-STORY-GRID-LAYER"
		 *
		 * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L146-L171
		 * */

		edit( props ) {
			const { setAttributes } = props;

			// If the page ID is not set, add one.
			if ( ! props.attributes.id ) {
				setAttributes( { id: uuid() } );
			}
			const block = getBlock( props.clientId );
			let grids = block.innerBlocks.length;
			let hasCTALayer = false;
			_.each( block.innerBlocks, function( child ) {
				if ( 'amp/amp-story-cta-layer' === child.name ) {
					grids--;
					hasCTALayer = true;
				} else if ( 'amp/amp-story-grid-layer' !== child.name ) {
					grids--;
				}
			} );

			// Have at least one layout grid in the template.
			if ( 0 === grids ) {
				grids = 1;
			}

			return (
				// Get the template dynamically.
				<InnerBlocks key='contents' template={ getStoryPageTemplate( grids, hasCTALayer ) } allowedBlocks={ ALLOWED_BLOCKS } />
			);
		},

		save( { attributes } ) {
			return (
				<amp-story-page id={ attributes.id }>
					<InnerBlocks.Content />
				</amp-story-page>
			);
		}
	}
);
